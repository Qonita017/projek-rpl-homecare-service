<?php

class PaymentController extends Controller {

    /**
     * Memproses pembayaran dari customer (Cash atau Transfer M-Banking)
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/customer/order");
            exit;
        }

        $customerId = $_SESSION['user_id'] ?? null;
        if (!$customerId) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
        $paymentMethod = trim($_POST['payment_method'] ?? '');
        $paymentProviderChoice = trim($_POST['payment_provider'] ?? '');
        $customProvider = trim($_POST['custom_provider'] ?? '');
        $senderName = trim($_POST['sender_name'] ?? '');
        $senderAccount = trim($_POST['sender_account'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if (!$orderId) {
            $_SESSION['error'] = "ID Pesanan tidak valid.";
            header("Location: " . BASE_URL . "/customer/order");
            exit;
        }

        $orderModel = $this->model('ServiceOrder');
        $paymentModel = $this->model('Payment');

        // 1. Verifikasi kepemilikan dan status pesanan
        $order = $orderModel->findByIdAndCustomer($orderId, $customerId);
        if (!$order) {
            $_SESSION['error'] = "Akses ditolak! Pesanan tidak ditemukan atau bukan milik Anda.";
            header("Location: " . BASE_URL . "/customer/order");
            exit;
        }

        if ($order['status'] !== 'COMPLETED') {
            $_SESSION['error'] = "Pembayaran hanya dapat dilakukan setelah status servis telah SELESAI (COMPLETED) dan biaya akhir telah ditetapkan oleh teknisi.";
            header("Location: " . BASE_URL . "/customer/order/detail?id=" . $orderId);
            exit;
        }

        // 2. Cek apakah sudah pernah dibayar
        $existingPayment = $paymentModel->findByOrderId($orderId);
        if ($existingPayment) {
            $_SESSION['error'] = "Pesanan ini sudah pernah dibayar sebelumnya.";
            header("Location: " . BASE_URL . "/customer/order/detail?id=" . $orderId);
            exit;
        }

        // 3. Validasi Metode Pembayaran
        $errors = [];
        if (!in_array($paymentMethod, ['CASH', 'TRANSFER'])) {
            $errors[] = "Silakan pilih metode pembayaran yang valid (Tunai / Cash atau Transfer M-Banking).";
        }

        $finalProvider = null;
        if ($paymentMethod === 'TRANSFER') {
            $allowedProviders = ['BRIMO', 'BCA', 'MANDIRI', 'SHOPEEPAY', 'DANA', 'GOPAY', 'OTHER'];
            if (empty($paymentProviderChoice) || !in_array($paymentProviderChoice, $allowedProviders)) {
                $errors[] = "Silakan pilih salah satu layanan M-Banking / E-Wallet (BRIMO, BCA, MANDIRI, SHOPEEPAY, DANA, GOPAY, atau Lainnya).";
            } elseif ($paymentProviderChoice === 'OTHER') {
                if (empty($customProvider)) {
                    $errors[] = "Silakan masukkan nama bank atau e-wallet lainnya yang Anda gunakan.";
                } else {
                    $finalProvider = $customProvider;
                }
            } else {
                $finalProvider = $paymentProviderChoice;
            }
        } else {
            $finalProvider = 'CASH';
        }

        // 4. Handle Upload Bukti Transfer (Opsional namun disarankan untuk TRANSFER)
        $proofPath = null;
        if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
            $fileInfo = pathinfo($_FILES['proof_image']['name']);
            $extension = strtolower($fileInfo['extension'] ?? '');

            if (!in_array($extension, $allowedExtensions)) {
                $errors[] = "Format bukti transfer tidak valid. Gunakan format JPG, PNG, WEBP, atau PDF.";
            } elseif ($_FILES['proof_image']['size'] > 5 * 1024 * 1024) {
                $errors[] = "Ukuran file bukti transfer maksimal adalah 5MB.";
            } else {
                $uploadDir = __DIR__ . '/../../public/uploads/payments/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filename = 'pay_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                $targetFile = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $targetFile)) {
                    $proofPath = 'uploads/payments/' . $filename;
                } else {
                    $errors[] = "Gagal mengunggah foto bukti pembayaran.";
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header("Location: " . BASE_URL . "/customer/order/detail?id=" . $orderId . "#paymentCard");
            exit;
        }

        // Hitung total tagihan dari final_cost (atau estimated_cost)
        $amount = !empty($order['final_cost']) ? (float)$order['final_cost'] : ((float)$order['estimated_cost'] ?? 0);

        // 5. Simpan Pembayaran
        $paymentData = [
            'order_id'         => $orderId,
            'customer_id'      => $customerId,
            'amount'           => $amount,
            'payment_method'   => $paymentMethod,
            'payment_provider' => $finalProvider,
            'sender_name'      => !empty($senderName) ? $senderName : null,
            'sender_account'   => !empty($senderAccount) ? $senderAccount : null,
            'proof_image'      => $proofPath,
            'notes'            => !empty($notes) ? $notes : null,
            'status'           => 'PAID',
            'paid_at'          => date('Y-m-d H:i:s')
        ];

        $paymentId = $paymentModel->createPayment($paymentData);

        if ($paymentId) {
            $methodLabel = ($paymentMethod === 'CASH') ? 'Tunai (Cash)' : 'Transfer M-Banking (' . htmlspecialchars($finalProvider) . ')';
            $orderNum = '#ORD-' . str_pad($orderId, 4, '0', STR_PAD_LEFT);
            $amountFmt = 'Rp ' . number_format($amount, 0, ',', '.');

            $_SESSION['success'] = "Pembayaran sebesar <strong>{$amountFmt}</strong> via <strong>{$methodLabel}</strong> berhasil dikonfirmasi dan dicatat!";

            // Kirim notifikasi pembayaran ke customer
            $notifModel = $this->model('Notification');
            $notifModel->create(
                $customerId,
                $orderId,
                'PAYMENT_SUCCESS',
                '💳 Pembayaran Berhasil — ' . $orderNum,
                "Pembayaran sebesar <strong>{$amountFmt}</strong> melalui <strong>{$methodLabel}</strong> untuk pesanan {$orderNum} telah berhasil kami terima. Terima kasih telah menggunakan HomeCare Service!"
            );

        } else {
            $_SESSION['error'] = "Terjadi kesalahan sistem saat memproses pembayaran. Silakan coba kembali.";
        }

        header("Location: " . BASE_URL . "/customer/order/detail?id=" . $orderId . "#paymentCard");
        exit;
    }

    /**
     * Cetak atau lihat kuitansi resmi pembayaran
     */
    public function receipt() {
        $customerId = $_SESSION['user_id'] ?? null;
        if (!$customerId) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
        if (!$orderId) {
            header("Location: " . BASE_URL . "/customer/order");
            exit;
        }

        $orderModel = $this->model('ServiceOrder');
        $order = $orderModel->findByIdAndCustomer($orderId, $customerId);

        if (!$order || empty($order['payment_id'])) {
            $_SESSION['error'] = "Bukti pembayaran tidak ditemukan.";
            header("Location: " . BASE_URL . "/customer/order/detail?id=" . $orderId);
            exit;
        }

        $data = [
            'title' => 'Kuitansi Pembayaran #ORD-' . str_pad($order['id'], 4, '0', STR_PAD_LEFT) . ' - HomeCare Service',
            'order' => $order
        ];

        $this->view('customer/order/receipt', $data);
    }

    /**
     * Halaman Menu Pembayaran Customer (Daftar Tagihan & Riwayat Pembayaran)
     */
    public function index() {
        $customerId = $_SESSION['user_id'] ?? null;
        if (!$customerId) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $paymentModel = $this->model('Payment');

        $unpaidOrders = $paymentModel->getUnpaidOrdersByCustomer($customerId);
        $paidOrders   = $paymentModel->getPaidOrdersByCustomer($customerId);

        $data = [
            'title'        => 'Pembayaran & Tagihan Saya - HomeCare Service',
            'unpaidOrders' => $unpaidOrders,
            'paidOrders'   => $paidOrders
        ];

        $this->view('customer/payment/index', $data);
    }
}
