<?php
require_once '../app/Services/AIService.php';

class AIChatController extends Controller {

    public function index() {
        $customerId = $_SESSION['user_id'] ?? null;
        if (!$customerId) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $sessionModel = $this->model('ChatSession');
        $messageModel = $this->model('ChatMessage');

        $sessions = $sessionModel->getSessionsByCustomer($customerId);
        $activeSessionId = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
        $activeSession = null;
        $messages = [];

        if ($activeSessionId > 0) {
            $activeSession = $sessionModel->findByIdAndCustomer($activeSessionId, $customerId);
        }

        // Default to most recent session if no active session specified
        if (!$activeSession && !empty($sessions)) {
            $activeSession = $sessions[0];
            $activeSessionId = $activeSession['id'];
        }

        if ($activeSession) {
            $messages = $messageModel->getMessagesBySession($activeSessionId);
        }

        $data = [
            'title' => 'AI Chat Assistant - HomeCare Service',
            'sessions' => $sessions,
            'activeSession' => $activeSession,
            'activeSessionId' => $activeSessionId,
            'messages' => $messages
        ];

        $this->view('customer/ai_chat/index', $data);
    }

    public function createSession() {
        $customerId = $_SESSION['user_id'] ?? null;
        if (!$customerId) {
            if ($this->isAjax()) {
                echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
                exit;
            }
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $sessionModel = $this->model('ChatSession');
        $sessionId = $sessionModel->createSession($customerId, 'Percakapan Baru');

        if ($this->isAjax()) {
            echo json_encode(['success' => true, 'session_id' => $sessionId]);
            exit;
        }

        header("Location: " . BASE_URL . "/customer/ai-chat?session_id=" . $sessionId);
        exit;
    }

    public function sendMessage() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
            exit;
        }

        $customerId = $_SESSION['user_id'] ?? null;
        if (!$customerId) {
            echo json_encode(['success' => false, 'message' => 'Sesi login telah berakhir. Silakan login kembali.']);
            exit;
        }

        $sessionId = isset($_POST['session_id']) ? (int)$_POST['session_id'] : 0;
        $userMessage = trim($_POST['message'] ?? '');

        if (empty($userMessage)) {
            echo json_encode(['success' => false, 'message' => 'Pesan tidak boleh kosong.']);
            exit;
        }

        if (mb_strlen($userMessage) > 1000) {
            echo json_encode(['success' => false, 'message' => 'Pesan terlalu panjang (maksimal 1000 karakter).']);
            exit;
        }

        $sessionModel = $this->model('ChatSession');
        $messageModel = $this->model('ChatMessage');

        // Create new session if none active
        if ($sessionId <= 0) {
            $sessionId = $sessionModel->createSession($customerId, mb_strimwidth($userMessage, 0, 35, '...'));
        }

        // Validate session ownership
        $session = $sessionModel->findByIdAndCustomer($sessionId, $customerId);
        if (!$session) {
            echo json_encode(['success' => false, 'message' => 'Akses ditolak! Sesi chat tidak ditemukan.']);
            exit;
        }

        // 1. Save USER message
        $userMsgId = $messageModel->addMessage($sessionId, 'USER', $userMessage);

        // Update session title if it's still default 'Percakapan Baru'
        if ($session['title'] === 'Percakapan Baru' || $messageModel->countMessagesBySession($sessionId) <= 2) {
            $newTitle = mb_strimwidth($userMessage, 0, 35, '...');
            $sessionModel->updateTitle($sessionId, $customerId, $newTitle);
        }

        // 2. Retrieve recent messages context (last 8 turns)
        $contextMessages = $messageModel->getLastMessagesBySession($sessionId, 8);

        // 3. Call AI Service
        try {
            $aiService = new AIService();
            $aiResult = $aiService->chat($contextMessages, $customerId);
            $rawReply = $aiResult['message'] ?? 'Maaf, terjadi masalah saat memproses tanggapan.';

            // Parse for Technician Service Order Recommendation Tag
            $recommendOrder = false;
            $equipment = '';
            $complaint = '';

            if (preg_match('/\[\[RECOMMEND_SERVICE_ORDER:\s*equipment=([^|]*)\|complaint=(.*?)\]\]/i', $rawReply, $matches)) {
                $recommendOrder = true;
                $equipment = trim($matches[1]);
                $complaint = trim(rawurldecode($matches[2]));
                // Strip the tag marker from display message
                $displayReply = trim(str_replace($matches[0], '', $rawReply));
            } else {
                $displayReply = $rawReply;
            }

            // 4. Save AI Response Message
            $aiMsgId = $messageModel->addMessage($sessionId, 'AI', $displayReply);

            // Fetch updated title
            $updatedSession = $sessionModel->findByIdAndCustomer($sessionId, $customerId);

            echo json_encode([
                'success' => true,
                'session_id' => $sessionId,
                'session_title' => $updatedSession['title'] ?? 'Percakapan',
                'user_message' => [
                    'id' => $userMsgId,
                    'sender' => 'USER',
                    'message' => htmlspecialchars($userMessage),
                    'time' => date('H:i')
                ],
                'ai_message' => [
                    'id' => $aiMsgId,
                    'sender' => 'AI',
                    'message' => $displayReply,
                    'time' => date('H:i')
                ],
                'recommend_order' => $recommendOrder,
                'order_params' => [
                    'equipment' => $equipment,
                    'complaint' => $complaint
                ]
            ]);
            exit;

        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Maaf, AI sedang mengalami gangguan. Silakan coba beberapa saat lagi.'
            ]);
            exit;
        }
    }

    public function deleteSession() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
            exit;
        }

        $customerId = $_SESSION['user_id'] ?? null;
        $sessionId = isset($_POST['session_id']) ? (int)$_POST['session_id'] : 0;

        if (!$customerId || !$sessionId) {
            echo json_encode(['success' => false, 'message' => 'Parameter tidak valid.']);
            exit;
        }

        $sessionModel = $this->model('ChatSession');
        $deleted = $sessionModel->deleteSession($sessionId, $customerId);

        if ($deleted) {
            echo json_encode(['success' => true, 'message' => 'Sesi percakapan berhasil dihapus.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus percakapan.']);
        }
        exit;
    }

    private function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}
