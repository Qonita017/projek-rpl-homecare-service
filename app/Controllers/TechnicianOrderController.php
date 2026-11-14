<?php
class TechnicianOrderController extends Controller {

    private function getTechnician() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) return null;

        $techModel = $this->model('Technician');
        $technician = $techModel->findByUserId($userId);
        
        if (!$technician) {
            $techModel->create($userId);
            $technician = $techModel->findByUserId($userId);
        }
        
        return $technician;
    }

    public function index() {
        $technician = $this->getTechnician();
        $technicianId = $technician['id'];

        $orderModel = $this->model('ServiceOrder');

        $status = $_GET['status'] ?? 'ALL';
        $search = trim($_GET['search'] ?? '');
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $total = $orderModel->getTotalOrdersByTechnician($technicianId, $status, $search);
        $orders = $orderModel->getOrdersByTechnician($technicianId, $status, $search, $limit, $offset);

        // Status counts for tabs
        $stats = [
            'ALL' => $orderModel->getTotalOrdersByTechnician($technicianId, 'ALL'),
            'ASSIGNED' => $orderModel->getTotalOrdersByTechnician($technicianId, 'ASSIGNED'),
            'ACCEPTED' => $orderModel->getTotalOrdersByTechnician($technicianId, 'ACCEPTED'),
            'ON_THE_WAY' => $orderModel->getTotalOrdersByTechnician($technicianId, 'ON_THE_WAY'),
            'IN_PROGRESS' => $orderModel->getTotalOrdersByTechnician($technicianId, 'IN_PROGRESS'),
            'COMPLETED' => $orderModel->getTotalOrdersByTechnician($technicianId, 'COMPLETED')
        ];

        $data = [
            'title' => 'Pekerjaan Saya - HomeCare Service',
            'orders' => $orders,
            'status' => $status,
            'search' => $search,
            'page' => $page,
            'total_pages' => max(1, ceil($total / $limit)),
            'total_count' => $total,
            'stats' => $stats,
            'technician' => $technician
        ];

        $this->view('technician/order/index', $data);
    }

    public function show() {
        $technician = $this->getTechnician();
        $technicianId = $technician['id'];

        $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$orderId) {
            $_SESSION['error'] = "ID Pekerjaan tidak valid.";
            header("Location: " . BASE_URL . "/technician/jobs");
            exit;
        }

        $orderModel = $this->model('ServiceOrder');
        // Backend Authorization: Strictly load only if assigned to this technician
        $order = $orderModel->findByIdAndTechnician($orderId, $technicianId);

        if (!$order) {
            $_SESSION['error'] = "Akses ditolak! Pekerjaan tidak ditemukan atau Anda tidak memiliki izin untuk mengakses pekerjaan ini.";
            header("Location: " . BASE_URL . "/technician/jobs");
            exit;
        }

        $data = [
            'title' => 'Detail Pekerjaan #' . $order['id'] . ' - HomeCare Service',
            'order' => $order,
            'technician' => $technician
        ];

        $this->view('technician/order/show', $data);
    }

    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/technician/jobs");
            exit;
        }

        $technician = $this->getTechnician();
        $technicianId = $technician['id'];

        $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
        $newStatus = trim($_POST['new_status'] ?? '');

        $orderModel = $this->model('ServiceOrder');
        // Backend Authorization: Verify order ownership
        $order = $orderModel->findByIdAndTechnician($orderId, $technicianId);

        if (!$order) {
            $_SESSION['error'] = "Akses ditolak! Anda tidak memiliki izin untuk mengubah status pekerjaan ini.";
            header("Location: " . BASE_URL . "/technician/jobs");
            exit;
        }

        // Validate allowed status transitions
        $currentStatus = $order['status'];
        $isValidTransition = false;
        $statusMessage = '';

        // Data notifikasi customer per transisi status
        $notifData = null;

        if ($currentStatus === 'ASSIGNED' && $newStatus === 'ACCEPTED') {
            $isValidTransition = true;
            $statusMessage = 'Pekerjaan berhasil diterima (ACCEPTED). Silakan persiapkan peralatan servis Anda.';
            $notifData = [
                'type'    => 'ACCEPTED',
                'title'   => '✅ Teknisi Menerima Pesanan Anda',
                'message' => 'Teknisi <strong>' . htmlspecialchars($technician['name'] ?? 'Teknisi') . '</strong> telah menerima pesanan servis <strong>#ORD-' . str_pad($orderId, 4, '0', STR_PAD_LEFT) . '</strong> untuk peralatan <strong>' . htmlspecialchars($order['equipment_name'] ?? '') . '</strong>. Teknisi akan segera menghubungi Anda dan berangkat ke lokasi.',
            ];
        } elseif ($currentStatus === 'ACCEPTED' && $newStatus === 'ON_THE_WAY') {
            $isValidTransition = true;
            $statusMessage = 'Status diperbarui menjadi Menuju Lokasi (ON_THE_WAY). Berhati-hatilah di jalan!';
            $notifData = [
                'type'    => 'ON_THE_WAY',
                'title'   => '🚗 Teknisi Sedang Menuju Lokasi Anda',
                'message' => 'Teknisi <strong>' . htmlspecialchars($technician['name'] ?? 'Teknisi') . '</strong> sedang dalam perjalanan menuju rumah Anda untuk pesanan <strong>#ORD-' . str_pad($orderId, 4, '0', STR_PAD_LEFT) . '</strong>. Pastikan Anda berada di rumah dan siap menerima kedatangan teknisi.',
            ];
        } elseif ($currentStatus === 'ON_THE_WAY' && $newStatus === 'IN_PROGRESS') {
            $isValidTransition = true;
            $statusMessage = 'Status diperbarui menjadi Sedang Dikerjakan (IN_PROGRESS). Selamat bekerja!';
            $notifData = [
                'type'    => 'IN_PROGRESS',
                'title'   => '🔧 Servis Sedang Dikerjakan',
                'message' => 'Teknisi <strong>' . htmlspecialchars($technician['name'] ?? 'Teknisi') . '</strong> telah tiba dan mulai mengerjakan servis <strong>' . htmlspecialchars($order['equipment_name'] ?? '') . '</strong> (Pesanan <strong>#ORD-' . str_pad($orderId, 4, '0', STR_PAD_LEFT) . '</strong>). Mohon tunggu sebentar hingga pekerjaan selesai.',
            ];
        }

        if (!$isValidTransition) {
            $_SESSION['error'] = "Perubahan status dari '{$currentStatus}' ke '{$newStatus}' tidak valid.";
            header("Location: " . BASE_URL . "/technician/jobs/detail?id=" . $orderId);
            exit;
        }

        $updated = $orderModel->updateStatusByTechnician($orderId, $technicianId, $newStatus);

        if ($updated) {
            $_SESSION['success'] = $statusMessage;

            // Kirim notifikasi ke customer jika ada data notif
            if ($notifData && !empty($order['customer_id'])) {
                $notifModel = $this->model('Notification');
                $notifModel->create(
                    $order['customer_id'],
                    $orderId,
                    $notifData['type'],
                    $notifData['title'],
                    $notifData['message']
                );
            }

            // Notifikasi WhatsApp Otomatis via Fonnte: Status ON_THE_WAY
            if ($newStatus === 'ON_THE_WAY') {
                $this->sendFonnteNotification(
                    $orderId,
                    'SERVICE_ON_THE_WAY',
                    $order,
                    $technician
                );
            }

            // Notifikasi Telegram Otomatis: Status "Sedang Diperbaiki" (IN_PROGRESS)
            if ($newStatus === 'IN_PROGRESS') {
                try {
                    require_once __DIR__ . '/../Services/TelegramService.php';
                    $customerName   = $order['customer_name'] ?? 'Pelanggan';
                    $equipmentName  = $order['equipment_name'] ?? 'Peralatan';
                    $customerPhone  = $order['customer_phone'] ?? null;
                    $technicianName = $technician['name'] ?? 'Teknisi';

                    TelegramService::notifyInProgress([
                        'phone' => $customerPhone,
                        'customer_name' => $customerName,
                        'order_id' => $orderId,
                        'equipment_name' => $equipmentName,
                        'technician_name' => $technicianName
                    ]);
                } catch (\Throwable $e) {
                    error_log("[TechnicianOrderController] Telegram Notification Error: " . $e->getMessage());
                }

                // Notifikasi WhatsApp Otomatis via Fonnte: Status IN_PROGRESS
                $this->sendFonnteNotification(
                    $orderId,
                    'SERVICE_IN_PROGRESS',
                    $order,
                    $technician
                );
            }
        } else {
            $_SESSION['error'] = "Gagal memperbarui status pekerjaan.";
        }

        header("Location: " . BASE_URL . "/technician/jobs/detail?id=" . $orderId);
        exit;
    }

    public function complete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/technician/jobs");
            exit;
        }

        $technician = $this->getTechnician();
        $technicianId = $technician['id'];

        $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
        $inspectionResult = trim($_POST['inspection_result'] ?? '');
        $repairAction = trim($_POST['repair_action'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $costInput = trim($_POST['cost'] ?? '0');
        $cost = (float)str_replace(['.', ','], ['', '.'], $costInput);

        $orderModel = $this->model('ServiceOrder');
        // Backend Authorization: Verify order ownership
        $order = $orderModel->findByIdAndTechnician($orderId, $technicianId);

        if (!$order) {
            $_SESSION['error'] = "Akses ditolak! Anda tidak memiliki izin untuk menyelesaikan pekerjaan ini.";
            header("Location: " . BASE_URL . "/technician/jobs");
            exit;
        }

        if ($order['status'] !== 'IN_PROGRESS') {
            $_SESSION['error'] = "Pekerjaan hanya dapat diselesaikan jika statusnya saat ini adalah 'IN_PROGRESS'.";
            header("Location: " . BASE_URL . "/technician/jobs/detail?id=" . $orderId);
            exit;
        }

        // Validation of Service Report fields
        $errors = [];
        if (empty($inspectionResult) || strlen($inspectionResult) < 5) {
            $errors[] = "Hasil pemeriksaan wajib diisi (minimal 5 karakter).";
        }
        if (empty($repairAction) || strlen($repairAction) < 5) {
            $errors[] = "Tindakan perbaikan wajib diisi (minimal 5 karakter).";
        }
        if ($cost < 0) {
            $errors[] = "Biaya servis tidak boleh negatif.";
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header("Location: " . BASE_URL . "/technician/jobs/detail?id=" . $orderId);
            exit;
        }

        $reportData = [
            'inspection_result' => $inspectionResult,
            'repair_action' => $repairAction,
            'notes' => $notes,
            'cost' => $cost
        ];

        $completed = $orderModel->completeServiceOrderByTechnician($orderId, $technicianId, $reportData);

        if ($completed) {
            $_SESSION['success'] = "Pekerjaan servis berhasil diselesaikan! <strong>Status: COMPLETED</strong>. Laporan perbaikan dan total biaya akhir sebesar <strong>Rp " . number_format($cost, 0, ',', '.') . "</strong> telah berhasil disimpan.";

            // Kirim notifikasi COMPLETED ke customer
            if (!empty($order['customer_id'])) {
                $orderNum  = '#ORD-' . str_pad($orderId, 4, '0', STR_PAD_LEFT);
                $costFmt   = 'Rp ' . number_format($cost, 0, ',', '.');
                $techName  = htmlspecialchars($technician['name'] ?? 'Teknisi');
                $equipName = htmlspecialchars($order['equipment_name'] ?? 'Peralatan');

                $notifTitle   = '🎉 Servis Selesai — ' . $orderNum;
                $notifMessage = 'Pesanan servis ' . $orderNum . ' untuk <strong>' . $equipName . '</strong> telah selesai dikerjakan oleh teknisi <strong>' . $techName . '</strong>.<br>'
                    . '<strong>Hasil Pemeriksaan:</strong> ' . htmlspecialchars($inspectionResult) . '<br>'
                    . '<strong>Tindakan Perbaikan:</strong> ' . htmlspecialchars($repairAction) . '<br>'
                    . (!empty($notes) ? '<strong>Catatan Teknisi:</strong> ' . htmlspecialchars($notes) . '<br>' : '')
                    . '<strong>💰 Total Tagihan: ' . $costFmt . '</strong>';

                $notifModel = $this->model('Notification');
                $notifModel->create(
                    $order['customer_id'],
                    $orderId,
                    'COMPLETED',
                    $notifTitle,
                    $notifMessage
                );
            }

            // Notifikasi Telegram Otomatis: Status "Selesai Diperbaiki" (COMPLETED)
            try {
                require_once __DIR__ . '/../Services/TelegramService.php';
                $customerName   = $order['customer_name'] ?? 'Pelanggan';
                $equipmentName  = $order['equipment_name'] ?? 'Peralatan';
                $customerPhone  = $order['customer_phone'] ?? null;
                $technicianName = $technician['name'] ?? 'Teknisi';

                TelegramService::notifyCompleted([
                    'phone'             => $customerPhone,
                    'customer_name'     => $customerName,
                    'order_id'          => $orderId,
                    'equipment_name'    => $equipmentName,
                    'technician_name'   => $technicianName,
                    'inspection_result' => $inspectionResult,
                    'repair_action'     => $repairAction,
                    'notes'             => $notes,
                    'total_cost'        => $cost
                ]);
            } catch (\Throwable $e) {
                error_log("[TechnicianOrderController] Telegram Notification Error: " . $e->getMessage());
            }

            // Notifikasi WhatsApp Otomatis via Fonnte: Status COMPLETED
            $this->sendFonnteNotification(
                $orderId,
                'SERVICE_COMPLETED',
                $order,
                $technician,
                [
                    'inspection_result' => $inspectionResult,
                    'repair_action'     => $repairAction,
                    'notes'             => $notes,
                    'total_cost'        => $cost,
                ]
            );
        } else {
            $_SESSION['error'] = "Terjadi kesalahan saat menyelesaikan pekerjaan dan menyimpan laporan servis.";
        }

        header("Location: " . BASE_URL . "/technician/jobs/detail?id=" . $orderId);
        exit;
    }

    // =========================================================================
    // Private helper: Kirim notifikasi WhatsApp via Fonnte + catat ke log
    // =========================================================================

    /**
     * Kirim notifikasi WhatsApp melalui Fonnte dan catat hasilnya.
     *
     * Metode ini TIDAK melempar exception ke luar — kegagalan Fonnte
     * dicatat di log dan tidak mempengaruhi alur utama perubahan status.
     *
     * @param int    $orderId          ID service order
     * @param string $notifType        'SERVICE_ON_THE_WAY' | 'SERVICE_IN_PROGRESS' | 'SERVICE_COMPLETED'
     * @param array  $order            Data order (dari findByIdAndTechnician)
     * @param array  $technician       Data teknisi
     * @param array  $extra            Data tambahan untuk COMPLETED (biaya, laporan)
     */
    private function sendFonnteNotification(
        int $orderId,
        string $notifType,
        array $order,
        array $technician,
        array $extra = []
    ): void {
        try {
            require_once __DIR__ . '/../Services/FonnteService.php';

            $waNotifModel = $this->model('WhatsappNotification');

            // Idempotency: jangan kirim ulang jika sudah pernah berhasil
            if ($waNotifModel->isAlreadySent($orderId, $notifType)) {
                error_log("[FonnteService] Notifikasi {$notifType} untuk order #{$orderId} sudah pernah dikirim. Dilewati.");
                return;
            }

            $customerPhone  = $order['customer_phone']  ?? '';
            $customerName   = $order['customer_name']   ?? 'Pelanggan';
            $equipmentName  = $order['equipment_name']  ?? 'Peralatan';
            $technicianName = $technician['name']        ?? 'Teknisi';

            // Normalisasi nomor sebelum membangun pesan
            $normalizedPhone = FonnteService::normalizePhone($customerPhone);

            if ($notifType === 'SERVICE_ON_THE_WAY') {
                $result = FonnteService::notifyOnTheWay([
                    'phone'           => $customerPhone,
                    'customer_name'   => $customerName,
                    'order_id'        => $orderId,
                    'equipment_name'  => $equipmentName,
                    'technician_name' => $technicianName,
                ]);
            } elseif ($notifType === 'SERVICE_IN_PROGRESS') {
                $result = FonnteService::notifyInProgress([
                    'phone'           => $customerPhone,
                    'customer_name'   => $customerName,
                    'order_id'        => $orderId,
                    'equipment_name'  => $equipmentName,
                    'technician_name' => $technicianName,
                ]);
            } else { // SERVICE_COMPLETED
                $result = FonnteService::notifyCompleted([
                    'phone'              => $customerPhone,
                    'customer_name'      => $customerName,
                    'order_id'           => $orderId,
                    'equipment_name'     => $equipmentName,
                    'technician_name'    => $technicianName,
                    'inspection_result'  => $extra['inspection_result'] ?? '',
                    'repair_action'      => $extra['repair_action']     ?? '',
                    'notes'              => $extra['notes']             ?? '',
                    'total_cost'         => $extra['total_cost']        ?? 0,
                ]);
            }

            // Catat hasil ke tabel whatsapp_notifications
            $waNotifModel->log([
                'service_order_id'  => $orderId,
                'notification_type' => $notifType,
                'recipient_phone'   => $normalizedPhone ?? ($customerPhone ?: '-'),
                'message'           => $result['message'] ?? ($result['raw_response']['message'] ?? '[pesan telah dikirim]'),
                'delivery_status'   => $result['delivery_status'],
                'provider_msg_id'   => $result['provider_msg_id'] ?? null,
                'error_message'     => $result['error_message']   ?? null,
                'sent_at'           => $result['success'] ? date('Y-m-d H:i:s') : null,
            ]);

            if ($result['success']) {
                error_log("[FonnteService] Notifikasi {$notifType} order #{$orderId} berhasil dikirim ke WA customer.");
            } else {
                error_log("[FonnteService] Notifikasi {$notifType} order #{$orderId} gagal. Status: {$result['delivery_status']}. Error: " . ($result['error_message'] ?? '-'));
            }

        } catch (\Throwable $e) {
            // Kegagalan apapun tidak boleh mengganggu alur utama
            error_log("[TechnicianOrderController] FonnteService Error: " . $e->getMessage());
        }
    }
}

