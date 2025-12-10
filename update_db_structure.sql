-- Add missing columns to the orders table
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS payment_status VARCHAR(20) DEFAULT 'pending',
ADD COLUMN IF NOT EXISTS tracking_status VARCHAR(50) DEFAULT 'created',
ADD COLUMN IF NOT EXISTS full_name VARCHAR(255),
ADD COLUMN IF NOT EXISTS home_address TEXT,
ADD COLUMN IF NOT EXISTS pickup_city VARCHAR(100),
ADD COLUMN IF NOT EXISTS pickup_address TEXT,
ADD COLUMN IF NOT EXISTS delivery_city VARCHAR(100),
ADD COLUMN IF NOT EXISTS delivery_address TEXT,
ADD COLUMN IF NOT EXISTS desired_date DATE,
ADD COLUMN IF NOT EXISTS insurance TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS packaging TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS fragile TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT 'cash',
ADD COLUMN IF NOT EXISTS comment TEXT;

-- Create tracking_status_history table if it doesn't exist
CREATE TABLE IF NOT EXISTS tracking_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Update existing orders to have tracking_status
UPDATE orders SET tracking_status = 'created' WHERE tracking_status IS NULL;