-- Database untuk Jasa Pengiriman Barang
-- Import file ini ke phpMyAdmin di XAMPP

CREATE DATABASE IF NOT EXISTS jasa_pengiriman CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jasa_pengiriman;

-- Tabel Roles
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Tabel Customers (Pelanggan)
CREATE TABLE IF NOT EXISTS customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(50) NOT NULL,
    postal_code VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel Packages (Paket)
CREATE TABLE IF NOT EXISTS packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    weight DECIMAL(10,2) NOT NULL,
    length DECIMAL(10,2),
    width DECIMAL(10,2),
    height DECIMAL(10,2),
    price DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel Shipments (Pengiriman)
CREATE TABLE IF NOT EXISTS shipments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tracking_number VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    package_id INT NOT NULL,
    sender_name VARCHAR(100) NOT NULL,
    sender_address TEXT NOT NULL,
    sender_phone VARCHAR(20) NOT NULL,
    receiver_name VARCHAR(100) NOT NULL,
    receiver_address TEXT NOT NULL,
    receiver_phone VARCHAR(20) NOT NULL,
    receiver_city VARCHAR(50) NOT NULL,
    receiver_postal_code VARCHAR(10),
    status ENUM('pending', 'in_transit', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_date DATE,
    delivery_date DATE,
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Insert default roles
INSERT INTO roles (name, description) VALUES
('Admin', 'Administrator dengan akses penuh'),
('Staff', 'Staff yang dapat mengelola pengiriman'),
('Customer', 'Pelanggan yang dapat melihat pengiriman mereka');

-- Insert default admin user (password: admin123)
-- Password hash akan diupdate oleh setup.php atau bisa diubah manual
INSERT INTO users (username, email, password, full_name, phone, role_id) VALUES
('admin', 'admin@jasa.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'Administrator', '081234567890', 1);

-- Insert sample data
INSERT INTO customers (name, email, phone, address, city, postal_code) VALUES
('John Doe', 'john@example.com', '081111111111', 'Jl. Contoh No. 123', 'Jakarta', '12345'),
('Jane Smith', 'jane@example.com', '082222222222', 'Jl. Sample No. 456', 'Bandung', '54321');

INSERT INTO packages (name, description, weight, length, width, height, price) VALUES
('Paket Reguler', 'Pengiriman reguler 3-5 hari', 1.00, 20, 15, 10, 15000),
('Paket Express', 'Pengiriman cepat 1-2 hari', 1.00, 20, 15, 10, 30000),
('Paket Super Express', 'Pengiriman sangat cepat 1 hari', 1.00, 20, 15, 10, 50000);

INSERT INTO shipments (tracking_number, customer_id, package_id, sender_name, sender_address, sender_phone, receiver_name, receiver_address, receiver_phone, receiver_city, receiver_postal_code, status, created_by) VALUES
('TRK001', 1, 1, 'John Doe', 'Jl. Contoh No. 123', '081111111111', 'Jane Smith', 'Jl. Sample No. 456', '082222222222', 'Bandung', '54321', 'in_transit', 1),
('TRK002', 2, 2, 'Jane Smith', 'Jl. Sample No. 456', '082222222222', 'John Doe', 'Jl. Contoh No. 123', '081111111111', 'Jakarta', '12345', 'pending', 1);

