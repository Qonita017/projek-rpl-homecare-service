<?php
/**
 * WhatsappNotification Model
 *
 * Mengelola log pengiriman notifikasi WhatsApp via Fonnte.
 * Digunakan untuk:
 * - Mencatat setiap percobaan pengiriman
 * - Mencegah duplikasi (idempotency)
 * - Memungkinkan mekanisme kirim ulang bagi admin
 *
 * Mengikuti pola Model yang sudah ada di proyek (extend class Model).
 */
class WhatsappNotification extends Model
{
    /**
     * Cek apakah notifikasi untuk order + type tertentu sudah berhasil dikirim.
     * Digunakan untuk mencegah duplikasi sebelum mencoba pengiriman baru.
     *
     * @param int    $orderId          ID service order
     * @param string $notificationType Tipe: SERVICE_ON_THE_WAY | SERVICE_IN_PROGRESS | SERVICE_COMPLETED
     * @return bool true jika sudah ada record dengan delivery_status = 'SENT'
     */
    public function isAlreadySent(int $orderId, string $notificationType): bool
    {
        $stmt = $this->getPDO()->prepare("
            SELECT COUNT(*) FROM whatsapp_notifications
            WHERE service_order_id  = :order_id
              AND notification_type = :type
              AND delivery_status   = 'SENT'
            LIMIT 1
        ");
        $stmt->execute([
            ':order_id' => $orderId,
            ':type'     => $notificationType,
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Simpan log percobaan pengiriman notifikasi WhatsApp.
     *
     * @param array $data {
     *   service_order_id  int
     *   notification_type string  'SERVICE_ON_THE_WAY' | 'SERVICE_IN_PROGRESS' | 'SERVICE_COMPLETED'
     *   recipient_phone   string
     *   message           string
     *   delivery_status   string  'SENT' | 'FAILED' | 'INVALID_NUMBER' | 'NO_TOKEN'
     *   provider_msg_id   string|null
     *   error_message     string|null
     *   sent_at           string|null  format: 'Y-m-d H:i:s'
     * }
     * @return int|false ID record yang baru dibuat, atau false jika gagal
     */
    public function log(array $data)
    {
        $stmt = $this->getPDO()->prepare("
            INSERT INTO whatsapp_notifications
                (service_order_id, notification_type, recipient_phone, message,
                 provider, provider_msg_id, delivery_status, error_message,
                 attempt_count, created_at, sent_at)
            VALUES
                (:service_order_id, :notification_type, :recipient_phone, :message,
                 'fonnte', :provider_msg_id, :delivery_status, :error_message,
                 1, NOW(), :sent_at)
        ");

        $ok = $stmt->execute([
            ':service_order_id'  => $data['service_order_id'],
            ':notification_type' => $data['notification_type'],
            ':recipient_phone'   => $data['recipient_phone'],
            ':message'           => $data['message'],
            ':provider_msg_id'   => $data['provider_msg_id']   ?? null,
            ':delivery_status'   => $data['delivery_status'],
            ':error_message'     => $data['error_message']     ?? null,
            ':sent_at'           => $data['sent_at']           ?? null,
        ]);

        return $ok ? (int)$this->getPDO()->lastInsertId() : false;
    }

    /**
     * Ambil seluruh log notifikasi untuk satu service order.
     * Berguna untuk tampilan di halaman admin atau detail order.
     *
     * @param int $orderId
     * @return array
     */
    public function getByOrderId(int $orderId): array
    {
        $stmt = $this->getPDO()->prepare("
            SELECT * FROM whatsapp_notifications
            WHERE service_order_id = :order_id
            ORDER BY created_at DESC
        ");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil semua notifikasi yang gagal (untuk mekanisme kirim ulang oleh admin).
     *
     * @param int $limit
     * @return array
     */
    public function getFailedNotifications(int $limit = 50): array
    {
        $stmt = $this->getPDO()->prepare("
            SELECT wn.*, so.customer_id, u.name AS customer_name
            FROM whatsapp_notifications wn
            JOIN service_orders so ON wn.service_order_id = so.id
            JOIN users u ON so.customer_id = u.id
            WHERE wn.delivery_status IN ('FAILED', 'INVALID_NUMBER', 'NO_TOKEN')
            ORDER BY wn.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tambah jumlah percobaan pada log yang sudah ada (untuk kirim ulang).
     *
     * @param int    $logId          ID record di whatsapp_notifications
     * @param string $newStatus      Status baru setelah retry
     * @param string|null $errorMsg  Pesan error baru jika masih gagal
     * @param string|null $msgId     provider_msg_id jika berhasil
     * @return bool
     */
    public function incrementRetry(int $logId, string $newStatus, ?string $errorMsg = null, ?string $msgId = null): bool
    {
        $stmt = $this->getPDO()->prepare("
            UPDATE whatsapp_notifications
            SET delivery_status  = :status,
                error_message    = :error_message,
                provider_msg_id  = COALESCE(:msg_id, provider_msg_id),
                attempt_count    = attempt_count + 1,
                sent_at          = CASE WHEN :status2 = 'SENT' THEN NOW() ELSE sent_at END,
                updated_at       = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            ':status'        => $newStatus,
            ':error_message' => $errorMsg,
            ':msg_id'        => $msgId,
            ':status2'       => $newStatus,
            ':id'            => $logId,
        ]);
    }
}
