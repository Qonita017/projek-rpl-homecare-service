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

$statusLabel = function($status) {
    switch($status) {
        case 'PENDING': return 'Menunggu Konfirmasi';
        case 'ASSIGNED': return 'Teknisi Ditugaskan';
        case 'ACCEPTED': return 'Diterima Teknisi';
        case 'ON_THE_WAY': return 'Teknisi Menuju Lokasi';
        case 'IN_PROGRESS': return 'Sedang Dikerjakan';
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
                <li class="breadcrumb-item active" aria-current="page">Pesanan Saya</li>
            </ol>
        </nav>
        <h1 class="h2"><i class="bi bi-clock-history text-primary me-2"></i>Pesanan Saya (Aktif)</h1>
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

<?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Navigation Tabs -->
<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link active fw-semibold" aria-current="page" href="<?= BASE_URL ?>/customer/order">
            <i class="bi bi-lightning-charge me-1"></i> Pesanan Aktif (<?= $total_count ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-dark fw-semibold" href="<?= BASE_URL ?>/customer/order/history">
            <i class="bi bi-check2-all me-1"></i> Riwayat Pesanan
        </a>
    </li>
</ul>

<!-- Search Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/customer/order" class="row g-2">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan No. Order, nama peralatan, merk, atau keluhan..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-secondary">Cari Pesanan</button>
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
                        <th>Jadwal Servis</th>
                        <th>Keluhan</th>
                        <th>Teknisi</th>
                        <th>Estimasi Biaya</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($orders)): ?>
                        <?php foreach($orders as $order): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($order['equipment_name']) ?></strong>
                                    <?php if(!empty($order['equipment_brand'])): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($order['equipment_brand']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><i class="bi bi-calendar3 me-1 text-muted"></i><?= date('d M Y', strtotime($order['service_date'])) ?></div>
                                    <div class="small text-muted"><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($order['service_date'])) ?> WIB</div>
                                </td>
                                <td>
                                    <span title="<?= htmlspecialchars($order['complaint']) ?>">
                                        <?= htmlspecialchars(mb_strimwidth($order['complaint'], 0, 35, '...')) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if(!empty($order['technician_name'])): ?>
                                        <div class="fw-semibold"><i class="bi bi-person-fill text-primary me-1"></i><?= htmlspecialchars($order['technician_name']) ?></div>
                                        <?php if(!empty($order['technician_phone'])): ?>
                                            <div class="small text-muted"><?= htmlspecialchars($order['technician_phone']) ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Belum Ditugaskan</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(!empty($order['estimated_cost'])): ?>
                                        <span class="fw-semibold text-success">Rp <?= number_format($order['estimated_cost'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">Menunggu Diagnosa</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass($order['status']) ?> px-2 py-1">
                                        <?= $statusLabel($order['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/customer/order/detail?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard-x fs-1 d-block mb-3 text-secondary"></i>
                                <h6 class="fw-bold">Tidak ada pesanan aktif saat ini</h6>
                                <p class="mb-3">Semua peralatan Anda berfungsi baik atau Anda belum membuat pesanan baru.</p>
                                <a href="<?= BASE_URL ?>/customer/order/create" class="btn btn-primary btn-sm rounded-pill px-4">
                                    <i class="bi bi-plus-circle me-1"></i>Buat Pesanan Sekarang
                                </a>
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
                        <a class="page-link" href="<?= BASE_URL ?>/customer/order?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
