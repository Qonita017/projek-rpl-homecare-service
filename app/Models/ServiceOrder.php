<?php
require_once '../app/Core/Model.php';

class ServiceOrder extends Model {
    
    // ADMIN STATS
    public function getAdminStats() {
        $stats = [];
        
        $stmt = $this->getPDO()->query("SELECT COUNT(*) FROM users WHERE role = 'CUSTOMER'");
        $stats['total_customer'] = $stmt->fetchColumn();
        
        $stmt = $this->getPDO()->query("SELECT COUNT(*) FROM users WHERE role = 'TEKNISI'");
        $stats['total_teknisi'] = $stmt->fetchColumn();
        
        $stmt = $this->getPDO()->query("SELECT COUNT(*) FROM service_orders WHERE status = 'PENDING'");
        $stats['pesanan_pending'] = $stmt->fetchColumn();
        
        $stmt = $this->getPDO()->query("SELECT COUNT(*) FROM service_orders WHERE status IN ('ASSIGNED', 'ACCEPTED', 'ON_THE_WAY', 'IN_PROGRESS')");
        $stats['pesanan_aktif'] = $stmt->fetchColumn();
        
        $stmt = $this->getPDO()->query("SELECT COUNT(*) FROM service_orders WHERE status = 'COMPLETED'");
        $stats['pesanan_selesai'] = $stmt->fetchColumn();
        
        return $stats;
    }

    public function getRecentAdminOrders($limit = 5) {
        $sql = "
            SELECT so.*, u.name as customer_name, e.name as equipment_name, t_user.name as technician_name
            FROM service_orders so
            JOIN users u ON so.customer_id = u.id
            JOIN equipment e ON so.equipment_id = e.id
            LEFT JOIN technicians t ON so.technician_id = t.id
            LEFT JOIN users t_user ON t.user_id = t_user.id
            ORDER BY so.created_at DESC
            LIMIT " . (int)$limit;
        return $this->getPDO()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // CUSTOMER STATS
    public function getCustomerStats($customerId) {
        $stats = [];
        
        $stmt = $this->getPDO()->prepare("SELECT COUNT(*) FROM service_orders WHERE customer_id = ?");
        $stmt->execute([$customerId]);
        $stats['total_pesanan'] = $stmt->fetchColumn();
        
        $stmt = $this->getPDO()->prepare("SELECT COUNT(*) FROM service_orders WHERE customer_id = ? AND status NOT IN ('COMPLETED', 'CANCELLED', 'PENDING')");
        $stmt->execute([$customerId]);
        $stats['pesanan_aktif'] = $stmt->fetchColumn();

        $stmt = $this->getPDO()->prepare("SELECT COUNT(*) FROM service_orders WHERE customer_id = ? AND status = 'COMPLETED'");
        $stmt->execute([$customerId]);
        $stats['pesanan_selesai'] = $stmt->fetchColumn();
        
        return $stats;
    }

    public function getRecentCustomerOrders($customerId, $limit = 5) {
        $sql = "
            SELECT so.*, e.name as equipment_name, t_user.name as technician_name
            FROM service_orders so
            JOIN equipment e ON so.equipment_id = e.id
            LEFT JOIN technicians t ON so.technician_id = t.id
            LEFT JOIN users t_user ON t.user_id = t_user.id
            WHERE so.customer_id = ?
            ORDER BY so.created_at DESC
            LIMIT " . (int)$limit;
        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // TECHNICIAN STATS
    public function getTechnicianStats($technicianId) {
        $stats = [];
        
        $stmt = $this->getPDO()->prepare("SELECT COUNT(*) FROM service_orders WHERE technician_id = ? AND status = 'ASSIGNED'");
        $stmt->execute([$technicianId]);
        $stats['pekerjaan_baru'] = $stmt->fetchColumn();
        
        $stmt = $this->getPDO()->prepare("SELECT COUNT(*) FROM service_orders WHERE technician_id = ? AND status IN ('ACCEPTED', 'ON_THE_WAY', 'IN_PROGRESS')");
        $stmt->execute([$technicianId]);
        $stats['pekerjaan_aktif'] = $stmt->fetchColumn();

        $stmt = $this->getPDO()->prepare("SELECT COUNT(*) FROM service_orders WHERE technician_id = ? AND status = 'COMPLETED'");
        $stmt->execute([$technicianId]);
        $stats['pekerjaan_selesai'] = $stmt->fetchColumn();
        
        return $stats;
    }

    public function getRecentTechnicianOrders($technicianId, $limit = 5) {
        $sql = "
            SELECT so.*, u.name as customer_name, e.name as equipment_name
            FROM service_orders so
            JOIN users u ON so.customer_id = u.id
            JOIN equipment e ON so.equipment_id = e.id
            WHERE so.technician_id = ?
            ORDER BY so.created_at DESC
            LIMIT " . (int)$limit;
        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute([$technicianId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // CUSTOMER SERVICE ORDER MANAGEMENT
    public function createOrder($data) {
        $sql = "
            INSERT INTO service_orders (customer_id, equipment_id, complaint, photo, ai_diagnosis, status, service_date, address, estimated_cost, final_cost, payment_preference)
            VALUES (:customer_id, :equipment_id, :complaint, :photo, :ai_diagnosis, 'PENDING', :service_date, :address, NULL, NULL, :payment_preference)
        ";
        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute([
            ':customer_id' => $data['customer_id'],
            ':equipment_id' => $data['equipment_id'],
            ':complaint' => $data['complaint'],
            ':photo' => $data['photo'] ?? null,
            ':ai_diagnosis' => $data['ai_diagnosis'] ?? null,
            ':service_date' => $data['service_date'],
            ':address' => $data['address'],
            ':payment_preference' => $data['payment_preference'] ?? null
        ]);
        return $this->getPDO()->lastInsertId();
    }

    public function findByIdAndCustomer($orderId, $customerId) {
        $sql = "
            SELECT so.*, 
                   e.name as equipment_name, e.brand as equipment_brand, e.category as equipment_category, e.description as equipment_description,
                   u.name as customer_name, u.phone as customer_phone, u.email as customer_email, u.address as customer_address,
                   t.id as technician_table_id, t.specialization as technician_specialization, t.rating as technician_rating, t.experience as technician_experience,
                   t_user.name as technician_name, t_user.phone as technician_phone, t_user.email as technician_email,
                   sr.inspection_result, sr.repair_action, sr.notes as report_notes, sr.cost as report_cost, sr.completed_at as report_completed_at,
                   r.rating as review_rating, r.comment as review_comment, r.created_at as review_created_at,
                   p.id as payment_id, p.payment_method, p.payment_provider, p.amount as payment_amount,
                   p.sender_name as payment_sender_name, p.sender_account as payment_sender_account,
                   p.proof_image as payment_proof_image, p.status as payment_status, p.paid_at as payment_paid_at,
                   p.notes as payment_notes
            FROM service_orders so
            JOIN users u ON so.customer_id = u.id
            JOIN equipment e ON so.equipment_id = e.id
            LEFT JOIN technicians t ON so.technician_id = t.id
            LEFT JOIN users t_user ON t.user_id = t_user.id
            LEFT JOIN service_reports sr ON so.id = sr.order_id
            LEFT JOIN reviews r ON so.id = r.order_id
            LEFT JOIN payments p ON so.id = p.order_id
            WHERE so.id = :order_id AND so.customer_id = :customer_id
            LIMIT 1
        ";
        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute([
            ':order_id' => $orderId,
            ':customer_id' => $customerId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOrdersByCustomer($customerId, $statusGroup = 'ALL', $search = '', $limit = 10, $offset = 0) {
        $sql = "
            SELECT so.*, 
                   e.name as equipment_name, e.brand as equipment_brand, e.category as equipment_category,
                   t_user.name as technician_name, t_user.phone as technician_phone,
                   p.id as payment_id, p.payment_method, p.payment_provider, p.status as payment_status, p.amount as payment_amount
            FROM service_orders so
            JOIN equipment e ON so.equipment_id = e.id
            LEFT JOIN technicians t ON so.technician_id = t.id
            LEFT JOIN users t_user ON t.user_id = t_user.id
            LEFT JOIN payments p ON so.id = p.order_id
            WHERE so.customer_id = :customer_id
        ";
        
        $params = [':customer_id' => $customerId];
        
        if ($statusGroup === 'ACTIVE') {
            $sql .= " AND so.status IN ('PENDING', 'ASSIGNED', 'ACCEPTED', 'ON_THE_WAY', 'IN_PROGRESS')";
        } elseif ($statusGroup === 'HISTORY') {
            $sql .= " AND so.status IN ('COMPLETED', 'CANCELLED')";
        }

        if (!empty($search)) {
            $sql .= " AND (so.id LIKE :search OR e.name LIKE :search OR e.brand LIKE :search OR so.complaint LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY so.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->getPDO()->prepare($sql);
        foreach($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalOrdersByCustomer($customerId, $statusGroup = 'ALL', $search = '') {
        $sql = "
            SELECT COUNT(*) 
            FROM service_orders so
            JOIN equipment e ON so.equipment_id = e.id
            WHERE so.customer_id = :customer_id
        ";
        
        $params = [':customer_id' => $customerId];
        
        if ($statusGroup === 'ACTIVE') {
            $sql .= " AND so.status IN ('PENDING', 'ASSIGNED', 'ACCEPTED', 'ON_THE_WAY', 'IN_PROGRESS')";
        } elseif ($statusGroup === 'HISTORY') {
            $sql .= " AND so.status IN ('COMPLETED', 'CANCELLED')";
        }

        if (!empty($search)) {
            $sql .= " AND (so.id LIKE :search OR e.name LIKE :search OR e.brand LIKE :search OR so.complaint LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // ADMIN SERVICE ORDER MANAGEMENT
    public function getAllOrdersForAdmin($statusFilter = '', $search = '', $limit = 10, $offset = 0) {
        $sql = "
            SELECT so.*, 
                   u.name as customer_name, u.phone as customer_phone, u.email as customer_email,
                   e.name as equipment_name, e.brand as equipment_brand, e.category as equipment_category,
                   t_user.name as technician_name, t_user.phone as technician_phone,
                   t.specialization as technician_specialization
            FROM service_orders so
            JOIN users u ON so.customer_id = u.id
            JOIN equipment e ON so.equipment_id = e.id
            LEFT JOIN technicians t ON so.technician_id = t.id
            LEFT JOIN users t_user ON t.user_id = t_user.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($statusFilter) && $statusFilter !== 'ALL') {
            $sql .= " AND so.status = :status";
            $params[':status'] = $statusFilter;
        }

        if (!empty($search)) {
            $sql .= " AND (so.id LIKE :search OR u.name LIKE :search OR u.phone LIKE :search OR e.name LIKE :search OR e.brand LIKE :search OR so.complaint LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY so.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->getPDO()->prepare($sql);
        foreach($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalOrdersForAdmin($statusFilter = '', $search = '') {
        $sql = "
            SELECT COUNT(*) 
            FROM service_orders so
            JOIN users u ON so.customer_id = u.id
            JOIN equipment e ON so.equipment_id = e.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (!empty($statusFilter) && $statusFilter !== 'ALL') {
            $sql .= " AND so.status = :status";
            $params[':status'] = $statusFilter;
        }

        if (!empty($search)) {
            $sql .= " AND (so.id LIKE :search OR u.name LIKE :search OR u.phone LIKE :search OR e.name LIKE :search OR e.brand LIKE :search OR so.complaint LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function findByIdForAdmin($orderId) {
        $sql = "
            SELECT so.*, 
                   e.name as equipment_name, e.brand as equipment_brand, e.category as equipment_category, e.description as equipment_description,
                   u.name as customer_name, u.phone as customer_phone, u.email as customer_email, u.address as customer_address,
                   t.id as technician_table_id, t.specialization as technician_specialization, t.rating as technician_rating, t.experience as technician_experience, t.availability as technician_availability,
                   t_user.name as technician_name, t_user.phone as technician_phone, t_user.email as technician_email,
                   sr.inspection_result, sr.repair_action, sr.notes as report_notes, sr.cost as report_cost, sr.completed_at as report_completed_at,
                   r.rating as review_rating, r.comment as review_comment, r.created_at as review_created_at,
                   p.id as payment_id, p.payment_method, p.payment_provider, p.amount as payment_amount,
                   p.sender_name as payment_sender_name, p.sender_account as payment_sender_account,
                   p.proof_image as payment_proof_image, p.status as payment_status, p.paid_at as payment_paid_at,
                   p.notes as payment_notes
            FROM service_orders so
            JOIN users u ON so.customer_id = u.id
            JOIN equipment e ON so.equipment_id = e.id
            LEFT JOIN technicians t ON so.technician_id = t.id
            LEFT JOIN users t_user ON t.user_id = t_user.id
            LEFT JOIN service_reports sr ON so.id = sr.order_id
            LEFT JOIN reviews r ON so.id = r.order_id
            LEFT JOIN payments p ON so.id = p.order_id
            WHERE so.id = :order_id
            LIMIT 1
        ";
        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function assignTechnician($orderId, $technicianId) {
        $sql = "
            UPDATE service_orders 
            SET technician_id = :technician_id, status = 'ASSIGNED'
            WHERE id = :order_id
        ";
        $stmt = $this->getPDO()->prepare($sql);
        return $stmt->execute([
            ':technician_id' => $technicianId,
            ':order_id' => $orderId
        ]);
    }

    // TECHNICIAN WORKFLOW MANAGEMENT
    public function getOrdersByTechnician($technicianId, $statusFilter = '', $search = '', $limit = 10, $offset = 0) {
        $sql = "
            SELECT so.*, 
                   u.name as customer_name, u.phone as customer_phone, u.email as customer_email,
                   e.name as equipment_name, e.brand as equipment_brand, e.category as equipment_category
            FROM service_orders so
            JOIN users u ON so.customer_id = u.id
            JOIN equipment e ON so.equipment_id = e.id
            WHERE so.technician_id = :technician_id
        ";

        $params = [':technician_id' => $technicianId];

        if (!empty($statusFilter) && $statusFilter !== 'ALL') {
            $sql .= " AND so.status = :status";
            $params[':status'] = $statusFilter;
        }

        if (!empty($search)) {
            $sql .= " AND (so.id LIKE :search OR u.name LIKE :search OR u.phone LIKE :search OR e.name LIKE :search OR e.brand LIKE :search OR so.complaint LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY CASE 
                    WHEN so.status = 'ASSIGNED' THEN 1 
                    WHEN so.status = 'ACCEPTED' THEN 2 
                    WHEN so.status = 'ON_THE_WAY' THEN 3 
                    WHEN so.status = 'IN_PROGRESS' THEN 4 
                    WHEN so.status = 'COMPLETED' THEN 5 
                    ELSE 6 END ASC, so.service_date ASC, so.created_at DESC 
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->getPDO()->prepare($sql);
        foreach($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalOrdersByTechnician($technicianId, $statusFilter = '', $search = '') {
        $sql = "
            SELECT COUNT(*) 
            FROM service_orders so
            JOIN users u ON so.customer_id = u.id
            JOIN equipment e ON so.equipment_id = e.id
            WHERE so.technician_id = :technician_id
        ";

        $params = [':technician_id' => $technicianId];

        if (!empty($statusFilter) && $statusFilter !== 'ALL') {
            $sql .= " AND so.status = :status";
            $params[':status'] = $statusFilter;
        }

        if (!empty($search)) {
            $sql .= " AND (so.id LIKE :search OR u.name LIKE :search OR u.phone LIKE :search OR e.name LIKE :search OR e.brand LIKE :search OR so.complaint LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function findByIdAndTechnician($orderId, $technicianId) {
        $sql = "
            SELECT so.*, 
                   e.name as equipment_name, e.brand as equipment_brand, e.category as equipment_category, e.description as equipment_description,
                   u.name as customer_name, u.phone as customer_phone, u.email as customer_email, u.address as customer_address,
                   sr.inspection_result, sr.repair_action, sr.notes as report_notes, sr.cost as report_cost, sr.completed_at as report_completed_at,
                   r.rating as review_rating, r.comment as review_comment, r.created_at as review_created_at,
                   p.id as payment_id, p.payment_method, p.payment_provider, p.amount as payment_amount,
                   p.sender_name as payment_sender_name, p.sender_account as payment_sender_account,
                   p.proof_image as payment_proof_image, p.status as payment_status, p.paid_at as payment_paid_at,
                   p.notes as payment_notes
            FROM service_orders so
            JOIN users u ON so.customer_id = u.id
            JOIN equipment e ON so.equipment_id = e.id
            LEFT JOIN service_reports sr ON so.id = sr.order_id
            LEFT JOIN reviews r ON so.id = r.order_id
            LEFT JOIN payments p ON so.id = p.order_id
            WHERE so.id = :order_id AND so.technician_id = :technician_id
            LIMIT 1
        ";
        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute([
            ':order_id' => $orderId,
            ':technician_id' => $technicianId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatusByTechnician($orderId, $technicianId, $newStatus) {
        $sql = "
            UPDATE service_orders 
            SET status = :status 
            WHERE id = :order_id AND technician_id = :technician_id
        ";
        $stmt = $this->getPDO()->prepare($sql);
        return $stmt->execute([
            ':status' => $newStatus,
            ':order_id' => $orderId,
            ':technician_id' => $technicianId
        ]);
    }

    public function completeServiceOrderByTechnician($orderId, $technicianId, $reportData) {
        $pdo = $this->getPDO();
        $pdo->beginTransaction();

        try {
            // 1. Insert or update service_reports
            $stmtCheck = $pdo->prepare("SELECT id FROM service_reports WHERE order_id = :order_id");
            $stmtCheck->execute([':order_id' => $orderId]);
            $exists = $stmtCheck->fetch();

            if ($exists) {
                $sqlReport = "
                    UPDATE service_reports 
                    SET inspection_result = :inspection_result,
                        repair_action = :repair_action,
                        notes = :notes,
                        cost = :cost,
                        completed_at = :completed_at
                    WHERE order_id = :order_id AND technician_id = :technician_id
                ";
            } else {
                $sqlReport = "
                    INSERT INTO service_reports (order_id, technician_id, inspection_result, repair_action, notes, cost, completed_at)
                    VALUES (:order_id, :technician_id, :inspection_result, :repair_action, :notes, :cost, :completed_at)
                ";
            }

            $stmtReport = $pdo->prepare($sqlReport);
            $stmtReport->execute([
                ':order_id' => $orderId,
                ':technician_id' => $technicianId,
                ':inspection_result' => $reportData['inspection_result'],
                ':repair_action' => $reportData['repair_action'] ?? null,
                ':notes' => $reportData['notes'] ?? null,
                ':cost' => $reportData['cost'],
                ':completed_at' => date('Y-m-d H:i:s')
            ]);

            // 2. Update service_orders status to COMPLETED and final_cost
            $stmtOrder = $pdo->prepare("
                UPDATE service_orders 
                SET status = 'COMPLETED',
                    final_cost = :final_cost
                WHERE id = :order_id AND technician_id = :technician_id
            ");
            $stmtOrder->execute([
                ':final_cost' => $reportData['cost'],
                ':order_id' => $orderId,
                ':technician_id' => $technicianId
            ]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }
}



