using System;
using System.Collections.Generic;
using System.IO;
using System.Net;
using System.Text;
using System.Threading;
using System.Threading.Tasks;
using System.Text.Json;
using System.Text.Json.Serialization;

namespace DeliveryLoggingServer
{
    public class LogEntry
    {
        [JsonPropertyName("timestamp")]
        public DateTime Timestamp { get; set; }
        
        [JsonPropertyName("level")]
        public string Level { get; set; } = string.Empty;
        
        [JsonPropertyName("message")]
        public string Message { get; set; } = string.Empty;
        
        [JsonPropertyName("details")]
        public Dictionary<string, object>? Details { get; set; }
        
        [JsonPropertyName("user")]
        public string User { get; set; } = string.Empty;
        
        [JsonPropertyName("ip")]
        public string IP { get; set; } = string.Empty;
    }

    public class LoggingServer
    {
        private HttpListener? listener;
        private string logDirectory;
        private List<LogEntry> logBuffer;
        private readonly object bufferLock = new object();
        private readonly object fileLock = new object();
        private Timer? flushTimer;
        private const int BUFFER_SIZE = 100;
        private const int FLUSH_INTERVAL_MS = 5000; // 5 seconds

        public LoggingServer(string logDirectory = "logs")
        {
            this.logDirectory = logDirectory;
            this.logBuffer = new List<LogEntry>();
            
            if (!Directory.Exists(this.logDirectory))
            {
                Directory.CreateDirectory(this.logDirectory);
            }
        }

        public void Start(int port = 8080)
        {
            listener = new HttpListener();
            listener.Prefixes.Add($"http://localhost:{port}/");
            listener.Prefixes.Add($"http://127.0.0.1:{port}/");
            
            listener.Start();
            Console.WriteLine($"Logging Server started on port {port}");
            Console.WriteLine("Available endpoints:");
            Console.WriteLine("  POST /log - Log a message");
            Console.WriteLine("  GET /logs - Get recent logs");
            Console.WriteLine("  GET /stats - Get statistics");
            Console.WriteLine("Press Ctrl+C to stop the server...\n");

            // Start timer to periodically flush logs to disk
            flushTimer = new Timer(FlushLogs, null, FLUSH_INTERVAL_MS, FLUSH_INTERVAL_MS);

            // Start handling requests
            Task.Run(() => HandleRequests());
        }

        private async Task HandleRequests()
        {
            while (listener.IsListening)
            {
                try
                {
                    HttpListenerContext context = await listener.GetContextAsync();
                    await ProcessRequest(context);
                }
                catch (HttpListenerException)
                {
                    // Listener was stopped
                    break;
                }
                catch (Exception ex)
                {
                    Console.WriteLine($"Error handling request: {ex.Message}");
                }
            }
        }

        private async Task ProcessRequest(HttpListenerContext context)
        {
            var request = context.Request;
            var response = context.Response;

            try
            {
                string responseString = "";
                int statusCode = 200;

                if (request.HttpMethod == "POST" && request.Url.AbsolutePath == "/log")
                {
                    responseString = await HandleLogRequest(request);
                }
                else if (request.HttpMethod == "GET" && request.Url.AbsolutePath == "/logs")
                {
                    responseString = await HandleGetLogsRequest(request);
                }
                else if (request.HttpMethod == "GET" && request.Url.AbsolutePath == "/stats")
                {
                    responseString = await HandleStatsRequest();
                }
                else if (request.HttpMethod == "GET" && request.Url.AbsolutePath == "/")
                {
                    responseString = GetServerStatus();
                }
                else
                {
                    responseString = "{\"error\":\"Endpoint not found\"}";
                    statusCode = 404;
                }

                // Send response
                byte[] buffer = Encoding.UTF8.GetBytes(responseString);
                response.ContentLength64 = buffer.Length;
                response.StatusCode = statusCode;
                response.ContentType = "application/json; charset=utf-8";
                
                using (var output = response.OutputStream)
                {
                    await output.WriteAsync(buffer, 0, buffer.Length);
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Error processing request: {ex.Message}");
                response.StatusCode = 500;
                response.Close();
            }
        }

        private async Task<string> HandleLogRequest(HttpListenerRequest request)
        {
            try
            {
                using (var reader = new StreamReader(request.InputStream, request.ContentEncoding))
                {
                    string json = await reader.ReadToEndAsync();
                    var logEntry = JsonSerializer.Deserialize<LogEntry>(json, new JsonSerializerOptions
                    {
                        PropertyNameCaseInsensitive = true
                    });

                    logEntry.Timestamp = DateTime.Now;
                    logEntry.IP = request.RemoteEndPoint?.ToString() ?? "unknown";

                    // Add to buffer
                    lock (bufferLock)
                    {
                        logBuffer.Add(logEntry);
                    }

                    // Log to console
                    Console.WriteLine($"[{logEntry.Timestamp:HH:mm:ss}] {logEntry.Level}: {logEntry.Message}");
                    if (logEntry.Details != null && logEntry.Details.Count > 0)
                    {
                        foreach (var detail in logEntry.Details)
                        {
                            Console.WriteLine($"  {detail.Key}: {detail.Value}");
                        }
                    }

                    return "{\"success\":true}";
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Error processing log request: {ex.Message}");
                return "{\"success\":false,\"error\":\"" + ex.Message + "\"}";
            }
        }

        private async Task<string> HandleGetLogsRequest(HttpListenerRequest request)
        {
            try
            {
                // Parse query parameters
                int count = 50; // default
                string level = null;
                
                if (request.QueryString["count"] != null)
                {
                    if (int.TryParse(request.QueryString["count"], out int queryCount))
                    {
                        count = Math.Min(queryCount, 1000); // max 1000 logs
                    }
                }
                
                if (request.QueryString["level"] != null)
                {
                    level = request.QueryString["level"];
                }

                // Get logs from buffer and recent file
                var logs = new List<LogEntry>();
                
                // Add buffered logs
                lock (bufferLock)
                {
                    logs.AddRange(logBuffer);
                }

                // Read from recent log file
                var recentLogFile = GetRecentLogFile();
                if (File.Exists(recentLogFile))
                {
                    var fileLogs = ReadLogsFromFile(recentLogFile, count);
                    logs.AddRange(fileLogs);
                }

                // Filter by level if specified
                if (!string.IsNullOrEmpty(level))
                {
                    logs = logs.Where(l => l.Level.Equals(level, StringComparison.OrdinalIgnoreCase)).ToList();
                }

                // Sort by timestamp (newest first) and take requested count
                logs = logs.OrderByDescending(l => l.Timestamp).Take(count).ToList();

                var options = new JsonSerializerOptions
                {
                    PropertyNamingPolicy = JsonNamingPolicy.CamelCase,
                    WriteIndented = true
                };

                return JsonSerializer.Serialize(logs, options);
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Error getting logs: {ex.Message}");
                return "{\"error\":\"" + ex.Message + "\"}";
            }
        }

        private async Task<string> HandleStatsRequest()
        {
            try
            {
                var recentLogFile = GetRecentLogFile();
                long totalLogs = 0;
                var levelCounts = new Dictionary<string, int>();
                
                if (File.Exists(recentLogFile))
                {
                    totalLogs = File.ReadLines(recentLogFile).Count();
                    
                    // Count by level
                    foreach (var line in File.ReadLines(recentLogFile))
                    {
                        try
                        {
                            var logEntry = JsonSerializer.Deserialize<LogEntry>(line, new JsonSerializerOptions
                            {
                                PropertyNameCaseInsensitive = true
                            });
                            
                            if (logEntry?.Level != null)
                            {
                                if (levelCounts.ContainsKey(logEntry.Level))
                                {
                                    levelCounts[logEntry.Level]++;
                                }
                                else
                                {
                                    levelCounts[logEntry.Level] = 1;
                                }
                            }
                        }
                        catch
                        {
                            // Skip invalid lines
                        }
                    }
                }

                // Add buffered logs to stats
                lock (bufferLock)
                {
                    totalLogs += logBuffer.Count;
                    foreach (var log in logBuffer)
                    {
                        if (log?.Level != null)
                        {
                            if (levelCounts.ContainsKey(log.Level))
                            {
                                levelCounts[log.Level]++;
                            }
                            else
                            {
                                levelCounts[log.Level] = 1;
                            }
                        }
                    }
                }

                var stats = new
                {
                    totalLogs = totalLogs,
                    levelCounts = levelCounts,
                    bufferedLogs = logBuffer.Count,
                    logDirectory = logDirectory,
                    serverStartTime = DateTime.Now.AddHours(-1) // Placeholder
                };

                var options = new JsonSerializerOptions
                {
                    PropertyNamingPolicy = JsonNamingPolicy.CamelCase,
                    WriteIndented = true
                };

                return JsonSerializer.Serialize(stats, options);
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Error getting stats: {ex.Message}");
                return "{\"error\":\"" + ex.Message + "\"}";
            }
        }

        private string GetServerStatus()
        {
            return $"{{\"status\":\"running\",\"logDirectory\":\"{logDirectory}\",\"bufferedLogs\":{logBuffer.Count}}}";
        }

        private List<LogEntry> ReadLogsFromFile(string filePath, int maxCount)
        {
            var logs = new List<LogEntry>();
            var lines = File.ReadLines(filePath);
            
            // Take last maxCount lines
            lines = lines.Skip(Math.Max(0, lines.Count() - maxCount));
            
            foreach (var line in lines)
            {
                try
                {
                    var logEntry = JsonSerializer.Deserialize<LogEntry>(line, new JsonSerializerOptions
                    {
                        PropertyNameCaseInsensitive = true
                    });
                    if (logEntry != null)
                    {
                        logs.Add(logEntry);
                    }
                }
                catch
                {
                    // Skip invalid lines
                }
            }
            
            return logs;
        }

        private string GetRecentLogFile()
        {
            string date = DateTime.Now.ToString("yyyy-MM-dd");
            return Path.Combine(logDirectory, $"log_{date}.json");
        }

        private void FlushLogs(object? state)
        {
            List<LogEntry> logsToWrite;
            
            lock (bufferLock)
            {
                if (logBuffer.Count == 0)
                    return;
                
                logsToWrite = new List<LogEntry>(logBuffer);
                logBuffer.Clear();
            }

            if (logsToWrite.Count > 0)
            {
                lock (fileLock)
                {
                    string logFile = GetRecentLogFile();
                    using (var writer = new StreamWriter(logFile, true, Encoding.UTF8))
                    {
                        foreach (var log in logsToWrite)
                        {
                            string json = JsonSerializer.Serialize(log, new JsonSerializerOptions
                            {
                                PropertyNamingPolicy = JsonNamingPolicy.CamelCase
                            });
                            writer.WriteLine(json);
                        }
                    }
                }
            }
        }

        public void Stop()
        {
            if (flushTimer != null)
            {
                flushTimer.Dispose();
            }

            if (listener != null && listener.IsListening)
            {
                listener.Stop();
                listener.Close();
            }

            // Flush remaining logs
            FlushLogs(null);
            
            Console.WriteLine("Logging Server stopped.");
        }

        public static void Main(string[] args)
        {
            var server = new LoggingServer();
            
            // Handle Ctrl+C gracefully
            Console.CancelKeyPress += (sender, e) =>
            {
                e.Cancel = true;
                Console.WriteLine("\nShutting down server...");
                server.Stop();
                Environment.Exit(0);
            };

            int port = 8080;
            if (args.Length > 0 && int.TryParse(args[0], out int parsedPort))
            {
                port = parsedPort;
            }

            server.Start(port);
            
            // Keep the server running
            Thread.Sleep(Timeout.Infinite);
        }
    }
}