<?php
// Migration script: Add payments table and payment_preference to service_orders
// Run via CLI: php database/add_payments.php
// Or via Browser: http://localhost/jasa%20peralatan%20runah%20tangga/database/add_payments.php

$host   = 'localhost';
$user   = 'root';
$pass   = '';
$dbname = 'homecare_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Create payments table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL UNIQUE,
            customer_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_method ENUM('CASH', 'TRANSFER') NOT NULL,
            payment_provider VARCHAR(50) NULL COMMENT 'BRIMO, BCA, MANDIRI, SHOPEEPAY, DANA, GOPAY, LAINNYA',
            sender_name VARCHAR(100) NULL,
            sender_account VARCHAR(100) NULL,
            proof_image VARCHAR(255) NULL,
            notes TEXT NULL,
            status ENUM('PAID', 'PENDING', 'REJECTED') DEFAULT 'PAID',
            paid_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES service_orders(id) ON DELETE CASCADE,
            FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_order (order_id),
            INDEX idx_customer (customer_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 2. Add payment_preference to service_orders if it does not exist
    $checkCol = $pdo->query("SHOW COLUMNS FROM service_orders LIKE 'payment_preference'");
    if ($checkCol->rowCount() === 0) {
        $pdo->exec("ALTER TABLE service_orders ADD COLUMN payment_preference VARCHAR(50) NULL AFTER final_cost");
    }

    echo "<div style='font-family:sans-serif;padding:30px;'>";
    echo "<h2 style='color:green;'>✅ Tabel <code>payments</code> dan kolom <code>payment_preference</code> berhasil dibuat!</h2>";
    echo "<p>Sistem pembayaran (Cash & Transfer M-Banking) telah siap digunakan.</p>";
    echo "<a href='../'>← Kembali ke Aplikasi</a>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='font-family:sans-serif;padding:30px;'>";
    echo "<h2 style='color:red;'>❌ Gagal migrasi pembayaran</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
