<?php
session_start();

try {
    $db = new PDO("mysql:host=localhost;dbname=delivery_by;charset=utf8mb4", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Подключение к MySQL не удалось: " . $e->getMessage());
}
?>