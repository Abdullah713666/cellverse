-- CellVerse Database Schema
-- Mobile Accessories Bulk Ordering Platform

CREATE DATABASE IF NOT EXISTS cellverse_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cellverse_db;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) UNIQUE NOT NULL,
    description TEXT,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(220) UNIQUE NOT NULL,
    description TEXT,
    category_id INT,
    image_path VARCHAR(500),
    price_per_unit DECIMAL(10,2) NOT NULL DEFAULT 0,
    moq INT NOT NULL DEFAULT 1,
    stock_qty INT DEFAULT 0,
    sku VARCHAR(50),
    status ENUM('available', 'low_stock', 'out_of_stock', 'discontinued') DEFAULT 'available',
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Bulk orders table
CREATE TABLE IF NOT EXISTS bulk_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(200) NOT NULL,
    contact_person VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL,
    phone VARCHAR(30),
    product_id INT,
    product_name VARCHAR(200),
    quantity INT NOT NULL,
    required_date DATE,
    delivery_address TEXT,
    notes TEXT,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Contact submissions table
CREATE TABLE IF NOT EXISTS contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL,
    phone VARCHAR(30),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Testimonials table
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(150) NOT NULL,
    client_title VARCHAR(150),
    company VARCHAR(200),
    quote TEXT NOT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- FAQs table
CREATE TABLE IF NOT EXISTS faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(100) DEFAULT 'General',
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Site settings table
CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Password reset tokens table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Registered users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(200) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    company VARCHAR(200),
    phone VARCHAR(30),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Seed Data
-- ============================================

-- Default admin user (password: admin123)
INSERT INTO admin_users (username, password_hash, email) VALUES
('admin', '$2y$10$lu.cfc9SzhtL8RxZHLlY.OD38oJBkujLOUKmaNETFKIVdfnQdTlvG', 'admin@cellverse.com');

-- Default categories
INSERT INTO categories (name, slug, description, display_order) VALUES
('Phone Cases', 'phone-cases', 'Protective cases for all smartphone models', 1),
('Screen Protectors', 'screen-protectors', 'Tempered glass and film protectors', 2),
('Chargers', 'chargers', 'Wall chargers, car chargers, and wireless chargers', 3),
('Cables', 'cables', 'USB-C, Lightning, and micro-USB cables', 4),
('Earphones', 'earphones', 'Wired and wireless earphones', 5),
('Power Banks', 'power-banks', 'Portable chargers and power banks', 6),
('Stands & Holders', 'stands-holders', 'Phone stands, car mounts, and holders', 7),
('Other Accessories', 'other-accessories', 'Grips, rings, stylus, and more', 8);

-- Sample products
INSERT INTO products (name, slug, description, category_id, price_per_unit, moq, stock_qty, sku, is_featured) VALUES
('Premium Silicone Case', 'premium-silicone-case', 'Soft silicone case with anti-slip grip, available in multiple colors', 1, 3.50, 50, 500, 'PC-001', 1),
('Tempered Glass Screen Protector', 'tempered-glass-screen-protector', '9H hardness tempered glass with oleophobic coating', 2, 1.20, 100, 1000, 'TG-001', 1),
('20W USB-C Fast Charger', '20w-usb-c-fast-charger', 'PD 3.0 fast charger compatible with all USB-C devices', 3, 8.00, 25, 200, 'CH-001', 1),
('Braided USB-C Cable 1.5m', 'braided-usb-c-cable', 'Durable braided nylon USB-C cable with fast charging support', 4, 2.50, 50, 800, 'CB-001', 0),
('Wireless Earbuds Pro', 'wireless-earbuds-pro', 'Bluetooth 5.0 earbuds with noise cancellation', 5, 12.00, 20, 150, 'WE-001', 1),
('10000mAh Power Bank', '10000mah-power-bank', 'Slim power bank with dual USB output and LED indicator', 6, 15.00, 10, 100, 'PB-001', 1),
('Adjustable Phone Stand', 'adjustable-phonestand', 'Foldable aluminum phone stand with adjustable angle', 7, 4.00, 30, 300, 'PS-001', 0),
('Universal Car Mount', 'universal-car-mount', 'Dashboard car mount with 360-degree rotation', 7, 6.50, 20, 250, 'CM-001', 0),
('Ring Holder Grip', 'ring-holder-grip', 'Metal finger ring grip with kickstand function', 8, 1.00, 100, 2000, 'RH-001', 0),
('Pop Socket Grip', 'pop-socket-grip', 'Expandable phone grip with custom logo option', 8, 1.50, 100, 1500, 'PS-002', 0),
('Wireless Charging Pad', 'wireless-charging-pad', '10W Qi wireless charger with LED indicator', 3, 7.00, 25, 180, 'WC-001', 1),
('Multi-Port USB Charger', 'multi-port-usb-charger', '6-port USB charging station with smart IC', 3, 18.00, 10, 80, 'MC-001', 0);

-- Sample testimonials
INSERT INTO testimonials (client_name, client_title, company, quote, is_featured) VALUES
('Ahmad Hassan', 'Procurement Manager', 'TechMart Electronics', 'CellVerse has been our go-to supplier for 3 years. Their bulk pricing and fast delivery keep our shelves stocked.', 1),
('Sarah Khan', 'Owner', 'MobileWorld Pakistan', 'Excellent quality products and very responsive team. The wholesale prices help us stay competitive.', 1),
('Ali Raza', 'Supply Chain Director', 'GadgetHub Stores', 'Reliable supplier with consistent quality. Their minimum order quantities are perfect for our business size.', 1);

-- Sample FAQs
INSERT INTO faqs (question, answer, category, display_order) VALUES
('What is the minimum order quantity?', 'Our MOQ varies by product. Most items have an MOQ of 10-50 units. Check the product page for specific MOQ details.', 'Ordering', 1),
('Do you offer bulk discounts?', 'Yes! We offer tiered pricing. The more you order, the more you save. Contact us for custom bulk pricing.', 'Ordering', 2),
('What payment methods do you accept?', 'We accept bank transfers, JazzCash, EasyPaisa, and cash on delivery for verified businesses.', 'Payment', 3),
('How long does delivery take?', 'Standard delivery takes 3-5 business days within Pakistan. Express delivery is available for an additional fee.', 'Shipping', 4),
('What is your return policy?', 'We offer a 7-day return policy for defective products. Please contact our support team to initiate a return.', 'Returns', 5),
('Can I get samples before ordering?', 'Yes, we offer sample orders at retail price. Sample costs are credited when you place a bulk order.', 'Ordering', 6),
('Do you ship internationally?', 'Currently we primarily serve the Pakistani market. For international orders, please contact us for custom arrangements.', 'Shipping', 7),
('How do I track my order?', 'Once your order is shipped, you will receive a tracking number via email and SMS.', 'Shipping', 8);

-- Default site settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'CellVerse'),
('site_tagline', 'Bulk Mobile Accessories at Wholesale Prices'),
('contact_email', ''),
('contact_phone', '+92 300 1234567'),
('contact_address', ''),
('whatsapp_number', '+923001234567'),
('map_latitude', '31.5204'),
('map_longitude', '74.3587'),
('map_embed_url', 'https://www.openstreetmap.org/export/embed.html?bbox=74.3487%2C31.5104%2C74.3687%2C31.5304&layer=mapnik&marker=31.5204%2C74.3587'),
('recaptcha_site_key', ''),
('recaptcha_secret_key', ''),
('about_text', 'CellVerse is Pakistan\'s leading wholesale supplier of mobile accessories. With over 10 years of experience, we provide high-quality products at competitive bulk prices to retailers and businesses across the country.'),
('facebook_url', 'https://facebook.com/cellverse'),
('instagram_url', 'https://instagram.com/cellverse'),
('youtube_url', 'https://youtube.com/cellverse');

-- ============================================
-- Migrations for existing installs
-- ============================================

-- Add email column to admin_users if missing
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'admin_users' AND column_name = 'email');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE admin_users ADD COLUMN email VARCHAR(200) AFTER password_hash', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Create password_resets table if missing
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
