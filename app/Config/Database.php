<?php
class Database {
    private static $sharedPdo = null;
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $dbh;
    private $error;

    public function __construct() {
        if (self::$sharedPdo !== null) {
            $this->dbh = self::$sharedPdo;
            return;
        }

        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=utf8mb4";
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5
        );
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
            self::$sharedPdo = $this->dbh;
        } catch(PDOException $e) {
            // Check if error is unknown database (error code 1049)
            if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
                try {
                    // Try to auto-create database and load schema
                    $pdoInit = new PDO("mysql:host=" . $this->host . ";charset=utf8mb4", $this->user, $this->pass, $options);
                    $pdoInit->exec("CREATE DATABASE IF NOT EXISTS `" . $this->dbname . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $pdoInit->exec("USE `" . $this->dbname . "`");
                    
                    $schemaFile = __DIR__ . '/../../database/schema.sql';
                    if (file_exists($schemaFile)) {
                        $sql = file_get_contents($schemaFile);
                        $pdoInit->exec($sql);
                    }
                    
                    // Now connect to the new DB
                    $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
                    self::$sharedPdo = $this->dbh;
                    return;
                } catch (PDOException $ex) {
                    $this->error = $ex->getMessage();
                }
            } else {
                $this->error = $e->getMessage();
            }
            
            if (!$this->dbh) {
                throw new Exception("Koneksi Database Gagal: " . $this->error . ". Pastikan MySQL pada XAMPP sudah aktif dan database/migrate.php sudah dijalankan.");
            }
        }
    }

    public function getConnection() {
        if (!$this->dbh) {
            throw new Exception("Koneksi Database Gagal: " . $this->error);
        }
        return $this->dbh;
    }
}


