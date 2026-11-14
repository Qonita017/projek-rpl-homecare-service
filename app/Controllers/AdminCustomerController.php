<?php
class AdminCustomerController extends Controller {
    
    /** List all users (all roles: CUSTOMER, TEKNISI, ADMIN) */
    public function index() {
        $userModel = $this->model('User');
        $search    = $_GET['search'] ?? '';
        $roleFilter = strtoupper($_GET['role'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = 10;
        $offset = ($page - 1) * $limit;

        // If role filter is applied, fetch by that role; else fetch all
        if ($roleFilter && in_array($roleFilter, ['CUSTOMER', 'TEKNISI', 'ADMIN'])) {
            $total     = $userModel->getTotalByRole($roleFilter, $search);
            $customers = $userModel->getAllByRole($roleFilter, $search, $limit, $offset);
        } else {
            $total     = $userModel->getTotalAllUsers($search);
            $customers = $userModel->getAllUsers($search, $limit, $offset);
        }

        $data = [
            'title'       => 'Daftar Akun Pengguna - Admin',
            'customers'   => $customers,
            'search'      => $search,
            'roleFilter'  => $roleFilter,
            'page'        => $page,
            'total_pages' => $total > 0 ? ceil($total / $limit) : 1
        ];

        $this->view('admin/customer/index', $data);
    }
    
    public function create() {
        $this->view('admin/customer/create', ['title' => 'Tambah Akun Pengguna']);
    }
    
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userModel = $this->model('User');
            
            $name     = trim($_POST['name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $phone    = trim($_POST['phone'] ?? '');
            $address  = trim($_POST['address'] ?? '');
            $role     = strtoupper($_POST['role'] ?? 'CUSTOMER');

            // Validate role
            if (!in_array($role, ['CUSTOMER', 'TEKNISI', 'ADMIN'])) {
                $role = 'CUSTOMER';
            }
            
            if ($userModel->findByEmail($email)) {
                $_SESSION['error'] = "Email sudah digunakan.";
                header("Location: " . BASE_URL . "/admin/customer/create");
                exit;
            }
            
            $userModel->create([
                'name'     => $name,
                'email'    => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role'     => $role,
                'phone'    => $phone,
                'address'  => $address
            ]);
            
            $_SESSION['success'] = "Akun berhasil ditambahkan.";
            header("Location: " . BASE_URL . "/admin/customer");
            exit;
        }
    }
    
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: " . BASE_URL . "/admin/customer");
            exit;
        }
        
        $userModel = $this->model('User');
        $customer  = $userModel->findById($id);
        
        if (!$customer) {
            header("Location: " . BASE_URL . "/admin/customer");
            exit;
        }
        
        $this->view('admin/customer/edit', ['title' => 'Edit Akun Pengguna', 'customer' => $customer]);
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id        = $_POST['id'] ?? null;
            $userModel = $this->model('User');

            $role = strtoupper($_POST['role'] ?? 'CUSTOMER');
            if (!in_array($role, ['CUSTOMER', 'TEKNISI', 'ADMIN'])) {
                $role = 'CUSTOMER';
            }
            
            $data = [
                'name'    => trim($_POST['name'] ?? ''),
                'role'    => $role,
                'phone'   => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
            ];

            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            
            $userModel->update($id, $data);
            $_SESSION['success'] = "Akun berhasil diupdate.";
            header("Location: " . BASE_URL . "/admin/customer");
            exit;
        }
    }
    
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id        = $_POST['id'] ?? null;
            $userModel = $this->model('User');
            
            if ($userModel->delete($id)) {
                $_SESSION['success'] = "Akun berhasil dihapus.";
            } else {
                $_SESSION['error'] = "Gagal menghapus akun.";
            }
            
            header("Location: " . BASE_URL . "/admin/customer");
            exit;
        }
    }
}
