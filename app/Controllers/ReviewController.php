<?php

class ReviewController extends Controller {

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
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
        $comment = trim($_POST['comment'] ?? '');

        if (!$orderId) {
            $_SESSION['error'] = "ID Pesanan tidak valid.";
            header("Location: " . BASE_URL . "/customer/order");
            exit;
        }

        $orderModel = $this->model('ServiceOrder');
        $reviewModel = $this->model('Review');

        // 1. Check order ownership & status
        $order = $orderModel->findByIdAndCustomer($orderId, $customerId);
        if (!$order) {
            $_SESSION['error'] = "Akses ditolak! Pesanan tidak ditemukan atau bukan milik Anda.";
            header("Location: " . BASE_URL . "/customer/order");
            exit;
        }

        if ($order['status'] !== 'COMPLETED') {
            $_SESSION['error'] = "Ulasan hanya dapat diberikan jika status pesanan telah SELESAI (COMPLETED).";
            header("Location: " . BASE_URL . "/customer/order/detail?id=" . $orderId);
            exit;
        }

        if (empty($order['technician_id'])) {
            $_SESSION['error'] = "Pesanan ini tidak memiliki data teknisi penanggung jawab.";
            header("Location: " . BASE_URL . "/customer/order/detail?id=" . $orderId);
            exit;
        }

        // 2. Check if already reviewed (One review per order rule)
        $existingReview = $reviewModel->findByOrderId($orderId);
        if ($existingReview) {
            $_SESSION['error'] = "Anda sudah pernah memberikan ulasan untuk pesanan ini.";
            header("Location: " . BASE_URL . "/customer/order/detail?id=" . $orderId);
            exit;
        }

        // 3. Validate rating
        if ($rating < 1 || $rating > 5) {
            $_SESSION['error'] = "Silakan berikan rating bintang antara 1 sampai 5.";
            header("Location: " . BASE_URL . "/customer/order/detail?id=" . $orderId);
            exit;
        }

        // 4. Create review & update technician rating
        $reviewData = [
            'order_id' => $orderId,
            'customer_id' => $customerId,
            'technician_id' => $order['technician_id'],
            'rating' => $rating,
            'comment' => $comment
        ];

        $created = $reviewModel->createReview($reviewData);

        if ($created) {
            $_SESSION['success'] = "Terima kasih! Ulasan dan rating bintang {$rating} untuk teknisi <strong>" . htmlspecialchars($order['technician_name'] ?? 'Teknisi') . "</strong> berhasil disimpan.";
        } else {
            $_SESSION['error'] = "Terjadi kesalahan saat menyimpan ulasan.";
        }

        header("Location: " . BASE_URL . "/customer/order/detail?id=" . $orderId);
        exit;
    }

    public function adminIndex() {
        $reviewModel = $this->model('Review');

        $search = trim($_GET['search'] ?? '');
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $total = $reviewModel->getTotalForAdmin($search);
        $reviews = $reviewModel->getAllForAdmin($search, $limit, $offset);

        $data = [
            'title' => 'Ulasan & Rating Customer - Admin',
            'reviews' => $reviews,
            'search' => $search,
            'page' => $page,
            'total_pages' => max(1, ceil($total / $limit)),
            'total_count' => $total
        ];

        $this->view('admin/review/index', $data);
    }
}
