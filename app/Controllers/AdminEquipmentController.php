<?php
class AdminEquipmentController extends Controller {
    
    public function index() {
        $eqModel = $this->model('Equipment');
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $total = $eqModel->getTotal($search);
        $equipments = $eqModel->getAll($search, $limit, $offset);
        
        $data = [
            'title' => 'Master Equipment - Admin',
            'equipments' => $equipments,
            'search' => $search,
            'page' => $page,
            'total_pages' => ceil($total / $limit)
        ];
        
        $this->view('admin/equipment/index', $data);
    }
    
    public function create() {
        $this->view('admin/equipment/create', ['title' => 'Tambah Equipment']);
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eqModel = $this->model('Equipment');
            
            $eqModel->create([
                'name' => $_POST['name'] ?? '',
                'brand' => $_POST['brand'] ?? '',
                'category' => $_POST['category'] ?? '',
                'description' => $_POST['description'] ?? '',
                'repair_price' => (float)($_POST['repair_price'] ?? 0)
            ]);
            
            $_SESSION['success'] = "Equipment berhasil ditambahkan.";
            header("Location: " . BASE_URL . "/admin/equipment");
            exit;
        }
    }
    
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: " . BASE_URL . "/admin/equipment");
            exit;
        }
        
        $eqModel = $this->model('Equipment');
        $equipment = $eqModel->findById($id);
        
        if (!$equipment) {
            header("Location: " . BASE_URL . "/admin/equipment");
            exit;
        }
        
        $this->view('admin/equipment/edit', ['title' => 'Edit Equipment', 'equipment' => $equipment]);
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $eqModel = $this->model('Equipment');
            
            $eqModel->update($id, [
                'name' => $_POST['name'] ?? '',
                'brand' => $_POST['brand'] ?? '',
                'category' => $_POST['category'] ?? '',
                'description' => $_POST['description'] ?? '',
                'repair_price' => (float)($_POST['repair_price'] ?? 0)
            ]);
            
            $_SESSION['success'] = "Equipment berhasil diupdate.";
            header("Location: " . BASE_URL . "/admin/equipment");
            exit;
        }
    }
    
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $eqModel = $this->model('Equipment');
            
            if ($eqModel->delete($id)) {
                $_SESSION['success'] = "Equipment berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus equipment.";
            }
            
            header("Location: " . BASE_URL . "/admin/equipment");
            exit;
        }
    }
}
