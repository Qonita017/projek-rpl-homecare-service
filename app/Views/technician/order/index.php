<?php
$badgeClass = function($status) {
    switch($status) {
        case 'ASSIGNED': return 'bg-warning text-dark';
        case 'ACCEPTED': return 'bg-info text-dark';
        case 'ON_THE_WAY': return 'bg-secondary text-white';
        case 'IN_PROGRESS': return 'bg-primary text-white';
        case 'COMPLETED': return 'bg-success text-white';
        default: return 'bg-light text-dark';
    }
};

$statusLabel = function($status) {
    switch($status) {
        case 'ASSIGNED': return 'Tugas Baru (Assigned)';
        case 'ACCEPTED': return 'Diterima (Accepted)';
        case 'ON_THE_WAY': return 'Menuju Lokasi (On The Way)';
        case 'IN_PROGRESS': return 'Sedang Dikerjakan (In Progress)';
        case 'COMPLETED': return 'Selesai (Completed)';
        default: return $status;
    }
};
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/technician/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pekerjaan Saya</li>
            </ol>
        </nav>
        <h1 class="h2"><i class="bi bi-briefcase-fill text-primary me-2"></i>Daftar Pekerjaan Saya</h1>
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

<!-- Status Filters -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body pb-2">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="text-muted small fw-bold text-uppercase me-2"><i class="bi bi-funnel-fill me-1"></i>Filter Status:</span>
            
            <a href="<?= BASE_URL ?>/technician/jobs?status=ALL&search=<?= urlencode($search) ?>" class="btn btn-sm <?= ($status === 'ALL' || empty($status)) ? 'btn-dark' : 'btn-outline-secondary' ?> rounded-pill">
                Semua <span class="badge bg-secondary ms-1"><?= $stats['ALL'] ?? 0 ?></span>
            </a>
            
            <a href="<?= BASE_URL ?>/technician/jobs?status=ASSIGNED&search=<?= urlencode($search) ?>" class="btn btn-sm <?= ($status === 'ASSIGNED') ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning text-dark' ?> rounded-pill">
                Tugas Baru <span class="badge bg-warning text-dark border ms-1"><?= $stats['ASSIGNED'] ?? 0 ?></span>
            </a>
            
            <a href="<?= BASE_URL ?>/technician/jobs?status=ACCEPTED&search=<?= urlencode($search) ?>" class="btn btn-sm <?= ($status === 'ACCEPTED') ? 'btn-info text-dark fw-bold' : 'btn-outline-info text-dark' ?> rounded-pill">
                Diterima <span class="badge bg-info text-dark ms-1"><?= $stats['ACCEPTED'] ?? 0 ?></span>
            </a>
            
            <a href="<?= BASE_URL ?>/technician/jobs?status=ON_THE_WAY&search=<?= urlencode($search) ?>" class="btn btn-sm <?= ($status === 'ON_THE_WAY') ? 'btn-secondary' : 'btn-outline-secondary' ?> rounded-pill">
                Menuju Lokasi <span class="badge bg-secondary ms-1"><?= $stats['ON_THE_WAY'] ?? 0 ?></span>
            </a>
            
            <a href="<?= BASE_URL ?>/technician/jobs?status=IN_PROGRESS&search=<?= urlencode($search) ?>" class="btn btn-sm <?= ($status === 'IN_PROGRESS') ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill">
                Sedang Dikerjakan <span class="badge bg-primary ms-1"><?= $stats['IN_PROGRESS'] ?? 0 ?></span>
            </a>
            
            <a href="<?= BASE_URL ?>/technician/jobs?status=COMPLETED&search=<?= urlencode($search) ?>" class="btn btn-sm <?= ($status === 'COMPLETED') ? 'btn-success' : 'btn-outline-success' ?> rounded-pill">
                Selesai <span class="badge bg-success ms-1"><?= $stats['COMPLETED'] ?? 0 ?></span>
            </a>
        </div>
    </div>
</div>

<!-- Search Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/technician/jobs" class="row g-2">
            <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari ID order, nama customer, no. HP, peralatan, merk, keluhan..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Cari</button>
            </div>
        </form>
    </div>
</div>

<!-- Jobs Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Order ID</th>
                        <th>Customer & Kontak</th>
                        <th>Peralatan</th>
                        <th>Jadwal Servis</th>
                        <th>Keluhan</th>
                        <th>Alamat Servis</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Aksi & Workflow</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($orders)): ?>
                        <?php foreach($orders as $order): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                                    <?php if(!empty($order['customer_phone'])): ?>
                                        <div class="small text-muted">
                                            <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($order['customer_phone']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
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
                                        <?= htmlspecialchars(mb_strimwidth($order['complaint'], 0, 30, '...')) ?>
                                    </span>
                                </td>
                                <td>
                                    <span title="<?= htmlspecialchars($order['address']) ?>">
                                        <i class="bi bi-geo-alt me-1 text-danger"></i><?= htmlspecialchars(mb_strimwidth($order['address'], 0, 25, '...')) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass($order['status']) ?> px-2 py-1">
                                        <?= $statusLabel($order['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="<?= BASE_URL ?>/technician/jobs/detail?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary" title="Lihat Detail & Kelola">
                                            <i class="bi bi-eye me-1"></i>Detail
                                        </a>

                                        <?php if($order['status'] === 'ASSIGNED'): ?>
                                            <form action="<?= BASE_URL ?>/technician/jobs/status" method="POST" class="d-inline" onsubmit="return confirm('Terima pekerjaan ini?')">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <input type="hidden" name="new_status" value="ACCEPTED">
                                                <button type="submit" class="btn btn-sm btn-success fw-bold">
                                                    <i class="bi bi-check2"></i> Terima
                                                </button>
                                            </form>
                                        <?php elseif($order['status'] === 'ACCEPTED'): ?>
                                            <form action="<?= BASE_URL ?>/technician/jobs/status" method="POST" class="d-inline" onsubmit="return confirm('Mulai perjalanan menuju lokasi customer?')">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <input type="hidden" name="new_status" value="ON_THE_WAY">
                                                <button type="submit" class="btn btn-sm btn-secondary fw-bold">
                                                    <i class="bi bi-truck"></i> Berangkat
                                                </button>
                                            </form>
                                        <?php elseif($order['status'] === 'ON_THE_WAY'): ?>
                                            <form action="<?= BASE_URL ?>/technician/jobs/status" method="POST" class="d-inline" onsubmit="return confirm('Mulai pengerjaan servis peralatan?')">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <input type="hidden" name="new_status" value="IN_PROGRESS">
                                                <button type="submit" class="btn btn-sm btn-primary fw-bold">
                                                    <i class="bi bi-tools"></i> Mulai Servis
                                                </button>
                                            </form>
                                        <?php elseif($order['status'] === 'IN_PROGRESS'): ?>
                                            <a href="<?= BASE_URL ?>/technician/jobs/detail?id=<?= $order['id'] ?>#reportCard" class="btn btn-sm btn-success fw-bold">
                                                <i class="bi bi-clipboard-check"></i> Selesaikan
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-briefcase fs-1 d-block mb-3 text-secondary"></i>
                                <h6 class="fw-bold">Tidak ada daftar pekerjaan pada filter ini</h6>
                                <p class="mb-0">Pekerjaan yang ditugaskan oleh Admin akan otomatis muncul di halaman ini.</p>
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
        <span class="small text-muted">Menampilkan halaman <?= $page ?> dari <?= $total_pages ?> (Total <?= $total_count ?> pekerjaan)</span>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/technician/jobs?page=<?= $i ?>&status=<?= urlencode($status) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
