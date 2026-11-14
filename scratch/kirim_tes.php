<?php
/**
 * Script Cepat Uji Coba Pengiriman Notifikasi Telegram
 * 
 * Penggunaan lewat Terminal / CMD:
 *   c:\xampp\php\php.exe scratch/kirim_tes.php 081234567890
 */

require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Services/TelegramService.php';

$phoneInput = $argv[1] ?? null;

if (empty($phoneInput)) {
    echo "===============================================================\n";
    echo " [HomeCare Service] Uji Coba Notifikasi Telegram MTProto\n";
    echo "===============================================================\n";
    echo "Cara penggunaan:\n";
    echo "  c:\\xampp\\php\\php.exe scratch/kirim_tes.php <NOMOR_HP_TELEGRAM>\n\n";
    echo "Contoh:\n";
    echo "  c:\\xampp\\php\\php.exe scratch/kirim_tes.php 081234567890\n";
    echo "===============================================================\n";
    exit(1);
}

echo "\n[*] Menyiapkan pengiriman notifikasi ke: {$phoneInput} ...\n";

// Mengirim contoh notifikasi Pesanan Dibuat
$result = TelegramService::notifyOrderCreated([
    'phone'          => $phoneInput,
    'customer_name'  => 'Pelanggan Uji Coba',
    'order_id'       => 999,
    'equipment_name' => 'Mesin Cuci Panasonic 1 Tabung',
    'complaint'      => 'Air tidak keluar saat proses pengisian',
    'service_date'   => date('d-m-Y H:i'),
    'address'        => 'Workshop HomeCare Service'
]);

echo "[*] Hasil Pengiriman:\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

if (!empty($result['success'])) {
    echo ">>> BERHASIL! Silakan cek aplikasi Telegram di nomor {$phoneInput}. <<<\n";
} else {
    echo ">>> GAGAL / PERINGATAN: " . ($result['message'] ?? 'Periksa konfigurasi .env') . " <<<\n";
}
