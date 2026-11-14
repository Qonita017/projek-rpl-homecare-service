<?php
$badgeClass = function($status) {
    switch($status) {
        case 'PENDING': return 'bg-warning text-dark';
        case 'ASSIGNED': return 'bg-info text-dark';
        case 'ACCEPTED': return 'bg-primary';
        case 'ON_THE_WAY': return 'bg-secondary';
        case 'IN_PROGRESS': return 'bg-primary';
        case 'COMPLETED': return 'bg-success';
        case 'CANCELLED': return 'bg-danger';
        default: return 'bg-light text-dark';
    }
};
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard Customer</h1>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <button type="button" onclick="if(window.toggleAiChatWidget) toggleAiChatWidget(true)" class="btn btn-outline-primary shadow-sm rounded-pill px-3">
            <i class="bi bi-chat-dots-fill me-1"></i>AI Chatbot
        </button>
        <a href="<?= BASE_URL ?>/ai-diagnosis" class="btn btn-outline-info shadow-sm rounded-pill px-3">
            <i class="bi bi-robot me-1"></i>Konsultasi AI
        </a>
        <a href="<?= BASE_URL ?>/customer/order/create" class="btn btn-primary shadow-sm rounded-pill px-4">
            <i class="bi bi-plus-circle me-2"></i>Pesan Jasa
        </a>
    </div>
</div>

<div class="card bg-primary text-white border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
    <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <span class="badge bg-warning text-dark mb-2">Fitur Cerdas Baru</span>
            <h4 class="fw-bold mb-1"><i class="bi bi-robot me-2"></i>Peralatan Anda Bermasalah?</h4>
            <p class="mb-0 text-white-50">Gunakan AI Assistant kami untuk diagnosa awal penyebab kerusakan atau konsultasi langsung melalui AI Chatbot.</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2">
            <button type="button" onclick="if(window.toggleAiChatWidget) toggleAiChatWidget(true)" class="btn btn-warning btn-lg fw-bold text-dark shadow-sm px-3">
                <i class="bi bi-chat-dots-fill me-1"></i> Chat AI Sekarang
            </button>
            <a href="<?= BASE_URL ?>/ai-diagnosis" class="btn btn-light btn-lg fw-bold text-primary shadow-sm px-3">
                <i class="bi bi-cpu me-1"></i> Form AI Diagnosis
            </a>
        </div>
    </div>
</div>

<div class="alert alert-info shadow-sm d-flex justify-content-between align-items-center flex-wrap">
    <div>Selamat datang kembali, <strong><?= htmlspecialchars($user_name) ?></strong>!</div>
    <div class="small text-muted"><i class="bi bi-calendar-event me-1"></i><?= date('d M Y') ?></div>
</div>

<?php if(!empty($unpaid_orders)): ?>
<div class="alert alert-warning border border-warning shadow-sm mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="p-2 bg-warning text-dark rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                <i class="bi bi-wallet2 fs-5"></i>
            </div>
            <div>
                <strong class="text-dark d-block">Ada <?= count($unpaid_orders) ?> tagihan servis yang siap dibayar oleh Anda</strong>
                <span class="small text-muted">Pilihan pembayaran sepenuhnya terserah Anda: Tunai (Cash) ke teknisi atau Transfer (BRIMO, BCA, Mandiri, ShopeePay, DANA, GoPay, dll).</span>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/customer/payment" class="btn btn-warning fw-bold text-dark shadow-sm">
            <i class="bi bi-credit-card me-1"></i> Bayar Sekarang
        </a>
    </div>
</div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-white bg-primary shadow-sm h-100">
            <div class="card-body text-center">
                <h6 class="card-title"><i class="bi bi-list-task mb-2 fs-3 d-block"></i>Jumlah Pesanan</h6>
                <p class="card-text fs-3 fw-bold"><?= $stats['total_pesanan'] ?? 0 ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-white bg-warning text-dark shadow-sm h-100">
            <div class="card-body text-center">
                <h6 class="card-title"><i class="bi bi-gear-wide-connected mb-2 fs-3 d-block"></i>Pesanan Aktif</h6>
                <p class="card-text fs-3 fw-bold"><?= $stats['pesanan_aktif'] ?? 0 ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <a href="<?= BASE_URL ?>/customer/payment" class="text-decoration-none">
            <div class="card text-white bg-danger shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="card-title"><i class="bi bi-wallet2 mb-2 fs-3 d-block"></i>Menunggu Bayar</h6>
                    <p class="card-text fs-3 fw-bold"><?= !empty($unpaid_orders) ? count($unpaid_orders) : 0 ?></p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-white bg-success shadow-sm h-100">
            <div class="card-body text-center">
                <h6 class="card-title"><i class="bi bi-check-circle mb-2 fs-3 d-block"></i>Pesanan Selesai</h6>
                <p class="card-text fs-3 fw-bold"><?= $stats['pesanan_selesai'] ?? 0 ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom pt-3 pb-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary fw-bold">Riwayat Servis Terbaru</h5>
        <a href="<?= BASE_URL ?>/customer/order" class="btn btn-sm btn-outline-primary">Lihat Semua Pesanan</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ID Order</th>
                        <th>Tanggal</th>
                        <th>Peralatan</th>
                        <th>Keluhan</th>
                        <th>Teknisi</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($recent_orders)): ?>
                        <?php foreach($recent_orders as $order): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars(date('d M Y', strtotime($order['created_at']))) ?></td>
                                <td><?= htmlspecialchars($order['equipment_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars(mb_strimwidth($order['complaint'], 0, 30, '...')) ?></td>
                                <td><?= htmlspecialchars($order['technician_name'] ?? 'Sedang Mencarikan') ?></td>
                                <td><span class="badge <?= $badgeClass($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span></td>
                                <td class="text-end pe-3"><a href="<?= BASE_URL ?>/customer/order/detail?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">Detail</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                Belum ada riwayat pesanan. Yuk buat pesanan pertama Anda!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
