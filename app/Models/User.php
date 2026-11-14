<?php
require_once '../app/Core/Model.php';

class User extends Model {
    
    public function findByEmail($email) {
        $stmt = $this->getPDO()->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->getPDO()->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->getPDO()->prepare("
            INSERT INTO users (name, email, password, role, phone, address) 
            VALUES (:name, :email, :password, :role, :phone, :address)
        ");
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null
        ]);
        return $this->getPDO()->lastInsertId();
    }
    
    public function getAllByRole($role, $search = '', $limit = 10, $offset = 0) {
        $sql = "SELECT * FROM users WHERE role = :role";
        $params = [':role' => $role];
        
        if (!empty($search)) {
            $sql .= " AND (name LIKE :search OR email LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->getPDO()->prepare($sql);
        foreach($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotalByRole($role, $search = '') {
        $sql = "SELECT COUNT(*) FROM users WHERE role = :role";
        $params = [':role' => $role];
        
        if (!empty($search)) {
            $sql .= " AND (name LIKE :search OR email LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /** Get all users regardless of role (for admin user management) */
    public function getAllUsers($search = '', $limit = 10, $offset = 0) {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (name LIKE :search OR email LIKE :search OR role LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY role ASC, id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->getPDO()->prepare($sql);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Count all users regardless of role */
    public function getTotalAllUsers($search = '') {
        $sql = "SELECT COUNT(*) FROM users WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (name LIKE :search OR email LIKE :search OR role LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    public function update($id, $data) {
        $setClauses = ["name = :name", "phone = :phone", "address = :address"];
        $params = [
            'id'      => $id,
            'name'    => $data['name'],
            'phone'   => $data['phone'] ?? null,
            'address' => $data['address'] ?? null
        ];

        if (!empty($data['role'])) {
            $setClauses[] = "role = :role";
            $params['role'] = $data['role'];
        }

        if (!empty($data['password'])) {
            $setClauses[] = "password = :password";
            $params['password'] = $data['password'];
        }

        $sql = "UPDATE users SET " . implode(', ', $setClauses) . " WHERE id = :id";
        $stmt = $this->getPDO()->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function updateProfile($id, $data) {
        $sql = "UPDATE users SET name = :name, phone = :phone, address = :address WHERE id = :id";
        $stmt = $this->getPDO()->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':phone' => $data['phone'] ?? null,
            ':address' => $data['address'] ?? null
        ]);
    }

    public function updatePassword($id, $hashedPassword) {
        $stmt = $this->getPDO()->prepare("UPDATE users SET password = :password WHERE id = :id");
        return $stmt->execute([
            ':password' => $hashedPassword,
            ':id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->getPDO()->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
