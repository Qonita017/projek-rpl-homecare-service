<?php
class CustomerController extends Controller {
    public function dashboard() {
        $orderModel = $this->model('ServiceOrder');
        $paymentModel = $this->model('Payment');
        $customerId = $_SESSION['user_id'];
        
        $unpaidOrders = $paymentModel->getUnpaidOrdersByCustomer($customerId);

        $data = [
            'title' => 'Customer Dashboard - HomeCare Service',
            'user_name' => $_SESSION['user_name'] ?? 'Customer',
            'stats' => $orderModel->getCustomerStats($customerId),
            'recent_orders' => $orderModel->getRecentCustomerOrders($customerId),
            'unpaid_orders' => $unpaidOrders
        ];
        $this->view('customer/dashboard', $data);
    }
}
