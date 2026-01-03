-- =====================================================
-- SEKUWA KERNER - E-Commerce Database Schema
-- Nepali Food Ordering Platform
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS sekuwa_kerner;
USE sekuwa_kerner;

-- =====================================================
-- USERS TABLE
-- Stores customer and admin accounts
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- CATEGORIES TABLE
-- Product categories (Chicken Sekuwa, Buff Sekuwa, etc.)
-- =====================================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- PRODUCTS TABLE
-- All food items available for ordering
-- =====================================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    category_id INT NOT NULL,
    image VARCHAR(255) DEFAULT 'default.jpg',
    is_featured TINYINT(1) DEFAULT 0,
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- ORDERS TABLE
-- Customer orders with delivery details
-- =====================================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    status ENUM('Pending', 'Cooking', 'Out for Delivery', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- ORDER ITEMS TABLE
-- Individual items in each order
-- =====================================================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- PAYMENTS TABLE
-- Payment information for each order
-- =====================================================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method ENUM('Cash on Delivery', 'eSewa', 'Khalti') NOT NULL,
    payment_status ENUM('Pending', 'Completed', 'Failed') DEFAULT 'Pending',
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- INSERT SAMPLE DATA
-- =====================================================

-- Insert Admin User (password: admin123)
INSERT INTO users (name, email, phone, password, role) VALUES
('Admin User', 'admin@sekuwakerner.com', '9841000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert Sample Customers (password: password123)
INSERT INTO users (name, email, phone, password, role) VALUES
('Ram Sharma', 'ram@example.com', '9841111111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Sita Thapa', 'sita@example.com', '9841222222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('Hari Gurung', 'hari@example.com', '9841333333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- Insert Categories
INSERT INTO categories (name, description) VALUES
('Chicken Sekuwa', 'Delicious grilled chicken marinated with Nepali spices'),
('Buff Sekuwa', 'Traditional buffalo meat grilled to perfection'),
('Veg Items', 'Vegetarian snacks and sides'),
('Drinks', 'Refreshing beverages to complement your meal');

-- Insert Sample Products
INSERT INTO products (name, price, description, category_id, image, is_featured, is_available) VALUES
-- Chicken Sekuwa Items
('Chicken Sekuwa Regular', 250.00, 'Tender chicken pieces marinated in traditional Nepali spices and grilled over charcoal. Served with beaten rice (chiura) and pickle.', 1, 'chicken_sekuwa.jpg', 1, 1),
('Chicken Sekuwa Large', 450.00, 'Large portion of our signature chicken sekuwa. Perfect for sharing with friends and family.', 1, 'chicken_sekuwa_large.jpg', 1, 1),
('Chicken Wings Sekuwa', 300.00, 'Crispy grilled chicken wings with special house marinade. A crowd favorite!', 1, 'chicken_wings.jpg', 0, 1),
('Chicken Tikka', 280.00, 'Boneless chicken pieces marinated in yogurt and spices, grilled in tandoor style.', 1, 'chicken_tikka.jpg', 0, 1),

-- Buff Sekuwa Items
('Buff Sekuwa Regular', 200.00, 'Classic buffalo meat sekuwa marinated with traditional spices. A Nepali street food favorite.', 2, 'buff_sekuwa.jpg', 1, 1),
('Buff Sekuwa Large', 380.00, 'Large serving of our tender buff sekuwa. Best enjoyed with cold drinks!', 2, 'buff_sekuwa_large.jpg', 1, 1),
('Buff Sukuti', 350.00, 'Dried buffalo meat, a traditional Nepali delicacy. Smoky and flavorful.', 2, 'buff_sukuti.jpg', 0, 1),
('Buff Choila', 320.00, 'Spicy grilled buffalo meat mixed with spices, onions, and tomatoes. Newari specialty.', 2, 'buff_choila.jpg', 1, 1),

-- Veg Items
('Aloo Chop', 80.00, 'Crispy potato patties with spiced filling. Perfect vegetarian snack.', 3, 'aloo_chop.jpg', 0, 1),
('Paneer Sekuwa', 280.00, 'Grilled cottage cheese marinated in Nepali spices. Vegetarian delight!', 3, 'paneer_sekuwa.jpg', 1, 1),
('Chatpate', 120.00, 'Spicy and tangy Nepali street snack with puffed rice, peanuts, and spices.', 3, 'chatpate.jpg', 0, 1),
('Chiura (Beaten Rice)', 50.00, 'Traditional Nepali beaten rice. Perfect accompaniment for sekuwa.', 3, 'chiura.jpg', 0, 1),

-- Drinks
('Coke', 60.00, 'Chilled Coca-Cola 500ml', 4, 'coke.jpg', 0, 1),
('Sprite', 60.00, 'Chilled Sprite 500ml', 4, 'sprite.jpg', 0, 1),
('Mango Lassi', 100.00, 'Sweet yogurt drink blended with fresh mangoes.', 4, 'mango_lassi.jpg', 0, 1),
('Masala Tea', 40.00, 'Traditional Nepali tea with spices. Hot and refreshing.', 4, 'masala_tea.jpg', 0, 1);

-- Insert Sample Orders (for testing)
INSERT INTO orders (user_id, total_price, address, phone, status, notes) VALUES
(2, 510.00, 'Thamel, Kathmandu', '9841111111', 'Delivered', 'Extra spicy please'),
(3, 380.00, 'Patan Durbar Square, Lalitpur', '9841222222', 'Cooking', NULL),
(4, 650.00, 'Lakeside, Pokhara', '9841333333', 'Pending', 'Call before delivery');

-- Insert Sample Order Items
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 1, 250.00),
(1, 13, 1, 60.00),
(1, 5, 1, 200.00),
(2, 6, 1, 380.00),
(3, 2, 1, 450.00),
(3, 5, 1, 200.00);

-- Insert Sample Payments
INSERT INTO payments (order_id, payment_method, payment_status) VALUES
(1, 'eSewa', 'Completed'),
(2, 'Cash on Delivery', 'Pending'),
(3, 'Khalti', 'Completed');

-- =====================================================
-- INDEXES FOR BETTER PERFORMANCE
-- =====================================================
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_order_items_order ON order_items(order_id);

-- =====================================================
-- END OF DATABASE SCHEMA
-- =====================================================

