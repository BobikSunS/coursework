<?php
require 'db.php';

try {
    // Create tracking_status_history table if it doesn't exist
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS tracking_status_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        status VARCHAR(50) NOT NULL,
        description VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    )";
    
    $db->exec($createTableSQL);
    echo "Таблица tracking_status_history создана или уже существует.\n";
    
    // Add tracking_status column to orders table if it doesn't exist
    $addColumnSQL = "
    ALTER TABLE orders 
    ADD COLUMN IF NOT EXISTS tracking_status VARCHAR(50) DEFAULT 'created' 
    AFTER comment";
    
    $db->exec($addColumnSQL);
    echo "Столбец tracking_status добавлен в таблицу orders или уже существует.\n";
    
    // Update any existing orders that might not have the tracking_status column set
    $updateExistingOrders = "
    UPDATE orders 
    SET tracking_status = 'processed' 
    WHERE tracking_status IS NULL OR tracking_status = ''";
    
    $db->exec($updateExistingOrders);
    echo "Существующие заказы обновлены со статусом 'processed'.\n";
    
    echo "База данных успешно обновлена для поддержки отслеживания статуса заказов!\n";
    
} catch (PDOException $e) {
    echo "Ошибка при обновлении базы данных: " . $e->getMessage() . "\n";
}
?>