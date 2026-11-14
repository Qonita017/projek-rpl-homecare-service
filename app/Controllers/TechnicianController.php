<?php
class TechnicianController extends Controller {
    public function dashboard() {
        $orderModel = $this->model('ServiceOrder');
        $techModel = $this->model('Technician');
        
        $userId = $_SESSION['user_id'];
        $technician = $techModel->findByUserId($userId);
        $technicianId = $technician ? $technician['id'] : null;
        
        $data = [
            'title' => 'Technician Dashboard - HomeCare Service',
            'user_name' => $_SESSION['user_name'] ?? 'Teknisi',
            'technician' => $technician,
            'stats' => $technicianId ? $orderModel->getTechnicianStats($technicianId) : [],
            'recent_orders' => $technicianId ? $orderModel->getRecentTechnicianOrders($technicianId) : []
        ];
        $this->view('technician/dashboard', $data);
    }
}
