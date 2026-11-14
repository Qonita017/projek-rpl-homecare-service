<?php
require_once __DIR__ . '/../Core/Model.php';

class Setting extends Model {
    
    public function __construct() {
        parent::__construct();
        $this->ensureTableExists();
    }

    /**
     * Ensure system_settings table exists in DB automatically
     */
    private function ensureTableExists(): void {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS system_settings (
                setting_key VARCHAR(100) PRIMARY KEY,
                setting_value LONGTEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $this->getPDO()->exec($sql);
        } catch (\Throwable $e) {
            error_log("[Setting Model] ensureTableExists error: " . $e->getMessage());
        }
    }

    /**
     * Get a setting by key, with optional fallback
     */
    public function get(string $key, $default = null) {
        try {
            $stmt = $this->getPDO()->prepare("SELECT setting_value FROM system_settings WHERE setting_key = :k LIMIT 1");
            $stmt->execute([':k' => $key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false && $row['setting_value'] !== null && $row['setting_value'] !== '') {
                return $row['setting_value'];
            }
        } catch (\Throwable $e) {
            error_log("[Setting Model] get '{$key}' error: " . $e->getMessage());
        }
        return $default;
    }

    /**
     * Set a setting key-value pair
     */
    public function set(string $key, ?string $value): bool {
        try {
            $stmt = $this->getPDO()->prepare("
                INSERT INTO system_settings (setting_key, setting_value)
                VALUES (:k, :v)
                ON DUPLICATE KEY UPDATE setting_value = :v2, updated_at = CURRENT_TIMESTAMP
            ");
            return $stmt->execute([
                ':k' => $key,
                ':v' => $value,
                ':v2' => $value
            ]);
        } catch (\Throwable $e) {
            error_log("[Setting Model] set '{$key}' error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get multiple settings as key => value map
     */
    public function getAll(): array {
        try {
            $stmt = $this->getPDO()->query("SELECT setting_key, setting_value FROM system_settings");
            $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Synchronize key-value settings to .env file if writable
     */
    public function updateEnv(array $keyValues): bool {
        $envPath = dirname(__DIR__, 2) . '/.env';
        if (!file_exists($envPath)) {
            return false;
        }

        try {
            $content = file_get_contents($envPath);
            foreach ($keyValues as $key => $val) {
                $valStr = (string)$val;
                // Update getenv & $_ENV in current process runtime
                putenv("{$key}={$valStr}");
                $_ENV[$key] = $valStr;

                // Regex replace in .env content or append
                $pattern = "/^{$key}=.*/m";
                if (preg_match($pattern, $content)) {
                    $content = preg_replace($pattern, "{$key}={$valStr}", $content);
                } else {
                    $content = rtrim($content) . "\n{$key}={$valStr}\n";
                }
            }
            return (bool)file_put_contents($envPath, $content);
        } catch (\Throwable $e) {
            error_log("[Setting Model] updateEnv error: " . $e->getMessage());
            return false;
        }
    }
}

