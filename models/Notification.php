<?php
class Notification extends Model {
    protected $table = 'notifications';

    public function getUserNotifications($userId, $limit = 20) {
        return $this->rawQuery("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?", [$userId, $limit]);
    }

    public function getUnreadCount($userId) {
        return $this->count("user_id = ? AND is_read = 0", [$userId]);
    }

    public function createNotification($userId, $title, $message, $type = 'info', $link = '') {
        return $this->create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markAsRead($id, $userId) {
        $this->rawExecute("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$id, $userId]);
    }

    public function markAllRead($userId) {
        $this->rawExecute("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [$userId]);
    }
}
