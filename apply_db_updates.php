<?php
require 'db.php';

try {
    // Добавляем недостающие колонки в таблицу orders
    $db->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_status VARCHAR(20) DEFAULT 'pending'");
    $db->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS tracking_status VARCHAR(50) DEFAULT 'created'");
    $db->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS full_name VARCHAR(255)");
    $db->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS home_address TEXT");
    $db->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS pickup_city VARCHAR(100)");
    $db->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS pickup_address TEXT");
    $db->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS delivery_city VARCHAR(100)");
    $db->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS delivery_address TEXT");
    $db->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS desired_date DATE");
    $db->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS insurance TINYINT(1) DEFAULT 0");
    $db->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS packaging TINYINT(1) DEFAULT 0");
    $db->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS fragile TINYINT(1) DEFAULT 0");
    $db->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT 'cash'");
    $db->exec("ALTER TABLE orders ADD COLUMN IF NOT EXISTS comment TEXT");

    // Создаем таблицу истории статусов, если не существует
    $db->exec("CREATE TABLE IF NOT EXISTS tracking_status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        status VARCHAR(50) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )");

    echo "Структура базы данных успешно обновлена!";
    
} catch (PDOException $e) {
    echo "Ошибка обновления структуры базы данных: " . $e->getMessage();
}
?>