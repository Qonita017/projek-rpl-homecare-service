<?php
require_once '../app/Core/Model.php';

class ChatSession extends Model {

    public function createSession($customerId, $title = 'Percakapan Baru') {
        $stmt = $this->getPDO()->prepare("
            INSERT INTO chat_sessions (customer_id, title) 
            VALUES (:customer_id, :title)
        ");
        $stmt->execute([
            ':customer_id' => $customerId,
            ':title' => $title
        ]);
        return $this->getPDO()->lastInsertId();
    }

    public function getSessionsByCustomer($customerId) {
        $stmt = $this->getPDO()->prepare("
            SELECT cs.*, 
                   (SELECT message FROM chat_messages cm WHERE cm.session_id = cs.id ORDER BY cm.id DESC LIMIT 1) as last_message,
                   (SELECT COUNT(*) FROM chat_messages cm WHERE cm.session_id = cs.id) as message_count
            FROM chat_sessions cs
            WHERE cs.customer_id = :customer_id
            ORDER BY cs.updated_at DESC, cs.id DESC
        ");
        $stmt->execute([':customer_id' => $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByIdAndCustomer($sessionId, $customerId) {
        $stmt = $this->getPDO()->prepare("
            SELECT * FROM chat_sessions 
            WHERE id = :id AND customer_id = :customer_id
            LIMIT 1
        ");
        $stmt->execute([
            ':id' => $sessionId,
            ':customer_id' => $customerId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateTitle($sessionId, $customerId, $title) {
        $stmt = $this->getPDO()->prepare("
            UPDATE chat_sessions 
            SET title = :title 
            WHERE id = :id AND customer_id = :customer_id
        ");
        return $stmt->execute([
            ':title' => mb_strimwidth($title, 0, 50, '...'),
            ':id' => $sessionId,
            ':customer_id' => $customerId
        ]);
    }

    public function touchSession($sessionId) {
        $stmt = $this->getPDO()->prepare("
            UPDATE chat_sessions 
            SET updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute([':id' => $sessionId]);
    }

    public function deleteSession($sessionId, $customerId) {
        $stmt = $this->getPDO()->prepare("
            DELETE FROM chat_sessions 
            WHERE id = :id AND customer_id = :customer_id
        ");
        return $stmt->execute([
            ':id' => $sessionId,
            ':customer_id' => $customerId
        ]);
    }
}
