-- ============================================================
-- Automotive Hub Platform — Database
-- Import this file in phpMyAdmin.
-- Default test login after import:
--   Admin:    admin@autohub.com / 123456
--   Customer: customer@autohub.com / 123456
-- ============================================================

CREATE DATABASE IF NOT EXISTS automotive_hub_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE automotive_hub_db;

SET FOREIGN_KEY_CHECKS = 0;

-- =========================
-- Users
-- =========================
CREATE TABLE IF NOT EXISTS Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    registration_date DATE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Admin
-- =========================
CREATE TABLE IF NOT EXISTS Admin (
    admin_id INT PRIMARY KEY,
    FOREIGN KEY (admin_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Customer
-- =========================
CREATE TABLE IF NOT EXISTS Customer (
    customer_id INT PRIMARY KEY,
    name VARCHAR(100),
    delivery_address VARCHAR(255),
    account_status BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (customer_id) REFERENCES Users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Product
-- =========================
CREATE TABLE IF NOT EXISTS Product (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    brand VARCHAR(100),
    category VARCHAR(100),
    sub_type VARCHAR(100) DEFAULT NULL,  -- e.g. Sport/Luxury/SUV/Sedan for Cars; Sport/Cruiser/Electric/Adventure for Motorcycles
    price DECIMAL(12,2) NOT NULL,
    stock_count INT DEFAULT 0,
    image VARCHAR(255),
    average_rating FLOAT DEFAULT 0,
    review_count INT DEFAULT 0,
    admin_id INT NULL,
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_product_brand (brand),
    INDEX idx_product_category (category),
    INDEX idx_product_price (price),
    INDEX idx_product_rating (average_rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Coupon
-- =========================
CREATE TABLE IF NOT EXISTS Coupon (
    coupon_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    discount FLOAT NOT NULL,
    expiry_date DATE,
    admin_id INT NULL,
    FOREIGN KEY (admin_id) REFERENCES Admin(admin_id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Cart
-- =========================
CREATE TABLE IF NOT EXISTS Cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    session_id VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES Customer(customer_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_cart_user (user_id),
    INDEX idx_cart_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Cart_Item
-- =========================
CREATE TABLE IF NOT EXISTS Cart_Item (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (cart_id) REFERENCES Cart(cart_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_cart_product (cart_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Wishlist
-- =========================
CREATE TABLE IF NOT EXISTS Wishlist (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES Customer(customer_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_wishlist_user_product (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Review
-- =========================
CREATE TABLE IF NOT EXISTS Review (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating FLOAT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Customer(customer_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CHECK (rating >= 1 AND rating <= 5),
    UNIQUE KEY uq_review_user_product (user_id, product_id),
    INDEX idx_review_product (product_id),
    INDEX idx_review_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Orders
-- =========================
CREATE TABLE IF NOT EXISTS Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_date DATE NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Processing',
    discount DECIMAL(10,2) DEFAULT 0,
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    tracking_number VARCHAR(100),
    delivery_address VARCHAR(255),
    coupon_id INT NULL,
    FOREIGN KEY (user_id) REFERENCES Customer(customer_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (coupon_id) REFERENCES Coupon(coupon_id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Auto-generate tracking number on new order
DELIMITER $$
CREATE TRIGGER trg_orders_tracking
BEFORE INSERT ON Orders
FOR EACH ROW
BEGIN
    IF NEW.tracking_number IS NULL OR NEW.tracking_number = '' THEN
        SET NEW.tracking_number = CONCAT('AH-', LPAD(FLOOR(RAND() * 999999), 6, '0'), '-', YEAR(CURDATE()));
    END IF;
END$$
DELIMITER ;

-- Auto-update product average_rating and review_count after review insert
DELIMITER $$
CREATE TRIGGER trg_review_after_insert
AFTER INSERT ON Review
FOR EACH ROW
BEGIN
    UPDATE Product
    SET average_rating = (SELECT ROUND(AVG(rating), 2) FROM Review WHERE product_id = NEW.product_id),
        review_count   = (SELECT COUNT(*) FROM Review WHERE product_id = NEW.product_id)
    WHERE product_id = NEW.product_id;
END$$
DELIMITER ;

-- Auto-update product average_rating and review_count after review delete
DELIMITER $$
CREATE TRIGGER trg_review_after_delete
AFTER DELETE ON Review
FOR EACH ROW
BEGIN
    UPDATE Product
    SET average_rating = COALESCE((SELECT ROUND(AVG(rating), 2) FROM Review WHERE product_id = OLD.product_id), 0),
        review_count   = (SELECT COUNT(*) FROM Review WHERE product_id = OLD.product_id)
    WHERE product_id = OLD.product_id;
END$$
DELIMITER ;

-- =========================
-- Order_Item
-- =========================
CREATE TABLE IF NOT EXISTS Order_Item (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,ش
    total DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Product(product_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- Payment
-- =========================
CREATE TABLE IF NOT EXISTS Payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    amount DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    status VARCHAR(50),
    payment_date DATE,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================
-- Admin user (password: 123456)
-- =========================
INSERT INTO Users (email, password, first_name, last_name, phone, registration_date)
VALUES ('admin@autohub.com', '$2y$12$rVv5DWYbMAOyhS7ReY.bXO3EUhdWIc6bmRrtLjznlHjXtSyFLHu3.', 'Admin', 'User', '01000000000', CURDATE())
ON DUPLICATE KEY UPDATE email = email;

SET @admin_id := (SELECT user_id FROM Users WHERE email = 'admin@autohub.com' LIMIT 1);

INSERT INTO Admin (admin_id) VALUES (@admin_id)
ON DUPLICATE KEY UPDATE admin_id = admin_id;

INSERT INTO Customer (customer_id, name, delivery_address, account_status)
VALUES (@admin_id, 'Admin User', '', TRUE)
ON DUPLICATE KEY UPDATE customer_id = customer_id;

-- =========================
-- Test customer (password: 123456)
-- =========================
INSERT INTO Users (email, password, first_name, last_name, phone, registration_date)
VALUES ('customer@autohub.com', '$2y$12$rVv5DWYbMAOyhS7ReY.bXO3EUhdWIc6bmRrtLjznlHjXtSyFLHu3.', 'Test', 'Customer', '01111111111', CURDATE())
ON DUPLICATE KEY UPDATE email = email;

SET @customer_id := (SELECT user_id FROM Users WHERE email = 'customer@autohub.com' LIMIT 1);

INSERT INTO Customer (customer_id, name, delivery_address, account_status)
VALUES (@customer_id, 'Test Customer', 'Cairo, Egypt', TRUE)
ON DUPLICATE KEY UPDATE customer_id = customer_id;

-- =========================
-- Sample Coupon
-- =========================
INSERT INTO Coupon (code, discount, expiry_date, admin_id)
SELECT 'SAVE10', 10, DATE_ADD(CURDATE(), INTERVAL 1 YEAR), @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Coupon WHERE code = 'SAVE10');

INSERT INTO Coupon (code, discount, expiry_date, admin_id)
SELECT 'AUTO20', 20, DATE_ADD(CURDATE(), INTERVAL 1 YEAR), @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Coupon WHERE code = 'AUTO20');

-- =========================
-- Cars (category = Cars, sub_type = Sport/Luxury/SUV/Sedan)
-- =========================
INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Lamborghini Huracán EVO', 'Naturally aspirated V10 supercar delivering a raw, thrilling driving experience. 5.2L V10, 640 HP.', 'Lamborghini', 'Cars', 'Sport', 248000.00, 3, 'images/Lamborghini Huracan.jpg', 5.0, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Lamborghini Huracán EVO');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Ferrari F8 Tributo', 'A tribute to the most powerful V8 in Ferrari history — pure Maranello passion. 3.9L Twin-V8, 710 HP.', 'Ferrari', 'Cars', 'Sport', 276550.00, 2, 'images/Ferrari F8 Tributo.jpg', 5.0, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Ferrari F8 Tributo');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Porsche 911 GT3', 'Pure racing DNA on the road — naturally aspirated flat-six revving to 9,000 rpm. 4.0L Flat-6, 510 HP.', 'Porsche', 'Cars', 'Sport', 169700.00, 4, 'images/Porsche 911 GT3.jpg', 5.0, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Porsche 911 GT3');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'McLaren 720S', 'Carbon-fiber supercar with active aerodynamics and mind-bending 0-100 in 2.9 seconds. 4.0L Twin-V8, 720 HP.', 'McLaren', 'Cars', 'Sport', 299000.00, 2, 'images/McLaren 720S.jpg', 5.0, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'McLaren 720S');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Rolls-Royce Ghost', 'The pinnacle of automotive luxury — hand-crafted perfection with a twin-turbo V12. 6.75L V12, 563 HP.', 'Rolls-Royce', 'Cars', 'Luxury', 332500.00, 1, 'images/Rolls-Royce Ghost.jpg', 5.0, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Rolls-Royce Ghost');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Mercedes-Benz S 580 4MATIC', 'The most technologically advanced luxury sedan with MBUX Hyperscreen and rear-axle steering. 4.0L V8, 496 HP.', 'Mercedes-Benz', 'Cars', 'Luxury', 114500.00, 5, 'images/Mercedes S-Class.jpg', 5.0, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Mercedes-Benz S 580 4MATIC');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Bentley Continental GT', 'Grand touring excellence — hand-stitched leather, W12 power, and effortless refinement. 6.0L W12, 626 HP.', 'Bentley', 'Cars', 'Luxury', 224900.00, 3, 'images/Bentley Continental GT.jpg', 5.0, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Bentley Continental GT');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'BMW X5 xDrive40i', 'The benchmark luxury SUV combining performance, tech, and practicality. 3.0L I6, 335 HP, AWD.', 'BMW', 'Cars', 'SUV', 67300.00, 8, 'images/BMW X5.jpg', 4.8, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'BMW X5 xDrive40i');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Range Rover Sport', 'Combines luxury, performance, and legendary off-road capability in one stunning package. 3.0L I6, 395 HP.', 'Land Rover', 'Cars', 'SUV', 89900.00, 6, 'images/Range Rover Sport.jpg', 4.8, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Range Rover Sport');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Porsche Cayenne Turbo', 'The fastest Cayenne ever — sports car performance in an SUV body. 4.0L Twin-V8, 650 HP.', 'Porsche', 'Cars', 'SUV', 131050.00, 4, 'images/Porsche Cayenne Turbo.jpg', 5.0, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Porsche Cayenne Turbo');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Toyota Camry XSE', 'Redesigned for 2024 with a bold new look, hybrid powertrain, and top safety ratings. 2.5L I4 Hybrid, 203 HP.', 'Toyota', 'Cars', 'Sedan', 28400.00, 15, 'images/Toyota Camry.jpg', 4.7, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Toyota Camry XSE');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'BMW 740i xDrive', 'Next-gen luxury sedan with 31.3-inch Theatre Screen and automated parking. 3.0L I6, 375 HP, AWD.', 'BMW', 'Cars', 'Sedan', 97500.00, 5, 'images/BMW 7 Series.jpg', 4.8, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'BMW 740i xDrive');

-- =========================
-- Motorcycles (category = Motorcycles, sub_type = Sport/Cruiser/Electric/Adventure)
-- =========================
INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Yamaha R1', 'Top supersport bike with cutting-edge electronics and MotoGP-derived tech. 998cc, 200 HP.', 'Yamaha', 'Motorcycles', 'Sport', 17500.00, 10, 'images/Yamaha R1.jpg', 4.9, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Yamaha R1');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Harley-Davidson Sportster S', 'Iconic American cruiser with modern liquid-cooled engine. Revolution Max 1250cc, 121 HP.', 'Harley-Davidson', 'Motorcycles', 'Cruiser', 14999.00, 7, 'images/Harley Sportster S.jpg', 4.8, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Harley-Davidson Sportster S');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Zero SR/F', 'Premium electric motorcycle with 259km range per charge. Electric motor, 110 HP, 14.4 kWh battery.', 'Zero Motorcycles', 'Motorcycles', 'Electric', 21000.00, 5, 'images/Zero SRF.jpg', 4.7, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Zero SR/F');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'BMW R 1250 GS', 'The world best adventure motorcycle for any terrain. 1254cc Boxer, 136 HP.', 'BMW Motorrad', 'Motorcycles', 'Adventure', 18200.00, 8, 'images/BMW R 1250 GS.jpg', 4.9, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'BMW R 1250 GS');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Kawasaki Ninja ZX-10R', 'Race-derived superbike delivering track performance on the road. 998cc inline-4, 203 HP.', 'Kawasaki', 'Motorcycles', 'Sport', 16999.00, 6, 'images/Kawasaki Ninja ZX-10R.jpg', 4.8, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Kawasaki Ninja ZX-10R');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Honda Gold Wing Tour', 'Ultimate touring motorcycle with premium comfort and tech. 1833cc flat-6, 125 HP.', 'Honda', 'Motorcycles', 'Cruiser', 28500.00, 4, 'images/Honda Gold Wing.jpg', 4.9, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Honda Gold Wing Tour');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Energica Ego', 'Italian-made electric superbike with 400km range. Electric motor, 145 HP, 21.5 kWh battery.', 'Energica', 'Motorcycles', 'Electric', 23000.00, 3, 'images/Energica Ego.jpg', 4.6, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Energica Ego');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Ducati Panigale V4', 'Italian masterpiece — the most powerful road-legal superbike. 1103cc V4, 215 HP.', 'Ducati', 'Motorcycles', 'Sport', 28995.00, 5, 'images/Ducati Panigale V4.jpg', 5.0, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Ducati Panigale V4');

-- =========================
-- Electric Vehicles (category = Electric)
-- =========================
INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Tesla Model S Plaid', 'The quickest production car ever made. Tri-motor AWD with 1020 HP and 652km range. 100 kWh battery.', 'Tesla', 'Electric', 'Sedan', 74990.00, 6, 'images/Model S Plaid.jpg', 4.9, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Tesla Model S Plaid');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Rivian R1T Adventure', 'The ultimate electric adventure truck. Quad-motor performance meets off-road capability. 835 HP, 560km range.', 'Rivian', 'Electric', 'Truck', 69900.00, 5, 'images/Rivian R1T1.jpg', 4.8, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Rivian R1T Adventure');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Porsche Taycan Turbo S', 'German precision meets electric performance. The benchmark for EV sports cars. 761 HP, 501km range.', 'Porsche', 'Electric', 'Sport', 185000.00, 3, 'images/porsche-taycan.jpeg', 5.0, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Porsche Taycan Turbo S');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'BMW iX M60', 'BMW flagship electric SUV with M Performance DNA, luxurious interior and 566km range. 610 HP.', 'BMW', 'Electric', 'SUV', 110900.00, 4, 'images/BMW iX.jpg', 4.8, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'BMW iX M60');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Mercedes EQS 580 4MATIC', 'The electric S-Class. 770km range, Hyperscreen dashboard and unmatched comfort. 516 HP.', 'Mercedes-Benz', 'Electric', 'Sedan', 125900.00, 4, 'images/Mercedes EQS.jpg', 4.9, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Mercedes EQS 580 4MATIC');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Audi Q8 e-tron Sportback', 'Audi flagship electric SUV blending quattro AWD with efficient 600km range. 402 HP.', 'Audi', 'Electric', 'SUV', 87500.00, 6, 'images/Audi Q8 e-tron.jpg', 4.7, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Audi Q8 e-tron Sportback');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Lucid Air Grand Touring', 'World record 832km range. The most energy-efficient EV ever produced with stunning luxury. 819 HP.', 'Lucid Motors', 'Electric', 'Sedan', 138000.00, 3, 'images/Lucid Air.jpg', 4.9, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Lucid Air Grand Touring');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Ford F-150 Lightning Pro', 'Americas best-selling truck goes electric. 580HP, bidirectional charging, and towing up to 4.5 tons.', 'Ford', 'Electric', 'Truck', 55974.00, 8, 'images/Ford F-150 Lightning.jpg', 4.6, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Ford F-150 Lightning Pro');

-- =========================
-- Spare Parts (category = Spare Parts, sub_type = Engine/Brakes/Suspension/Electrical/Body)
-- =========================
INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Brembo GT Brake Kit 6-Piston', 'World-class Italian braking performance. 380mm slotted disc with 6-piston monoblock caliper for extreme stopping power.', 'Brembo', 'Spare Parts', 'Brakes', 1850.00, 20, 'images/GT Brake Kit 6-Piston.jpg', 4.9, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Brembo GT Brake Kit 6-Piston');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'K&N High-Flow Air Filter', 'Drop-in replacement filter increasing airflow by up to 15%. Washable and reusable with million-mile limited warranty.', 'K&N', 'Spare Parts', 'Engine', 89.00, 50, 'images/K&N Air Filter.jpg', 4.8, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'K&N High-Flow Air Filter');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Bilstein B6 Sport Shock Absorbers', 'German-engineered monotube gas pressure shocks. Direct OEM replacement with sport-tuned damping for improved handling.', 'Bilstein', 'Spare Parts', 'Suspension', 620.00, 15, 'images/Bilstein Shocks.jpg', 4.9, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Bilstein B6 Sport Shock Absorbers');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Bosch Alternator 180A Remanufactured', 'Professional-grade remanufactured alternator. OEM specifications with 100% electrical testing before shipping.', 'Bosch', 'Spare Parts', 'Electrical', 340.00, 25, 'images/Bosch Alternator.jpg', 4.7, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Bosch Alternator 180A Remanufactured');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'NGK Iridium IX Spark Plugs', 'Ultra-fine 0.6mm iridium center electrode for improved ignitability and fuel efficiency. Lasts up to 100,000km.', 'NGK', 'Spare Parts', 'Engine', 75.00, 60, 'images/NGK Spark Plugs.jpg', 4.8, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'NGK Iridium IX Spark Plugs');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Enkei RPF1 Alloy Wheels 18"', 'Legendary lightweight forged alloy wheel. Used by professional racing teams worldwide. Flow-form construction for maximum strength.', 'Enkei', 'Spare Parts', 'Body', 1200.00, 12, 'images/Enkei Alloy Wheels.jpg', 5.0, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Enkei RPF1 Alloy Wheels 18"');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'EBC Yellowstuff Street & Track Pads', 'High-friction street and track brake pads rated to 500°C. Low dust formula with chamfered and slotted design.', 'EBC Brakes', 'Spare Parts', 'Brakes', 145.00, 30, 'images/EBC Brake Pads.jpg', 4.6, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'EBC Yellowstuff Street & Track Pads');

INSERT INTO Product (name, description, brand, category, sub_type, price, stock_count, image, average_rating, review_count, admin_id)
SELECT 'Denso OEM Starter Motor 1.4kW', 'Original equipment quality starter motor. Designed and built to exact OEM specifications for reliable cold-start performance.', 'Denso', 'Spare Parts', 'Electrical', 210.00, 18, 'images/Denso Starter Motor.jpg', 4.8, 0, @admin_id
WHERE NOT EXISTS (SELECT 1 FROM Product WHERE name = 'Denso OEM Starter Motor 1.4kW');
