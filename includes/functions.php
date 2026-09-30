<?php
/**
 * Utility & Helper Functions
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/lang.php';

// Clean raw input before saving to database (preserves characters like & without HTML entity corruption)
function clean_input($data) {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    // Decode any pre-existing encoded entities so raw clean string is saved
    $str = html_entity_decode((string)$data, ENT_QUOTES, 'UTF-8');
    return trim($str);
}

// Sanitize string for HTML output (prevents XSS while avoiding double-encoding of &amp;)
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8', false);
}

// Generate URL
function base_url($path = '') {
    $cleanPath = ltrim($path, '/');
    
    // On Vercel or Production Custom Domain, serve media uploads directly from GitHub Raw CDN
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $isLocalhost = in_array($host, ['localhost', '127.0.0.1']) || strpos($host, 'localhost:') === 0;
    $isProduction = !$isLocalhost || !empty($_ENV['VERCEL']) || !empty($_SERVER['VERCEL']) || !empty($_SERVER['HTTP_X_VERCEL_ID']);
    
    if ($isProduction && strpos($cleanPath, 'uploads/') === 0) {
        return 'https://raw.githubusercontent.com/Svz1404/web-portofolio/main/' . $cleanPath;
    }
    
    return BASE_URL . $cleanPath;
}

// Redirect
function redirect($path) {
    $targetUrl = (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) ? $path : base_url($path);
    if (!headers_sent()) {
        header("Location: " . $targetUrl);
    } else {
        echo "<script>window.location.replace(" . json_encode($targetUrl) . ");</script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=" . htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8') . "'></noscript>";
    }
    exit;
}

// Flash Messages
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// CSRF Token
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

// Authentication Check
function isLoggedIn() {
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAuth() {
    if (!isLoggedIn()) {
        setFlash('danger', 'Silakan login terlebih dahulu untuk mengakses halaman admin.');
        redirect('MrSvz1404/login.php');
    }
}

// File Upload Helper
function handleUpload($file, $subFolder = 'uploads', $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif']) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'Parameter upload tidak valid.'];
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'filename' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Gagal upload file (Error Code: ' . $file['error'] . ')'];
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        return ['success' => false, 'error' => 'Ukuran file maksimal 5MB.'];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $ext = strtolower(trim($ext));
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf'];

    if (!in_array($ext, $allowedExts)) {
        return ['success' => false, 'error' => 'Tipe file tidak diizinkan. Harap upload format JPG, PNG, atau WEBP.'];
    }

    // Determine MIME type safely without requiring finfo extension
    $mime = null;
    if (class_exists('finfo')) {
        try {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
        } catch (Exception $e) {}
    } elseif (function_exists('mime_content_type')) {
        $mime = @mime_content_type($file['tmp_name']);
    }

    // For image files, also verify with getimagesize
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) && function_exists('getimagesize')) {
        $imgCheck = @getimagesize($file['tmp_name']);
        if ($imgCheck === false && $ext !== 'webp') { // webp might not be supported in older GD versions
            return ['success' => false, 'error' => 'File yang diunggah bukan gambar yang valid.'];
        }
    }

    if ($mime !== null && !empty($allowedTypes) && !in_array($mime, $allowedTypes) && $ext !== 'pdf') {
        return ['success' => false, 'error' => 'Format file tidak sesuai ketentuan (MIME: ' . htmlspecialchars($mime, ENT_QUOTES, 'UTF-8') . ').'];
    }

    $uniqueName = uniqid('file_', true) . '.' . $ext;
    $targetDir = BASE_DIR . trim($subFolder, '/\\') . DIRECTORY_SEPARATOR;

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $targetFile = $targetDir . $uniqueName;

    if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['success' => false, 'error' => 'Gagal memindahkan file yang diunggah.'];
    }
    @chmod($targetFile, 0666);

    $relativePath = trim(str_replace('\\', '/', $subFolder), '/') . '/' . $uniqueName;
    return ['success' => true, 'filename' => $relativePath];
}

// Helper to get Profile Data
function getProfileData() {
    $db = Database::getConnection();
    $stmt = $db->query("SELECT * FROM profile WHERE id = 1 LIMIT 1");
    $profile = $stmt->fetch();
    if (!$profile) {
        return [
            'full_name' => 'SAPUTRA',
            'title' => 'Programmer / Welder',
            'phone' => '+628 1277 900210',
            'email' => 'masputra1404@gmail.com',
            'address' => 'Griya Laguna Mas c3 18',
            'linkedin' => 'https://www.linkedin.com/in/masputra1404/',
            'github' => 'https://github.com/masputra1404',
            'bio' => 'Saya adalah seorang Welder Kombinasi dengan pengalaman dalam proses GTAW, SMAW, GMAW, dan FCAW...',
            'avatar' => 'assets/images/avatar.png'
        ];
    }
    return $profile;
}
