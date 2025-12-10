<?php
require 'db.php';

// Check if tracking_status column exists in orders table
try {
    $stmt = $db->query("DESCRIBE orders");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current columns in orders table:\n";
    foreach($columns as $column) {
        echo "- $column\n";
    }
    
    if (!in_array('tracking_status', $columns)) {
        echo "\nAdding tracking_status column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN tracking_status VARCHAR(50) DEFAULT 'created'");
        echo "Column tracking_status added successfully!\n";
    } else {
        echo "\ntracking_status column already exists.\n";
    }
    
    if (!in_array('full_name', $columns)) {
        echo "\nAdding full_name column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN full_name VARCHAR(255)");
        echo "Column full_name added successfully!\n";
    } else {
        echo "\nfull_name column already exists.\n";
    }
    
    if (!in_array('home_address', $columns)) {
        echo "\nAdding home_address column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN home_address TEXT");
        echo "Column home_address added successfully!\n";
    } else {
        echo "\nhome_address column already exists.\n";
    }
    
    if (!in_array('pickup_city', $columns)) {
        echo "\nAdding pickup_city column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN pickup_city VARCHAR(100)");
        echo "Column pickup_city added successfully!\n";
    } else {
        echo "\npickup_city column already exists.\n";
    }
    
    if (!in_array('pickup_address', $columns)) {
        echo "\nAdding pickup_address column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN pickup_address TEXT");
        echo "Column pickup_address added successfully!\n";
    } else {
        echo "\npickup_address column already exists.\n";
    }
    
    if (!in_array('delivery_city', $columns)) {
        echo "\nAdding delivery_city column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN delivery_city VARCHAR(100)");
        echo "Column delivery_city added successfully!\n";
    } else {
        echo "\ndelivery_city column already exists.\n";
    }
    
    if (!in_array('delivery_address', $columns)) {
        echo "\nAdding delivery_address column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN delivery_address TEXT");
        echo "Column delivery_address added successfully!\n";
    } else {
        echo "\ndelivery_address column already exists.\n";
    }
    
    if (!in_array('desired_date', $columns)) {
        echo "\nAdding desired_date column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN desired_date DATE");
        echo "Column desired_date added successfully!\n";
    } else {
        echo "\ndesired_date column already exists.\n";
    }
    
    if (!in_array('insurance', $columns)) {
        echo "\nAdding insurance column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN insurance TINYINT(1) DEFAULT 0");
        echo "Column insurance added successfully!\n";
    } else {
        echo "\ninsurance column already exists.\n";
    }
    
    if (!in_array('packaging', $columns)) {
        echo "\nAdding packaging column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN packaging TINYINT(1) DEFAULT 0");
        echo "Column packaging added successfully!\n";
    } else {
        echo "\npackaging column already exists.\n";
    }
    
    if (!in_array('fragile', $columns)) {
        echo "\nAdding fragile column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN fragile TINYINT(1) DEFAULT 0");
        echo "Column fragile added successfully!\n";
    } else {
        echo "\nfragile column already exists.\n";
    }
    
    if (!in_array('payment_method', $columns)) {
        echo "\nAdding payment_method column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'cash'");
        echo "Column payment_method added successfully!\n";
    } else {
        echo "\npayment_method column already exists.\n";
    }
    
    if (!in_array('payment_status', $columns)) {
        echo "\nAdding payment_status column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) DEFAULT 'pending'");
        echo "Column payment_status added successfully!\n";
    } else {
        echo "\npayment_status column already exists.\n";
    }
    
    if (!in_array('tracking_status', $columns)) {
        echo "\nAdding tracking_status column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN tracking_status VARCHAR(50) DEFAULT 'created'");
        echo "Column tracking_status added successfully!\n";
    } else {
        echo "\ntracking_status column already exists.\n";
    }
    
    if (!in_array('comment', $columns)) {
        echo "\nAdding comment column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN comment TEXT");
        echo "Column comment added successfully!\n";
    } else {
        echo "\ncomment column already exists.\n";
    }
    
    if (!in_array('from_office', $columns)) {
        echo "\nAdding from_office column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN from_office INT");
        echo "Column from_office added successfully!\n";
    } else {
        echo "\nfrom_office column already exists.\n";
    }
    
    if (!in_array('to_office', $columns)) {
        echo "\nAdding to_office column to orders table...\n";
        $db->exec("ALTER TABLE orders ADD COLUMN to_office INT");
        echo "Column to_office added successfully!\n";
    } else {
        echo "\nto_office column already exists.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>