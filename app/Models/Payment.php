<?php
require_once '../app/Core/Model.php';

class Payment extends Model {

    /**
     * Cari pembayaran berdasarkan order_id
     */
    public function findByOrderId($orderId) {
        $stmt = $this->getPDO()->prepare("
            SELECT p.*,
                   so.final_cost, so.estimated_cost, so.status as order_status,
                   u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                   e.name as equipment_name, e.brand as equipment_brand
            FROM payments p
            JOIN service_orders so ON p.order_id = so.id
            JOIN users u ON p.customer_id = u.id
            JOIN equipment e ON so.equipment_id = e.id
            WHERE p.order_id = :order_id
            LIMIT 1
        ");
        $stmt->execute([':order_id' => $orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cari pembayaran berdasarkan ID
     */
    public function findById($id) {
        $stmt = $this->getPDO()->prepare("
            SELECT p.*,
                   so.final_cost, so.estimated_cost, so.status as order_status,
                   u.name as customer_name, u.email as customer_email, u.phone as customer_phone,
                   e.name as equipment_name, e.brand as equipment_brand
            FROM payments p
            JOIN service_orders so ON p.order_id = so.id
            JOIN users u ON p.customer_id = u.id
            JOIN equipment e ON so.equipment_id = e.id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Simpan data pembayaran baru
     */
    public function createPayment($data) {
        $sql = "
            INSERT INTO payments (
                order_id, 
                customer_id, 
                amount, 
                payment_method, 
                payment_provider, 
                sender_name, 
                sender_account, 
                proof_image, 
                notes, 
                status, 
                paid_at, 
                created_at
            ) VALUES (
                :order_id, 
                :customer_id, 
                :amount, 
                :payment_method, 
                :payment_provider, 
                :sender_name, 
                :sender_account, 
                :proof_image, 
                :notes, 
                :status, 
                :paid_at, 
                NOW()
            )
        ";

        $stmt = $this->getPDO()->prepare($sql);
        $result = $stmt->execute([
            ':order_id'         => $data['order_id'],
            ':customer_id'      => $data['customer_id'],
            ':amount'           => $data['amount'],
            ':payment_method'   => $data['payment_method'],
            ':payment_provider' => $data['payment_provider'] ?? null,
            ':sender_name'      => $data['sender_name'] ?? null,
            ':sender_account'   => $data['sender_account'] ?? null,
            ':proof_image'      => $data['proof_image'] ?? null,
            ':notes'            => $data['notes'] ?? null,
            ':status'           => $data['status'] ?? 'PAID',
            ':paid_at'          => $data['paid_at'] ?? date('Y-m-d H:i:s')
        ]);

        return $result ? (int)$this->getPDO()->lastInsertId() : false;
    }

    /**
     * Update status pembayaran
     */
    public function updateStatus($paymentId, $status) {
        $stmt = $this->getPDO()->prepare("
            UPDATE payments 
            SET status = :status, updated_at = NOW() 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':status' => $status,
            ':id'     => $paymentId
        ]);
    }

    /**
     * Ambil semua pesanan customer yang statusnya COMPLETED dan belum dibayar
     */
    public function getUnpaidOrdersByCustomer($customerId) {
        $stmt = $this->getPDO()->prepare("
            SELECT so.*,
                   e.name as equipment_name, e.brand as equipment_brand, e.category as equipment_category,
                   t_user.name as technician_name, t_user.phone as technician_phone,
                   sr.inspection_result, sr.repair_action, sr.cost as report_cost
            FROM service_orders so
            JOIN equipment e ON so.equipment_id = e.id
            LEFT JOIN technicians t ON so.technician_id = t.id
            LEFT JOIN users t_user ON t.user_id = t_user.id
            LEFT JOIN service_reports sr ON so.id = sr.order_id
            LEFT JOIN payments p ON so.id = p.order_id
            WHERE so.customer_id = :customer_id 
              AND so.status = 'COMPLETED'
              AND p.id IS NULL
            ORDER BY so.updated_at DESC
        ");
        $stmt->execute([':customer_id' => $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ambil riwayat pembayaran yang telah dilakukan oleh customer
     */
    public function getPaidOrdersByCustomer($customerId) {
        $stmt = $this->getPDO()->prepare("
            SELECT p.*,
                   so.id as order_id, so.service_date, so.final_cost,
                   e.name as equipment_name, e.brand as equipment_brand,
                   t_user.name as technician_name
            FROM payments p
            JOIN service_orders so ON p.order_id = so.id
            JOIN equipment e ON so.equipment_id = e.id
            LEFT JOIN technicians t ON so.technician_id = t.id
            LEFT JOIN users t_user ON t.user_id = t_user.id
            WHERE p.customer_id = :customer_id
            ORDER BY p.paid_at DESC
        ");
        $stmt->execute([':customer_id' => $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

