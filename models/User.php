<?php
class User extends Model {
    protected $table = 'users';
    
    public function findByEmail($email) {
        return $this->findBy('email', $email);
    }

    public function createUser($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['verification_code'] = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $data['verification_expires'] = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->create($data);
    }

    public function verifyEmail($email, $code) {
        $user = $this->findByEmail($email);
        if (!$user) return false;
        if ($user['verification_code'] !== $code) return false;
        if (strtotime($user['verification_expires']) < time()) return false;
        
        $this->update($user['id'], [
            'email_verified' => 1,
            'verification_code' => null,
            'verification_expires' => null,
        ]);
        return true;
    }

    public function generateResetToken($email) {
        $user = $this->findByEmail($email);
        if (!$user) return false;

        $token = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->update($user['id'], [
            'reset_token' => $token,
            'reset_expires' => date('Y-m-d H:i:s', strtotime('+30 minutes')),
        ]);
        return $token;
    }

    public function findByResetToken($token) {
        $user = $this->findBy('reset_token', $token);
        if (!$user) return null;
        if (strtotime($user['reset_expires']) < time()) return null;
        return $user;
    }

    public function findValidResetUser($email, $code) {
        $user = $this->findByEmail($email);
        if (!$user) return null;
        if (($user['reset_token'] ?? '') !== $code) return null;
        if (empty($user['reset_expires']) || strtotime($user['reset_expires']) < time()) return null;
        return $user;
    }

    public function resetPassword($token, $newPassword) {
        $user = $this->findByResetToken($token);
        if (!$user) return false;
        
        $this->update($user['id'], [
            'password' => password_hash($newPassword, PASSWORD_BCRYPT),
            'reset_token' => null,
            'reset_expires' => null,
        ]);
        return true;
    }

    public function resetPasswordByEmailCode($email, $code, $newPassword) {
        $user = $this->findValidResetUser($email, $code);
        if (!$user) return false;

        $this->update($user['id'], [
            'password' => password_hash($newPassword, PASSWORD_BCRYPT),
            'reset_token' => null,
            'reset_expires' => null,
        ]);
        return true;
    }

    public function regenerateVerificationCode($email) {
        $user = $this->findByEmail($email);
        if (!$user) return false;
        
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->update($user['id'], [
            'verification_code' => $code,
            'verification_expires' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
        ]);
        return $code;
    }

    public function getStatsByRole() {
        return $this->rawQuery("
            SELECT role, COUNT(*) as count
            FROM users
            GROUP BY role
            ORDER BY FIELD(role, 'buyer', 'seller', 'admin')
        ");
    }
}
