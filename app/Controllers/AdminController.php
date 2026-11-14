<?php
class AdminController extends Controller {
    public function dashboard() {
        $orderModel = $this->model('ServiceOrder');
        $settingModel = $this->model('Setting');
        
        $aiProvider = $settingModel->get('ai_provider', defined('AI_PROVIDER') ? AI_PROVIDER : 'nvidia');
        $aiModel    = $settingModel->get('ai_model', defined('AI_MODEL') ? AI_MODEL : 'meta/llama-3.2-11b-vision-instruct');

        $data = [
            'title' => 'Admin Dashboard - HomeCare Service',
            'user_name' => $_SESSION['user_name'] ?? 'Admin',
            'stats' => $orderModel->getAdminStats(),
            'recent_orders' => $orderModel->getRecentAdminOrders(),
            'ai_provider' => $aiProvider,
            'ai_model' => $aiModel
        ];
        $this->view('admin/dashboard', $data);
    }
}
