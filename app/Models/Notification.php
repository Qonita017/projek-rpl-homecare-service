<?php
/**
 * Notification Model
 * Mengelola notifikasi untuk customer berdasarkan perubahan status pekerjaan servis
 */
class Notification extends Model {

    /**
     * Kirim notifikasi ke user (customer)
     */
    public function create($userId, $orderId, $type, $title, $message) {
        $pdo = $this->getPDO();
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, order_id, type, title, message, is_read, created_at)
            VALUES (:user_id, :order_id, :type, :title, :message, 0, NOW())
        ");
        return $stmt->execute([
            ':user_id'  => $userId,
            ':order_id' => $orderId,
            ':type'     => $type,
            ':title'    => $title,
            ':message'  => $message,
        ]);
    }

    /**
     * Ambil semua notifikasi milik user (terbaru dulu), limit 20
     */
    public function getByUser($userId, $limit = 20) {
        $pdo = $this->getPDO();
        $stmt = $pdo->prepare("
            SELECT n.*, so.id AS service_order_id
            FROM notifications n
            LEFT JOIN service_orders so ON n.order_id = so.id
            WHERE n.user_id = :user_id
            ORDER BY n.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit',   $limit,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Hitung notifikasi yang belum dibaca milik user
     */
    public function countUnread($userId) {
        $pdo  = $this->getPDO();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM notifications
            WHERE user_id = :user_id AND is_read = 0
        ");
        $stmt->execute([':user_id' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca
     */
    public function markAsRead($notificationId, $userId) {
        $pdo  = $this->getPDO();
        $stmt = $pdo->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE id = :id AND user_id = :user_id
        ");
        return $stmt->execute([':id' => $notificationId, ':user_id' => $userId]);
    }

    /**
     * Tandai SEMUA notifikasi user sebagai sudah dibaca
     */
    public function markAllAsRead($userId) {
        $pdo  = $this->getPDO();
        $stmt = $pdo->prepare("
            UPDATE notifications SET is_read = 1
            WHERE user_id = :user_id AND is_read = 0
        ");
        return $stmt->execute([':user_id' => $userId]);
    }

    /**
     * Hapus notifikasi tertentu milik user
     */
    public function deleteOne($notificationId, $userId) {
        $pdo  = $this->getPDO();
        $stmt = $pdo->prepare("
            DELETE FROM notifications
            WHERE id = :id AND user_id = :user_id
        ");
        return $stmt->execute([':id' => $notificationId, ':user_id' => $userId]);
    }
}
