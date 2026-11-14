<?php
require_once '../app/Services/AIService.php';

class AIDiagnosisController extends Controller {

    public function index() {
        $equipmentModel = $this->model('Equipment');
        $equipments = $equipmentModel->getAllList();

        $data = [
            'title' => 'AI Assistant Diagnosis Kerusakan - HomeCare Service',
            'equipments' => $equipments,
            'is_logged_in' => isset($_SESSION['user_id']),
            'user_role' => $_SESSION['user_role'] ?? 'GUEST'
        ];

        $this->view('customer/ai/index', $data);
    }

    public function diagnose() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                'success' => false,
                'message' => 'Metode request tidak valid.'
            ]);
            exit;
        }

        $equipmentType = trim($_POST['equipment_type'] ?? '');
        $brand = trim($_POST['brand'] ?? '');
        $complaint = trim($_POST['complaint'] ?? '');
        $symptoms = trim($_POST['symptoms'] ?? '');

        if (empty($equipmentType)) {
            echo json_encode([
                'success' => false,
                'message' => 'Silakan pilih atau masukkan jenis peralatan.'
            ]);
            exit;
        }

        if (empty($complaint) || strlen($complaint) < 5) {
            echo json_encode([
                'success' => false,
                'message' => 'Silakan jelaskan keluhan/masalah yang dialami (minimal 5 karakter).'
            ]);
            exit;
        }

        try {
            $aiService = new AIService();
            $result = $aiService->diagnose($equipmentType, $brand, $complaint, $symptoms);

            echo json_encode([
                'success' => true,
                'data' => $result,
                'input' => [
                    'equipment_type' => $equipmentType,
                    'brand' => $brand,
                    'complaint' => $complaint,
                    'symptoms' => $symptoms
                ]
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses diagnosa AI: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    public function proceedToOrder() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/ai-diagnosis");
            exit;
        }

        $_SESSION['ai_diagnosis'] = trim($_POST['raw_diagnosis'] ?? '');
        $_SESSION['ai_complaint'] = trim($_POST['complaint'] ?? '');
        $_SESSION['ai_equipment'] = trim($_POST['equipment_type'] ?? '');
        $_SESSION['ai_brand'] = trim($_POST['brand'] ?? '');
        $_SESSION['ai_estimated_cost'] = trim($_POST['estimated_cost'] ?? '');

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Silakan login terlebih dahulu untuk membuat pesanan servis dengan hasil diagnosa AI.";
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        header("Location: " . BASE_URL . "/customer/order/create");
        exit;
    }
}
