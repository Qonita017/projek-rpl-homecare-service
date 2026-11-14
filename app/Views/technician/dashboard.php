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
    <h1 class="h2">Dashboard Teknisi</h1>
</div>
<div class="alert alert-warning shadow-sm">
    Selamat datang, <strong><?= htmlspecialchars($user_name) ?></strong>! Ada pekerjaan baru menunggu untuk Anda kerjakan.
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <a href="<?= BASE_URL ?>/technician/jobs?status=ASSIGNED" class="text-decoration-none">
            <div class="card text-white bg-primary shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="card-title"><i class="bi bi-bell mb-2 fs-3 d-block"></i>Pekerjaan Baru</h6>
                    <p class="card-text fs-3 fw-bold"><?= $stats['pekerjaan_baru'] ?? 0 ?></p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="<?= BASE_URL ?>/technician/jobs?status=IN_PROGRESS" class="text-decoration-none">
            <div class="card text-dark bg-info shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="card-title"><i class="bi bi-cone-striped mb-2 fs-3 d-block"></i>Pekerjaan Aktif</h6>
                    <p class="card-text fs-3 fw-bold"><?= $stats['pekerjaan_aktif'] ?? 0 ?></p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="<?= BASE_URL ?>/technician/jobs?status=COMPLETED" class="text-decoration-none">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="card-title"><i class="bi bi-check-circle mb-2 fs-3 d-block"></i>Pekerjaan Selesai</h6>
                    <p class="card-text fs-3 fw-bold"><?= $stats['pekerjaan_selesai'] ?? 0 ?></p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-dark bg-warning shadow-sm h-100">
            <div class="card-body text-center">
                <h6 class="card-title"><i class="bi bi-star-fill mb-2 fs-3 d-block"></i>Rating Saya</h6>
                <p class="card-text fs-3 fw-bold"><?= isset($technician['rating']) ? number_format($technician['rating'], 1) : '0.0' ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom pt-3 pb-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary fw-bold">Pekerjaan Terbaru</h5>
        <a href="<?= BASE_URL ?>/technician/jobs" class="btn btn-sm btn-outline-primary">Lihat Semua Pekerjaan</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ID Order</th>
                        <th>Tanggal Tugas</th>
                        <th>Customer</th>
                        <th>Peralatan</th>
                        <th>Keluhan</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($recent_orders)): ?>
                        <?php foreach($recent_orders as $order): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td><?= htmlspecialchars(date('d M Y H:i', strtotime($order['updated_at']))) ?></td>
                                <td><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($order['equipment_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars(mb_strimwidth($order['complaint'], 0, 30, '...')) ?></td>
                                <td><span class="badge <?= $badgeClass($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span></td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/technician/jobs/detail?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Kelola
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada tugas pekerjaan masuk.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
