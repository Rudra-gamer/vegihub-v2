<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        $this->mailer->isSMTP();
        $this->mailer->Host = env('MAIL_HOST', 'smtp.gmail.com');
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = env('MAIL_USERNAME', '');
        $this->mailer->Password = env('MAIL_PASSWORD', '');
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = (int)env('MAIL_PORT', 587);
        
        $this->mailer->setFrom(
            env('MAIL_FROM_ADDRESS', 'noreply@vegihub.com'),
            env('MAIL_FROM_NAME', 'Vegihub')
        );
        
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = 'UTF-8';
    }

    public function sendVerificationCode($toEmail, $toName, $code) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->Subject = 'Verify Your Email - Vegihub';
            
            $this->mailer->Body = $this->getVerificationTemplate($toName, $code);
            $this->mailer->AltBody = "Your Vegihub verification code is: {$code}. This code expires in 10 minutes.";
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    public function sendPasswordReset($toEmail, $toName, $resetCode) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->Subject = 'Reset Your Password - Vegihub';
            
            $this->mailer->Body = $this->getResetTemplate($toName, $resetCode);
            $this->mailer->AltBody = "Your Vegihub password reset code is: {$resetCode}. This code expires in 30 minutes.";
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    public function sendOrderConfirmation($toEmail, $toName, $order) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);
            $this->mailer->Subject = "Order Confirmed #{$order['order_number']} - Vegihub";
            
            $this->mailer->Body = $this->getOrderTemplate($toName, $order);
            $this->mailer->AltBody = "Your order #{$order['order_number']} has been confirmed. Total: ₹{$order['total']}";
            
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    private function getVerificationTemplate($name, $code) {
        return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Inter,Arial,sans-serif;">
<div style="max-width:500px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
    <div style="background:linear-gradient(135deg,#2D6A4F,#52B788);padding:40px 30px;text-align:center;">
        <h1 style="color:#fff;margin:0;font-size:28px;">🌿 Vegihub</h1>
        <p style="color:rgba(255,255,255,0.9);margin:8px 0 0;">Fresh Vegetables, Delivered Fresh</p>
    </div>
    <div style="padding:40px 30px;text-align:center;">
        <h2 style="color:#1B4332;margin:0 0 10px;">Verify Your Email</h2>
        <p style="color:#6c757d;margin:0 0 30px;">Hi ' . e($name) . ', use this code to verify your email:</p>
        <div style="background:linear-gradient(135deg,#f8f9fa,#e9ecef);border-radius:12px;padding:20px;display:inline-block;margin:0 0 30px;">
            <span style="font-size:36px;font-weight:700;color:#2D6A4F;letter-spacing:8px;">' . e($code) . '</span>
        </div>
        <p style="color:#adb5bd;font-size:14px;">This code expires in <strong>10 minutes</strong>.</p>
        <p style="color:#adb5bd;font-size:13px;margin-top:20px;">If you didn\'t create an account, you can safely ignore this email.</p>
    </div>
    <div style="background:#f8f9fa;padding:20px 30px;text-align:center;">
        <p style="color:#adb5bd;font-size:12px;margin:0;">© ' . date('Y') . ' Vegihub. All rights reserved.</p>
    </div>
</div>
</body>
</html>';
    }

    private function getResetTemplate($name, $resetCode) {
        return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Inter,Arial,sans-serif;">
<div style="max-width:500px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
    <div style="background:linear-gradient(135deg,#2D6A4F,#52B788);padding:40px 30px;text-align:center;">
        <h1 style="color:#fff;margin:0;font-size:28px;">🌿 Vegihub</h1>
    </div>
    <div style="padding:40px 30px;text-align:center;">
        <h2 style="color:#1B4332;margin:0 0 10px;">Reset Your Password</h2>
        <p style="color:#6c757d;margin:0 0 30px;">Hi ' . e($name) . ', use this 6-digit code to reset your password:</p>
        <div style="background:linear-gradient(135deg,#f8f9fa,#e9ecef);border-radius:12px;padding:20px;display:inline-block;margin:0 0 30px;">
            <span style="font-size:36px;font-weight:700;color:#2D6A4F;letter-spacing:8px;">' . e($resetCode) . '</span>
        </div>
        <p style="color:#adb5bd;font-size:14px;margin-top:30px;">This code expires in <strong>30 minutes</strong>.</p>
        <p style="color:#adb5bd;font-size:13px;">If you didn\'t request this, you can safely ignore this email.</p>
    </div>
    <div style="background:#f8f9fa;padding:20px 30px;text-align:center;">
        <p style="color:#adb5bd;font-size:12px;margin:0;">© ' . date('Y') . ' Vegihub. All rights reserved.</p>
    </div>
</div>
</body>
</html>';
    }

    private function getOrderTemplate($name, $order) {
        return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Inter,Arial,sans-serif;">
<div style="max-width:550px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">
    <div style="background:linear-gradient(135deg,#2D6A4F,#52B788);padding:40px 30px;text-align:center;">
        <h1 style="color:#fff;margin:0;font-size:28px;">🌿 Vegihub</h1>
        <p style="color:rgba(255,255,255,0.9);margin:8px 0 0;">Order Confirmed!</p>
    </div>
    <div style="padding:40px 30px;">
        <h2 style="color:#1B4332;margin:0 0 5px;">Thank you, ' . e($name) . '! 🎉</h2>
        <p style="color:#6c757d;margin:0 0 25px;">Your order has been placed successfully.</p>
        <div style="background:#f8f9fa;border-radius:12px;padding:20px;margin:0 0 25px;">
            <table style="width:100%;border-collapse:collapse;">
                <tr><td style="color:#6c757d;padding:5px 0;">Order Number</td><td style="text-align:right;font-weight:600;color:#1B4332;">' . e($order['order_number']) . '</td></tr>
                <tr><td style="color:#6c757d;padding:5px 0;">Payment</td><td style="text-align:right;font-weight:600;color:#1B4332;">' . ucfirst(e($order['payment_method'])) . '</td></tr>
                <tr><td style="color:#6c757d;padding:5px 0;border-top:1px solid #dee2e6;padding-top:10px;">Total</td><td style="text-align:right;font-weight:700;color:#2D6A4F;font-size:20px;border-top:1px solid #dee2e6;padding-top:10px;">₹' . number_format($order['total'], 2) . '</td></tr>
            </table>
        </div>
        <a href="' . base_url('orders/' . $order['id']) . '" style="display:block;text-align:center;background:linear-gradient(135deg,#2D6A4F,#52B788);color:#fff;padding:14px;border-radius:8px;text-decoration:none;font-weight:600;">Track Your Order</a>
    </div>
    <div style="background:#f8f9fa;padding:20px 30px;text-align:center;">
        <p style="color:#adb5bd;font-size:12px;margin:0;">© ' . date('Y') . ' Vegihub. All rights reserved.</p>
    </div>
</div>
</body>
</html>';
    }
}
