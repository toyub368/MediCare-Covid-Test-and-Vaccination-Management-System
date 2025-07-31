-- COVID-19 Test & Vaccination Booking System Database
-- Run this SQL file in phpMyAdmin or MySQL command line

CREATE DATABASE IF NOT EXISTS covid_booking_system;
USE covid_booking_system;

-- Admin table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Hospitals table
CREATE TABLE IF NOT EXISTS hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_name VARCHAR(200) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    pincode VARCHAR(10) NOT NULL,
    license_number VARCHAR(100) UNIQUE NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    test_available BOOLEAN DEFAULT TRUE,
    vaccine_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Patients table
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    pincode VARCHAR(10) NOT NULL,
    aadhar_number VARCHAR(12) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vaccine inventory table
CREATE TABLE IF NOT EXISTS vaccine_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    vaccine_name VARCHAR(100) NOT NULL,
    available_doses INT DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0.00,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE
);

-- Test bookings table
CREATE TABLE IF NOT EXISTS test_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    hospital_id INT NOT NULL,
    test_type ENUM('RT-PCR', 'Rapid Antigen', 'Antibody') NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    test_result ENUM('positive', 'negative', 'pending') DEFAULT 'pending',
    result_date DATE NULL,
    price DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE
);

-- Vaccination bookings table
CREATE TABLE IF NOT EXISTS vaccination_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    hospital_id INT NOT NULL,
    vaccine_name VARCHAR(100) NOT NULL,
    dose_number ENUM('1', '2', 'booster') NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    vaccination_date DATE NULL,
    certificate_number VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE
);

-- Insert default admin
INSERT INTO admins (username, email, password, full_name) VALUES 
('admin', 'admin@covidbook.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator');

-- Insert sample hospitals
INSERT INTO hospitals (hospital_name, email, password, phone, address, city, state, pincode, license_number, status) VALUES 
('City General Hospital', 'city@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', '123 Main Street', 'Mumbai', 'Maharashtra', '400001', 'LIC001', 'approved'),
('Metro Medical Center', 'metro@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543211', '456 Park Avenue', 'Delhi', 'Delhi', '110001', 'LIC002', 'approved');

-- Insert sample vaccine inventory
INSERT INTO vaccine_inventory (hospital_id, vaccine_name, available_doses, price) VALUES 
(1, 'Covishield', 100, 250.00),
(1, 'Covaxin', 80, 300.00),
(2, 'Covishield', 150, 250.00),
(2, 'Sputnik V', 50, 400.00);

-- Insert sample patient
INSERT INTO patients (full_name, email, password, phone, date_of_birth, gender, address, city, state, pincode, aadhar_number) VALUES 
('John Doe', 'john@patient.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543212', '1990-01-01', 'male', '789 Oak Street', 'Mumbai', 'Maharashtra', '400002', '123456789012');