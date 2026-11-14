<?php
/**
 * NotificationController
 * Menangani endpoint AJAX untuk manajemen notifikasi customer
 */
class NotificationController extends Controller {

    /**
     * API: Ambil notifikasi + jumlah unread (JSON) — polling dari header
     */
    public function fetch() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['unread' => 0, 'notifications' => []]);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $notifModel = $this->model('Notification');

        $notifications = $notifModel->getByUser($userId, 20);
        $unread        = $notifModel->countUnread($userId);

        // Format waktu relatif
        foreach ($notifications as &$n) {
            $n['time_ago'] = $this->timeAgo($n['created_at']);
        }

        echo json_encode([
            'unread'        => $unread,
            'notifications' => $notifications,
        ]);
        exit;
    }

    /**
     * API: Tandai SEMUA notifikasi sebagai dibaca (POST)
     */
    public function markAllRead() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $notifModel = $this->model('Notification');
        $notifModel->markAllAsRead($userId);

        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * API: Tandai SATU notifikasi sebagai dibaca (POST)
     */
    public function markRead() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $notifId = (int)($_POST['id'] ?? 0);

        if (!$notifId) {
            echo json_encode(['success' => false]);
            exit;
        }

        $notifModel = $this->model('Notification');
        $notifModel->markAsRead($notifId, $userId);

        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * API: Hapus satu notifikasi (POST)
     */
    public function delete() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            exit;
        }

        $userId  = (int)$_SESSION['user_id'];
        $notifId = (int)($_POST['id'] ?? 0);

        if (!$notifId) {
            echo json_encode(['success' => false]);
            exit;
        }

        $notifModel = $this->model('Notification');
        $notifModel->deleteOne($notifId, $userId);

        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Helper: Ubah timestamp ke waktu relatif (x menit lalu, dll)
     */
    private function timeAgo($datetime) {
        $now  = new DateTime();
        $past = new DateTime($datetime);
        $diff = $now->diff($past);

        if ($diff->y > 0) return $diff->y . ' tahun lalu';
        if ($diff->m > 0) return $diff->m . ' bulan lalu';
        if ($diff->d > 0) return $diff->d . ' hari lalu';
        if ($diff->h > 0) return $diff->h . ' jam lalu';
        if ($diff->i > 0) return $diff->i . ' menit lalu';
        return 'Baru saja';
    }
}
