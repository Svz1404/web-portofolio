<?php
if (!ob_get_level()) {
    ob_start();
}
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}
/**
 * Vercel Serverless Entry Point & Router
 * Routes requests to index.php, cv.php, and admin pages
 */

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH) ?: '/';

// If asking for uploaded media (/uploads/...)
if (strpos($path, '/uploads/') === 0) {
    $cleanPath = '/' . ltrim($path, '/');
    $localFile = dirname(__DIR__) . $cleanPath;
    
    // 1. If physical file exists in local container, serve it directly
    if (is_file($localFile)) {
        $ext = strtolower(pathinfo($localFile, PATHINFO_EXTENSION));
        $mimes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            'pdf'  => 'application/pdf'
        ];
        $mime = $mimes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Content-Length: ' . filesize($localFile));
        readfile($localFile);
        exit;
    }
    
    // 2. Fallback to GitHub Raw CDN (always available, fast, global CDN)
    $githubRawUrl = 'https://raw.githubusercontent.com/Svz1404/web-portofolio/main' . $cleanPath;
    header('Location: ' . $githubRawUrl, true, 302);
    exit;
}

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
