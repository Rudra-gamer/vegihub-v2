<?php


function loadEnv($path, $overwrite = false) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = array_map('trim', explode('=', $line, 2));
        $value = trim($value, '"\'');
        if ($overwrite || !array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

loadEnv(dirname(__DIR__) . '/.env');
loadEnv(dirname(__DIR__) . '/.env.local', true);

function env($key, $default = null) {
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false) return $default;
    switch (strtolower($value)) {
        case 'true': return true;
        case 'false': return false;
        case 'null': return null;
    }
    return $value;
}

date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Kolkata'));

function is_https_request() {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }

    if (($_SERVER['SERVER_PORT'] ?? null) == 443) {
        return true;
    }

    return strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

function detect_app_url() {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scheme = is_https_request() ? 'https' : 'http';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = str_replace('\\', '/', dirname($scriptName));
    $basePath = $basePath === '/' ? '' : rtrim($basePath, '/');

    if (substr($basePath, -7) === '/public') {
        $basePath = substr($basePath, 0, -7);
    }

    return $scheme . '://' . $host . $basePath;
}

function resolve_app_url() {
    $configuredUrl = trim((string) env('APP_URL', ''));
    $detectedUrl = detect_app_url();

    if ($configuredUrl === '') {
        return $detectedUrl;
    }

    $configuredHost = parse_url($configuredUrl, PHP_URL_HOST) ?? '';
    $requestHost = $_SERVER['HTTP_HOST'] ?? '';

    if ($configuredHost === '' || $configuredHost === 'localhost') {
        return $detectedUrl;
    }

    if ($requestHost && strcasecmp($configuredHost, $requestHost) !== 0) {
        return $detectedUrl;
    }

    return rtrim($configuredUrl, '/');
}

define('APP_NAME', env('APP_NAME', 'Vegihub'));
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_URL', resolve_app_url());
define('APP_DEBUG', env('APP_DEBUG', false));
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('VIEW_PATH', BASE_PATH . '/views');

function is_production() {
    return strtolower((string) APP_ENV) === 'production';
}

function base_url($path = '') {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

function asset($path) {
    return base_url('public/' . ltrim($path, '/'));
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function back() {
    $referer = $_SERVER['HTTP_REFERER'] ?? base_url();
    redirect($referer);
}

function old($key, $default = '') {
    return $_SESSION['_old_input'][$key] ?? $default;
}

function csrf_token() {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf($token) {
    return isset($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
}

function flash($key, $value = null) {
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
    } else {
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
}

function has_flash($key) {
    return isset($_SESSION['_flash'][$key]);
}

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function format_price($amount) {
    return '₹' . number_format((float)$amount, 2);
}

function time_ago($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

function generate_order_number() {
    return 'VH-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
}

function generate_slug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function is_admin() {
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

function is_seller() {
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'seller';
}

function is_buyer() {
    return is_logged_in() && ($_SESSION['user']['role'] ?? '') === 'buyer';
}

function get_cart_count() {
    return $_SESSION['cart_count'] ?? 0;
}
