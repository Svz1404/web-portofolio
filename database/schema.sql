-- ========================================================
-- Database Schema for MasPutra Portfolio & Admin Control
-- If using MySQL in phpMyAdmin:
-- 1. Create database: masputra_db
-- 2. Import this file
-- 3. Set DB_DRIVER = 'mysql' in config/config.php
-- ========================================================

CREATE DATABASE IF NOT EXISTS `masputra_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `masputra_db`;

-- Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: admin / admin123
INSERT IGNORE INTO `admins` (`id`, `username`, `password`, `name`, `email`) 
VALUES (1, 'admin', '$2y$10$w09aV455YfDghhI6JqI8re6lM32iHnJm06vE.Kj7h1d7Nf3W4b2W2', 'Saputra', 'masputra1404@gmail.com');

-- Profile Table
CREATE TABLE IF NOT EXISTS `profile` (
  `id` INT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(50),
  `email` VARCHAR(100),
  `address` VARCHAR(255),
  `linkedin` VARCHAR(255),
  `github` VARCHAR(255),
  `bio` TEXT,
  `avatar` VARCHAR(255),
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `profile` (`id`, `full_name`, `title`, `phone`, `email`, `address`, `linkedin`, `github`, `bio`, `avatar`)
VALUES (1, 'SAPUTRA', 'Programmer / Welder', '+628 1277 900210', 'masputra1404@gmail.com', 'Griya Laguna Mas c3 18', 'https://www.linkedin.com/in/masputra1404/', 'https://github.com/masputra1404', 
'Saya adalah seorang Welder Kombinasi dengan pengalaman dalam proses GTAW, SMAW, GMAW, dan FCAW. Selain bidang fabrikasi dan pengelasan, saya juga memiliki kemampuan sebagai programmer dengan fokus pada automation web & scraping menggunakan Node.js. Saya familiar dengan berbagai bahasa pemrograman seperti PHP, VB, C#, Node.js, dan HTML. Berpengetahuan luas tentang dunia komputer, cepat beradaptasi, dan memiliki rasa tanggung jawab tinggi dalam setiap pekerjaan.', 
'assets/images/avatar.png');

-- Skills Table
CREATE TABLE IF NOT EXISTS `skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `percentage` INT NOT NULL,
  `category` VARCHAR(50) DEFAULT 'skill',
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `skills` (`id`, `name`, `percentage`, `category`, `sort_order`) VALUES
(1, 'Welding', 100, 'skill', 1),
(2, 'Node JS', 85, 'skill', 2),
(3, 'VB. Net', 80, 'skill', 3),
(4, 'PHP & HTML', 85, 'skill', 4),
(5, 'C#', 75, 'skill', 5),
(6, 'Microsoft Office', 85, 'skill', 6),
(7, 'Typing', 100, 'skill', 7),
(8, 'Computer', 100, 'skill', 8),
(9, 'Indonesia', 100, 'bahasa', 9),
(10, 'Inggris', 75, 'bahasa', 10);

-- Education Table
CREATE TABLE IF NOT EXISTS `education` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `year_range` VARCHAR(50) NOT NULL,
  `institution` VARCHAR(150) NOT NULL,
  `major` VARCHAR(150) NOT NULL,
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `education` (`id`, `year_range`, `institution`, `major`, `sort_order`) VALUES
(1, '(2021 - 2021)', 'PT. GLOBAL SARANA INTERNUSA', 'WELDER GTAW + SMAW 6G', 1),
(2, '(2021 - 2021)', 'PT. GLOBAL SARANA INTERNUSA', 'WELDER SMAW 3G + 4G', 2),
(3, '(2014 - 2017)', 'SMK MULTISTUDI HIGHSCHOOL', 'Rekayasa Perangkat Lunak', 3);

-- Experience Table
CREATE TABLE IF NOT EXISTS `experience` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `date_range` VARCHAR(100) NOT NULL,
  `company` VARCHAR(150) NOT NULL,
  `role` VARCHAR(150) NOT NULL,
  `details` TEXT,
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `experience` (`id`, `date_range`, `company`, `role`, `details`, `sort_order`) VALUES
(1, 'JANUARI 2026 – JUNE 2026', 'CATERPILLAR INDONESIA', 'Welder GMAW', '• WELDER GMAW\n• Pengelasan presisi komponen alat berat sesuai standar mutu internasional Caterpillar.', 1),
(2, 'MARET 2024 – OKTOBER 2025', 'PT. TOYO KANETSU INDONESIA', 'Welder Kombinasi', '• Welder GTAW + SMAW 6G\n• Welder GTAW + GMAW 6G\n• Fabrikasi tangki dan bejana tekan (pressure vessel) berstandar lolos uji radiografi (X-ray).', 2),
(3, 'AGUSTUS 2023 – FEBRUARI 2024', 'PT. ERAJAYA GOPTI ABADI', 'Welder GTAW + SMAW 6G', '• Welder GTAW + SMAW 6G\n• Pekerjaan pengelasan struktur baja dan perpipaan bertekanan tinggi.', 3),
(4, 'SEPTEMBER 2021 – DESEMBER 2021', 'PT. KHEE', 'Welder SMAW 6G', '• Welder SMAW 6G\n• Pengelasan struktur berat konstruksi lepas pantai dan fabrikasi industri.', 4),
(5, 'DESEMBER 2021 – JULY 2023', 'FREELANCER', 'Web Automation & Developer', '• Web Automation & Scrapping menggunakan Node.js dan Puppeteer\n• Data Mining & integrasi API otomatis\n• Community Manager teknis & administrasi server', 5),
(6, 'SEPTEMBER 2019 – SEPTEMBER 2021', 'PT. MATRICK PACK', 'Operator Blowing', '• Operator Blowing mesin industri\n• Monitoring dan pemeliharaan mesin produksi kemasan plastik.', 6),
(7, 'JULI 2017 – AGUSTUS 2019', 'PT. PANCA JAYA ABADI', 'Operator Warehouse', '• Operator Warehouse\n• Manajemen inventaris barang, input data komputer, dan logistik pergudangan.', 7);

-- Certificates Table
CREATE TABLE IF NOT EXISTS `certificates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `issuer` VARCHAR(150) NOT NULL,
  `issue_date` VARCHAR(50),
  `credential_id` VARCHAR(100),
  `credential_url` VARCHAR(255),
  `image` VARCHAR(255),
  `description` TEXT,
  `featured` INT DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Projects Table
CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `tech_stack` VARCHAR(255),
  `live_url` VARCHAR(255),
  `github_url` VARCHAR(255),
  `image` VARCHAR(255),
  `featured` INT DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Messages Table
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(150),
  `message` TEXT NOT NULL,
  `is_read` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
