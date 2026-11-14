<?php
class AuthController extends Controller {
    
    public function login() {
        $this->view('auth/login', ['title' => 'Login - HomeCare Service'], 'auth_layout');
    }

    public function register() {
        $this->view('auth/register', ['title' => 'Register - HomeCare Service'], 'auth_layout');
    }
    
    public function loginPost() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['flash_error'] = "Email dan Password wajib diisi.";
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $userModel = $this->model('User');
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            // Valid credentials
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'ADMIN') {
                header("Location: " . BASE_URL . "/admin/dashboard");
            } elseif ($user['role'] === 'TEKNISI') {
                header("Location: " . BASE_URL . "/technician/dashboard");
            } else {
                header("Location: " . BASE_URL . "/customer/dashboard");
            }
            exit;
        } else {
            $_SESSION['flash_error'] = "Email atau Password salah.";
            header("Location: " . BASE_URL . "/login");
            exit;
        }
    }
    
    public function registerPost() {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'customer'; // Default

        // Validate
        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['flash_error'] = "Semua field wajib diisi.";
            header("Location: " . BASE_URL . "/register");
            exit;
        }

        if (strlen($password) < 8) {
            $_SESSION['flash_error'] = "Password minimal 8 karakter.";
            header("Location: " . BASE_URL . "/register");
            exit;
        }
        
        $roleEnum = match($role) {
            'technician' => 'TEKNISI',
            'admin'      => 'ADMIN',
            default      => 'CUSTOMER',
        };

        $userModel = $this->model('User');
        
        // Check if email exists
        if ($userModel->findByEmail($email)) {
            $_SESSION['flash_error'] = "Email sudah terdaftar. Silakan gunakan email lain.";
            header("Location: " . BASE_URL . "/register");
            exit;
        }

        // Use Database connection for transaction
        $db = new Database();
        $pdo = $db->getConnection();
        
        try {
            $pdo->beginTransaction();
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $userId = $userModel->create([
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => $roleEnum
            ]);

            if ($roleEnum === 'TEKNISI') {
                $techModel = $this->model('Technician');
                $techModel->create($userId);
            }

            $pdo->commit();
            
            $_SESSION['flash_success'] = "Registrasi berhasil! Silakan login.";
            header("Location: " . BASE_URL . "/login");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['flash_error'] = "Terjadi kesalahan sistem: " . $e->getMessage();
            header("Location: " . BASE_URL . "/register");
            exit;
        }
    }

    public function logout() {
        session_destroy();
        header("Location: " . BASE_URL . "/login");
        exit;
    }
}
