<?php
class AdminTechnicianController extends Controller {
    
    public function index() {
        $techModel = $this->model('Technician');
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $total = $techModel->getTotal($search);
        $technicians = $techModel->getAll($search, $limit, $offset);
        
        $data = [
            'title' => 'Master Teknisi - Admin',
            'technicians' => $technicians,
            'search' => $search,
            'page' => $page,
            'total_pages' => ceil($total / $limit)
        ];
        
        $this->view('admin/technician/index', $data);
    }
    
    public function create() {
        $this->view('admin/technician/create', ['title' => 'Tambah Teknisi']);
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = $this->model('User');
            $techModel = $this->model('Technician');
            
            $email = $_POST['email'] ?? '';
            
            if ($userModel->findByEmail($email)) {
                $_SESSION['error'] = "Email sudah digunakan.";
                header("Location: " . BASE_URL . "/admin/technician/create");
                exit;
            }
            
            $userId = $userModel->create([
                'name' => $_POST['name'] ?? '',
                'email' => $email,
                'password' => password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT),
                'role' => 'TEKNISI',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? ''
            ]);
            
            if ($userId) {
                // Initial technician record
                $techModel->create($userId);
                // Update with specialization
                $techData = $techModel->findByUserId($userId);
                if ($techData) {
                    $techModel->update($techData['id'], [
                        'specialization' => $_POST['specialization'] ?? '',
                        'experience' => $_POST['experience'] ?? 0,
                        'availability' => isset($_POST['availability']) ? 1 : 0
                    ]);
                }
            }
            
            $_SESSION['success'] = "Teknisi berhasil ditambahkan.";
            header("Location: " . BASE_URL . "/admin/technician");
            exit;
        }
    }
    
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: " . BASE_URL . "/admin/technician");
            exit;
        }
        
        $techModel = $this->model('Technician');
        $technician = $techModel->findByIdWithUser($id);
        
        if (!$technician) {
            header("Location: " . BASE_URL . "/admin/technician");
            exit;
        }
        
        $this->view('admin/technician/edit', ['title' => 'Edit Teknisi', 'technician' => $technician]);
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $userId = $_POST['user_id'] ?? null;
            
            $userModel = $this->model('User');
            $techModel = $this->model('Technician');
            
            $userData = [
                'name' => $_POST['name'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'password' => !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null
            ];
            
            $userModel->update($userId, $userData);
            
            $techModel->update($id, [
                'specialization' => $_POST['specialization'] ?? '',
                'experience' => (int)($_POST['experience'] ?? 0),
                'availability' => isset($_POST['availability']) ? 1 : 0
            ]);
            
            $_SESSION['success'] = "Teknisi berhasil diupdate.";
            header("Location: " . BASE_URL . "/admin/technician");
            exit;
        }
    }
    
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? null;
            $userModel = $this->model('User');
            
            // Delete user will cascade delete technician
            if ($userModel->delete($userId)) {
                $_SESSION['success'] = "Teknisi berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus teknisi.";
            }
            
            header("Location: " . BASE_URL . "/admin/technician");
            exit;
        }
    }
}
