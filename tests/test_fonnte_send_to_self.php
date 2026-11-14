<?php
/**
 * Uji Kirim Nyata via Fonnte ke Nomor Sendiri
 *
 * GANTI $testPhone dengan nomor WhatsApp Anda sendiri sebelum menjalankan.
 * Jalankan: C:\xampp\php\php.exe tests\test_fonnte_send_to_self.php
 *
 * PERINGATAN: Script ini akan benar-benar mengirim pesan WhatsApp.
 * Gunakan HANYA nomor milik Anda sendiri untuk pengujian.
 */

// ============================================================
// KONFIGURASI — UBAH INI
// ============================================================
$testPhone    = '08xxxxxxxxxx'; // ← Ganti dengan nomor WA Anda sendiri (format lokal 08xxx)
$testScenario = 'on_the_way'; // Pilih: 'on_the_way', 'in_progress', atau 'completed'
// ============================================================

// Tolak jika nomor belum diubah
if ($testPhone === '08xxxxxxxxxx' || empty($testPhone)) {
    echo "ERROR: Harap ganti \$testPhone dengan nomor WhatsApp Anda sendiri.\n";
    echo "       Edit file: tests/test_fonnte_send_to_self.php\n";
    exit(1);
}

// Load env
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $val] = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($val, " \t\"'"));
        }
    }
}

require_once __DIR__ . '/../app/Services/FonnteService.php';

echo "=======================================================\n";
echo "  Uji Kirim WA via Fonnte ke Nomor Sendiri\n";
echo "=======================================================\n";
echo "Nomor tujuan : {$testPhone}\n";
echo "Skenario     : {$testScenario}\n\n";

// Normalisasi nomor
$normalized = FonnteService::normalizePhone($testPhone);
if ($normalized === null) {
    echo "ERROR: Nomor tidak valid setelah normalisasi.\n";
    exit(1);
}
echo "Nomor setelah normalisasi: {$normalized}\n\n";

// Kirim berdasarkan skenario
if ($testScenario === 'on_the_way') {
    echo "Mengirim notifikasi ON_THE_WAY...\n";
    $result = FonnteService::notifyOnTheWay([
        'phone'           => $testPhone,
        'customer_name'   => 'Pelanggan Uji Coba',
        'order_id'        => 9999,
        'equipment_name'  => 'Mesin Cuci Sharp 1 Tabung',
        'technician_name' => 'Teknisi Demo',
    ]);
} elseif ($testScenario === 'in_progress') {
    echo "Mengirim notifikasi IN_PROGRESS...\n";
    $result = FonnteService::notifyInProgress([
        'phone'           => $testPhone,
        'customer_name'   => 'Pelanggan Uji Coba',
        'order_id'        => 9999,
        'equipment_name'  => 'Mesin Cuci Sharp 1 Tabung',
        'technician_name' => 'Teknisi Demo',
    ]);
} else {
    echo "Mengirim notifikasi COMPLETED...\n";
    $result = FonnteService::notifyCompleted([
        'phone'              => $testPhone,
        'customer_name'      => 'Pelanggan Uji Coba',
        'order_id'           => 9999,
        'equipment_name'     => 'Mesin Cuci Sharp 1 Tabung',
        'technician_name'    => 'Teknisi Demo',
        'total_cost'         => 275000,
    ]);
}

echo "-------------------------------------------------------\n";
echo "Hasil:\n";
echo "  success         : " . ($result['success'] ? 'true' : 'false') . "\n";
echo "  delivery_status : " . $result['delivery_status'] . "\n";
echo "  provider_msg_id : " . ($result['provider_msg_id'] ?? '(null)') . "\n";
echo "  error_message   : " . ($result['error_message'] ?? '(null)') . "\n";
echo "=======================================================\n\n";

if ($result['success']) {
    echo "✅ Pesan berhasil dikirim! Periksa WhatsApp nomor {$normalized}.\n";
    echo "   Jika tidak muncul dalam 1 menit, periksa dashboard Fonnte.\n";
} else {
    echo "❌ Pengiriman gagal.\n";
    echo "   Status  : {$result['delivery_status']}\n";
    echo "   Detail  : " . ($result['error_message'] ?? '-') . "\n\n";
    echo "Langkah debug:\n";
    echo "  1. Pastikan FONNTE_API_TOKEN di .env benar\n";
    echo "  2. Pastikan device WhatsApp di Fonnte dalam keadaan CONNECT\n";
    echo "  3. Cek dashboard Fonnte: https://fonnte.com/dashboard\n";
}
