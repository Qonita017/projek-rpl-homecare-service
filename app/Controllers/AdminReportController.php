<?php

class AdminReportController extends Controller {

    public function index() {
        $reportModel = $this->model('Report');
        $equipmentModel = $this->model('Equipment');
        $technicianModel = $this->model('Technician');

        // Extract filter inputs
        $filters = [
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'status' => $_GET['status'] ?? 'ALL',
            'equipment_id' => $_GET['equipment_id'] ?? 'ALL',
            'technician_id' => $_GET['technician_id'] ?? 'ALL'
        ];

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;

        // Fetch reporting data
        $summary = $reportModel->getSummaryStats($filters);
        $statusDistribution = $reportModel->getOrdersByStatus($filters);
        $equipmentDistribution = $reportModel->getOrdersByEquipment($filters);
        $technicianJobs = $reportModel->getJobsPerTechnician($filters);

        $totalFiltered = $reportModel->getTotalFilteredOrders($filters);
        $orders = $reportModel->getFilteredOrders($filters, $limit, $offset);

        // Fetch master filter options
        $equipments = $equipmentModel->getAllList();
        $technicians = $technicianModel->getAll();

        $data = [
            'title' => 'Laporan & Statistik Service - Admin',
            'filters' => $filters,
            'summary' => $summary,
            'statusDistribution' => $statusDistribution,
            'equipmentDistribution' => $equipmentDistribution,
            'technicianJobs' => $technicianJobs,
            'orders' => $orders,
            'equipments' => $equipments,
            'technicians' => $technicians,
            'page' => $page,
            'total_pages' => max(1, ceil($totalFiltered / $limit)),
            'total_count' => $totalFiltered
        ];

        $this->view('admin/report/index', $data);
    }
}
