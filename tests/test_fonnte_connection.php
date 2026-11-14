<?php
/**
 * Uji koneksi token Fonnte ke API.
 * Jalankan: C:\xampp\php\php.exe tests\test_fonnte_connection.php
 * File ini TIDAK mengirim pesan nyata ke customer.
 */

// Baca token dari .env
$envPath = dirname(__DIR__) . '/.env';
$token = '';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, 'FONNTE_API_TOKEN=') === 0) {
            $token = trim(substr($line, strlen('FONNTE_API_TOKEN=')), " \t\"'");
            break;
        }
    }
}

if (empty($token)) {
    echo "ERROR: FONNTE_API_TOKEN tidak ditemukan di .env\n";
    exit(1);
}

echo "Token ditemukan, panjang: " . strlen($token) . " karakter\n";
echo "Menghubungi Fonnte API...\n\n";

// Kirim request dengan target kosong → Fonnte akan membalas error 'target required'
// Ini cukup untuk membuktikan token valid tanpa mengirim pesan nyata
$ch = curl_init('https://api.fonnte.com/send');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => ['target' => '', 'message' => 'test'],
    CURLOPT_HTTPHEADER     => ['Authorization: ' . $token],
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => true,
]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

if ($body === false) {
    echo "cURL Error: " . $err . "\n";
    exit(1);
}

echo "HTTP Code   : " . $code . "\n";
echo "Respons JSON: " . $body . "\n\n";

$decoded = json_decode($body, true);
if (isset($decoded['status']) && $decoded['status'] === false && isset($decoded['reason'])) {
    $reason = is_array($decoded['reason']) ? implode(', ', $decoded['reason']) : $decoded['reason'];
    if (stripos($reason, 'token') !== false || stripos($reason, 'invalid') !== false) {
        echo "STATUS: Token TIDAK VALID atau kedaluwarsa. Perbarui di .env\n";
    } else {
        // Token valid, Fonnte menolak karena target kosong — ini yang diharapkan
        echo "STATUS: Token VALID ✓ (Fonnte menolak karena target kosong: '{$reason}')\n";
        echo "        Token dapat digunakan untuk kirim notifikasi.\n";
    }
} elseif (isset($decoded['status']) && $decoded['status'] === true) {
    echo "STATUS: Token VALID ✓ (request diterima)\n";
} else {
    echo "STATUS: Respons tidak dikenali. Periksa manual.\n";
}
