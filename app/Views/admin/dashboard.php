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
    <h1 class="h2">Dashboard Admin</h1>
</div>
<div class="alert alert-success shadow-sm">
    Selamat datang, <strong><?= htmlspecialchars($user_name) ?></strong>! Anda berada di area Admin.
</div>

<!-- AI Chatbot Engine Status Card -->
<div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #1a2a6c 0%, #27408b 100%); color: #fff; border-radius: 12px; overflow: hidden;">
    <div class="card-body p-3 p-md-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center">
            <div class="rounded-circle bg-white bg-opacity-20 p-3 me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px; flex-shrink: 0;">
                <i class="bi bi-robot fs-3 text-warning"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h5 class="fw-bold mb-0">Mesin AI Chatbot & Diagnosis</h5>
                    <span class="badge bg-success bg-opacity-75"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>
                </div>
                <p class="mb-0 small text-light opacity-90">
                    Provider: <strong class="badge bg-light text-dark text-uppercase"><?= htmlspecialchars($ai_provider ?? 'NVIDIA') ?></strong>
                    <span class="mx-2">&bull;</span>
                    Model: <code class="bg-black bg-opacity-25 text-warning px-2 py-1 rounded"><?= htmlspecialchars($ai_model ?? 'meta/llama-3.2-11b-vision-instruct') ?></code>
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= BASE_URL ?>/admin/ai-setting" class="btn btn-warning fw-semibold shadow-sm px-3">
                <i class="bi bi-sliders2-vertical me-1"></i> Edit API Key & Model
            </a>
            <a href="<?= BASE_URL ?>/admin/ai-chat" class="btn btn-outline-light px-3">
                <i class="bi bi-chat-dots-fill me-1"></i> Buka Chatbot
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <div class="card text-white bg-primary shadow-sm h-100">
            <div class="card-body text-center">
                <h6 class="card-title"><i class="bi bi-people mb-2 fs-3 d-block"></i>Total Customer</h6>
                <p class="card-text fs-4 fw-bold"><?= $stats['total_customer'] ?? 0 ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card text-white bg-success shadow-sm h-100">
            <div class="card-body text-center">
                <h6 class="card-title"><i class="bi bi-tools mb-2 fs-3 d-block"></i>Total Teknisi</h6>
                <p class="card-text fs-4 fw-bold"><?= $stats['total_teknisi'] ?? 0 ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card text-dark bg-warning shadow-sm h-100">
            <div class="card-body text-center">
                <h6 class="card-title"><i class="bi bi-hourglass-split mb-2 fs-3 d-block"></i>Pesanan Pending</h6>
                <p class="card-text fs-4 fw-bold"><?= $stats['pesanan_pending'] ?? 0 ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info shadow-sm h-100">
            <div class="card-body text-center">
                <h6 class="card-title"><i class="bi bi-gear-wide-connected mb-2 fs-3 d-block"></i>Pesanan Aktif</h6>
                <p class="card-text fs-4 fw-bold"><?= $stats['pesanan_aktif'] ?? 0 ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-dark shadow-sm h-100">
            <div class="card-body text-center">
                <h6 class="card-title"><i class="bi bi-check-circle mb-2 fs-3 d-block"></i>Pesanan Selesai</h6>
                <p class="card-text fs-4 fw-bold"><?= $stats['pesanan_selesai'] ?? 0 ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom pt-3 pb-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary fw-bold">Service Order Terbaru</h5>
        <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-sm btn-outline-primary">Lihat Semua Service Orders</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ID Order</th>
                        <th>Tanggal</th>
                        <th>Customer</th>
                        <th>Peralatan</th>
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
                                <td><?= htmlspecialchars(date('d M Y H:i', strtotime($order['created_at']))) ?></td>
                                <td><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($order['equipment_name'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if(!empty($order['technician_name'])): ?>
                                        <span class="text-success fw-semibold"><i class="bi bi-person-check-fill me-1"></i><?= htmlspecialchars($order['technician_name']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark border">Belum Ditugaskan</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $badgeClass($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span></td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/admin/orders/detail?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada pesanan masuk.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
