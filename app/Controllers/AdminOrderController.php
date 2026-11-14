<?php
class AdminOrderController extends Controller {

    public function index() {
        $orderModel = $this->model('ServiceOrder');
        $technicianModel = $this->model('Technician');

        $status = $_GET['status'] ?? 'ALL';
        $search = trim($_GET['search'] ?? '');
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $total = $orderModel->getTotalOrdersForAdmin($status, $search);
        $orders = $orderModel->getAllOrdersForAdmin($status, $search, $limit, $offset);
        $availableTechnicians = $technicianModel->getAvailableTechnicians();

        // Get status counts for filter badges
        $stats = [
            'ALL' => $orderModel->getTotalOrdersForAdmin('ALL'),
            'PENDING' => $orderModel->getTotalOrdersForAdmin('PENDING'),
            'ASSIGNED' => $orderModel->getTotalOrdersForAdmin('ASSIGNED'),
            'ACCEPTED' => $orderModel->getTotalOrdersForAdmin('ACCEPTED'),
            'ON_THE_WAY' => $orderModel->getTotalOrdersForAdmin('ON_THE_WAY'),
            'IN_PROGRESS' => $orderModel->getTotalOrdersForAdmin('IN_PROGRESS'),
            'COMPLETED' => $orderModel->getTotalOrdersForAdmin('COMPLETED'),
            'CANCELLED' => $orderModel->getTotalOrdersForAdmin('CANCELLED')
        ];

        $data = [
            'title' => 'Daftar Service Orders - Admin',
            'orders' => $orders,
            'status' => $status,
            'search' => $search,
            'page' => $page,
            'total_pages' => max(1, ceil($total / $limit)),
            'total_count' => $total,
            'stats' => $stats,
            'availableTechnicians' => $availableTechnicians
        ];

        $this->view('admin/order/index', $data);
    }

    public function show() {
        $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$orderId) {
            $_SESSION['error'] = "ID Pesanan tidak valid.";
            header("Location: " . BASE_URL . "/admin/orders");
            exit;
        }

        $orderModel = $this->model('ServiceOrder');
        $technicianModel = $this->model('Technician');

        $order = $orderModel->findByIdForAdmin($orderId);
        if (!$order) {
            $_SESSION['error'] = "Data pesanan tidak ditemukan.";
            header("Location: " . BASE_URL . "/admin/orders");
            exit;
        }

        $availableTechnicians = $technicianModel->getAvailableTechnicians();

        $data = [
            'title' => 'Detail Service Order #' . $order['id'] . ' - Admin',
            'order' => $order,
            'availableTechnicians' => $availableTechnicians
        ];

        $this->view('admin/order/show', $data);
    }

    public function assignTechnician() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/admin/orders");
            exit;
        }

        $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
        $technicianId = isset($_POST['technician_id']) ? (int)$_POST['technician_id'] : 0;

        if (!$orderId || !$technicianId) {
            $_SESSION['error'] = "Silakan pilih teknisi yang valid untuk ditugaskan.";
            header("Location: " . BASE_URL . "/admin/orders/detail?id=" . $orderId);
            exit;
        }

        $orderModel = $this->model('ServiceOrder');
        $technicianModel = $this->model('Technician');

        // Check if technician exists and is available
        $technician = $technicianModel->findByIdWithUser($technicianId);
        if (!$technician) {
            $_SESSION['error'] = "Teknisi tidak ditemukan di database.";
            header("Location: " . BASE_URL . "/admin/orders/detail?id=" . $orderId);
            exit;
        }

        if (empty($technician['availability'])) {
            $_SESSION['error'] = "Penugasan gagal! Teknisi " . htmlspecialchars($technician['name']) . " saat ini berstatus TIDAK TERSEDIA (Unavailable).";
            header("Location: " . BASE_URL . "/admin/orders/detail?id=" . $orderId);
            exit;
        }

        // Perform assignment & update status to ASSIGNED
        $updated = $orderModel->assignTechnician($orderId, $technicianId);

        if ($updated) {
            $_SESSION['success'] = "Teknisi <strong>" . htmlspecialchars($technician['name']) . "</strong> berhasil ditugaskan ke Order #{$orderId}! Status pesanan kini menjadi <strong>ASSIGNED</strong>.";
        } else {
            $_SESSION['error'] = "Gagal memperbarui status penugasan teknisi.";
        }

        $redirect = $_POST['redirect'] ?? ('/admin/orders/detail?id=' . $orderId);
        header("Location: " . BASE_URL . $redirect);
        exit;
    }
}
