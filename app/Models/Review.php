<?php
require_once '../app/Core/Model.php';

class Review extends Model {

    public function findByOrderId($orderId) {
        $stmt = $this->getPDO()->prepare("
            SELECT r.*, 
                   u_cust.name as customer_name,
                   u_tech.name as technician_name
            FROM reviews r
            JOIN users u_cust ON r.customer_id = u_cust.id
            JOIN technicians t ON r.technician_id = t.id
            JOIN users u_tech ON t.user_id = u_tech.id
            WHERE r.order_id = :order_id
            LIMIT 1
        ");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createReview($data) {
        $pdo = $this->getPDO();
        $pdo->beginTransaction();

        try {
            $sql = "
                INSERT INTO reviews (order_id, customer_id, technician_id, rating, comment, created_at)
                VALUES (:order_id, :customer_id, :technician_id, :rating, :comment, NOW())
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':order_id' => $data['order_id'],
                ':customer_id' => $data['customer_id'],
                ':technician_id' => $data['technician_id'],
                ':rating' => $data['rating'],
                ':comment' => $data['comment'] ?? null
            ]);

            // Recalculate & Update Technician's average rating
            $sqlRating = "
                UPDATE technicians 
                SET rating = COALESCE((SELECT AVG(rating) FROM reviews WHERE technician_id = :tech_id), 0.00)
                WHERE id = :tech_id
            ";
            $stmtRating = $pdo->prepare($sqlRating);
            $stmtRating->execute([':tech_id' => $data['technician_id']]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    public function getAllForAdmin($search = '', $limit = 10, $offset = 0) {
        $sql = "
            SELECT r.*, 
                   u_cust.name as customer_name, u_cust.email as customer_email,
                   u_tech.name as technician_name, t.specialization as technician_specialization,
                   e.name as equipment_name, e.brand as equipment_brand,
                   so.service_date
            FROM reviews r
            JOIN service_orders so ON r.order_id = so.id
            JOIN equipment e ON so.equipment_id = e.id
            JOIN users u_cust ON r.customer_id = u_cust.id
            JOIN technicians t ON r.technician_id = t.id
            JOIN users u_tech ON t.user_id = u_tech.id
            WHERE 1=1
        ";

        $params = [];
        if (!empty($search)) {
            $sql .= " AND (r.order_id LIKE :search OR u_cust.name LIKE :search OR u_tech.name LIKE :search OR r.comment LIKE :search OR e.name LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->getPDO()->prepare($sql);
        foreach($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalForAdmin($search = '') {
        $sql = "
            SELECT COUNT(*) 
            FROM reviews r
            JOIN service_orders so ON r.order_id = so.id
            JOIN users u_cust ON r.customer_id = u_cust.id
            JOIN technicians t ON r.technician_id = t.id
            JOIN users u_tech ON t.user_id = u_tech.id
            WHERE 1=1
        ";

        $params = [];
        if (!empty($search)) {
            $sql .= " AND (r.order_id LIKE :search OR u_cust.name LIKE :search OR u_tech.name LIKE :search OR r.comment LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
