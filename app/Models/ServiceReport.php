<?php
require_once '../app/Core/Model.php';

class ServiceReport extends Model {
    
    public function findByOrderId($orderId) {
        $stmt = $this->getPDO()->prepare("
            SELECT sr.*, u.name as technician_name, u.phone as technician_phone 
            FROM service_reports sr
            JOIN technicians t ON sr.technician_id = t.id
            JOIN users u ON t.user_id = u.id
            WHERE sr.order_id = :order_id
            LIMIT 1
        ");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createOrUpdate($orderId, $technicianId, $data) {
        $existing = $this->findByOrderId($orderId);
        
        if ($existing) {
            $sql = "
                UPDATE service_reports 
                SET inspection_result = :inspection_result,
                    repair_action = :repair_action,
                    notes = :notes,
                    cost = :cost,
                    completed_at = :completed_at
                WHERE order_id = :order_id AND technician_id = :technician_id
            ";
        } else {
            $sql = "
                INSERT INTO service_reports (order_id, technician_id, inspection_result, repair_action, notes, cost, completed_at)
                VALUES (:order_id, :technician_id, :inspection_result, :repair_action, :notes, :cost, :completed_at)
            ";
        }

        $stmt = $this->getPDO()->prepare($sql);
        return $stmt->execute([
            ':order_id' => $orderId,
            ':technician_id' => $technicianId,
            ':inspection_result' => $data['inspection_result'],
            ':repair_action' => $data['repair_action'] ?? null,
            ':notes' => $data['notes'] ?? null,
            ':cost' => $data['cost'] ?? 0,
            ':completed_at' => $data['completed_at'] ?? date('Y-m-d H:i:s')
        ]);
    }
}
