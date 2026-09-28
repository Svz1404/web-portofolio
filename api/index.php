<?php
if (!ob_get_level()) {
    ob_start();
}
/**
 * Vercel Serverless Entry Point & Router
 * Routes requests to index.php, cv.php, and admin pages
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH) ?: '/';

// If asking for root /
if ($path === '/' || $path === '') {
    require __DIR__ . '/../index.php';
    exit;
}

// If asking for cv.php or /cv
if ($path === '/cv.php' || $path === '/cv') {
    require __DIR__ . '/../cv.php';
    exit;
}

// If asking for MrSvz1404 admin pages
if (strpos($path, '/MrSvz1404') === 0) {
    if ($path === '/MrSvz1404' || $path === '/MrSvz1404/') {
        require __DIR__ . '/../MrSvz1404/index.php';
        exit;
    }
    
    // Normalize path (strip '/MrSvz1404')
    $relPath = ltrim(substr($path, 10), '/');
    if (empty($relPath)) {
        require __DIR__ . '/../MrSvz1404/index.php';
        exit;
    }
    
    $adminTarget = __DIR__ . '/../MrSvz1404/' . $relPath;
    if (is_file($adminTarget)) {
        require $adminTarget;
        exit;
    }
    if (is_file($adminTarget . '.php')) {
        require $adminTarget . '.php';
        exit;
    }
}

// If direct php file requested
$directFile = __DIR__ . '/..' . $path;
if (is_file($directFile) && substr($directFile, -4) === '.php') {
    require $directFile;
    exit;
}

// Fallback to index.php
require __DIR__ . '/../index.php';
