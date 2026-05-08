<?php

class Controller {

    protected function sendNoCacheHeaders() {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    }
    
    protected function view($view, $data = []) {
        if (is_logged_in()) {
            $this->sendNoCacheHeaders();
        }

        extract($data);
        $viewFile = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            throw new Exception("View not found: {$view}");
        }
        ob_start();
        include $viewFile;
        echo ob_get_clean();
    }

    protected function json($data, $statusCode = 200) {
        if (is_logged_in()) {
            $this->sendNoCacheHeaders();
        }

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function requireAuth() {
        if (!is_logged_in()) {
            flash('error', 'Please login to continue.');
            redirect(base_url('login'));
        }

        $this->sendNoCacheHeaders();
    }

    protected function requireRole($role) {
        $this->requireAuth();
        $user = current_user();
        if ($user['role'] !== $role) {
            flash('error', 'You do not have permission to access this page.');
            redirect(base_url());
        }
    }

    protected function requireAdmin() {
        $this->requireRole('admin');
    }

    protected function requireSeller() {
        $this->requireRole('seller');
    }

    protected function requireBuyer() {
        $this->requireAuth();
        $user = current_user();
        if ($user['role'] === 'admin') {
            return;
        }
        if ($user['role'] !== 'buyer') {
            flash('error', 'This feature is only for buyers.');
            redirect(base_url());
        }
    }

    protected function validateCsrf() {
        $token = $_POST['_csrf_token'] ?? '';
        if (!verify_csrf($token)) {
            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
            $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
            $expectsJson = stripos($accept, 'application/json') !== false || strtolower($requestedWith) === 'xmlhttprequest';
            if ($expectsJson) {
                $this->json(['success' => false, 'message' => 'Security token expired. Refresh the page and try again.'], 419);
            }
            flash('error', 'Invalid security token. Please try again.');
            back();
        }
    }

    protected function uploadFile($file, $directory = 'products', $allowedTypes = ['image/jpeg', 'image/png', 'image/webp']) {
        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        
        if (!in_array($file['type'], $allowedTypes)) {
            flash('error', 'Invalid file type. Only JPG, PNG, and WebP are allowed.');
            return false;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            flash('error', 'File too large. Maximum size is 5MB.');
            return false;
        }

        $uploadDir = UPLOAD_PATH . '/' . $directory;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $ext;
        $destination = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $filename;
        }

        return false;
    }

    protected function deleteFile($filename, $directory = 'products') {
        $filepath = UPLOAD_PATH . '/' . $directory . '/' . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    protected function updateCartCount() {
        if (is_logged_in()) {
            $db = getDB();
            $stmt = $db->prepare("SELECT COALESCE(SUM(quantity), 0) as count FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $_SESSION['cart_count'] = (int)$stmt->fetchColumn();
        }
    }

    protected function getStrongPasswordError($password, $minLength = 8) {
        if (strlen($password) < $minLength) {
            return "Password must be at least {$minLength} characters.";
        }
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            return 'Password must be alphanumeric (contain both letters and numbers).';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return 'Password must contain at least one special character.';
        }
        return null;
    }

    protected function logAuditEvent($event, array $context = []) {
        $actor = current_user();
        $payload = [
            'event' => $event,
            'actor_id' => $actor['id'] ?? null,
            'actor_role' => $actor['role'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'path' => $_SERVER['REQUEST_URI'] ?? null,
            'context' => $context,
        ];

        error_log('[AUDIT] ' . json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
