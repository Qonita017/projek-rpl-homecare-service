<?php
// Script untuk menambahkan tabel notifications ke database yang sudah ada
// Jalankan: http://localhost/jasa%20peralatan%20runah%20tangga/database/add_notifications.php

$host   = 'localhost';
$user   = 'root';
$pass   = '';
$dbname = 'homecare_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            order_id INT NULL,
            type VARCHAR(50) NOT NULL COMMENT 'ACCEPTED, ON_THE_WAY, IN_PROGRESS, COMPLETED',
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (order_id) REFERENCES service_orders(id) ON DELETE SET NULL,
            INDEX idx_user_read (user_id, is_read),
            INDEX idx_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "<div style='font-family:sans-serif;padding:30px;'>";
    echo "<h2 style='color:green;'>✅ Tabel <code>notifications</code> berhasil dibuat!</h2>";
    echo "<p>Sistem notifikasi sudah siap digunakan.</p>";
    echo "<a href='../'>← Kembali ke Aplikasi</a>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='font-family:sans-serif;padding:30px;'>";
    echo "<h2 style='color:red;'>❌ Gagal membuat tabel</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
