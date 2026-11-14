<?php
/**
 * Test Suite: FonnteService & WhatsApp Notification Flow
 *
 * Menguji logika FonnteService secara terisolasi dengan mock HTTP client.
 * TIDAK mengirim pesan nyata ke nomor WhatsApp customer.
 *
 * Jalankan via CLI:
 *   C:\xampp\php\php.exe tests/FonnteServiceTest.php
 *
 * Atau via browser:
 *   http://localhost/jasa%20peralatan%20runah%20tangga/tests/FonnteServiceTest.php
 */

define('BASE_URL', 'http://localhost/test');

require_once __DIR__ . '/../app/Services/FonnteService.php';

// ── Helpers ───────────────────────────────────────────────────────────────────

$passed = 0;
$failed = 0;
$results = [];

function assert_equal($label, $actual, $expected): void {
    global $passed, $failed, $results;
    $ok = ($actual === $expected);
    if ($ok) {
        $passed++;
        $results[] = ['pass', $label];
    } else {
        $failed++;
        $results[] = ['fail', $label, "Expected: " . var_export($expected, true) . " | Got: " . var_export($actual, true)];
    }
}

function assert_true($label, $actual): void {
    assert_equal($label, (bool)$actual, true);
}

function assert_false($label, $actual): void {
    assert_equal($label, (bool)$actual, false);
}

function assert_null($label, $actual): void {
    assert_equal($label, $actual, null);
}

// ── Test Group 1: normalizePhone ──────────────────────────────────────────────

echo "\n=== [Group 1] normalizePhone ===\n";

assert_equal(
    'Nomor lokal 0 → 62',
    FonnteService::normalizePhone('081234567890'),
    '6281234567890'
);

assert_equal(
    'Sudah diawali 62 → tidak berubah',
    FonnteService::normalizePhone('6281234567890'),
    '6281234567890'
);

assert_equal(
    'Diawali +62 → hapus +',
    FonnteService::normalizePhone('+6281234567890'),
    '6281234567890'
);

assert_equal(
    'Spasi di tengah → dibersihkan',
    FonnteService::normalizePhone('0812 3456 7890'),
    '6281234567890'
);

assert_equal(
    'Format dengan strip → dibersihkan',
    FonnteService::normalizePhone('0812-3456-7890'),
    '6281234567890'
);

assert_null(
    'Nomor kosong → null',
    FonnteService::normalizePhone('')
);

assert_null(
    'Nomor terlalu pendek → null',
    FonnteService::normalizePhone('0812')
);

assert_null(
    'Bukan angka → null',
    FonnteService::normalizePhone('nomor-tidak-valid')
);

assert_null(
    'Terlalu panjang (>15 digit) → null',
    FonnteService::normalizePhone('08123456789012345')
);

// ── Test Group 2: FonnteService::send() dengan token kosong ──────────────────

echo "\n=== [Group 2] send() — token tidak tersedia ===\n";

putenv('FONNTE_API_TOKEN=');
$_ENV['FONNTE_API_TOKEN'] = '';

$result = FonnteService::send('081234567890', 'Test pesan');

assert_false('success = false jika NO_TOKEN',       $result['success']);
assert_equal('delivery_status = NO_TOKEN',           $result['delivery_status'], 'NO_TOKEN');
assert_null('provider_msg_id = null jika NO_TOKEN',  $result['provider_msg_id']);
assert_true('error_message terisi',                  !empty($result['error_message']));
assert_equal('message tersimpan di return array',    $result['message'], 'Test pesan');

// ── Test Group 3: send() dengan nomor tidak valid ─────────────────────────────

echo "\n=== [Group 3] send() — nomor tidak valid ===\n";

putenv('FONNTE_API_TOKEN=MOCK_TOKEN_VAL_123');
$_ENV['FONNTE_API_TOKEN'] = 'MOCK_TOKEN_VAL_123';

$mockCalled = false;
FonnteService::setHttpHandler(function($token, $phone, $message) use (&$mockCalled) {
    $mockCalled = true;
    return ['http_code' => 200, 'body' => '{"status":true,"id":"msg_123"}'];
});

$result = FonnteService::send('', 'Test pesan');
assert_false('success = false jika nomor kosong',            $result['success']);
assert_equal('delivery_status = INVALID_NUMBER',             $result['delivery_status'], 'INVALID_NUMBER');
assert_false('HTTP handler TIDAK dipanggil jika nomor kosong', $mockCalled);

$result2 = FonnteService::send('abc-bukan-nomor', 'Test');
assert_false('success = false jika nomor non-digit',         $result2['success']);
assert_equal('delivery_status = INVALID_NUMBER (non-digit)', $result2['delivery_status'], 'INVALID_NUMBER');
assert_false('HTTP handler TIDAK dipanggil jika bukan nomor', $mockCalled);

// ── Test Group 4: ON_THE_WAY Notifikasi via Mock Handler ──────────────────────

echo "\n=== [Group 4] Notifikasi ON_THE_WAY ===\n";

$capturedReq = null;
FonnteService::setHttpHandler(function($token, $phone, $message) use (&$capturedReq) {
    $capturedReq = [
        'token'   => $token,
        'phone'   => $phone,
        'message' => $message
    ];
    return [
        'http_code' => 200,
        'body'      => json_encode(['status' => true, 'id' => 'fonnte_msg_otw_001', 'detail' => 'success'])
    ];
});

$otwData = [
    'phone'           => '081234567890',
    'customer_name'   => 'Ahmad Fauzi',
    'order_id'        => 15,
    'equipment_name'  => 'AC Daikin Inverter 1 PK',
    'technician_name' => 'Budi Santoso'
];

$resOtw = FonnteService::notifyOnTheWay($otwData);

assert_true('ON_THE_WAY: success = true', $resOtw['success']);
assert_equal('ON_THE_WAY: delivery_status = SENT', $resOtw['delivery_status'], 'SENT');
assert_equal('ON_THE_WAY: provider_msg_id = fonnte_msg_otw_001', $resOtw['provider_msg_id'], 'fonnte_msg_otw_001');
assert_equal('ON_THE_WAY: target nomor ternormalisasi (6281234567890)', $capturedReq['phone'], '6281234567890');
assert_true('ON_THE_WAY: pesan memuat nama customer', strpos($capturedReq['message'], 'Ahmad Fauzi') !== false);
assert_true('ON_THE_WAY: pesan memuat nomor pesanan #ORD-0015', strpos($capturedReq['message'], '#ORD-0015') !== false);
assert_true('ON_THE_WAY: pesan memuat nama peralatan', strpos($capturedReq['message'], 'AC Daikin Inverter 1 PK') !== false);
assert_true('ON_THE_WAY: pesan memuat status Teknisi Sedang Menuju Lokasi', strpos($capturedReq['message'], 'Teknisi Sedang Menuju Lokasi') !== false);
assert_true('ON_THE_WAY: pesan memuat instruksi customer dapat dihubungi', strpos($capturedReq['message'], 'dapat dihubungi') !== false);

// ── Test Group 5: IN_PROGRESS Notifikasi via Mock Handler ─────────────────────

echo "\n=== [Group 5] Notifikasi IN_PROGRESS ===\n";

FonnteService::setHttpHandler(function($token, $phone, $message) use (&$capturedReq) {
    $capturedReq = ['token' => $token, 'phone' => $phone, 'message' => $message];
    return [
        'http_code' => 200,
        'body'      => json_encode(['status' => true, 'id' => 'fonnte_msg_inp_002', 'detail' => 'success'])
    ];
});

$inpData = [
    'phone'           => '081234567890',
    'customer_name'   => 'Ahmad Fauzi',
    'order_id'        => 15,
    'equipment_name'  => 'AC Daikin Inverter 1 PK',
    'technician_name' => 'Budi Santoso'
];

$resInp = FonnteService::notifyInProgress($inpData);

assert_true('IN_PROGRESS: success = true', $resInp['success']);
assert_equal('IN_PROGRESS: delivery_status = SENT', $resInp['delivery_status'], 'SENT');
assert_equal('IN_PROGRESS: provider_msg_id = fonnte_msg_inp_002', $resInp['provider_msg_id'], 'fonnte_msg_inp_002');
assert_equal('IN_PROGRESS: target nomor ternormalisasi', $capturedReq['phone'], '6281234567890');
assert_true('IN_PROGRESS: pesan memuat perbaikan sedang berlangsung', strpos($capturedReq['message'], 'sedang berlangsung') !== false);
assert_true('IN_PROGRESS: pesan memuat status Sedang Diperbaiki', strpos($capturedReq['message'], 'Sedang Diperbaiki') !== false);

// ── Test Group 6: COMPLETED Notifikasi via Mock Handler ───────────────────────

echo "\n=== [Group 6] Notifikasi COMPLETED ===\n";

FonnteService::setHttpHandler(function($token, $phone, $message) use (&$capturedReq) {
    $capturedReq = ['token' => $token, 'phone' => $phone, 'message' => $message];
    return [
        'http_code' => 200,
        'body'      => json_encode(['status' => true, 'id' => 'fonnte_msg_cmp_003', 'detail' => 'success'])
    ];
});

$cmpData = [
    'phone'           => '081234567890',
    'customer_name'   => 'Ahmad Fauzi',
    'order_id'        => 15,
    'equipment_name'  => 'AC Daikin Inverter 1 PK',
    'technician_name' => 'Budi Santoso'
];

$resCmp = FonnteService::notifyCompleted($cmpData);

assert_true('COMPLETED: success = true', $resCmp['success']);
assert_equal('COMPLETED: delivery_status = SENT', $resCmp['delivery_status'], 'SENT');
assert_equal('COMPLETED: provider_msg_id = fonnte_msg_cmp_003', $resCmp['provider_msg_id'], 'fonnte_msg_cmp_003');
assert_equal('COMPLETED: target nomor ternormalisasi', $capturedReq['phone'], '6281234567890');
assert_true('COMPLETED: pesan memuat kabar baik selesai', strpos($capturedReq['message'], 'Kabar baik! Perbaikan peralatan Anda telah selesai.') !== false);
assert_true('COMPLETED: pesan memuat status Selesai Diperbaiki', strpos($capturedReq['message'], 'Selesai Diperbaiki') !== false);

// ── Test Group 7: Dua Customer Berbeda Menerima ke Nomor Masing-masing ─────────

echo "\n=== [Group 7] Dua Customer Berbeda Menerima ke Nomor Masing-masing ===\n";

$sentMessages = [];
FonnteService::setHttpHandler(function($token, $phone, $message) use (&$sentMessages) {
    $sentMessages[] = [
        'phone'   => $phone,
        'message' => $message
    ];
    return [
        'http_code' => 200,
        'body'      => json_encode(['status' => true, 'id' => 'msg_' . count($sentMessages)])
    ];
});

// Customer A
FonnteService::notifyOnTheWay([
    'phone'          => '081211112222',
    'customer_name'  => 'Customer A',
    'order_id'       => 101,
    'equipment_name' => 'Kulkas Samsung'
]);

// Customer B
FonnteService::notifyOnTheWay([
    'phone'          => '085733334444',
    'customer_name'  => 'Customer B',
    'order_id'       => 102,
    'equipment_name' => 'Mesin Cuci LG'
]);

assert_equal('Total 2 request dikirim', count($sentMessages), 2);
assert_equal('Customer A menerima di nomor 6281211112222', $sentMessages[0]['phone'], '6281211112222');
assert_true('Pesan Customer A memuat Customer A dan #ORD-0101', strpos($sentMessages[0]['message'], 'Customer A') !== false && strpos($sentMessages[0]['message'], '#ORD-0101') !== false);

assert_equal('Customer B menerima di nomor 6285733334444', $sentMessages[1]['phone'], '6285733334444');
assert_true('Pesan Customer B memuat Customer B dan #ORD-0102', strpos($sentMessages[1]['message'], 'Customer B') !== false && strpos($sentMessages[1]['message'], '#ORD-0102') !== false);

// ── Test Group 8: Pencegahan Nomor Kosong Mengirim ke Nomor Admin ─────────────

echo "\n=== [Group 8] Validasi Nomor Kosong & Tidak Terkirim ke Pihak Lain ===\n";

$unexpectedCall = false;
FonnteService::setHttpHandler(function($token, $phone, $message) use (&$unexpectedCall) {
    $unexpectedCall = true;
    return ['http_code' => 200, 'body' => '{"status":true}'];
});

$resEmpty = FonnteService::notifyOnTheWay([
    'phone'          => '',
    'customer_name'  => 'User Tanpa HP',
    'order_id'       => 200,
    'equipment_name' => 'Pompa Air'
]);

assert_false('Nomor kosong menghasilkan success = false', $resEmpty['success']);
assert_equal('Nomor kosong delivery_status = INVALID_NUMBER', $resEmpty['delivery_status'], 'INVALID_NUMBER');
assert_false('Mock HTTP handler TIDAK pernah dipanggil untuk nomor kosong', $unexpectedCall);

// ── Test Group 9: Ketahanan Kegagalan Fonnte (Error Handling & Token Redaction) ─

echo "\n=== [Group 9] Ketahanan terhadap Kegagalan API & Redaction Token ===\n";

// Skenario A: Fonnte membalas error status: false
FonnteService::setHttpHandler(function($token, $phone, $message) {
    return [
        'http_code' => 200,
        'body'      => json_encode(['status' => false, 'reason' => 'device disconnected'])
    ];
});

$resFail = FonnteService::send('081234567890', 'Pesan uji coba');
assert_false('Fonnte device disconnected menghasilkan success = false', $resFail['success']);
assert_equal('Fonnte status false delivery_status = FAILED', $resFail['delivery_status'], 'FAILED');
assert_true('Error message memuat reason', strpos($resFail['error_message'], 'device disconnected') !== false);

// Skenario B: cURL exception dilempar (timeout/koneksi terputus)
FonnteService::setHttpHandler(function($token, $phone, $message) {
    throw new \RuntimeException("Connection timeout to api.fonnte.com using token {$token}");
});

$resException = FonnteService::send('081234567890', 'Pesan uji coba');
assert_false('Exception tidak menghentikan eksekusi (success = false)', $resException['success']);
assert_equal('Exception delivery_status = FAILED', $resException['delivery_status'], 'FAILED');
assert_true('Token dirahasiakan / di-redact dari error message', strpos($resException['error_message'], 'MOCK_TOKEN_VAL_123') === false);
assert_true('Pesan redact terpasang', strpos($resException['error_message'], '[REDACTED]') !== false);

// Reset handler setelah selesai
FonnteService::setHttpHandler(null);

// ── Ringkasan ─────────────────────────────────────────────────────────────────

echo "\n";
echo str_repeat('=', 65) . "\n";
echo " HASIL TEST SUITE — FonnteService & WhatsApp Notification\n";
echo str_repeat('=', 65) . "\n";

$totalTests = $passed + $failed;
foreach ($results as $r) {
    $icon = $r[0] === 'pass' ? '✅' : '❌';
    echo " {$icon} " . $r[1] . "\n";
    if ($r[0] === 'fail' && isset($r[2])) {
        echo "     ↳ " . $r[2] . "\n";
    }
}

echo str_repeat('-', 65) . "\n";
echo " Total: {$totalTests} | Lulus: {$passed} | Gagal: {$failed}\n";
echo str_repeat('=', 65) . "\n\n";

if ($failed > 0) {
    echo "⚠️  Ada {$failed} test gagal. Periksa kode FonnteService.php.\n\n";
    exit(1);
} else {
    echo "🎉 Semua {$passed} test lulus dengan sukses!\n\n";
    exit(0);
}
