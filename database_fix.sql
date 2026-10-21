-- Fix orders and payments table for checkout functionality
USE unibizhub;

-- Store uploaded shop images
ALTER TABLE shops ADD COLUMN IF NOT EXISTS shop_image VARCHAR(255) AFTER shop_name;

-- Add payment_method column to orders table if it doesn't exist
ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) AFTER order_status;

-- Update payments table to include Bank Transfer
ALTER TABLE payments MODIFY COLUMN payment_method 
    ENUM('Credit Card', 'Debit Card', 'Cash on Delivery', 'Mobile Banking', 'Bank Transfer') NOT NULL;

-- Add phone column to orders for customer contact
ALTER TABLE orders ADD COLUMN IF NOT EXISTS phone VARCHAR(20) AFTER shipping_address;
