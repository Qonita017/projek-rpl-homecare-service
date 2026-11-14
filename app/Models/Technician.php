<?php
require_once '../app/Core/Model.php';

class Technician extends Model {
    
    public function create($userId) {
        $stmt = $this->getPDO()->prepare("
            INSERT INTO technicians (user_id) 
            VALUES (:user_id)
        ");
        return $stmt->execute(['user_id' => $userId]);
    }

    public function findByUserId($userId) {
        $stmt = $this->getPDO()->prepare("
            SELECT t.*, u.name, u.email, u.phone
            FROM technicians t
            JOIN users u ON t.user_id = u.id
            WHERE t.user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getAll($search = '', $limit = 10, $offset = 0) {
        $sql = "SELECT t.*, u.name, u.email, u.phone 
                FROM technicians t 
                JOIN users u ON t.user_id = u.id";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE u.name LIKE :search OR u.email LIKE :search OR t.specialization LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        $sql .= " ORDER BY t.id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->getPDO()->prepare($sql);
        foreach($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotal($search = '') {
        $sql = "SELECT COUNT(*) FROM technicians t JOIN users u ON t.user_id = u.id";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE u.name LIKE :search OR u.email LIKE :search OR t.specialization LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    public function findByIdWithUser($id) {
        $stmt = $this->getPDO()->prepare("
            SELECT t.*, u.name, u.email, u.phone, u.address 
            FROM technicians t 
            JOIN users u ON t.user_id = u.id 
            WHERE t.id = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $data) {
        $stmt = $this->getPDO()->prepare("
            UPDATE technicians 
            SET specialization = :specialization, experience = :experience, availability = :availability
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'specialization' => $data['specialization'] ?? null,
            'experience' => $data['experience'] ?? null,
            'availability' => isset($data['availability']) ? (int)$data['availability'] : 1
        ]);
    }

    public function updateByUserId($userId, $data) {
        $stmt = $this->getPDO()->prepare("
            UPDATE technicians 
            SET specialization = :specialization, experience = :experience, availability = :availability
            WHERE user_id = :user_id
        ");
        return $stmt->execute([
            'user_id' => $userId,
            'specialization' => $data['specialization'] ?? null,
            'experience' => $data['experience'] ?? null,
            'availability' => isset($data['availability']) ? (int)$data['availability'] : 1
        ]);
    }

    public function getAvailableTechnicians() {
        $sql = "
            SELECT t.*, u.name, u.email, u.phone 
            FROM technicians t 
            JOIN users u ON t.user_id = u.id
            WHERE t.availability = 1
            ORDER BY u.name ASC
        ";
        return $this->getPDO()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRating($technicianId) {
        $sql = "
            UPDATE technicians 
            SET rating = COALESCE((SELECT AVG(rating) FROM reviews WHERE technician_id = :tech_id), 0.00)
            WHERE id = :id
        ";
        $stmt = $this->getPDO()->prepare($sql);
        return $stmt->execute([':tech_id' => $technicianId, ':id' => $technicianId]);
    }
}


