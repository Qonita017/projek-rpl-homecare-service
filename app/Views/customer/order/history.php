<?php
$badgeClass = function($status) {
    switch($status) {
        case 'COMPLETED': return 'bg-success';
        case 'CANCELLED': return 'bg-danger';
        default: return 'bg-secondary';
    }
};

$statusLabel = function($status) {
    switch($status) {
        case 'COMPLETED': return 'Selesai';
        case 'CANCELLED': return 'Dibatalkan';
        default: return $status;
    }
};
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/customer/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Riwayat Pesanan</li>
            </ol>
        </nav>
        <h1 class="h2"><i class="bi bi-journal-check text-success me-2"></i>Riwayat Pesanan Servis</h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/customer/order/create" class="btn btn-primary shadow-sm rounded-pill px-4">
            <i class="bi bi-plus-circle me-2"></i>Buat Pesanan Baru
        </a>
    </div>
</div>

<?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- Navigation Tabs -->
<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link text-dark fw-semibold" href="<?= BASE_URL ?>/customer/order">
            <i class="bi bi-lightning-charge me-1"></i> Pesanan Aktif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active bg-success fw-semibold" aria-current="page" href="<?= BASE_URL ?>/customer/order/history">
            <i class="bi bi-check2-all me-1"></i> Riwayat Pesanan (<?= $total_count ?>)
        </a>
    </li>
</ul>

<!-- Search Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/customer/order/history" class="row g-2">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari riwayat pesanan berdasarkan No. Order, peralatan, merk, atau keluhan..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-secondary">Cari Riwayat</button>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No. Order</th>
                        <th>Peralatan</th>
                        <th>Tanggal Servis</th>
                        <th>Keluhan</th>
                        <th>Teknisi</th>
                        <th>Biaya Akhir</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($orders)): ?>
                        <?php foreach($orders as $order): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-secondary">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($order['equipment_name']) ?></strong>
                                    <?php if(!empty($order['equipment_brand'])): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($order['equipment_brand']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><i class="bi bi-calendar3 me-1 text-muted"></i><?= date('d M Y', strtotime($order['service_date'])) ?></div>
                                </td>
                                <td>
                                    <span title="<?= htmlspecialchars($order['complaint']) ?>">
                                        <?= htmlspecialchars(mb_strimwidth($order['complaint'], 0, 35, '...')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if(!empty($order['technician_name'])): ?>
                                        <div class="fw-semibold"><i class="bi bi-person-check-fill text-success me-1"></i><?= htmlspecialchars($order['technician_name']) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(!empty($order['final_cost'])): ?>
                                        <span class="fw-bold text-success">Rp <?= number_format($order['final_cost'], 0, ',', '.') ?></span>
                                    <?php elseif(!empty($order['estimated_cost'])): ?>
                                        <span class="text-muted">Rp <?= number_format($order['estimated_cost'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass($order['status']) ?> px-2 py-1">
                                        <?= $statusLabel($order['status']) ?>
                                    </span>
                                    <?php if($order['status'] === 'COMPLETED'): ?>
                                        <?php if(!empty($order['payment_status']) && $order['payment_status'] === 'PAID'): ?>
                                            <div class="mt-1">
                                                <span class="badge bg-success-subtle text-success border border-success" style="font-size: 0.72rem;">
                                                    <i class="bi bi-check-circle me-1"></i>Lunas (<?= ($order['payment_method'] === 'CASH') ? 'Tunai' : htmlspecialchars($order['payment_provider'] ?? 'Transfer') ?>)
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <div class="mt-1">
                                                <a href="<?= BASE_URL ?>/customer/order/detail?id=<?= $order['id'] ?>#paymentCard" class="badge bg-warning-subtle text-warning border border-warning text-decoration-none" style="font-size: 0.72rem;">
                                                    <i class="bi bi-wallet2 me-1"></i>Bayar Sekarang
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/customer/order/detail?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-receipt me-1"></i>Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-archive fs-1 d-block mb-3 text-secondary"></i>
                                <h6 class="fw-bold">Belum ada riwayat pesanan selesai</h6>
                                <p class="mb-0">Pesanan yang telah selesai atau dibatalkan akan muncul di sini.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    <?php if($total_pages > 1): ?>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
        <span class="small text-muted">Menampilkan halaman <?= $page ?> dari <?= $total_pages ?></span>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/customer/order/history?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
