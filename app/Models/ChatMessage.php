<?php
require_once '../app/Core/Model.php';

class ChatMessage extends Model {

    public function addMessage($sessionId, $sender, $message) {
        $stmt = $this->getPDO()->prepare("
            INSERT INTO chat_messages (session_id, sender, message, created_at)
            VALUES (:session_id, :sender, :message, NOW())
        ");
        $stmt->execute([
            ':session_id' => $sessionId,
            ':sender' => $sender,
            ':message' => $message
        ]);

        // Touch session updated_at timestamp
        $stmtTouch = $this->getPDO()->prepare("UPDATE chat_sessions SET updated_at = NOW() WHERE id = :session_id");
        $stmtTouch->execute([':session_id' => $sessionId]);

        return $this->getPDO()->lastInsertId();
    }

    public function getMessagesBySession($sessionId) {
        $stmt = $this->getPDO()->prepare("
            SELECT * FROM chat_messages
            WHERE session_id = :session_id
            ORDER BY id ASC
        ");
        $stmt->execute([':session_id' => $sessionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLastMessagesBySession($sessionId, $limit = 10) {
        $sql = "
            SELECT * FROM (
                SELECT * FROM chat_messages
                WHERE session_id = :session_id
                ORDER BY id DESC
                LIMIT :limit
            ) sub
            ORDER BY id ASC
        ";
        $stmt = $this->getPDO()->prepare($sql);
        $stmt->bindValue(':session_id', (int)$sessionId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countMessagesBySession($sessionId) {
        $stmt = $this->getPDO()->prepare("SELECT COUNT(*) FROM chat_messages WHERE session_id = :session_id");
        $stmt->execute([':session_id' => $sessionId]);
        return $stmt->fetchColumn();
    }
}
