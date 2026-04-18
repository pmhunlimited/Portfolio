-- Database: cyber_pulse_portfolio
CREATE DATABASE IF NOT EXISTS cyber_pulse_portfolio;
USE cyber_pulse_portfolio;

-- Global Settings Table
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Initial Settings
INSERT INTO settings (setting_key, setting_value) VALUES 
('appTitle', 'CYBER-PULSE'),
('heroSubtext', 'Sophisticated full-stack engineering. Powered by Gemini AI. Crafted for the elite digital frontier.'),
('gemini_api_key', ''),
('deepseek_api_key', ''),
('pagespeed_api_key', ''),
('admin_username', 'philmorehost@gmail.com'),
('admin_password', 'password1234'),
('authorized_email', 'philmorehost@gmail.com');

-- Projects Table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT,
    site_url VARCHAR(500),
    thumbnail_url VARCHAR(500),
    project_type ENUM('web', 'app') DEFAULT 'web',
    wa_message TEXT,
    meta_title VARCHAR(255),
    meta_description TEXT,
    speed INT DEFAULT 98,
    security INT DEFAULT 100,
    inquiries_count INT DEFAULT 0,
    is_pinned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Gallery Table
CREATE TABLE IF NOT EXISTS project_gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    media_url VARCHAR(500),
    sort_order INT DEFAULT 0,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Tech Stacks Table
CREATE TABLE IF NOT EXISTS tech_stacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    name VARCHAR(100),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Keywords Table (for SEO)
CREATE TABLE IF NOT EXISTS keywords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    keyword VARCHAR(100),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Admin Table (simple session-based auth)
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL
);
