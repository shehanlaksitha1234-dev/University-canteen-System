-- CANTEEN MANAGEMENT SYSTEM - DATABASE SCHEMA
-- Database: canteen_db
-- This file contains all table structures for the canteen management system

-- ============================================
-- 1. USERS TABLE - Stores student and admin information
-- ============================================
-- Purpose: Keeps track of all registered users (students, staff, admins)
-- Fields: id (unique identifier), name, email, faculty, password (hashed), role
-- Primary Key: id (auto-increments for each new user)
-- Example: A student registers with name, email, faculty, and password
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    faculty VARCHAR(100),
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin', 'staff') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 2. MENU_ITEMS TABLE - Stores food items available in canteen
-- ============================================
-- Purpose: Contains all food items that can be ordered (biryani, pizza, etc.)
-- Fields: id, name, description, price, image_path, is_available
-- Primary Key: id
-- Example: Biryani costs Rs. 250, image stored in assets/images/biryani.jpg
CREATE TABLE menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_path VARCHAR(255),
    category ENUM('Breakfast', 'Lunch', 'Snacks', 'Beverages') DEFAULT 'Snacks',
    is_available BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 3. ORDERS TABLE - Stores order headers/information
-- ============================================
-- Purpose: Each row represents one complete order placed by a student
-- Fields: id, user_id (which student), total_amount, status, created_at
-- Primary Key: id
-- Foreign Key: user_id (links to users table)
-- Example: Order #1 by Student Ahmed for Rs. 500, Status: Pending
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('Pending', 'Preparing', 'Completed', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 4. ORDER_ITEMS TABLE - Stores individual items in each order
-- ============================================
-- Purpose: Breaks down each order into individual food items
-- Fields: id, order_id, menu_item_id, quantity, price
-- Primary Keys: id
-- Foreign Keys: order_id (links to orders), menu_item_id (links to menu_items)
-- Example: Order #1 contains 2 Biryani + 1 Coke
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 5. INVENTORY TABLE - Tracks stock of menu items
-- ============================================
-- Purpose: Monitors available quantity of each food item
-- Fields: id, menu_item_id, quantity_available, quantity_used, last_updated
-- Primary Key: id
-- Foreign Key: menu_item_id (links to menu_items)
-- Example: Biryani stock: 50 items available, 12 used today
CREATE TABLE inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    menu_item_id INT NOT NULL UNIQUE,
    quantity_available INT DEFAULT 0,
    quantity_used INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 6. PAYMENTS TABLE - Records payment information
-- ============================================
-- Purpose: Stores payment details for each order
-- Fields: id, order_id, amount, payment_method, status
-- Primary Key: id
-- Foreign Key: order_id (links to orders table)
-- Example: Order #1 payment of Rs. 500 via Cash, Status: Paid
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL UNIQUE,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('Cash', 'Card', 'Online') DEFAULT 'Cash',
    status ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- SUMMARY - How tables relate to each other:
-- ============================================
-- users → orders (one user can have many orders)
-- orders → order_items (one order can have many items)
-- menu_items → order_items (one menu item can be in many orders)
-- menu_items → inventory (one menu item has one inventory record)
-- orders → payments (one order has one payment)
-- ============================================
