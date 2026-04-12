<?php
class Address extends Model {
    protected $table = 'addresses';

    public function getUserAddresses($userId) {
        return $this->rawQuery("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC", [$userId]);
    }

    public function getDefault($userId) {
        $addr = $this->rawQueryOne("SELECT * FROM addresses WHERE user_id = ? AND is_default = 1", [$userId]);
        if (!$addr) {
            $addr = $this->rawQueryOne("SELECT * FROM addresses WHERE user_id = ? ORDER BY created_at DESC LIMIT 1", [$userId]);
        }
        return $addr;
    }

    public function setDefault($userId, $addressId) {
        $this->db->beginTransaction();
        try {
            $address = $this->rawQueryOne(
                "SELECT id FROM addresses WHERE id = ? AND user_id = ?",
                [$addressId, $userId]
            );

            if (!$address) {
                $this->db->rollBack();
                return false;
            }

            $this->rawExecute("UPDATE addresses SET is_default = 0 WHERE user_id = ?", [$userId]);
            $this->rawExecute("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?", [$addressId, $userId]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
