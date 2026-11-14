-- Database Schema for HomeCare Service

SET foreign_key_checks = 0;

DROP TABLE IF EXISTS chat_messages;
DROP TABLE IF EXISTS chat_sessions;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS service_reports;
DROP TABLE IF EXISTS service_orders;
DROP TABLE IF EXISTS equipment;
DROP TABLE IF EXISTS technicians;
DROP TABLE IF EXISTS users;

SET foreign_key_checks = 1;

-- Table: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('ADMIN', 'CUSTOMER', 'TEKNISI') NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- Table: technicians
CREATE TABLE technicians (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    specialization VARCHAR(255),
    experience INT COMMENT 'Pengalaman dalam tahun',
    availability BOOLEAN DEFAULT TRUE,
    rating DECIMAL(3,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: equipment
CREATE TABLE equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    brand VARCHAR(100),
    category VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category)
) ENGINE=InnoDB;

-- Table: service_orders
CREATE TABLE service_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    technician_id INT NULL,
    equipment_id INT NOT NULL,
    complaint TEXT NOT NULL,
    ai_diagnosis TEXT NULL,
    status ENUM('PENDING', 'ASSIGNED', 'ACCEPTED', 'ON_THE_WAY', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED') DEFAULT 'PENDING',
    service_date DATETIME,
    photo VARCHAR(255) NULL,
    address TEXT,
    estimated_cost DECIMAL(10,2) NULL,
    final_cost DECIMAL(10,2) NULL,
    payment_preference VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES technicians(id) ON DELETE SET NULL,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_service_date (service_date)
) ENGINE=InnoDB;

-- Table: service_reports
CREATE TABLE service_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    technician_id INT NOT NULL,
    inspection_result TEXT NOT NULL,
    repair_action TEXT,
    notes TEXT,
    cost DECIMAL(10,2),
    completed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES service_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES technicians(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: reviews
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    technician_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES service_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (technician_id) REFERENCES technicians(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table: notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_id INT NULL,
    type VARCHAR(50) NOT NULL COMMENT 'ACCEPTED, ON_THE_WAY, IN_PROGRESS, COMPLETED',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES service_orders(id) ON DELETE SET NULL,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- Table: payments
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('CASH', 'TRANSFER') NOT NULL,
    payment_provider VARCHAR(50) NULL COMMENT 'BRIMO, BCA, MANDIRI, SHOPEEPAY, DANA, GOPAY, LAINNYA',
    sender_name VARCHAR(100) NULL,
    sender_account VARCHAR(100) NULL,
    proof_image VARCHAR(255) NULL,
    notes TEXT NULL,
    status ENUM('PAID', 'PENDING', 'REJECTED') DEFAULT 'PAID',
    paid_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES service_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Table: chat_sessions
CREATE TABLE chat_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL DEFAULT 'Percakapan Baru',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id)
) ENGINE=InnoDB;

-- Table: chat_messages
CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    sender ENUM('USER', 'AI') NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE,
    INDEX idx_session (session_id)
) ENGINE=InnoDB;

-- Table: system_settings
CREATE TABLE IF NOT EXISTS system_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value LONGTEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


