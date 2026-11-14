<?php
require_once '../app/Core/Model.php';

class Report extends Model {

    private function buildWhereClause($filters, &$params) {
        $where = " WHERE 1=1";

        if (!empty($filters['start_date'])) {
            $where .= " AND DATE(so.service_date) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $where .= " AND DATE(so.service_date) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($filters['status']) && $filters['status'] !== 'ALL') {
            $where .= " AND so.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['equipment_id']) && $filters['equipment_id'] !== 'ALL') {
            $where .= " AND so.equipment_id = :equipment_id";
            $params[':equipment_id'] = (int)$filters['equipment_id'];
        }

        if (!empty($filters['technician_id']) && $filters['technician_id'] !== 'ALL') {
            $where .= " AND so.technician_id = :technician_id";
            $params[':technician_id'] = (int)$filters['technician_id'];
        }

        return $where;
    }

    public function getSummaryStats($filters = []) {
        $params = [];
        $where = $this->buildWhereClause($filters, $params);

        $sql = "
            SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN so.status = 'COMPLETED' THEN 1 ELSE 0 END) as total_completed,
                SUM(CASE WHEN so.status = 'PENDING' THEN 1 ELSE 0 END) as total_pending,
                SUM(CASE WHEN so.status IN ('ASSIGNED', 'ACCEPTED', 'ON_THE_WAY', 'IN_PROGRESS') THEN 1 ELSE 0 END) as total_active,
                SUM(CASE WHEN so.status = 'CANCELLED' THEN 1 ELSE 0 END) as total_cancelled,
                COALESCE(SUM(so.final_cost), 0) as total_revenue
            FROM service_orders so
            JOIN equipment e ON so.equipment_id = e.id
            {$where}
        ";

        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute($params);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        // Average Technician Rating
        $stmtRating = $this->getPDO()->query("SELECT COALESCE(AVG(rating), 0) FROM technicians");
        $summary['avg_technician_rating'] = $stmtRating->fetchColumn();

        return $summary;
    }

    public function getOrdersByStatus($filters = []) {
        $params = [];
        $where = $this->buildWhereClause($filters, $params);

        $sql = "
            SELECT so.status, COUNT(*) as count
            FROM service_orders so
            JOIN equipment e ON so.equipment_id = e.id
            {$where}
            GROUP BY so.status
        ";

        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function getOrdersByEquipment($filters = []) {
        $params = [];
        $where = $this->buildWhereClause($filters, $params);

        $sql = "
            SELECT e.name, COUNT(*) as count
            FROM service_orders so
            JOIN equipment e ON so.equipment_id = e.id
            {$where}
            GROUP BY e.name
            ORDER BY count DESC
            LIMIT 10
        ";

        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getJobsPerTechnician($filters = []) {
        $params = [];
        $where = $this->buildWhereClause($filters, $params);

        $sql = "
            SELECT 
                t.id as technician_id,
                u.name as technician_name,
                t.specialization,
                t.rating,
                t.experience,
                COUNT(so.id) as total_assigned,
                SUM(CASE WHEN so.status = 'COMPLETED' THEN 1 ELSE 0 END) as total_completed,
                SUM(CASE WHEN so.status IN ('ASSIGNED', 'ACCEPTED', 'ON_THE_WAY', 'IN_PROGRESS') THEN 1 ELSE 0 END) as total_in_progress,
                COALESCE(SUM(so.final_cost), 0) as total_revenue
            FROM technicians t
            JOIN users u ON t.user_id = u.id
            LEFT JOIN service_orders so ON t.id = so.technician_id
            {$where}
            GROUP BY t.id, u.name, t.specialization, t.rating, t.experience
            ORDER BY total_completed DESC, t.rating DESC
        ";

        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFilteredOrders($filters = [], $limit = 20, $offset = 0) {
        $params = [];
        $where = $this->buildWhereClause($filters, $params);

        $sql = "
            SELECT 
                so.*, 
                u_cust.name as customer_name, u_cust.phone as customer_phone,
                e.name as equipment_name, e.brand as equipment_brand, e.category as equipment_category,
                u_tech.name as technician_name
            FROM service_orders so
            JOIN users u_cust ON so.customer_id = u_cust.id
            JOIN equipment e ON so.equipment_id = e.id
            LEFT JOIN technicians t ON so.technician_id = t.id
            LEFT JOIN users u_tech ON t.user_id = u_tech.id
            {$where}
            ORDER BY so.service_date DESC, so.id DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->getPDO()->prepare($sql);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalFilteredOrders($filters = []) {
        $params = [];
        $where = $this->buildWhereClause($filters, $params);

        $sql = "
            SELECT COUNT(*)
            FROM service_orders so
            JOIN equipment e ON so.equipment_id = e.id
            {$where}
        ";

        $stmt = $this->getPDO()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
