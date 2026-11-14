<?php
/**
 * Migrasi: Tabel whatsapp_notifications
 *
 * Menyimpan log pengiriman notifikasi WhatsApp via Fonnte.
 * Digunakan untuk pencatatan hasil pengiriman dan pencegahan duplikasi.
 *
 * Jalankan sekali via browser:
 * http://localhost/jasa%20peralatan%20runah%20tangga/database/add_whatsapp_notifications.php
 */

// Ambil kredensial DB dari .env yang sama
$envPath = dirname(__DIR__) . '/.env';
$dbHost  = '127.0.0.1';
$dbUser  = 'root';
$dbPass  = '';
$dbName  = 'homecare_db';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            switch ($key) {
                case 'DB_HOST': $dbHost = $val; break;
                case 'DB_USER': $dbUser = $val; break;
                case 'DB_PASS': $dbPass = $val; break;
                case 'DB_NAME': $dbName = $val; break;
            }
        }
    }
}

try {
    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS whatsapp_notifications (
            id                INT AUTO_INCREMENT PRIMARY KEY,
            service_order_id  INT          NOT NULL COMMENT 'Referensi ke service_orders.id',
            notification_type VARCHAR(30)  NOT NULL COMMENT 'SERVICE_IN_PROGRESS | SERVICE_COMPLETED',
            recipient_phone   VARCHAR(30)  NOT NULL COMMENT 'Nomor WA tujuan (format 62xxx)',
            message           TEXT         NOT NULL COMMENT 'Isi pesan yang dikirim',
            provider          VARCHAR(20)  NOT NULL DEFAULT 'fonnte',
            provider_msg_id   VARCHAR(100) NULL     COMMENT 'ID pesan dari provider jika tersedia',
            delivery_status   VARCHAR(20)  NOT NULL DEFAULT 'PENDING'
                              COMMENT 'PENDING | SENT | FAILED | INVALID_NUMBER | NO_TOKEN',
            error_message     TEXT         NULL     COMMENT 'Pesan error untuk debugging (tanpa token)',
            attempt_count     TINYINT      NOT NULL DEFAULT 1,
            created_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            sent_at           DATETIME     NULL,
            updated_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (service_order_id) REFERENCES service_orders(id) ON DELETE CASCADE,
            INDEX idx_order  (service_order_id),
            INDEX idx_type   (notification_type),
            INDEX idx_status (delivery_status),
            INDEX idx_created(created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
          COMMENT='Log pengiriman notifikasi WhatsApp via Fonnte'
    ");

    echo '<div style="font-family:sans-serif;padding:30px;max-width:600px;margin:auto;">';
    echo '<h2 style="color:#16a34a;">&#x2705; Tabel <code>whatsapp_notifications</code> berhasil dibuat!</h2>';
    echo '<p>Log notifikasi WhatsApp via Fonnte sudah siap digunakan.</p>';
    echo '<ul>';
    echo '<li>Tabel: <strong>whatsapp_notifications</strong></li>';
    echo '<li>Field <code>error_message</code> untuk debugging &mdash; token <strong>tidak</strong> dicatat.</li>';
    echo '</ul>';
    echo '<p><a href="../">&#x2190; Kembali ke Aplikasi</a></p>';
    echo '</div>';

} catch (PDOException $e) {
    echo '<div style="font-family:sans-serif;padding:30px;">';
    echo '<h2 style="color:red;">&#x274C; Gagal membuat tabel</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
}
