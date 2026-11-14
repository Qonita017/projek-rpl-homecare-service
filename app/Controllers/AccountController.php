<?php
class AccountController extends Controller {

    public function index() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $userModel = $this->model('User');
        $user = $userModel->findById($userId);

        if (!$user) {
            $_SESSION['flash_error'] = "Data pengguna tidak ditemukan.";
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $technician = null;
        $stats = [];

        if ($user['role'] === 'TEKNISI') {
            $techModel = $this->model('Technician');
            $technician = $techModel->findByUserId($userId);

            $orderModel = $this->model('ServiceOrder');
            if ($technician) {
                $stats = $orderModel->getTechnicianStats($technician['id']);
            }
        } elseif ($user['role'] === 'CUSTOMER') {
            $orderModel = $this->model('ServiceOrder');
            $stats = $orderModel->getCustomerStats($userId);
        } elseif ($user['role'] === 'ADMIN') {
            $orderModel = $this->model('ServiceOrder');
            $stats = $orderModel->getAdminStats();
        }

        $data = [
            'title' => 'Pengaturan Akun & Profil - HomeCare Service',
            'user' => $user,
            'technician' => $technician,
            'stats' => $stats,
            'user_name' => $user['name'],
            'user_role' => $user['role']
        ];

        $this->view('account/settings', $data);
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/account/settings");
            exit;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $userModel = $this->model('User');
        $user = $userModel->findById($userId);

        if (!$user) {
            $_SESSION['flash_error'] = "Akun tidak ditemukan.";
            header("Location: " . BASE_URL . "/account/settings");
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        // Validation
        if (empty($name) || mb_strlen($name) < 2) {
            $_SESSION['flash_error'] = "Nama lengkap wajib diisi minimal 2 karakter.";
            header("Location: " . BASE_URL . "/account/settings");
            exit;
        }

        if (!empty($phone) && !preg_match('/^[0-9+\-\s()]{8,20}$/', $phone)) {
            $_SESSION['flash_error'] = "Format nomor telepon tidak valid.";
            header("Location: " . BASE_URL . "/account/settings");
            exit;
        }

        // Update User Profile
        $userModel->updateProfile($userId, [
            'name' => $name,
            'phone' => $phone,
            'address' => $address
        ]);

        // If Technician, update technician specific attributes
        if ($user['role'] === 'TEKNISI') {
            $specialization = trim($_POST['specialization'] ?? '');
            $experience = (int)($_POST['experience'] ?? 0);
            $availability = isset($_POST['availability']) ? (int)$_POST['availability'] : 1;

            if ($experience < 0) $experience = 0;

            $techModel = $this->model('Technician');
            $existingTech = $techModel->findByUserId($userId);

            if ($existingTech) {
                $techModel->updateByUserId($userId, [
                    'specialization' => $specialization,
                    'experience' => $experience,
                    'availability' => $availability
                ]);
            } else {
                $techId = $techModel->create($userId);
                $techModel->update($techId, [
                    'specialization' => $specialization,
                    'experience' => $experience,
                    'availability' => $availability
                ]);
            }
        }

        // Update session user name
        $_SESSION['user_name'] = $name;
        $_SESSION['flash_success'] = "Profil akun Anda berhasil diperbarui!";

        header("Location: " . BASE_URL . "/account/settings");
        exit;
    }

    public function updatePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "/account/settings");
            exit;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        $userModel = $this->model('User');
        $user = $userModel->findById($userId);

        if (!$user) {
            $_SESSION['flash_error'] = "Akun tidak ditemukan.";
            header("Location: " . BASE_URL . "/account/settings");
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['flash_error'] = "Semua kolom password wajib diisi.";
            header("Location: " . BASE_URL . "/account/settings#security");
            exit;
        }

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            $_SESSION['flash_error'] = "Password saat ini salah. Silakan coba lagi.";
            header("Location: " . BASE_URL . "/account/settings#security");
            exit;
        }

        // Validate new password length
        if (strlen($newPassword) < 6) {
            $_SESSION['flash_error'] = "Password baru minimal harus 6 karakter.";
            header("Location: " . BASE_URL . "/account/settings#security");
            exit;
        }

        // Validate password confirmation match
        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error'] = "Konfirmasi password baru tidak cocok.";
            header("Location: " . BASE_URL . "/account/settings#security");
            exit;
        }

        // Prevent reusing same password
        if (password_verify($newPassword, $user['password'])) {
            $_SESSION['flash_error'] = "Password baru tidak boleh sama dengan password saat ini.";
            header("Location: " . BASE_URL . "/account/settings#security");
            exit;
        }

        // Hash and update
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $userModel->updatePassword($userId, $hashedPassword);

        $_SESSION['flash_success'] = "Password akun berhasil diubah! Gunakan password baru untuk login berikutnya.";
        header("Location: " . BASE_URL . "/account/settings#security");
        exit;
    }
}
