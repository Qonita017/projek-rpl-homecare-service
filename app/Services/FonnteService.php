<?php
/**
 * FonnteService
 *
 * Layanan pengiriman notifikasi WhatsApp menggunakan Fonnte REST API.
 * API endpoint: https://api.fonnte.com/send
 *
 * Token diambil dari environment variable FONNTE_API_TOKEN.
 * Token TIDAK pernah ditulis langsung di kode ini.
 * Token TIDAK pernah dikirim ke browser atau dicatat di log.
 *
 * Pola kelas mengikuti TelegramService.php yang sudah ada di proyek.
 */
class FonnteService
{
    /** Endpoint Fonnte */
    const API_URL = 'https://api.fonnte.com/send';

    /** Timeout koneksi cURL dalam detik */
    const CONNECT_TIMEOUT = 10;

    /** Timeout total cURL dalam detik */
    const REQUEST_TIMEOUT = 30;

    // -------------------------------------------------------------------------
    // Public helpers: Notifikasi berdasarkan status
    // -------------------------------------------------------------------------

    /**
     * Kirim notifikasi WhatsApp: status ON_THE_WAY (Teknisi Sedang Menuju Lokasi)
     *
     * @param array $data {
     *   phone          string  Nomor HP customer (format lokal atau internasional)
     *   customer_name  string  Nama customer
     *   order_id       int     ID service order
     *   equipment_name string  Nama peralatan
     *   technician_name string Nama teknisi
     * }
     * @return array Hasil pengiriman.
     */
    public static function notifyOnTheWay(array $data): array
    {
        $phone         = $data['phone']          ?? '';
        $customerName  = $data['customer_name']  ?? 'Pelanggan';
        $orderId       = $data['order_id']        ?? '-';
        $equipmentName = $data['equipment_name']  ?? 'Peralatan';

        $orderNum = is_numeric($orderId) ? ('#ORD-' . str_pad((int)$orderId, 4, '0', STR_PAD_LEFT)) : $orderId;

        $message  = "Halo {$customerName},\n\n";
        $message .= "Teknisi HomeCare Service sedang menuju lokasi Anda untuk menangani pesanan servis.\n\n";
        $message .= "Nomor Pesanan: {$orderNum}\n";
        $message .= "Peralatan: {$equipmentName}\n";
        $message .= "Status: Teknisi Sedang Menuju Lokasi\n\n";
        $message .= "Mohon pastikan Anda dapat dihubungi saat teknisi tiba.\n\n";
        $message .= "Terima kasih telah menggunakan HomeCare Service.";

        return self::send($phone, $message);
    }

    /**
     * Kirim notifikasi WhatsApp: status IN_PROGRESS (Sedang Diperbaiki)
     *
     * @param array $data {
     *   phone          string  Nomor HP customer (format lokal atau internasional)
     *   customer_name  string  Nama customer
     *   order_id       int     ID service order
     *   equipment_name string  Nama peralatan
     *   technician_name string Nama teknisi
     * }
     * @return array Hasil pengiriman. Lihat self::send() untuk format.
     */
    public static function notifyInProgress(array $data): array
    {
        $phone         = $data['phone']          ?? '';
        $customerName  = $data['customer_name']  ?? 'Pelanggan';
        $orderId       = $data['order_id']        ?? '-';
        $equipmentName = $data['equipment_name']  ?? 'Peralatan';

        $orderNum = is_numeric($orderId) ? ('#ORD-' . str_pad((int)$orderId, 4, '0', STR_PAD_LEFT)) : $orderId;

        $message  = "Halo {$customerName},\n\n";
        $message .= "Kami informasikan bahwa perbaikan peralatan Anda sedang berlangsung.\n\n";
        $message .= "Nomor Pesanan: {$orderNum}\n";
        $message .= "Peralatan: {$equipmentName}\n";
        $message .= "Status: Sedang Diperbaiki\n\n";
        $message .= "Terima kasih telah menggunakan HomeCare Service.";

        return self::send($phone, $message);
    }

    /**
     * Kirim notifikasi WhatsApp: status COMPLETED (Selesai Diperbaiki)
     *
     * @param array $data {
     *   phone              string  Nomor HP customer
     *   customer_name      string  Nama customer
     *   order_id           int     ID service order
     *   equipment_name     string  Nama peralatan
     *   technician_name    string  Nama teknisi
     *   inspection_result  string  Hasil pemeriksaan
     *   repair_action      string  Tindakan perbaikan
     *   notes              string  Catatan tambahan (opsional)
     *   total_cost         float   Total biaya servis
     * }
     * @return array Hasil pengiriman.
     */
    public static function notifyCompleted(array $data): array
    {
        $phone         = $data['phone']          ?? '';
        $customerName  = $data['customer_name']  ?? 'Pelanggan';
        $orderId       = $data['order_id']        ?? '-';
        $equipmentName = $data['equipment_name']  ?? 'Peralatan';

        $orderNum = is_numeric($orderId) ? ('#ORD-' . str_pad((int)$orderId, 4, '0', STR_PAD_LEFT)) : $orderId;

        $message  = "Halo {$customerName},\n\n";
        $message .= "Kabar baik! Perbaikan peralatan Anda telah selesai.\n\n";
        $message .= "Nomor Pesanan: {$orderNum}\n";
        $message .= "Peralatan: {$equipmentName}\n";
        $message .= "Status: Selesai Diperbaiki\n\n";
        $message .= "Terima kasih telah menggunakan HomeCare Service.";

        return self::send($phone, $message);
    }

    // -------------------------------------------------------------------------
    // Core: Kirim pesan ke Fonnte API
    // -------------------------------------------------------------------------

    /** Handler kustom untuk keperluan automated testing (mock HTTP transport) */
    private static $httpHandler = null;

    /**
     * Set handler kustom untuk pengujian (mock).
     * Jika diset, httpPost() akan memanggil handler ini alih-alih cURL nyata.
     *
     * @param callable|null $handler callable(string $token, string $phone, string $message): array ['http_code' => int, 'body' => string]
     */
    public static function setHttpHandler(?callable $handler): void
    {
        self::$httpHandler = $handler;
    }

    /**
     * Kirim pesan WhatsApp melalui Fonnte API.
     *
     * Format request Fonnte (POST multipart/form-data):
     *   target   => nomor tujuan (format internasional tanpa +, contoh: 6281234567890)
     *   message  => isi pesan teks
     * Header:
     *   Authorization: <token>   (tanpa kata "Bearer")
     *
     * @param string $phone   Nomor tujuan (lokal/internasional)
     * @param string $message Isi pesan
     * @return array {
     *   success         bool
     *   delivery_status string   'SENT' | 'FAILED' | 'INVALID_NUMBER' | 'NO_TOKEN'
     *   provider_msg_id string|null
     *   error_message   string|null  (tanpa token)
     *   raw_response    array|null
     *   message         string
     * }
     */
    public static function send(string $phone, string $message): array
    {
        // 1. Ambil token dari environment (tidak pernah di-hardcode)
        $token = getenv('FONNTE_API_TOKEN');
        if (empty($token) && isset($_ENV['FONNTE_API_TOKEN'])) {
            $token = $_ENV['FONNTE_API_TOKEN'];
        }
        if (empty($token)) {
            error_log('[FonnteService] FONNTE_API_TOKEN tidak ditemukan di environment.');
            return [
                'success'         => false,
                'delivery_status' => 'NO_TOKEN',
                'provider_msg_id' => null,
                'error_message'   => 'FONNTE_API_TOKEN tidak dikonfigurasi di environment.',
                'raw_response'    => null,
                'message'         => $message,
            ];
        }

        // 2. Normalisasi & validasi nomor
        $normalizedPhone = self::normalizePhone($phone);
        if ($normalizedPhone === null) {
            $safePhone = self::maskPhone($phone);
            error_log("[FonnteService] Nomor WhatsApp tidak valid: {$safePhone}");
            return [
                'success'         => false,
                'delivery_status' => 'INVALID_NUMBER',
                'provider_msg_id' => null,
                'error_message'   => "Nomor WhatsApp tidak valid atau kosong.",
                'raw_response'    => null,
                'message'         => $message,
            ];
        }

        // 3. Kirim HTTP POST ke Fonnte (tidak mengekspos token ke log)
        try {
            $result = self::httpPost($token, $normalizedPhone, $message);
        } catch (\Throwable $e) {
            // Pastikan pesan exception tidak mengandung token
            $safeError = str_replace($token, '[REDACTED]', $e->getMessage());
            error_log("[FonnteService] cURL Error: {$safeError}");
            return [
                'success'         => false,
                'delivery_status' => 'FAILED',
                'provider_msg_id' => null,
                'error_message'   => "Koneksi ke Fonnte gagal: {$safeError}",
                'raw_response'    => null,
                'message'         => $message,
            ];
        }

        // 4. Evaluasi respons Fonnte
        $parsed = self::parseResponse($result, $token);
        $parsed['message'] = $message;
        return $parsed;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Kirim HTTP POST menggunakan cURL atau mock handler.
     *
     * @throws \RuntimeException jika cURL gagal diinisialisasi atau request timeout.
     * @return array ['http_code' => int, 'body' => string]
     */
    private static function httpPost(string $token, string $phone, string $message): array
    {
        if (self::$httpHandler !== null) {
            return (self::$httpHandler)($token, $phone, $message);
        }

        if (!function_exists('curl_init')) {
            throw new \RuntimeException('cURL tidak tersedia di PHP environment ini.');
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => self::API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => [
                'target'  => $phone,
                'message' => $message,
            ],
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . $token,   // Fonnte: token di Authorization header (tanpa "Bearer")
            ],
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT        => self::REQUEST_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $body     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        $curlErrNo = curl_errno($ch);
        curl_close($ch);

        if ($body === false) {
            // Errno 28 = timeout
            $errDesc = $curlErrNo === 28 ? 'Request timeout' : $curlErr;
            throw new \RuntimeException("cURL errno {$curlErrNo}: {$errDesc}");
        }

        return ['http_code' => $httpCode, 'body' => (string)$body];
    }

    /**
     * Parse respons Fonnte dan kembalikan hasil terstruktur.
     *
     * Dokumentasi Fonnte: respons JSON berisi field:
     *   status  => true/false   (apakah request diterima)
     *   detail  => string|array (keterangan tambahan)
     *   id      => string|array (message ID, jika tersedia)
     *   reason  => string       (alasan kegagalan, jika ada)
     *
     * @param array  $result ['http_code' => int, 'body' => string]
     * @param string $token  Token asli — digunakan HANYA untuk redact, tidak dicatat.
     */
    private static function parseResponse(array $result, string $token): array
    {
        $httpCode = $result['http_code'];
        $body     = $result['body'];

        // Redact token dari body respons sebelum diproses lebih lanjut
        $safeBody = str_replace($token, '[REDACTED]', $body);

        $decoded = json_decode($body, true);

        // HTTP 200 + status true → dianggap berhasil diterima Fonnte
        if ($httpCode === 200 && is_array($decoded) && !empty($decoded['status'])) {
            $msgId = null;
            if (isset($decoded['id'])) {
                $msgId = is_array($decoded['id']) ? implode(',', $decoded['id']) : (string)$decoded['id'];
            }

            return [
                'success'         => true,
                'delivery_status' => 'SENT',
                'provider_msg_id' => $msgId,
                'error_message'   => null,
                'raw_response'    => $decoded,
            ];
        }

        // Kegagalan — bangun pesan error yang aman (tanpa token)
        $reason = null;
        if (is_array($decoded)) {
            $reason = $decoded['reason'] ?? ($decoded['detail'] ?? null);
            if (is_array($reason)) {
                $reason = implode('; ', $reason);
            }
        }

        $errorMsg = "HTTP {$httpCode}";
        if ($reason) {
            $errorMsg .= ": " . str_replace($token, '[REDACTED]', (string)$reason);
        } else {
            $errorMsg .= ". Respons: " . mb_substr($safeBody, 0, 200);
        }

        // HTTP 401/403 → token tidak valid atau kedaluwarsa
        $deliveryStatus = ($httpCode === 401 || $httpCode === 403) ? 'NO_TOKEN' : 'FAILED';

        error_log("[FonnteService] Gagal mengirim WA. Status HTTP: {$httpCode}. Reason: " . ($reason ? str_replace($token, '[REDACTED]', (string)$reason) : 'N/A'));

        return [
            'success'         => false,
            'delivery_status' => $deliveryStatus,
            'provider_msg_id' => null,
            'error_message'   => $errorMsg,
            'raw_response'    => is_array($decoded) ? $decoded : null,
        ];
    }

    /**
     * Normalisasi nomor telepon Indonesia ke format internasional Fonnte (62xxx).
     *
     * - Awalan 0  → diganti 62  (081234567890 → 6281234567890)
     * - Awalan 62 → dibiarkan   (6281234567890 tetap)
     * - Awalan +62→ + dihapus   (+6281234567890 → 6281234567890)
     * - Nomor lain (sudah internasional tanpa 62) → dikembalikan apa adanya
     *
     * Mengembalikan null jika nomor kosong atau terlalu pendek.
     *
     * @param string $phone
     * @return string|null Nomor yang dinormalisasi, atau null jika tidak valid.
     */
    public static function normalizePhone(string $phone): ?string
    {
        // Hapus semua spasi, strip, kurung
        $cleaned = preg_replace('/[\s\-\(\)\.]+/', '', $phone);

        if (empty($cleaned)) {
            return null;
        }

        // Hapus awalan +
        if (strpos($cleaned, '+') === 0) {
            $cleaned = substr($cleaned, 1);
        }

        // Ganti awalan 0 dengan 62
        if (strpos($cleaned, '0') === 0) {
            $cleaned = '62' . substr($cleaned, 1);
        }

        // Validasi: hanya angka, minimal 10 digit (termasuk kode negara), maks 15
        if (!ctype_digit($cleaned) || strlen($cleaned) < 10 || strlen($cleaned) > 15) {
            return null;
        }

        return $cleaned;
    }

    /**
     * Sensor sebagian nomor telepon untuk keperluan logging.
     * Contoh: 6281234567890 → 628****7890
     */
    private static function maskPhone(string $phone): string
    {
        if (strlen($phone) <= 6) {
            return str_repeat('*', strlen($phone));
        }
        return substr($phone, 0, 3) . str_repeat('*', strlen($phone) - 6) . substr($phone, -4);
    }
}
