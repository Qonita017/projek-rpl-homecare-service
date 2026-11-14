<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/public/index.php';
require_once "app/Config/config.php";
require_once "app/Config/Database.php";

try {
    $db = new Database();
    $pdo = $db->getConnection();

    $sql = "
    CREATE TABLE IF NOT EXISTS chat_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        title VARCHAR(255) NOT NULL DEFAULT 'Percakapan Baru',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_customer (customer_id)
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        session_id INT NOT NULL,
        sender ENUM('USER', 'AI') NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE,
        INDEX idx_session (session_id)
    ) ENGINE=InnoDB;
    ";

    $pdo->exec($sql);
    echo "Tables chat_sessions & chat_messages successfully created!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
