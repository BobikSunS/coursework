<?php
require_once 'db.php';

echo "Setting up database structure for delivery system...\n";

try {
    // Check if full_name column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'full_name'");
    $fullNameExists = $stmt->fetch();
    
    if (!$fullNameExists) {
        echo "Adding full_name column...\n";
        $pdo->exec("ALTER TABLE `orders` ADD COLUMN `full_name` VARCHAR(255) NULL AFTER `user_id`");
    } else {
        echo "full_name column already exists.\n";
    }
    
    // Check if tracking_status column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'tracking_status'");
    $trackingStatusExists = $stmt->fetch();
    
    if (!$trackingStatusExists) {
        echo "Adding tracking_status column...\n";
        $pdo->exec("ALTER TABLE `orders` ADD COLUMN `tracking_status` VARCHAR(50) DEFAULT 'Создан' AFTER `track_number`");
    } else {
        echo "tracking_status column already exists.\n";
    }
    
    // Check if status_updated_at column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'status_updated_at'");
    $statusUpdatedExists = $stmt->fetch();
    
    if (!$statusUpdatedExists) {
        echo "Adding status_updated_at column...\n";
        $pdo->exec("ALTER TABLE `orders` ADD COLUMN `status_updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `tracking_status`");
    } else {
        echo "status_updated_at column already exists.\n";
    }
    
    // Check if client_info column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'client_info'");
    $clientInfoExists = $stmt->fetch();
    
    if (!$clientInfoExists) {
        echo "Adding client_info column...\n";
        $pdo->exec("ALTER TABLE `orders` ADD COLUMN `client_info` TEXT NULL AFTER `status_updated_at`");
    } else {
        echo "client_info column already exists.\n";
    }
    
    // Check if tracking_status_history table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'tracking_status_history'");
    $historyTableExists = $stmt->fetch();
    
    if (!$historyTableExists) {
        echo "Creating tracking_status_history table...\n";
        $pdo->exec("CREATE TABLE IF NOT EXISTS `tracking_status_history` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `order_id` int(11) NOT NULL,
          `status` varchar(50) NOT NULL,
          `changed_at` timestamp DEFAULT CURRENT_TIMESTAMP,
          `changed_by` varchar(100) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `order_id` (`order_id`),
          CONSTRAINT `tracking_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    } else {
        echo "tracking_status_history table already exists.\n";
    }
    
    // Update existing orders to have client info from users table
    echo "Updating existing orders with client information...\n";
    $pdo->exec("UPDATE `orders` o 
                JOIN `users` u ON o.user_id = u.id 
                SET o.full_name = u.name, 
                    o.client_info = CONCAT('Email: ', COALESCE(u.email, 'N/A'), ', Phone: ', COALESCE(u.phone, 'N/A'))
                WHERE o.full_name IS NULL OR o.client_info IS NULL");
    
    echo "Database structure updated successfully!\n";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
    echo "Please make sure your database is running and accessible.\n";
}

echo "Setup complete. You can now create orders and update statuses without errors.\n";
?>