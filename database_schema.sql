-- Simplified Database Schema for Book Dress Issue System
-- Ready to execute version

-- Drop database if it exists (be careful with this in production)
DROP DATABASE IF EXISTS bookdress_db;

-- Create database
CREATE DATABASE bookdress_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE bookdress_db;

-- 1. Admin table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL
);

-- 2. Schools table
CREATE TABLE schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id VARCHAR(20) UNIQUE NOT NULL,
    school_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    notification_settings TEXT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME NOT NULL
);

-- 3. Parents table
CREATE TABLE parents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id VARCHAR(20) UNIQUE NOT NULL,
    parent_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL
);

-- 4. Students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(255) NOT NULL,
    school_id INT NOT NULL,
    parent_id INT NOT NULL,
    class VARCHAR(20) NOT NULL,
    section VARCHAR(10) NULL,
    photo VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE
);

-- 5. Inventory table
CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    category ENUM('Book', 'Uniform', 'Stationery', 'Accessories') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    class VARCHAR(20),
    type VARCHAR(50) NULL,
    sku VARCHAR(50) NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
);

-- 6. Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    student_id INT NOT NULL,
    school_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Processing', 'Ready', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
);

-- 7. Order Items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    inventory_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (inventory_id) REFERENCES inventory(id) ON DELETE CASCADE
);

-- 8. System logs table for tracking logins and important system events
CREATE TABLE system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type VARCHAR(20) NOT NULL,
    action VARCHAR(50) NOT NULL,
    details TEXT NULL,
    timestamp DATETIME NOT NULL
);

-- 9. School Registration Codes table (needed for school registration)
CREATE TABLE school_registration_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    created_by INT NOT NULL,
    created_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    used_at DATETIME NULL,
    used_by_school INT NULL
);

-- Create indexes for performance
CREATE INDEX idx_students_school ON students(school_id);
CREATE INDEX idx_students_parent ON students(parent_id);
CREATE INDEX idx_inventory_school ON inventory(school_id);
CREATE INDEX idx_inventory_category ON inventory(category);
CREATE INDEX idx_orders_school ON orders(school_id);
CREATE INDEX idx_orders_student ON orders(student_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_system_logs_user ON system_logs(user_id, user_type);

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO admins (username, email, password, created_at) 
VALUES ('admin', 'admin@bookdress.com', 'admin123', NOW());

-- Add delivery_address column to existing orders table (for migrations)
ALTER TABLE orders ADD COLUMN delivery_address TEXT AFTER notes;

ALTER TABLE inventory ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;