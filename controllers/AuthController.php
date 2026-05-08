<?php
class AuthController extends Controller {
    private function throttleKey($action, $identifier) {
        return '_throttle_' . $action . '_' . sha1(strtolower(trim((string)$identifier)));
    }

    private function isThrottled($action, $identifier, $seconds) {
        $key = $this->throttleKey($action, $identifier);
        $lastAt = (int)($_SESSION[$key] ?? 0);
        return $lastAt > 0 && (time() - $lastAt) < $seconds;
    }

    private function markThrottle($action, $identifier) {
        $_SESSION[$this->throttleKey($action, $identifier)] = time();
    }

    private function passwordErrorOrNull($password) {
        return $this->getStrongPasswordError($password, 8);
    }
    
    public function loginForm() {
        if (is_logged_in()) redirect(base_url());
        $this->view('auth/login', ['pageTitle' => 'Sign In - Vegihub', 'extraCss' => ['auth.css'], 'extraJs' => ['auth.js']]);
    }

    public function login() {
        $this->validateCsrf();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            flash('error', 'Please fill in all fields.');
            redirect(base_url('login'));
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            flash('error', 'Invalid email or password.');
            redirect(base_url('login'));
        }

        if ($user['status'] === 'banned') {
            flash('error', 'Your account has been banned. Contact support.');
            redirect(base_url('login'));
        }

        if (!$user['email_verified']) {
            $_SESSION['verify_email'] = $email;
            flash('warning', 'Please verify your email first.');
            redirect(base_url('verify'));
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar' => $user['avatar'],
        ];

        $this->updateCartCount();

        flash('success', 'Welcome back, ' . $user['name'] . '! 🎉');

        switch ($user['role']) {
            case 'admin': redirect(base_url('admin')); break;
            case 'seller': redirect(base_url('seller')); break;
            default: redirect(base_url()); break;
        }
    }

    public function registerForm() {
        if (is_logged_in()) redirect(base_url());
        $this->view('auth/register', ['pageTitle' => 'Create Account - Vegihub', 'extraCss' => ['auth.css'], 'extraJs' => ['auth.js']]);
    }

    public function register() {
        $this->validateCsrf();
        
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'buyer';

        if (!$name || !$email || !$phone || !$password) {
            flash('error', 'All fields are required.');
            $_SESSION['_old_input'] = $_POST;
            redirect(base_url('register'));
        }

        if (!preg_match('/^\d{10}$/', $phone)) {
            flash('error', 'Phone number must be exactly 10 digits.');
            $_SESSION['_old_input'] = $_POST;
            redirect(base_url('register'));
        }
        if (preg_match('/^(\d)\1{9}$/', $phone)) {
            flash('error', 'Invalid phone number (all digits same).');
            $_SESSION['_old_input'] = $_POST;
            redirect(base_url('register'));
        }

        if ($password !== $confirmPassword) {
            flash('error', 'Passwords do not match.');
            $_SESSION['_old_input'] = $_POST;
            redirect(base_url('register'));
        }

        $passwordError = $this->passwordErrorOrNull($password);
        if ($passwordError !== null) {
            flash('error', $passwordError);
            $_SESSION['_old_input'] = $_POST;
            redirect(base_url('register'));
        }

        if (!in_array($role, ['buyer', 'seller'])) $role = 'buyer';

        $userModel = new User();
        
        if ($userModel->findByEmail($email)) {
            flash('error', 'An account with this email already exists.');
            $_SESSION['_old_input'] = $_POST;
            redirect(base_url('register'));
        }

        $userId = $userModel->createUser([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'role' => $role,
        ]);

        $user = $userModel->find($userId);

        try {
            $mailer = new Mailer();
            $mailer->sendVerificationCode($email, $name, $user['verification_code']);
        } catch (Exception $e) {
            error_log("Failed to send verification email: " . $e->getMessage());
        }

        $_SESSION['verify_email'] = $email;
        flash('success', 'Account created! Please check your email for verification code.');
        redirect(base_url('verify'));
    }

    public function verifyForm() {
        $email = $_SESSION['verify_email'] ?? '';
        if (!$email) redirect(base_url('login'));
        $this->view('auth/verify', [
            'pageTitle' => 'Verify Email - Vegihub',
            'email' => $email,
            'extraCss' => ['auth.css'],
            'extraJs' => ['auth.js']
        ]);
    }

    public function verify() {
        $this->validateCsrf();
        $email = $_SESSION['verify_email'] ?? '';
        $code = trim($_POST['verification_code'] ?? '');

        if (!$email || !$code) {
            flash('error', 'Invalid verification attempt.');
            redirect(base_url('verify'));
        }

        $userModel = new User();
        if ($userModel->verifyEmail($email, $code)) {
            unset($_SESSION['verify_email']);
            flash('success', 'Email verified successfully! Please login. 🎉');
            redirect(base_url('login'));
        }

        flash('error', 'Invalid or expired verification code.');
        redirect(base_url('verify'));
    }

    public function resendCode() {
        $this->validateCsrf();
        $email = $_POST['email'] ?? ($_SESSION['verify_email'] ?? '');
        
        if (!$email) {
            $this->json(['success' => false, 'message' => 'Email not found.'], 422);
        }

        if ($this->isThrottled('resend_code', $email, 60)) {
            $this->json(['success' => false, 'message' => 'Please wait before requesting another code.'], 429);
        }

        $userModel = new User();
        $code = $userModel->regenerateVerificationCode($email);
        
        if (!$code) {
            $this->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $user = $userModel->findByEmail($email);
        
        try {
            $mailer = new Mailer();
            $mailer->sendVerificationCode($email, $user['name'], $code);
            $this->markThrottle('resend_code', $email);
            $this->json(['success' => true, 'message' => 'Verification code sent! Check your email.']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to send email. Please try again.']);
        }
    }

    public function forgotForm() {
        $this->view('auth/forgot_password', ['pageTitle' => 'Forgot Password - Vegihub', 'extraCss' => ['auth.css'], 'extraJs' => ['auth.js']]);
    }

    public function forgotPassword() {
        $this->validateCsrf();
        $email = trim($_POST['email'] ?? '');

        if (!$email) {
            flash('error', 'Please enter your email.');
            redirect(base_url('forgot-password'));
        }

        if ($this->isThrottled('forgot_password', $email, 60)) {
            flash('warning', 'Please wait before requesting another reset code.');
            redirect(base_url('forgot-password'));
        }

        $userModel = new User();
        $token = $userModel->generateResetToken($email);
        $_SESSION['reset_email'] = $email;
        $this->markThrottle('forgot_password', $email);

        if ($token) {
            try {
                $user = $userModel->findByEmail($email);
                $mailer = new Mailer();
                $mailer->sendPasswordReset($email, $user['name'], $token);
            } catch (Exception $e) {
                error_log("Reset email failed: " . $e->getMessage());
            }
        }

        flash('success', 'If an account exists with this email, you will receive a 6-digit password reset code.');
        redirect(base_url('reset-password'));
    }

    public function resetForm() {
        $email = $_SESSION['reset_email'] ?? '';
        if (!$email) redirect(base_url('forgot-password'));

        $this->view('auth/reset_password', [
            'pageTitle' => 'Reset Password - Vegihub',
            'email' => $email,
            'extraCss' => ['auth.css'],
            'extraJs' => ['auth.js']
        ]);
    }

    public function resetPassword() {
        $this->validateCsrf();
        $email = trim($_SESSION['reset_email'] ?? '');
        $code = trim($_POST['verification_code'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!$email || !$code) {
            flash('error', 'Invalid or expired reset attempt.');
            redirect(base_url('forgot-password'));
        }

        if ($password !== $confirmPassword) {
            flash('error', 'Passwords do not match.');
            redirect(base_url('reset-password'));
        }

        $passwordError = $this->passwordErrorOrNull($password);
        if ($passwordError !== null) {
            flash('error', $passwordError);
            redirect(base_url('reset-password'));
        }

        $userModel = new User();
        if ($userModel->resetPasswordByEmailCode($email, $code, $password)) {
            unset($_SESSION['reset_email']);
            flash('success', 'Password reset successful! Please login with your new password.');
            redirect(base_url('login'));
        }

        flash('error', 'Invalid or expired reset code.');
        redirect(base_url('reset-password'));
    }

    public function logout() {
        $this->sendNoCacheHeaders();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        session_start();
        flash('success', 'Logged out successfully.');
        redirect(base_url());
    }
}
