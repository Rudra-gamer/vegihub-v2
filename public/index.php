<?php
require_once dirname(__DIR__) . '/config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    $sessionLifetime = max(0, (int) env('SESSION_LIFETIME', 120)) * 60;
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', is_https_request() ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', (string) $sessionLifetime);

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => $sessionLifetime,
            'path' => '/',
            'secure' => is_https_request(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    session_start();
}

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/razorpay.php';
require_once dirname(__DIR__) . '/core/Router.php';
require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/core/Model.php';
require_once dirname(__DIR__) . '/core/Mailer.php';
$modelDir = dirname(__DIR__) . '/models/';
if (is_dir($modelDir)) {
    foreach (glob($modelDir . '*.php') as $model) {
        require_once $model;
    }
}
require_once dirname(__DIR__) . '/routes.php';
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

try {
    Router::dispatch($requestUri, $requestMethod);
} catch (Exception $e) {
    if (APP_DEBUG) {
        echo '<h1>Error</h1><pre>' . e($e->getMessage()) . '</pre>';
        echo '<pre>' . e($e->getTraceAsString()) . '</pre>';
    } else {
        error_log($e->getMessage());
        http_response_code(500);
        echo '<h1>Something went wrong</h1><p>Please try again later.</p>';
    }
}
