<?php
/**
 * TelegramService (MTProto Userbot Client)
 * 
 * Layanan notifikasi Telegram tanpa menggunakan Telegram Bot API.
 * Menggunakan Telegram Client API / MTProto (Userbot) melalui library Telethon (Python)
 * dengan session string yang tersimpan di lingkungan .env agar akun toko/admin tidak ter-logout.
 * 
 * Mampu mengirimkan pesan notifikasi langsung ke nomor HP pelanggan tanpa mewajibkan
 * pelanggan menekan /start terlebih dahulu.
 */
class TelegramService {

    /**
     * Jalankan proses bridge ke engine Python Telethon menggunakan proc_open via STDIN.
     * Menggunakan STDIN menghindari masalah escaping karakter khusus pada Windows PowerShell/CMD.
     * 
     * @param string $action Nama fungsi/action (notify_order_created, notify_in_progress, notify_completed, send_message)
     * @param array $payload Data associative array yang akan dikirim
     * @return array Hasil eksekusi [success => bool, message => string, error_code => string|null, data => array|null]
     */
    public static function executeBridge(string $action, array $payload): array {
        $pythonBin = getenv('PYTHON_BIN_PATH') ?: ($_ENV['PYTHON_BIN_PATH'] ?? 'python');
        $scriptPath = realpath(__DIR__ . '/../../telegram_userbot/notifier.py');

        if (!$scriptPath || !file_exists($scriptPath)) {
            $err = "[TelegramService] File script MTProto tidak ditemukan di: " . __DIR__ . '/../../telegram_userbot/notifier.py';
            error_log($err);
            return [
                'success' => false,
                'error_code' => 'SCRIPT_NOT_FOUND',
                'message' => $err,
                'data' => null
            ];
        }

        // Siapkan input JSON
        $inputJson = json_encode([
            'action' => $action,
            'data'   => $payload
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $command = escapeshellcmd($pythonBin) . ' ' . escapeshellarg($scriptPath) . ' --stdin';

        $descriptors = [
            0 => ['pipe', 'r'], // STDIN
            1 => ['pipe', 'w'], // STDOUT
            2 => ['pipe', 'w'], // STDERR
        ];

        $workDir = dirname($scriptPath, 2);
        $process = proc_open($command, $descriptors, $pipes, $workDir);

        if (!is_resource($process)) {
            $err = "[TelegramService] Gagal menjalankan proses Python Telethon MTProto.";
            error_log($err);
            return [
                'success' => false,
                'error_code' => 'PROC_OPEN_FAILED',
                'message' => $err,
                'data' => null
            ];
        }

        // Tulis payload ke STDIN proses Python
        fwrite($pipes[0], $inputJson);
        fclose($pipes[0]);

        // Baca hasil stdout dan stderr
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        $stdoutTrimmed = trim($stdout);
        $parsed = json_decode($stdoutTrimmed, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
            // Cek jika nomor tidak terdaftar
            if (isset($parsed['error_code']) && $parsed['error_code'] === 'PHONE_NOT_REGISTERED') {
                error_log("[TelegramService] [NOTICE] " . ($parsed['message'] ?? 'Nomor pelanggan tidak terdaftar di Telegram.'));
            } elseif (isset($parsed['success']) && !$parsed['success']) {
                error_log("[TelegramService] [ERROR] " . ($parsed['message'] ?? 'Gagal mengirim pesan MTProto'));
            }
            return $parsed;
        }

        // Tangani jika terjadi syntax error atau python crash
        $errorMessage = !empty($stderr) ? trim($stderr) : (!empty($stdoutTrimmed) ? $stdoutTrimmed : "Unknown execution error (Exit code: {$exitCode})");
        error_log("[TelegramService] Python Error: " . $errorMessage);

        return [
            'success' => false,
            'error_code' => 'EXECUTION_ERROR',
            'message' => $errorMessage,
            'raw_output' => $stdoutTrimmed
        ];
    }

    /**
     * Kondisi 1: Pesanan Dibuat (notify_order_created)
     * Mengirim konfirmasi ke pelanggan bahwa pesanan jasa peralatan rumah tangga telah diterima.
     * 
     * @param array $data Data pesanan:
     *   - phone: string (Nomor HP pelanggan, misal 08123456789)
     *   - customer_name: string (Nama pelanggan)
     *   - order_id: int|string (ID Pesanan)
     *   - equipment_name: string (Nama unit peralatan, misal "Mesin Cuci Sharp")
     *   - complaint: string (Keluhan / kendala kerusakan)
     *   - service_date: string (Jadwal tanggal/waktu servis)
     *   - address: string (Alamat lokasi servis)
     * @return array
     */
    public static function notifyOrderCreated(array $data): array {
        return self::executeBridge('notify_order_created', $data);
    }

    /**
     * Kondisi 2: Sedang Diperbaiki (notify_in_progress)
     * Mengabarkan bahwa barang (sebutkan nama unitnya) sedang dikerjakan oleh teknisi.
     * 
     * @param array $data Data pengerjaan:
     *   - phone: string (Nomor HP pelanggan)
     *   - customer_name: string (Nama pelanggan)
     *   - order_id: int|string (ID Pesanan)
     *   - equipment_name: string (Nama unit peralatan)
     *   - technician_name: string (Nama teknisi yang mengerjakan)
     * @return array
     */
    public static function notifyInProgress(array $data): array {
        return self::executeBridge('notify_in_progress', $data);
    }

    /**
     * Kondisi 3: Selesai Diperbaiki (notify_completed)
     * Mengabarkan bahwa perbaikan selesai, barang siap diambil/dikirim, beserta rincian total biaya.
     * 
     * @param array $data Data penyelesaian:
     *   - phone: string (Nomor HP pelanggan)
     *   - customer_name: string (Nama pelanggan)
     *   - order_id: int|string (ID Pesanan)
     *   - equipment_name: string (Nama unit peralatan)
     *   - technician_name: string (Nama teknisi)
     *   - inspection_result: string (Hasil diagnosa/pemeriksaan)
     *   - repair_action: string (Tindakan perbaikan yang dilakukan)
     *   - notes: string (Catatan tambahan)
     *   - total_cost: float|int|string (Total biaya / tagihan servis)
     * @return array
     */
    public static function notifyCompleted(array $data): array {
        return self::executeBridge('notify_completed', $data);
    }

    /**
     * Kirim pesan bebas langsung ke nomor HP pelanggan via MTProto
     * 
     * @param string $phone Nomor HP tujuan
     * @param string $message Isi pesan HTML
     * @param string $customerName Nama customer (opsional)
     * @return array
     */
    public static function sendMessageToPhone(string $phone, string $message, string $customerName = 'Pelanggan'): array {
        return self::executeBridge('send_message', [
            'phone' => $phone,
            'message' => $message,
            'customer_name' => $customerName
        ]);
    }

    // =========================================================================
    // Backward Compatibility Helpers (Memudahkan transisi controller lama)
    // =========================================================================

    /**
     * Helper kompatibilitas lama untuk Status IN_PROGRESS
     */
    public static function notifySedangDiperbaiki(string $customerName, string $equipmentName, $chatIdOrPhone = null, $orderId = null, $technicianName = 'Teknisi'): array {
        return self::notifyInProgress([
            'phone' => (string)$chatIdOrPhone,
            'customer_name' => $customerName,
            'equipment_name' => $equipmentName,
            'order_id' => $orderId ?: '-',
            'technician_name' => $technicianName
        ]);
    }

    /**
     * Helper kompatibilitas lama untuk Status COMPLETED
     */
    public static function notifySelesaiDiperbaiki(string $customerName, string $equipmentName, $chatIdOrPhone = null, $orderId = null, $cost = 0, $technicianName = 'Teknisi', $inspection = 'Pemeriksaan menyeluruh', $repair = 'Perbaikan komponen'): array {
        return self::notifyCompleted([
            'phone' => (string)$chatIdOrPhone,
            'customer_name' => $customerName,
            'equipment_name' => $equipmentName,
            'order_id' => $orderId ?: '-',
            'total_cost' => $cost,
            'technician_name' => $technicianName,
            'inspection_result' => $inspection,
            'repair_action' => $repair
        ]);
    }
}
