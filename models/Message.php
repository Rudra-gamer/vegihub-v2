<?php
class Message extends Model {
    protected $table = 'messages';
    
    public function getUnreadCount() {
        return $this->count("is_read = 0");
    }
}
