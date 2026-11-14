<?php
$scriptName = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"] ?? ""));
// Hapus /public jika ada di akhir path agar link tidak menggunakan /public
$scriptName = preg_replace('#/public$#i', '', $scriptName);
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
$host = $_SERVER["HTTP_HOST"] ?? "localhost";
define("BASE_URL", $protocol . "://" . $host . $scriptName);
define("DB_HOST", "127.0.0.1");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "homecare_db");

// Load environment variables from .env if present
$envPath = dirname(__DIR__, 2) . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            if (!getenv($key)) {
                putenv("{$key}={$val}");
                $_ENV[$key] = $val;
            }
        }
    }
}

// AI Configuration (NVIDIA NIM API Key for equipment damage analysis & repair)
define("AI_API_KEY", getenv("AI_API_KEY") ?: "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");
define("AI_PROVIDER", getenv("AI_PROVIDER") ?: "nvidia");
define("AI_MODEL", getenv("AI_MODEL") ?: "meta/llama-3.2-11b-vision-instruct");

// Telegram Bot Configuration
define("TELEGRAM_BOT_TOKEN", getenv("TELEGRAM_BOT_TOKEN") ?: "8969596843:AAF6HNQAAqILpyq0bl6n8BUe4ddAkA2HibU");
define("TELEGRAM_DEFAULT_CHAT_ID", getenv("TELEGRAM_DEFAULT_CHAT_ID") ?: "1683261210");



