<?php
class CustomerOrderController extends Controller {

    public function index() {
        $customerId = $_SESSION['user_id'];
        $orderModel = $this->model('ServiceOrder');
        
        $search = trim($_GET['search'] ?? '');
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 8;
        $offset = ($page - 1) * $limit;
        
        $total = $orderModel->getTotalOrdersByCustomer($customerId, 'ACTIVE', $search);
        $orders = $orderModel->getOrdersByCustomer($customerId, 'ACTIVE', $search, $limit, $offset);
        
        $data = [
            'title' => 'Pesanan Saya - HomeCare Service',
            'orders' => $orders,
            'search' => $search,
            'page' => $page,
            'total_pages' => max(1, ceil($total / $limit)),
            'total_count' => $total
        ];
        
        $this->view('customer/order/index', $data);
    }

    public function history() {
        $customerId = $_SESSION['user_id'];
        $orderModel = $this->model('ServiceOrder');
        
        $search = trim($_GET['search'] ?? '');
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 8;
        $offset = ($page - 1) * $limit;
        
        $total = $orderModel->getTotalOrdersByCustomer($customerId, 'HISTORY', $search);
        $orders = $orderModel->getOrdersByCustomer($customerId, 'HISTORY', $search, $limit, $offset);
        
        $data = [
            'title' => 'Riwayat Pesanan - HomeCare Service',
            'orders' => $orders,
            'search' => $search,
            'page' => $page,
            'total_pages' => max(1, ceil($total / $limit)),
            'total_count' => $total
        ];
        
        $this->view('customer/order/history', $data);
    }

    public function create() {
        $userModel = $this->model('User');
        $equipmentModel = $this->model('Equipment');
        
        $user = $userModel->findById($_SESSION['user_id']);
        $equipments = $equipmentModel->getAllList();
        
        // Check if there is an AI diagnosis passed from AI Assistant
        $aiDiagnosis = $_SESSION['ai_diagnosis'] ?? ($_GET['ai_diagnosis'] ?? '');
        $aiEstimatedCost = $_SESSION['ai_estimated_cost'] ?? ($_GET['estimated_cost'] ?? '');
        $initialComplaint = $_SESSION['ai_complaint'] ?? ($_GET['complaint'] ?? '');
        $initialEquipmentName = $_SESSION['ai_equipment'] ?? ($_GET['equipment'] ?? '');
        $initialBrand = $_SESSION['ai_brand'] ?? ($_GET['brand'] ?? '');

        unset($_SESSION['ai_diagnosis'], $_SESSION['ai_estimated_cost'], $_SESSION['ai_complaint'], $_SESSION['ai_equipment'], $_SESSION['ai_brand']);

        $data = [
            'title' => 'Buat Pesanan Servis - HomeCare Service',
            'user' => $user,
            'equipments' => $equipments,
            'old' => $_SESSION['old_input'] ?? [],
            'ai_diagnosis' => $aiDiagnosis,
            'ai_estimated_cost' => $aiEstimatedCost,
            'initial_complaint' => $initialComplaint,
            'initial_equipment_name' => $initialEquipmentName,
            'initial_brand' => $initialBrand
        ];
        
        unset($_SESSION['old_input']);
        
        $this->view('customer/order/create', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/customer/order/create");
            exit;
        }

        $customerId = $_SESSION['user_id'];
        $equipmentChoice = $_POST['equipment_choice'] ?? '';
        $customName = trim($_POST['custom_name'] ?? '');
        $customBrand = trim($_POST['custom_brand'] ?? '');
        $complaint = trim($_POST['complaint'] ?? '');
        $aiDiagnosis = trim($_POST['ai_diagnosis'] ?? '');
        $serviceDate = trim($_POST['service_date'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        $_SESSION['old_input'] = $_POST;

        // 1. Validation
        $errors = [];
        if (empty($equipmentChoice)) {
            $errors[] = "Silakan pilih atau tentukan peralatan yang akan diservis.";
        } elseif ($equipmentChoice === 'new' && (empty($customName) || empty($customBrand))) {
            $errors[] = "Nama jenis peralatan dan merk wajib diisi untuk peralatan baru.";
        }

        if (empty($complaint) || strlen($complaint) < 5) {
            $errors[] = "Keluhan/masalah kerusakan wajib diisi (minimal 5 karakter).";
        }

        if (empty($serviceDate)) {
            $errors[] = "Tanggal dan waktu rencana servis wajib dipilih.";
        }

        if (empty($address)) {
            $errors[] = "Alamat lengkap lokasi servis wajib diisi.";
        }

        if (empty($phone)) {
            $errors[] = "Nomor telepon/WhatsApp aktif wajib diisi.";
        }

        // Handle Photo Upload (if provided)
        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $fileInfo = pathinfo($_FILES['photo']['name']);
            $extension = strtolower($fileInfo['extension'] ?? '');
            
            if (!in_array($extension, $allowedExtensions)) {
                $errors[] = "Format foto tidak valid. Gunakan format JPG, PNG, atau WEBP.";
            } elseif ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
                $errors[] = "Ukuran file foto maksimal adalah 5MB.";
            } else {
                $uploadDir = __DIR__ . '/../../public/uploads/damage_photos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $filename = 'dmg_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                $targetFile = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
                    $photoPath = 'uploads/damage_photos/' . $filename;
                } else {
                    $errors[] = "Gagal mengunggah foto kerusakan.";
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header("Location: " . BASE_URL . "/customer/order/create");
            exit;
        }

        // 2. Equipment resolution
        $equipmentModel = $this->model('Equipment');
        if ($equipmentChoice === 'new') {
            $equipmentId = $equipmentModel->findOrCreate($customName, $customBrand, 'Peralatan Rumah Tangga', 'Didaftarkan saat pemesanan servis');
        } else {
            $equipmentId = (int)$equipmentChoice;
        }

        // Update user phone & address if provided
        $userModel = $this->model('User');
        $currentUser = $userModel->findById($customerId);
        if ($currentUser) {
            $userModel->update($customerId, [
                'name' => $currentUser['name'],
                'phone' => $phone,
                'address' => $address
            ]);
        }

        $paymentPreference = trim($_POST['payment_preference'] ?? '');

        // 3. Save Service Order
        $orderModel = $this->model('ServiceOrder');
        $orderData = [
            'customer_id' => $customerId,
            'equipment_id' => $equipmentId,
            'complaint' => $complaint,
            'photo' => $photoPath,
            'ai_diagnosis' => !empty($aiDiagnosis) ? $aiDiagnosis : null,
            'service_date' => date('Y-m-d H:i:s', strtotime($serviceDate)),
            'address' => $address,
            'payment_preference' => !empty($paymentPreference) ? $paymentPreference : null
        ];

        $newOrderId = $orderModel->createOrder($orderData);

        unset($_SESSION['old_input']);

        if ($newOrderId) {
            $_SESSION['success'] = "Pesanan servis berhasil dibuat! <strong>Order ID: #" . htmlspecialchars($newOrderId) . "</strong>. Status pesanan Anda saat ini adalah <strong>PENDING</strong>.";

            // Kirim notifikasi Telegram MTProto ke nomor HP pelanggan
            try {
                require_once __DIR__ . '/../Services/TelegramService.php';
                $equipmentObj = $equipmentModel->findById($equipmentId);
                $equipmentTitle = ($equipmentChoice === 'new') 
                    ? ($customName . (!empty($customBrand) ? ' ' . $customBrand : '')) 
                    : ($equipmentObj['name'] ?? 'Peralatan Rumah Tangga' . (!empty($equipmentObj['brand']) ? ' ' . $equipmentObj['brand'] : ''));

                TelegramService::notifyOrderCreated([
                    'phone' => $phone,
                    'customer_name' => $currentUser['name'] ?? 'Pelanggan',
                    'order_id' => $newOrderId,
                    'equipment_name' => $equipmentTitle,
                    'complaint' => $complaint,
                    'service_date' => $serviceDate,
                    'address' => $address
                ]);
            } catch (\Throwable $e) {
                error_log("[CustomerOrderController] Telegram Notification Error: " . $e->getMessage());
            }

            header("Location: " . BASE_URL . "/customer/order/detail?id=" . $newOrderId);
            exit;
        } else {
            $_SESSION['error'] = "Terjadi kesalahan saat menyimpan pesanan. Silakan coba lagi.";
            header("Location: " . BASE_URL . "/customer/order/create");
            exit;
        }
    }

    public function show() {
        $customerId = $_SESSION['user_id'];
        $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if (!$orderId) {
            $_SESSION['error'] = "ID Pesanan tidak valid.";
            header("Location: " . BASE_URL . "/customer/order");
            exit;
        }

        $orderModel = $this->model('ServiceOrder');
        $order = $orderModel->findByIdAndCustomer($orderId, $customerId);

        // Security check: Customer can only view their own order
        if (!$order) {
            $_SESSION['error'] = "Akses ditolak! Pesanan tidak ditemukan atau Anda tidak memiliki izin untuk melihat pesanan ini.";
            header("Location: " . BASE_URL . "/customer/order");
            exit;
        }

        $data = [
            'title' => 'Detail Pesanan #' . $order['id'] . ' - HomeCare Service',
            'order' => $order
        ];

        $this->view('customer/order/show', $data);
    }
}
