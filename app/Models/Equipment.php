<?php
require_once '../app/Core/Model.php';

class Equipment extends Model {
    
    public function getAll($search = '', $limit = 10, $offset = 0) {
        $sql = "SELECT * FROM equipment";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE name LIKE :search OR brand LIKE :search OR category LIKE :search";
            $params['search'] = "%$search%";
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
    
    public function getTotal($search = '') {
        $sql = "SELECT COUNT(*) FROM equipment";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " WHERE name LIKE :search OR brand LIKE :search OR category LIKE :search";
            $params['search'] = "%$search%";
        }
        
        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    public function findById($id) {
        $stmt = $this->getPDO()->prepare("SELECT * FROM equipment WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $stmt = $this->getPDO()->prepare("
            INSERT INTO equipment (name, brand, category, description, repair_price)
            VALUES (:name, :brand, :category, :description, :repair_price)
        ");
        return $stmt->execute([
            'name' => $data['name'],
            'brand' => $data['brand'] ?? null,
            'category' => $data['category'] ?? null,
            'description' => $data['description'] ?? null,
            'repair_price' => $data['repair_price'] ?? 0
        ]);
    }
    
    public function update($id, $data) {
        $stmt = $this->getPDO()->prepare("
            UPDATE equipment 
            SET name = :name, brand = :brand, category = :category, description = :description, repair_price = :repair_price
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'brand' => $data['brand'] ?? null,
            'category' => $data['category'] ?? null,
            'description' => $data['description'] ?? null,
            'repair_price' => $data['repair_price'] ?? 0
        ]);
    }
    
    public function delete($id) {
        $stmt = $this->getPDO()->prepare("DELETE FROM equipment WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getAllList() {
        $stmt = $this->getPDO()->query("SELECT * FROM equipment ORDER BY category ASC, name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findOrCreate($name, $brand = '', $category = 'Peralatan Rumah Tangga', $description = '') {
        $stmt = $this->getPDO()->prepare("SELECT * FROM equipment WHERE LOWER(name) = LOWER(:name) AND LOWER(brand) = LOWER(:brand) LIMIT 1");
        $stmt->execute(['name' => trim($name), 'brand' => trim($brand)]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            return $existing['id'];
        }
        
        $this->create([
            'name' => trim($name),
            'brand' => trim($brand),
            'category' => $category,
            'description' => $description
        ]);
        
        return $this->getPDO()->lastInsertId();
    }
}

