<?php
class Middleware {
    public static function handle($middlewareString) {
        $parts = explode(':', $middlewareString);
        $name = $parts[0];
        $params = isset($parts[1]) ? explode('|', $parts[1]) : [];

        switch ($name) {
            case 'auth':
                if (!isset($_SESSION['user_id'])) {
                    $_SESSION['flash_error'] = "Silakan login terlebih dahulu.";
                    header('Location: ' . BASE_URL . '/login');
                    exit;
                }
                break;
            
            case 'guest':
                if (isset($_SESSION['user_id'])) {
                    // Redirect based on role
                    $role = $_SESSION['user_role'] ?? '';
                    if ($role === 'ADMIN') header('Location: ' . BASE_URL . '/admin/dashboard');
                    elseif ($role === 'CUSTOMER') header('Location: ' . BASE_URL . '/customer/dashboard');
                    elseif ($role === 'TEKNISI') header('Location: ' . BASE_URL . '/technician/dashboard');
                    else header('Location: ' . BASE_URL . '/');
                    exit;
                }
                break;
            
            case 'role':
                if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $params)) {
                    $_SESSION['flash_error'] = "Anda tidak memiliki akses ke halaman ini.";
                    // Redirect back or to home
                    header('Location: ' . BASE_URL . '/');
                    exit;
                }
                break;
        }
    }
}
