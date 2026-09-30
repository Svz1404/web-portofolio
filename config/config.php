<?php
/**
 * Configuration File
 * MasPutra Portfolio & Admin Control
 */

// Error reporting (can be disabled in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enable output buffering
if (!ob_get_level()) {
    ob_start();
}

// Session setup
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

// Base URL Detection (Compatible with Localhost, HTTPS, Reverse Proxy, and Vercel)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$protocol = $isHttps ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
// Normalize directory to root of project
$basePath = rtrim(str_replace('\\', '/', $scriptDir), '/');
// If inside /MrSvz1404 or /admin, strip it
if (substr($basePath, -10) === '/MrSvz1404') {
    $basePath = substr($basePath, 0, -10);
}
if (substr($basePath, -6) === '/admin') {
    $basePath = substr($basePath, 0, -6);
}
// If executed via Vercel serverless /api router, strip /api
if (substr($basePath, -4) === '/api') {
    $basePath = substr($basePath, 0, -4);
}
$baseUrl = $protocol . $host . $basePath;
define('BASE_URL', rtrim($baseUrl, '/') . '/');
define('BASE_DIR', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// Database Configuration
// By default we use 'sqlite' which works instantly with zero setup,
// but you can easily switch to 'mysql' if you prefer phpMyAdmin / MySQL!
define('DB_DRIVER', 'sqlite'); // 'sqlite' or 'mysql'

// SQLite Path
define('DB_SQLITE_FILE', BASE_DIR . 'database' . DIRECTORY_SEPARATOR . 'masputra.db');

// MySQL Settings (if DB_DRIVER is set to 'mysql')
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'masputra_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// App details
define('APP_NAME', 'SAPUTRA - Portfolio & CV');
define('ADMIN_NAME', 'Admin Mas Putra');
