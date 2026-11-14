<?php
require_once __DIR__ . '/../Config/Database.php';

class DatabaseContextService {
    private $pdo;

    public function __construct($pdo = null) {
        if ($pdo) {
            $this->pdo = $pdo;
        } else {
            $this->pdo = (new Database())->getConnection();
        }
    }

    /**
     * Get profile, orders, reports, and reviews for a specific user (Role-Aware)
     * Mendukung peran: CUSTOMER, ADMIN, dan TEKNISI
     */
    public function getUserContext($userId) {
        if (!$userId && isset($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
        }
        if (!$userId) return null;

        // 1. User profile
        $stmt = $this->pdo->prepare("SELECT id, name, email, role, phone, address, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) return null;

        $role = strtoupper($user['role'] ?? 'CUSTOMER');
        $params = [];
        $whereClause = "";

        // Role-specific extensions
        $adminData = null;
        $technicianData = null;

        if ($role === 'ADMIN') {
            // Admin sees all system orders
            $whereClause = "1=1";

            // OMSET = total semua pesanan yang sudah dibayar (baik di tabel payments maupun pesanan COMPLETED dengan final_cost)
            $stmtOmset = $this->pdo->query("
                SELECT 
                    COALESCE(SUM(COALESCE(p.amount, so.final_cost, 0)), 0) as total_omset,
                    COUNT(DISTINCT so.id) as paid_orders_count
                FROM service_orders so
                LEFT JOIN payments p ON so.id = p.order_id AND p.status = 'PAID'
                WHERE p.status = 'PAID' OR so.status = 'COMPLETED'
            ");
            $omsetRow = $stmtOmset->fetch(PDO::FETCH_ASSOC);
            $totalOmset = (float)($omsetRow['total_omset'] ?? 0);
            $paidOrdersCount = (int)($omsetRow['paid_orders_count'] ?? 0);

            // Total nilai pesanan selesai (final_cost)
            $stmtCompleted = $this->pdo->query("SELECT COALESCE(SUM(final_cost), 0) FROM service_orders WHERE status = 'COMPLETED'");
            $totalCompletedCost = (float)$stmtCompleted->fetchColumn();

            // Pesanan selesai tapi belum dibayar
            $stmtUnpaid = $this->pdo->query("
                SELECT COUNT(DISTINCT so.id) as cnt, COALESCE(SUM(so.final_cost), 0) as total
                FROM service_orders so
                LEFT JOIN payments p ON so.id = p.order_id AND p.status = 'PAID'
                WHERE so.status = 'COMPLETED' AND p.id IS NULL
            ");
            $unpaidData = $stmtUnpaid->fetch(PDO::FETCH_ASSOC);
            $unpaidOrdersCount = (int)($unpaidData['cnt'] ?? 0);
            $unpaidOrdersCost  = (float)($unpaidData['total'] ?? 0);

            // Total pembayaran tercatat (untuk backward compat)
            $totalPaid = $totalOmset;

            // Total customer
            $totalCust = (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'CUSTOMER'")->fetchColumn();

            // Daftar pesanan pending yang belum ditugaskan
            $stmtPending = $this->pdo->query("
                SELECT so.id, so.complaint, so.service_date, so.created_at,
                       u.name as customer_name, u.phone as customer_phone, u.address as customer_address,
                       e.name as equipment_name, e.brand as equipment_brand
                FROM service_orders so
                JOIN users u ON so.customer_id = u.id
                JOIN equipment e ON so.equipment_id = e.id
                WHERE so.status = 'PENDING'
                ORDER BY so.created_at DESC
                LIMIT 10
            ");
            $pendingOrders = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

            // Daftar customer terbaru
            $stmtRecentCust = $this->pdo->query("
                SELECT id, name, email, phone, address, created_at 
                FROM users 
                WHERE role = 'CUSTOMER' 
                ORDER BY id DESC 
                LIMIT 10
            ");
            $recentCustomers = $stmtRecentCust->fetchAll(PDO::FETCH_ASSOC);

            // Statistik per status di database
            $orderCounts = [
                'PENDING'     => (int)$this->pdo->query("SELECT COUNT(*) FROM service_orders WHERE status = 'PENDING'")->fetchColumn(),
                'ASSIGNED'    => (int)$this->pdo->query("SELECT COUNT(*) FROM service_orders WHERE status = 'ASSIGNED'")->fetchColumn(),
                'ACCEPTED'    => (int)$this->pdo->query("SELECT COUNT(*) FROM service_orders WHERE status = 'ACCEPTED'")->fetchColumn(),
                'ON_THE_WAY'  => (int)$this->pdo->query("SELECT COUNT(*) FROM service_orders WHERE status = 'ON_THE_WAY'")->fetchColumn(),
                'IN_PROGRESS' => (int)$this->pdo->query("SELECT COUNT(*) FROM service_orders WHERE status = 'IN_PROGRESS'")->fetchColumn(),
                'COMPLETED'   => (int)$this->pdo->query("SELECT COUNT(*) FROM service_orders WHERE status = 'COMPLETED'")->fetchColumn(),
                'CANCELLED'   => (int)$this->pdo->query("SELECT COUNT(*) FROM service_orders WHERE status = 'CANCELLED'")->fetchColumn(),
            ];

            $adminData = [
                'total_omset'          => $totalOmset,         // OMSET: pesanan sudah dibayar
                'paid_orders_count'    => $paidOrdersCount,    // Jumlah pesanan sudah dibayar
                'total_completed_cost' => $totalCompletedCost, // Total biaya semua pesanan selesai
                'unpaid_orders_count'  => $unpaidOrdersCount,  // Pesanan selesai belum dibayar
                'unpaid_orders_cost'   => $unpaidOrdersCost,   // Nilai pesanan belum dibayar
                'total_revenue'        => $totalOmset,         // Alias untuk backward compat (= omset)
                'total_paid'           => $totalOmset,
                'total_customers'      => $totalCust,
                'order_counts'         => $orderCounts,
                'pending_orders'       => $pendingOrders,
                'recent_customers'     => $recentCustomers,
                'paid_orders'          => $this->getPaidOrders()
            ];

        } elseif ($role === 'TEKNISI') {
            // Technician profile
            $stmtTech = $this->pdo->prepare("SELECT id, specialization, experience, availability, rating FROM technicians WHERE user_id = ?");
            $stmtTech->execute([$userId]);
            $techProfile = $stmtTech->fetch(PDO::FETCH_ASSOC);
            $techId = $techProfile['id'] ?? 0;

            if ($techId) {
                $whereClause = "so.technician_id = ?";
                $params = [$techId];

                // Hitung total pekerjaan selesai oleh teknisi ini
                $stmtDone = $this->pdo->prepare("SELECT COUNT(*) FROM service_orders WHERE technician_id = ? AND status = 'COMPLETED'");
                $stmtDone->execute([$techId]);
                $completedJobs = (int)$stmtDone->fetchColumn();

                // Hitung estimasi omset/biaya pekerjaan teknisi ini
                $stmtEarnings = $this->pdo->prepare("SELECT COALESCE(SUM(final_cost), 0) FROM service_orders WHERE technician_id = ? AND status = 'COMPLETED'");
                $stmtEarnings->execute([$techId]);
                $totalEarnings = (float)$stmtEarnings->fetchColumn();

                // Ambil ulasan dari pelanggan untuk teknisi ini
                $stmtRev = $this->pdo->prepare("
                    SELECT r.rating, r.comment, r.created_at, u.name as customer_name, e.name as equipment_name
                    FROM reviews r
                    JOIN service_orders so ON r.order_id = so.id
                    JOIN users u ON r.customer_id = u.id
                    JOIN equipment e ON so.equipment_id = e.id
                    WHERE r.technician_id = ?
                    ORDER BY r.created_at DESC
                    LIMIT 8
                ");
                $stmtRev->execute([$techId]);
                $myReviews = $stmtRev->fetchAll(PDO::FETCH_ASSOC);

                $technicianData = [
                    'tech_id'        => $techId,
                    'profile'        => $techProfile,
                    'completed_jobs' => $completedJobs,
                    'total_earnings' => $totalEarnings,
                    'my_reviews'     => $myReviews
                ];
            } else {
                $whereClause = "1=0";
            }
        } else {
            // Default: CUSTOMER sees own orders
            $whereClause = "so.customer_id = ?";
            $params = [$userId];
        }

        // Orders query with details, equipment, technician, reports, reviews, AND payment status
        $sql = "
            SELECT so.id, so.status, so.complaint, so.ai_diagnosis, so.service_date, so.address as order_address,
                   so.estimated_cost, so.final_cost, so.created_at,
                   u_cust.name as customer_name, u_cust.phone as customer_phone, u_cust.email as customer_email, u_cust.address as customer_main_address,
                   COALESCE(e.id, 0) as equipment_id, 
                   COALESCE(e.name, 'Peralatan Rumah Tangga') as equipment_name, 
                   COALESCE(e.brand, '-') as equipment_brand, 
                   COALESCE(e.category, 'Umum') as equipment_category,
                   t.id as technician_id, u_tech.name as technician_name, u_tech.phone as technician_phone,
                   t.specialization as technician_specialization, t.rating as technician_rating,
                   sr.inspection_result, sr.repair_action, sr.notes as report_notes, sr.cost as report_cost, sr.completed_at as report_completed_at,
                   r.rating as review_rating, r.comment as review_comment, r.created_at as review_created_at,
                   p.id as payment_id, p.amount as payment_amount, p.payment_method, p.payment_provider,
                   p.status as payment_status, p.paid_at
            FROM service_orders so
            LEFT JOIN users u_cust ON so.customer_id = u_cust.id
            LEFT JOIN equipment e ON so.equipment_id = e.id
            LEFT JOIN technicians t ON so.technician_id = t.id
            LEFT JOIN users u_tech ON t.user_id = u_tech.id
            LEFT JOIN service_reports sr ON so.id = sr.order_id
            LEFT JOIN reviews r ON so.id = r.order_id
            LEFT JOIN payments p ON so.id = p.order_id AND p.status = 'PAID'
            WHERE {$whereClause}
            ORDER BY so.created_at DESC
            LIMIT 30
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Stats summary
        $stats = [
            'total'     => count($orders),
            'pending'   => 0,
            'active'    => 0, // ASSIGNED, ACCEPTED, ON_THE_WAY, IN_PROGRESS
            'completed' => 0,
            'cancelled' => 0
        ];

        foreach ($orders as $ord) {
            $st = strtoupper($ord['status']);
            if ($st === 'PENDING') {
                $stats['pending']++;
            } elseif (in_array($st, ['ASSIGNED', 'ACCEPTED', 'ON_THE_WAY', 'IN_PROGRESS'])) {
                $stats['active']++;
            } elseif ($st === 'COMPLETED') {
                $stats['completed']++;
            } elseif ($st === 'CANCELLED') {
                $stats['cancelled']++;
            }
        }

        return [
            'profile'         => $user,
            'role'            => $role,
            'stats'           => $stats,
            'orders'          => $orders,
            'admin_data'      => $adminData,
            'technician_data' => $technicianData
        ];
    }

    /**
     * Get all equipment from database
     */
    public function getEquipmentCatalog() {
        $stmt = $this->pdo->query("SELECT id, name, brand, category, description, repair_price FROM equipment ORDER BY category ASC, name ASC, brand ASC");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $byCategory = [];
        foreach ($items as $item) {
            $cat = $item['category'] ?: 'Lainnya';
            if (!isset($byCategory[$cat])) {
                $byCategory[$cat] = [];
            }
            $byCategory[$cat][] = $item;
        }

        return [
            'total'       => count($items),
            'items'       => $items,
            'by_category' => $byCategory
        ];
    }

    /**
     * Get all technicians from database
     */
    public function getTechnicians() {
        $sql = "
            SELECT t.id, t.specialization, t.experience, t.availability, t.rating,
                   u.name, u.email, u.phone, u.address,
                   (SELECT COUNT(*) FROM service_orders WHERE technician_id = t.id AND status = 'COMPLETED') as completed_jobs
            FROM technicians t
            JOIN users u ON t.user_id = u.id
            ORDER BY t.availability DESC, t.rating DESC, t.experience DESC
        ";
        $stmt = $this->pdo->query($sql);
        $techs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $availableCount = 0;
        foreach ($techs as $tc) {
            if (!empty($tc['availability'])) $availableCount++;
        }

        return [
            'total'           => count($techs),
            'available_count' => $availableCount,
            'busy_count'      => count($techs) - $availableCount,
            'technicians'     => $techs
        ];
    }

    /**
     * Get recent reviews
     */
    public function getReviews($limit = 10) {
        $sql = "
            SELECT r.id, r.order_id, r.rating, r.comment, r.created_at,
                   u_cust.name as customer_name,
                   u_tech.name as technician_name,
                   e.name as equipment_name, e.brand as equipment_brand
            FROM reviews r
            JOIN service_orders so ON r.order_id = so.id
            JOIN equipment e ON so.equipment_id = e.id
            JOIN users u_cust ON r.customer_id = u_cust.id
            JOIN technicians t ON r.technician_id = t.id
            JOIN users u_tech ON t.user_id = u_tech.id
            ORDER BY r.created_at DESC
            LIMIT " . (int)$limit;
        $stmt = $this->pdo->query($sql);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $avgRatingStmt = $this->pdo->query("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews");
        $avgData = $avgRatingStmt->fetch(PDO::FETCH_ASSOC);

        return [
            'average_rating' => round((float)($avgData['avg_rating'] ?? 0), 2),
            'total_reviews'  => (int)($avgData['total_reviews'] ?? 0),
            'recent'         => $reviews
        ];
    }

    /**
     * Get platform overview stats
     */
    public function getPlatformStats() {
        $totalCust = (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'CUSTOMER'")->fetchColumn();
        $totalTech = (int)$this->pdo->query("SELECT COUNT(*) FROM technicians")->fetchColumn();
        $totalAdmins = (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'ADMIN'")->fetchColumn();
        $totalUsers = (int)$this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalEquip = (int)$this->pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
        $totalOrders = (int)$this->pdo->query("SELECT COUNT(*) FROM service_orders")->fetchColumn();
        $completedOrders = (int)$this->pdo->query("SELECT COUNT(*) FROM service_orders WHERE status = 'COMPLETED'")->fetchColumn();
        $activeOrders = (int)$this->pdo->query("SELECT COUNT(*) FROM service_orders WHERE status IN ('ASSIGNED', 'ACCEPTED', 'ON_THE_WAY', 'IN_PROGRESS')")->fetchColumn();
        $pendingOrders = (int)$this->pdo->query("SELECT COUNT(*) FROM service_orders WHERE status = 'PENDING'")->fetchColumn();
        $cancelledOrders = (int)$this->pdo->query("SELECT COUNT(*) FROM service_orders WHERE status = 'CANCELLED'")->fetchColumn();
        $totalReports = (int)$this->pdo->query("SELECT COUNT(*) FROM service_reports")->fetchColumn();
        $totalPayments = (int)$this->pdo->query("SELECT COUNT(*) FROM payments")->fetchColumn();
        $totalReviews = (int)$this->pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
        $avgRating = round((float)$this->pdo->query("SELECT COALESCE(AVG(rating), 5.0) FROM reviews")->fetchColumn(), 1);
        $totalNotifs = (int)$this->pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
        $totalSessions = (int)$this->pdo->query("SELECT COUNT(*) FROM chat_sessions")->fetchColumn();
        $totalMessages = (int)$this->pdo->query("SELECT COUNT(*) FROM chat_messages")->fetchColumn();

        // OMSET = seluruh akumulasi pesanan yang sudah dibayar (baik dari payments maupun pesanan COMPLETED)
        $stmtOmset = $this->pdo->query("
            SELECT 
                COALESCE(SUM(COALESCE(p.amount, so.final_cost, 0)), 0) as total_omset,
                COUNT(DISTINCT so.id) as paid_orders_count
            FROM service_orders so
            LEFT JOIN payments p ON so.id = p.order_id AND p.status = 'PAID'
            WHERE p.status = 'PAID' OR so.status = 'COMPLETED'
        ");
        $omsetRow = $stmtOmset->fetch(PDO::FETCH_ASSOC);
        $totalOmset = (float)($omsetRow['total_omset'] ?? 0);
        $paidOrdersCount = (int)($omsetRow['paid_orders_count'] ?? 0);

        // Total biaya pesanan selesai (final_cost)
        $totalCompletedCost = (float)$this->pdo->query("SELECT COALESCE(SUM(final_cost), 0) FROM service_orders WHERE status = 'COMPLETED'")->fetchColumn();

        return [
            'total_customers'       => $totalCust,
            'total_technicians'     => $totalTech,
            'total_admins'          => $totalAdmins,
            'total_users'           => $totalUsers,
            'total_equipment_items' => $totalEquip,
            'total_orders'          => $totalOrders,
            'completed_orders'      => $completedOrders,
            'active_orders'         => $activeOrders,
            'pending_orders'        => $pendingOrders,
            'cancelled_orders'      => $cancelledOrders,
            'total_reports'         => $totalReports,
            'total_payments'        => $totalPayments,
            'total_reviews'         => $totalReviews,
            'average_rating'        => $avgRating,
            'total_notifications'   => $totalNotifs,
            'total_chat_sessions'   => $totalSessions,
            'total_chat_messages'   => $totalMessages,
            'total_revenue'         => $totalOmset,        // OMSET = sudah dibayar
            'total_omset'           => $totalOmset,        // alias eksplisit
            'paid_orders_count'     => $paidOrdersCount,   // jumlah pesanan dibayar
            'total_completed_cost'  => $totalCompletedCost // total final_cost selesai
        ];
    }

    /**
     * Get recent system-wide service orders with full relations
     */
    public function getSystemOrders($limit = 15) {
        $sql = "
            SELECT so.id, so.status, so.complaint, so.ai_diagnosis, so.service_date, so.address as order_address,
                   so.estimated_cost, so.final_cost, so.created_at,
                   u_cust.name as customer_name, u_cust.phone as customer_phone, u_cust.email as customer_email, u_cust.address as customer_main_address,
                   COALESCE(e.id, 0) as equipment_id, 
                   COALESCE(e.name, 'Peralatan Rumah Tangga') as equipment_name, 
                   COALESCE(e.brand, '-') as equipment_brand, 
                   COALESCE(e.category, 'Umum') as equipment_category,
                   t.id as technician_id, u_tech.name as technician_name, u_tech.phone as technician_phone,
                   t.specialization as technician_specialization, t.rating as technician_rating,
                   sr.inspection_result, sr.repair_action, sr.notes as report_notes, sr.cost as report_cost, sr.completed_at as report_completed_at,
                   p.id as payment_id, p.amount as payment_amount, p.payment_method, p.payment_provider,
                   p.status as payment_status, p.paid_at
            FROM service_orders so
            LEFT JOIN users u_cust ON so.customer_id = u_cust.id
            LEFT JOIN equipment e ON so.equipment_id = e.id
            LEFT JOIN technicians t ON so.technician_id = t.id
            LEFT JOIN users u_tech ON t.user_id = u_tech.id
            LEFT JOIN service_reports sr ON so.id = sr.order_id
            LEFT JOIN payments p ON so.id = p.order_id AND p.status = 'PAID'
            ORDER BY so.created_at DESC
            LIMIT " . (int)$limit;
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all paid orders (live real-time omset data from payments & completed service orders)
     */
    public function getPaidOrders() {
        $sql = "
            SELECT COALESCE(p.id, so.id) as payment_id, so.id as order_id, 
                   COALESCE(p.amount, so.final_cost, 0) as payment_amount,
                   COALESCE(p.payment_method, 'CASH') as payment_method, 
                   COALESCE(p.payment_provider, 'CASH') as payment_provider,
                   COALESCE(p.sender_name, u_cust.name) as sender_name, 
                   p.sender_account, 
                   COALESCE(p.status, 'PAID') as payment_status, 
                   COALESCE(p.paid_at, sr.completed_at, so.updated_at, so.created_at) as paid_at,
                   COALESCE(p.created_at, so.created_at) as payment_created_at,
                   so.id, so.status, so.complaint, so.final_cost, so.estimated_cost, so.service_date, so.created_at,
                   so.address as order_address,
                   u_cust.name as customer_name, u_cust.phone as customer_phone, u_cust.email as customer_email, u_cust.address as customer_main_address,
                   COALESCE(e.id, 0) as equipment_id,
                   COALESCE(e.name, 'Peralatan Rumah Tangga') as equipment_name,
                   COALESCE(e.brand, '-') as equipment_brand,
                   COALESCE(e.category, 'Umum') as equipment_category,
                   u_tech.name as technician_name, u_tech.phone as technician_phone, t.rating as technician_rating
            FROM service_orders so
            LEFT JOIN payments p ON so.id = p.order_id AND p.status = 'PAID'
            LEFT JOIN service_reports sr ON so.id = sr.order_id
            LEFT JOIN users u_cust ON so.customer_id = u_cust.id
            LEFT JOIN equipment e ON so.equipment_id = e.id
            LEFT JOIN technicians t ON so.technician_id = t.id
            LEFT JOIN users u_tech ON t.user_id = u_tech.id
            WHERE p.status = 'PAID' OR so.status = 'COMPLETED'
            ORDER BY COALESCE(p.paid_at, sr.completed_at, so.created_at) DESC, so.id DESC
        ";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 1. Get all service reports from service_reports table with relations
     */
    public function getServiceReports($limit = 50) {
        $sql = "
            SELECT sr.id, sr.order_id, sr.technician_id, sr.inspection_result, sr.repair_action, sr.notes, sr.cost, sr.completed_at, sr.created_at,
                   so.complaint, so.status as order_status, so.service_date,
                   COALESCE(e.name, 'Peralatan') as equipment_name,
                   COALESCE(e.brand, '-') as equipment_brand,
                   COALESCE(e.category, 'Umum') as equipment_category,
                   u_tech.name as technician_name, u_tech.phone as technician_phone,
                   u_cust.name as customer_name, u_cust.phone as customer_phone
            FROM service_reports sr
            JOIN service_orders so ON sr.order_id = so.id
            LEFT JOIN equipment e ON so.equipment_id = e.id
            LEFT JOIN technicians t ON sr.technician_id = t.id
            LEFT JOIN users u_tech ON t.user_id = u_tech.id
            LEFT JOIN users u_cust ON so.customer_id = u_cust.id
            ORDER BY sr.created_at DESC
            LIMIT " . (int)$limit;
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 2. Get notifications from notifications table
     */
    public function getNotifications($userId = null, $limit = 50) {
        $sql = "
            SELECT n.id, n.user_id, n.order_id, n.type, n.title, n.message, n.is_read, n.created_at,
                   u.name as user_name, u.role as user_role
            FROM notifications n
            LEFT JOIN users u ON n.user_id = u.id
            ORDER BY n.created_at DESC
            LIMIT " . (int)$limit;
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 3. Get all payments from payments table
     */
    public function getPayments($limit = 50) {
        $sql = "
            SELECT p.id, p.order_id, p.customer_id, p.amount, p.payment_method, p.payment_provider,
                   p.sender_name, p.sender_account, p.proof_image, p.notes, p.status, p.paid_at, p.created_at,
                   u.name as customer_name, u.phone as customer_phone,
                   COALESCE(e.name, 'Peralatan') as equipment_name,
                   COALESCE(e.brand, '-') as equipment_brand,
                   so.status as order_status
            FROM payments p
            LEFT JOIN service_orders so ON p.order_id = so.id
            LEFT JOIN users u ON p.customer_id = u.id
            LEFT JOIN equipment e ON so.equipment_id = e.id
            ORDER BY p.created_at DESC
            LIMIT " . (int)$limit;
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 4. Get all users from users table
     */
    public function getAllUsers($limit = 100) {
        $sql = "
            SELECT id, name, email, role, phone, address, created_at
            FROM users
            ORDER BY role ASC, id ASC
            LIMIT " . (int)$limit;
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 4.A Get all customers from users table
     */
    public function getCustomers($limit = 100) {
        $sql = "
            SELECT id, name, email, role, phone, address, created_at
            FROM users
            WHERE role = 'CUSTOMER'
            ORDER BY id ASC
            LIMIT " . (int)$limit;
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 5. Get chat sessions and message statistics
     */
    public function getChatSessionsAndStats($limit = 30) {
        $totalSessions = (int)$this->pdo->query("SELECT COUNT(*) FROM chat_sessions")->fetchColumn();
        $totalMessages = (int)$this->pdo->query("SELECT COUNT(*) FROM chat_messages")->fetchColumn();
        $userMsgCount = (int)$this->pdo->query("SELECT COUNT(*) FROM chat_messages WHERE sender = 'USER'")->fetchColumn();
        $aiMsgCount = (int)$this->pdo->query("SELECT COUNT(*) FROM chat_messages WHERE sender = 'AI'")->fetchColumn();

        $stmt = $this->pdo->query("
            SELECT cs.id, cs.customer_id, cs.title, cs.created_at, cs.updated_at,
                   u.name as customer_name,
                   (SELECT COUNT(*) FROM chat_messages cm WHERE cm.session_id = cs.id) as message_count
            FROM chat_sessions cs
            LEFT JOIN users u ON cs.customer_id = u.id
            ORDER BY cs.updated_at DESC
            LIMIT " . (int)$limit);
        $recentSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total_sessions'  => $totalSessions,
            'total_messages'  => $totalMessages,
            'user_messages'   => $userMsgCount,
            'ai_messages'     => $aiMsgCount,
            'recent_sessions' => $recentSessions
        ];
    }

    /**
     * Build complete structured data context for both LLM prompt & fallback engine
     * Chatbot dapat membaca seluruh database (homecare_db) secara menyeluruh
     */
    public function getFullContextData($userId = null) {
        if (!$userId && isset($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
        }

        $userContext = $userId ? $this->getUserContext($userId) : null;
        $role = strtoupper($userContext['role'] ?? 'CUSTOMER');
        $allOrders = $this->getSystemOrders(100);

        // Seluruh 10 tabel database homecare_db dimuat penuh untuk chatbot
        $context = [
            'user'            => $userContext,
            'role'            => $role,
            'users'           => $this->getAllUsers(100),
            'customers'       => $this->getCustomers(100),
            'technicians'     => $this->getTechnicians(),
            'equipment'       => $this->getEquipmentCatalog(),
            'service_orders'  => $allOrders,
            'system_orders'   => $allOrders,
            'service_reports' => $this->getServiceReports(50),
            'payments'        => $this->getPayments(50),
            'paid_orders'     => $this->getPaidOrders(),
            'reviews'         => $this->getReviews(30),
            'notifications'   => $this->getNotifications($userId, 50),
            'chat_data'       => $this->getChatSessionsAndStats(30),
            'platform_stats'  => $this->getPlatformStats()
        ];

        return $context;
    }

    /**
     * Build human-readable formatted Markdown knowledge base for LLM system prompt
     * Chatbot membaca SELURUH isi database homecare_db (10 tabel penuh)
     */
    public function buildKnowledgePrompt($userId = null) {
        if (!$userId && isset($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
        }
        $data = $this->getFullContextData($userId);

        $userContext = $data['user'] ?? null;
        $role = strtoupper($userContext['role'] ?? 'CUSTOMER');
        $pStats = $data['platform_stats'];

        $totalOmsetPrompt = number_format($pStats['total_omset'] ?? 0, 0, ',', '.');
        $paidCountPrompt = $pStats['paid_orders_count'] ?? 0;
        $totalCustPrompt = $pStats['total_customers'] ?? 7;
        $totalTechPrompt = $pStats['total_technicians'] ?? 6;
        $totalOrdersPrompt = $pStats['total_orders'] ?? 10;
        $totalEquipPrompt = $pStats['total_equipment_items'] ?? 27;
        $totalReviewsPrompt = $pStats['total_reviews'] ?? 3;
        $avgRatingPrompt = $pStats['average_rating'] ?? 5.0;
        $totalReportsPrompt = $pStats['total_reports'] ?? 6;

        $prompt = "=== DATABASE RESMI & DATA LIVE LENGKAP SISTEM HOMECARE SERVICE ===\n";
        $prompt .= "Anda memiliki akses langsung dan penuh ke seluruh tabel database sistem HomeCare Service (homecare_db).\n\n";

        $prompt .= "🚨 ATURAN EMAS MENJAWAB CHATBOT (MUTLAK & TIDAK BISA DITAWAR) 🚨\n";
        $prompt .= "1. BERIKAN JAWABAN TO THE POINT TANPA RINCIAN UNTUK SEMUA PERTANYAAN:\n";
        $prompt .= "   - Jawab langsung ke inti pertanyaan secara padat, tegas, dan faktual sesuai data database.\n";
        $prompt .= "   - DILARANG KERAS memberikan rincian, daftar poin-poin (bullet points/numbered list), tabel, atau rincian per-item KECUALI pengguna secara eksplisit meminta rincian!\n";
        $prompt .= "2. SAAT DITANYA JUMLAH / KUANTITAS / NOMINAL (CONTOH: JUMLAH CUSTOMER, JUMLAH TEKNISI, JUMLAH PESANAN, JUMLAH OMSET, JUMLAH PERALATAN, JUMLAH ULASAN, JUMLAH LAPORAN, DLL.):\n";
        $prompt .= "   - SEBUTKAN HANYA JUMLAHNYA / NOMINALNYA SAJA DALAM 1 KALIMAT PADAT! DILARANG membuat rincian jika hanya ditanya jumlah!\n";
        $prompt .= "   - SAMA SEKALI JANGAN MENYEBUTKAN NAMA-NAMA, DAFTAR BARANG, ATAU RINCIAN DATA!\n";
        $prompt .= "   - Contoh pertanyaan customer:\n";
        $prompt .= "     Pengguna: 'berapa jumlah customer?' / 'ada berapa customer?' / 'jumlah customer'\n";
        $prompt .= "     -> Jawaban: 'Jumlah customer terdaftar di sistem saat ini ada {$totalCustPrompt} orang.' (DILARANG menyebutkan nama atau daftar pelanggan!)\n";
        $prompt .= "   - Contoh pertanyaan teknisi:\n";
        $prompt .= "     Pengguna: 'berapa jumlah teknisi?' / 'ada berapa teknisi?'\n";
        $prompt .= "     -> Jawaban: 'Jumlah teknisi saat ini ada {$totalTechPrompt} orang.'\n";
        $prompt .= "   - Contoh pertanyaan pesanan:\n";
        $prompt .= "     Pengguna: 'berapa jumlah pesanan?' / 'total order'\n";
        $prompt .= "     -> Jawaban: 'Total seluruh pesanan di sistem saat ini ada {$totalOrdersPrompt} pesanan.'\n";
        $prompt .= "   - Contoh pertanyaan omset:\n";
        $prompt .= "     Pengguna: 'berapa jumlah omset?' / 'sebutkan semua jumlah pembayaran yang sudah dibayar'\n";
        $prompt .= "     -> Jawaban: 'Total omset dari semua pesanan yang sudah dibayar saat ini adalah sebesar Rp {$totalOmsetPrompt} dari {$paidCountPrompt} pesanan yang sudah lunas.'\n";
        $prompt .= "   - Contoh pertanyaan peralatan:\n";
        $prompt .= "     Pengguna: 'berapa jumlah peralatan?' / 'ada berapa alat di katalog?'\n";
        $prompt .= "     -> Jawaban: 'Jumlah peralatan yang terdaftar di katalog saat ini ada {$totalEquipPrompt} jenis peralatan.'\n";
        $prompt .= "   - Contoh pertanyaan ulasan:\n";
        $prompt .= "     Pengguna: 'berapa jumlah ulasan?' / 'total review'\n";
        $prompt .= "     -> Jawaban: 'Total ulasan pelanggan saat ini ada {$totalReviewsPrompt} ulasan dengan rata-rata rating {$avgRatingPrompt} / 5.0 ⭐.'\n";
        $prompt .= "3. RINCIAN HANYA DIBERIKAN JIKA DAN HANYA JIKA PENGGUNA SECARA EKSPLISIT MEMINTA RINCIAN:\n";
        $prompt .= "   - Kata kunci rincian: 'rincikan', 'rincian', 'rinciannya', 'detail', 'daftar', 'siapa saja', 'apa saja', 'list', 'sebutkan nama-namanya'.\n";
        $prompt .= "   - Contoh rincian omset: Jika pengguna meminta 'rincikan omset' / 'rincian omset' / 'detail omset' / 'apa saja omsetnya' / 'rincikan pembayaran', WAJIB sebutkan daftar pesanan yang sudah dibayar beserta nominal dan nama pelanggannya secara ringkas per baris.\n";
        $prompt .= "   - Jika pengguna meminta rincian, berikan rincian ringkas to-the-point per baris tanpa bertele-tele.\n";
        $prompt .= "4. INI BERLAKU UNTUK SEMUA PERTANYAAN TANPA TERKECUALI!\n";
        $prompt .= "5. DILARANG KERAS MEMBERIKAN KOMENTAR APAPUN: Dilarang opini, wejangan, nasihat/tips yang tidak diminta, catatan tambahan ('Catatan: ...', 'Tips: ...'), penjelasan dalam tanda kurung, atau kalimat basa-basi penutup ('Ada yang bisa dibantu lagi?', 'Semoga harimu menyenangkan', dll.).\n\n";

        $prompt .= "🚨 ATURAN KHUSUS KATALOG ALAT & HARGA PERBAIKAN (MUTLAK) 🚨\n";
        $prompt .= "1. Saat pengguna bertanya tentang KATALOG ALAT, DAFTAR PERALATAN, atau BIAYA / HARGA PERBAIKAN PER BARANG:\n";
        $prompt .= "   - Berikan nama peralatan beserta estimasi harga perbaikannya per barang sesuai data database.\n";
        $prompt .= "   - WAJIB tambahkan kalimat disclaimer: \"*Catatan / Disclaimer: Harga perbaikan di atas merupakan estimasi awal dan bisa saja berubah tergantung keputusan teknisi setelah pemeriksaan kondisi kerusakan secara langsung di lokasi.*\"\n\n";

        // Identity Card
        if (!empty($userContext) && !empty($userContext['profile'])) {
            $prof = $userContext['profile'];
            $roleLabel = [
                'CUSTOMER' => 'Pelanggan (Customer)', 
                'ADMIN'    => 'Administrator Sistem (Admin)', 
                'TEKNISI'  => 'Teknisi Resmi (Technician)'
            ][$role] ?? $role;

            $prompt .= "### ⚡ IDENTITAS PENGGUNA YANG SEDANG LOGIN:\n";
            $prompt .= "- NAMA LENGKAP : " . ($prof['name'] ?: 'Tidak diketahui') . "\n";
            $prompt .= "- EMAIL        : " . ($prof['email'] ?: '-') . "\n";
            $prompt .= "- NO. TELEPON  : " . ($prof['phone'] ?: 'Belum diisi') . "\n";
            $prompt .= "- ALAMAT       : " . ($prof['address'] ?: 'Belum diisi') . "\n";
            $prompt .= "- PERAN (ROLE) : {$roleLabel}\n\n";
        }

        // ==========================================
        // 1. DATA TABEL: users (Customer, Teknisi, Admin)
        // ==========================================
        $users = $data['users'] ?? [];
        $customers = $data['customers'] ?? [];
        $prompt .= "### 👥 DATA TABEL: users (Seluruh Pengguna Terdaftar Sistem):\n";
        $prompt .= "- Total Seluruh Pengguna: " . count($users) . " orang (Customer: {$pStats['total_customers']}, Teknisi: {$pStats['total_technicians']}, Admin: {$pStats['total_admins']})\n";
        $prompt .= "- DAFTAR CUSTOMER / PELANGGAN ({$pStats['total_customers']} orang):\n";
        foreach ($customers as $c) {
            $prompt .= "  • Customer #{$c['id']}: {$c['name']} | Telp: {$c['phone']} | Email: {$c['email']} | Alamat: " . ($c['address'] ?: '-') . "\n";
        }
        $prompt .= "- DAFTAR PENGGUNA LAINNYA:\n";
        foreach ($users as $u) {
            if ($u['role'] !== 'CUSTOMER') {
                $prompt .= "  • User #{$u['id']} [{$u['role']}]: {$u['name']} | Email: {$u['email']} | Telp: {$u['phone']}\n";
            }
        }
        $prompt .= "\n";

        // ==========================================
        // 2. DATA TABEL: technicians (Tim Teknisi)
        // ==========================================
        $prompt .= "### 👨‍🔧 DATA TABEL: technicians (Tim Teknisi Resmi):\n";
        $prompt .= "- Total Teknisi: {$data['technicians']['total']} orang ({$data['technicians']['available_count']} Siap Bertugas / Ready, {$data['technicians']['busy_count']} Sedang Bertugas)\n";
        if (!empty($data['technicians']['technicians'])) {
            foreach ($data['technicians']['technicians'] as $t) {
                $statusAvail = $t['availability'] ? 'Tersedia (Ready)' : 'Sedang Bertugas / Sibuk';
                $prompt .= "- {$t['name']} (ID: {$t['id']}): Spesialisasi: {$t['specialization']} | Pengalaman: {$t['experience']} Thn | Rating: {$t['rating']} ⭐ ({$t['completed_jobs']} servis selesai) | Status: {$statusAvail} | Kontak: {$t['phone']}\n";
            }
        }
        $prompt .= "\n";

        // ==========================================
        // 3. DATA TABEL: equipment (Katalog Peralatan)
        // ==========================================
        $prompt .= "### 📦 DATA TABEL: equipment (Katalog Peralatan Rumah Tangga & Estimasi Biaya Perbaikan):\n";
        $prompt .= "Total Peralatan: {$pStats['total_equipment_items']} jenis alat terdaftar.\n";
        if (!empty($data['equipment']['by_category'])) {
            foreach ($data['equipment']['by_category'] as $cat => $items) {
                $prompt .= "Kategori [{$cat}]:\n";
                foreach ($items as $item) {
                    $priceFmt = !empty($item['repair_price']) ? 'Rp ' . number_format($item['repair_price'], 0, ',', '.') : 'Rp 100.000';
                    $prompt .= "  • {$item['name']} (Merk: {$item['brand']}) | Estimasi Perbaikan: {$priceFmt} | Info: {$item['description']}\n";
                }
            }
        }
        $prompt .= "\n";

        // ==========================================
        // 4. DATA TABEL: service_orders (Seluruh Pesanan Sistem)
        // ==========================================
        $ordersToDisplay = !empty($data['service_orders']) ? $data['service_orders'] : (!empty($data['system_orders']) ? $data['system_orders'] : []);
        $prompt .= "### 📋 DATA TABEL: service_orders (Seluruh Pesanan Servis Sistem):\n";
        $prompt .= "Total Pesanan: {$pStats['total_orders']} pesanan (Selesai: {$pStats['completed_orders']}, Aktif: {$pStats['active_orders']}, Pending: {$pStats['pending_orders']}, Batal: {$pStats['cancelled_orders']}):\n";
        foreach ($ordersToDisplay as $ord) {
            $statusIndo = [
                'PENDING'     => 'Menunggu Konfirmasi / Mencari Teknisi',
                'ASSIGNED'    => 'Teknisi Ditugaskan',
                'ACCEPTED'    => 'Teknisi Menerima Tugas',
                'ON_THE_WAY'  => 'Teknisi Sedang Menuju Lokasi',
                'IN_PROGRESS' => 'Sedang Dikerjakan / Dalam Perbaikan',
                'COMPLETED'   => 'Selesai',
                'CANCELLED'   => 'Dibatalkan'
            ][$ord['status']] ?? $ord['status'];

            $tglJadwal = $ord['service_date'] ? date('d M Y, H:i', strtotime($ord['service_date'])) : 'Belum ditentukan';
            $techStr = !empty($ord['technician_name']) ? "{$ord['technician_name']} (Rating: {$ord['technician_rating']} ⭐, Telp: {$ord['technician_phone']})" : 'Belum ditugaskan';
            $isPaid = !empty($ord['payment_id']) || !empty($ord['payment_amount']);
            $payStatusStr = $isPaid ? "SUDAH DIBAYAR (LUNAS) via {$ord['payment_method']}" : (!empty($ord['final_cost']) ? "BELUM DIBAYAR (Tagihan: Rp " . number_format($ord['final_cost'], 0, ',', '.') . ")" : "Belum ada tagihan");

            $prompt .= "* ORDER #{$ord['id']}:\n"
                    . "  - Peralatan: {$ord['equipment_name']} ({$ord['equipment_brand']}) [{$ord['equipment_category']}]\n"
                    . "  - Status: {$ord['status']} ({$statusIndo})\n"
                    . "  - Pelanggan: {$ord['customer_name']} (Telp: {$ord['customer_phone']}, Alamat: " . ($ord['order_address'] ?: $ord['customer_main_address'] ?: '-') . ")\n"
                    . "  - Teknisi Bertugas: {$techStr}\n"
                    . "  - Jadwal: {$tglJadwal} | Keluhan: {$ord['complaint']}\n"
                    . "  - Biaya Final: " . (!empty($ord['final_cost']) ? 'Rp ' . number_format($ord['final_cost'], 0, ',', '.') : 'Rp 0') . " | Status Bayar: {$payStatusStr}\n";
        }
        $prompt .= "\n";

        // ==========================================
        // 5. DATA TABEL: service_reports (Laporan Servis)
        // ==========================================
        $reports = $data['service_reports'] ?? [];
        $prompt .= "### 🛠️ DATA TABEL: service_reports (Laporan Hasil Servis & Perbaikan):\n";
        $prompt .= "Total Laporan: " . count($reports) . " laporan pengerjaan servis tercatat di database:\n";
        foreach ($reports as $sr) {
            $repDate = !empty($sr['completed_at']) ? date('d M Y, H:i', strtotime($sr['completed_at'])) : date('d M Y', strtotime($sr['created_at']));
            $costFmt = "Rp " . number_format($sr['cost'], 0, ',', '.');
            $prompt .= "- Report #{$sr['id']} (Order #{$sr['order_id']} — {$sr['equipment_name']} {$sr['equipment_brand']}):\n"
                    . "  • Pelanggan: {$sr['customer_name']} (Telp: {$sr['customer_phone']})\n"
                    . "  • Teknisi: {$sr['technician_name']}\n"
                    . "  • Biaya Servis: {$costFmt}\n"
                    . "  • Hasil Inspeksi: {$sr['inspection_result']}\n"
                    . "  • Tindakan Perbaikan: {$sr['repair_action']}\n"
                    . "  • Catatan & Garansi: {$sr['notes']}\n"
                    . "  • Waktu Selesai: {$repDate}\n";
        }
        $prompt .= "\n";

        // ==========================================
        // 6. DATA TABEL: payments (Transaksi Pembayaran & Omset)
        // ==========================================
        $payments = $data['payments'] ?? [];
        $totalOmset = $pStats['total_omset'] ?? 0;
        $paidCount = $pStats['paid_orders_count'] ?? 0;

        $prompt .= "### 💳 DATA TABEL: payments (Transaksi Pembayaran & Omset):\n";
        $prompt .= "- **TOTAL OMSET TERVERIFIKASI (STATUS PAID):** Rp " . number_format($totalOmset, 0, ',', '.') . " dari {$paidCount} pesanan lunas\n";
        $prompt .= "Total Transaksi Pembayaran: " . count($payments) . " transaksi:\n";
        foreach ($payments as $p) {
            $pDate = !empty($p['paid_at']) ? date('d M Y, H:i', strtotime($p['paid_at'])) : date('d M Y', strtotime($p['created_at']));
            $pAmt = "Rp " . number_format($p['amount'], 0, ',', '.');
            $pMet = !empty($p['payment_method']) ? "{$p['payment_method']} ({$p['payment_provider']})" : 'CASH';
            $prompt .= "- Payment #{$p['id']} (Order #{$p['order_id']} — {$p['equipment_name']} {$p['equipment_brand']}): {$pAmt} | Status: {$p['status']} | Metode: {$pMet} | Pelanggan: {$p['customer_name']} | Pengirim: " . ($p['sender_name'] ?: '-') . " | Waktu: {$pDate}\n";
        }
        $prompt .= "\n";

        // ==========================================
        // 7. DATA TABEL: reviews (Ulasan Pelanggan)
        // ==========================================
        $reviews = $data['reviews']['recent'] ?? [];
        $prompt .= "### ⭐ DATA TABEL: reviews (Seluruh Ulasan Pelanggan):\n";
        $prompt .= "- Total Ulasan: {$data['reviews']['total_reviews']} ulasan (Rating Rata-Rata: {$data['reviews']['average_rating']} / 5.0):\n";
        foreach ($reviews as $rev) {
            $prompt .= "- {$rev['rating']} ⭐ dari {$rev['customer_name']} untuk {$rev['technician_name']} ({$rev['equipment_name']} {$rev['equipment_brand']}): \"{$rev['comment']}\"\n";
        }
        $prompt .= "\n";

        // ==========================================
        // 8. DATA TABEL: notifications (Notifikasi Sistem)
        // ==========================================
        $notifs = $data['notifications'] ?? [];
        $prompt .= "### 🔔 DATA TABEL: notifications (Notifikasi Sistem):\n";
        $prompt .= "Total Notifikasi Tercatat: " . count($notifs) . " notifikasi:\n";
        foreach (array_slice($notifs, 0, 15) as $n) {
            $tglNotif = date('d M Y, H:i', strtotime($n['created_at']));
            $msgClean = strip_tags(str_replace(['<br>', '</p>'], " ", $n['message']));
            $prompt .= "- Notif #{$n['id']} [{$n['type']}] ({$tglNotif}) untuk {$n['user_name']}: \"{$n['title']}\" — {$msgClean}\n";
        }
        $prompt .= "\n";

        // ==========================================
        // 9 & 10. DATA TABEL: chat_sessions & chat_messages
        // ==========================================
        $chatData = $data['chat_data'] ?? [];
        if (!empty($chatData)) {
            $prompt .= "### 💬 DATA TABEL: chat_sessions & chat_messages (Statistik Konsultasi Chat):\n";
            $prompt .= "- Total Sesi Obrolan: {$chatData['total_sessions']} sesi\n";
            $prompt .= "- Total Pesan Chat: {$chatData['total_messages']} pesan (User: {$chatData['user_messages']}, AI: {$chatData['ai_messages']})\n";
            $prompt .= "Sesi Chat Terbaru:\n";
            foreach ($chatData['recent_sessions'] as $cs) {
                $tglCs = date('d M Y, H:i', strtotime($cs['updated_at']));
                $prompt .= "- Sesi #{$cs['id']}: \"{$cs['title']}\" | Pelanggan: {$cs['customer_name']} | Pesan: {$cs['message_count']} chat | Update: {$tglCs}\n";
            }
            $prompt .= "\n";
        }

        // Ringkasan Statistik Platform
        $prompt .= "### 📈 REKAPITULASI STATISTIK PLATFORM REAL-TIME:\n";
        $prompt .= "- Total Pengguna: {$pStats['total_users']} orang (Pelanggan: {$pStats['total_customers']}, Teknisi: {$pStats['total_technicians']}, Admin: {$pStats['total_admins']})\n";
        $prompt .= "- Total Pesanan: {$pStats['total_orders']} (Selesai: {$pStats['completed_orders']}, Aktif: {$pStats['active_orders']}, Pending: {$pStats['pending_orders']}, Batal: {$pStats['cancelled_orders']})\n";
        $prompt .= "- OMSET LUNAS (PAID): Rp " . number_format($pStats['total_omset'], 0, ',', '.') . " dari {$pStats['paid_orders_count']} pesanan\n";
        $prompt .= "- Total Nilai Pesanan Selesai: Rp " . number_format($pStats['total_completed_cost'], 0, ',', '.') . "\n";
        $prompt .= "- Total Peralatan: {$pStats['total_equipment_items']} jenis alat | Total Laporan: {$pStats['total_reports']} laporan | Total Ulasan: {$pStats['total_reviews']} ulasan ({$pStats['average_rating']} ⭐)\n";
        $prompt .= "=== AKHIR DATA DATABASE HOMECARE_DB ===\n\n";

        return $prompt;
    }
}
