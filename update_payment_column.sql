ALTER TABLE `orders` 
ADD COLUMN `payment_status` VARCHAR(20) DEFAULT 'pending',
ADD COLUMN `tracking_status` VARCHAR(50) DEFAULT 'created';

-- Create tracking_status_history table if it doesn't exist
CREATE TABLE IF NOT EXISTS `tracking_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `description` varchar(255),
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;