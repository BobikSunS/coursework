<?php
require 'db.php';

try {
    // Add all missing columns to the orders table
    
    // Add full_name column
    $sql = "SHOW COLUMNS FROM orders LIKE 'full_name'";
    $result = $db->query($sql);
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE orders ADD COLUMN full_name VARCHAR(255) AFTER track_number");
        echo "Column 'full_name' added to orders table.\n";
    } else {
        echo "Column 'full_name' already exists in orders table.\n";
    }
    
    // Add home_address column
    $sql = "SHOW COLUMNS FROM orders LIKE 'home_address'";
    $result = $db->query($sql);
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE orders ADD COLUMN home_address TEXT AFTER full_name");
        echo "Column 'home_address' added to orders table.\n";
    } else {
        echo "Column 'home_address' already exists in orders table.\n";
    }
    
    // Add pickup_city column
    $sql = "SHOW COLUMNS FROM orders LIKE 'pickup_city'";
    $result = $db->query($sql);
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE orders ADD COLUMN pickup_city VARCHAR(100) AFTER home_address");
        echo "Column 'pickup_city' added to orders table.\n";
    } else {
        echo "Column 'pickup_city' already exists in orders table.\n";
    }
    
    // Add pickup_address column
    $sql = "SHOW COLUMNS FROM orders LIKE 'pickup_address'";
    $result = $db->query($sql);
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE orders ADD COLUMN pickup_address TEXT AFTER pickup_city");
        echo "Column 'pickup_address' added to orders table.\n";
    } else {
        echo "Column 'pickup_address' already exists in orders table.\n";
    }
    
    // Add delivery_city column
    $sql = "SHOW COLUMNS FROM orders LIKE 'delivery_city'";
    $result = $db->query($sql);
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE orders ADD COLUMN delivery_city VARCHAR(100) AFTER pickup_address");
        echo "Column 'delivery_city' added to orders table.\n";
    } else {
        echo "Column 'delivery_city' already exists in orders table.\n";
    }
    
    // Add delivery_address column
    $sql = "SHOW COLUMNS FROM orders LIKE 'delivery_address'";
    $result = $db->query($sql);
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE orders ADD COLUMN delivery_address TEXT AFTER delivery_city");
        echo "Column 'delivery_address' added to orders table.\n";
    } else {
        echo "Column 'delivery_address' already exists in orders table.\n";
    }
    
    // Add desired_date column
    $sql = "SHOW COLUMNS FROM orders LIKE 'desired_date'";
    $result = $db->query($sql);
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE orders ADD COLUMN desired_date DATE AFTER delivery_address");
        echo "Column 'desired_date' added to orders table.\n";
    } else {
        echo "Column 'desired_date' already exists in orders table.\n";
    }
    
    // Add insurance column
    $sql = "SHOW COLUMNS FROM orders LIKE 'insurance'";
    $result = $db->query($sql);
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE orders ADD COLUMN insurance TINYINT(1) DEFAULT 0 AFTER desired_date");
        echo "Column 'insurance' added to orders table.\n";
    } else {
        echo "Column 'insurance' already exists in orders table.\n";
    }
    
    // Add packaging column
    $sql = "SHOW COLUMNS FROM orders LIKE 'packaging'";
    $result = $db->query($sql);
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE orders ADD COLUMN packaging TINYINT(1) DEFAULT 0 AFTER insurance");
        echo "Column 'packaging' added to orders table.\n";
    } else {
        echo "Column 'packaging' already exists in orders table.\n";
    }
    
    // Add fragile column
    $sql = "SHOW COLUMNS FROM orders LIKE 'fragile'";
    $result = $db->query($sql);
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE orders ADD COLUMN fragile TINYINT(1) DEFAULT 0 AFTER packaging");
        echo "Column 'fragile' added to orders table.\n";
    } else {
        echo "Column 'fragile' already exists in orders table.\n";
    }
    
    // Add payment_method column
    $sql = "SHOW COLUMNS FROM orders LIKE 'payment_method'";
    $result = $db->query($sql);
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'cash' AFTER fragile");
        echo "Column 'payment_method' added to orders table.\n";
    } else {
        echo "Column 'payment_method' already exists in orders table.\n";
    }
    
    // Add payment_status column
    $sql = "SHOW COLUMNS FROM orders LIKE 'payment_status'";
    $result = $db->query($sql);
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) DEFAULT 'pending' AFTER payment_method");
        echo "Column 'payment_status' added to orders table.\n";
    } else {
        echo "Column 'payment_status' already exists in orders table.\n";
    }
    
    // Add tracking_status column
    $sql = "SHOW COLUMNS FROM orders LIKE 'tracking_status'";
    $result = $db->query($sql);
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE orders ADD COLUMN tracking_status VARCHAR(50) DEFAULT 'created' AFTER payment_status");
        echo "Column 'tracking_status' added to orders table.\n";
    } else {
        echo "Column 'tracking_status' already exists in orders table.\n";
    }
    
    // Add comment column
    $sql = "SHOW COLUMNS FROM orders LIKE 'comment'";
    $result = $db->query($sql);
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE orders ADD COLUMN comment TEXT AFTER tracking_status");
        echo "Column 'comment' added to orders table.\n";
    } else {
        echo "Column 'comment' already exists in orders table.\n";
    }
    
    // Make sure delivery_hours column exists (from original schema)
    $sql = "SHOW COLUMNS FROM orders LIKE 'delivery_hours'";
    $result = $db->query($sql);
    if ($result->rowCount() == 0) {
        $db->exec("ALTER TABLE orders ADD COLUMN delivery_hours DECIMAL(8,2) DEFAULT NULL AFTER cost");
        echo "Column 'delivery_hours' added to orders table.\n";
    } else {
        echo "Column 'delivery_hours' already exists in orders table.\n";
    }

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
    echo "Table 'tracking_status_history' created or already exists.\n";
    
    echo "Database structure has been successfully updated!\n";

} catch (PDOException $e) {
    echo "Error updating database structure: " . $e->getMessage() . "\n";
}
?>