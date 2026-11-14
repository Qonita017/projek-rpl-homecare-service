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
        case 'PENDING': return 'Pending';
        case 'ASSIGNED': return 'Assigned';
        case 'ACCEPTED': return 'Accepted';
        case 'ON_THE_WAY': return 'On The Way';
        case 'IN_PROGRESS': return 'In Progress';
        case 'COMPLETED': return 'Completed';
        case 'CANCELLED': return 'Cancelled';
        default: return $status;
    }
};
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Service Orders</li>
            </ol>
        </nav>
        <h1 class="h2"><i class="bi bi-card-checklist text-primary me-2"></i>Kelola Service Orders</h1>
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
            
            <a href="<?= BASE_URL ?>/admin/orders?status=ALL&search=<?= urlencode($search) ?>" class="btn btn-sm <?= ($status === 'ALL' || empty($status)) ? 'btn-dark' : 'btn-outline-secondary' ?> rounded-pill">
                Semua <span class="badge bg-secondary ms-1"><?= $stats['ALL'] ?? 0 ?></span>
            </a>
            
            <a href="<?= BASE_URL ?>/admin/orders?status=PENDING&search=<?= urlencode($search) ?>" class="btn btn-sm <?= ($status === 'PENDING') ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning text-dark' ?> rounded-pill">
                Pending <span class="badge bg-warning text-dark border ms-1"><?= $stats['PENDING'] ?? 0 ?></span>
            </a>
            
            <a href="<?= BASE_URL ?>/admin/orders?status=ASSIGNED&search=<?= urlencode($search) ?>" class="btn btn-sm <?= ($status === 'ASSIGNED') ? 'btn-info text-dark fw-bold' : 'btn-outline-info text-dark' ?> rounded-pill">
                Assigned <span class="badge bg-info text-dark ms-1"><?= $stats['ASSIGNED'] ?? 0 ?></span>
            </a>
            
            <a href="<?= BASE_URL ?>/admin/orders?status=ACCEPTED&search=<?= urlencode($search) ?>" class="btn btn-sm <?= ($status === 'ACCEPTED') ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill">
                Accepted <span class="badge bg-primary ms-1"><?= $stats['ACCEPTED'] ?? 0 ?></span>
            </a>

            <a href="<?= BASE_URL ?>/admin/orders?status=ON_THE_WAY&search=<?= urlencode($search) ?>" class="btn btn-sm <?= ($status === 'ON_THE_WAY') ? 'btn-secondary' : 'btn-outline-secondary' ?> rounded-pill">
                On The Way <span class="badge bg-secondary ms-1"><?= $stats['ON_THE_WAY'] ?? 0 ?></span>
            </a>
            
            <a href="<?= BASE_URL ?>/admin/orders?status=IN_PROGRESS&search=<?= urlencode($search) ?>" class="btn btn-sm <?= ($status === 'IN_PROGRESS') ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill">
                In Progress <span class="badge bg-primary ms-1"><?= $stats['IN_PROGRESS'] ?? 0 ?></span>
            </a>
            
            <a href="<?= BASE_URL ?>/admin/orders?status=COMPLETED&search=<?= urlencode($search) ?>" class="btn btn-sm <?= ($status === 'COMPLETED') ? 'btn-success' : 'btn-outline-success' ?> rounded-pill">
                Completed <span class="badge bg-success ms-1"><?= $stats['COMPLETED'] ?? 0 ?></span>
            </a>
            
            <a href="<?= BASE_URL ?>/admin/orders?status=CANCELLED&search=<?= urlencode($search) ?>" class="btn btn-sm <?= ($status === 'CANCELLED') ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill">
                Cancelled <span class="badge bg-danger ms-1"><?= $stats['CANCELLED'] ?? 0 ?></span>
            </a>
        </div>
    </div>
</div>

<!-- Search Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/admin/orders" class="row g-2">
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

<!-- Service Orders Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No. Order</th>
                        <th>Customer</th>
                        <th>Peralatan</th>
                        <th>Jadwal Servis</th>
                        <th>Keluhan</th>
                        <th>Teknisi Ditugaskan</th>
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
                                    <strong><?= htmlspecialchars($order['customer_name']) ?></strong>
                                    <?php if(!empty($order['customer_phone'])): ?>
                                        <div class="small text-muted"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($order['customer_phone']) ?></div>
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
                                    <?php if(!empty($order['technician_name'])): ?>
                                        <div class="fw-semibold text-success">
                                            <i class="bi bi-person-check-fill me-1"></i><?= htmlspecialchars($order['technician_name']) ?>
                                        </div>
                                        <small class="text-muted"><?= htmlspecialchars($order['technician_specialization'] ?? 'Teknisi') ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark border">
                                            <i class="bi bi-exclamation-circle me-1"></i>Belum Ditugaskan
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass($order['status']) ?> px-2 py-1">
                                        <?= $statusLabel($order['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group">
                                        <a href="<?= BASE_URL ?>/admin/orders/detail?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                        
                                        <?php if(in_array($order['status'], ['PENDING', 'ASSIGNED'])): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="openAssignModal(<?= $order['id'] ?>, '<?= htmlspecialchars(addslashes($order['customer_name'])) ?>', '<?= htmlspecialchars(addslashes($order['equipment_name'])) ?>')" title="Tugaskan Teknisi">
                                            <i class="bi bi-person-plus"></i> Tugaskan
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3 text-secondary"></i>
                                <h6 class="fw-bold">Tidak ada data service order yang sesuai</h6>
                                <p class="mb-0">Coba sesuaikan filter status atau kata kunci pencarian Anda.</p>
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
        <span class="small text-muted">Menampilkan halaman <?= $page ?> dari <?= $total_pages ?> (Total <?= $total_count ?> order)</span>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/admin/orders?page=<?= $i ?>&status=<?= urlencode($status) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Quick Assign Technician -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= BASE_URL ?>/admin/orders/assign" method="POST" onsubmit="return confirmAssignment()">
                <input type="hidden" name="order_id" id="modal_order_id" value="">
                <input type="hidden" name="redirect" value="/admin/orders">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="assignModalLabel"><i class="bi bi-person-gear me-2"></i>Penugasan Teknisi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="alert alert-light border mb-3">
                        <div class="small text-muted">Order ID:</div>
                        <div class="fw-bold text-primary fs-5" id="modal_order_badge">#ORD-0000</div>
                        <div class="mt-2 small">
                            <strong>Customer:</strong> <span id="modal_customer_name">-</span><br>
                            <strong>Peralatan:</strong> <span id="modal_equipment_name">-</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_technician_select" class="form-label fw-bold">Pilih Teknisi Tersedia <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg" name="technician_id" id="modal_technician_select" required>
                            <option value="">-- Pilih Teknisi (Hanya yang Tersedia) --</option>
                            <?php if(!empty($availableTechnicians)): ?>
                                <?php foreach($availableTechnicians as $tech): ?>
                                    <option value="<?= $tech['id'] ?>" data-name="<?= htmlspecialchars($tech['name']) ?>">
                                        <?= htmlspecialchars($tech['name']) ?> - Spesialis: <?= htmlspecialchars($tech['specialization'] ?? 'Elektronik') ?> (★ <?= number_format($tech['rating'] ?? 5.0, 1) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Tidak ada teknisi yang berstatus Tersedia saat ini</option>
                            <?php endif; ?>
                        </select>
                        <div class="form-text text-muted">Hanya teknisi dengan status ketersediaan aktif (Available) yang dapat dipilih.</div>
                    </div>

                    <div class="alert alert-warning py-2 small mb-0">
                        <i class="bi bi-info-circle me-1"></i> Setelah teknisi ditugaskan, status order akan berubah menjadi <strong>ASSIGNED</strong> dan notifikasi akan terlihat di dashboard teknisi & customer.
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold" <?= empty($availableTechnicians) ? 'disabled' : '' ?>>
                        <i class="bi bi-check2-circle me-1"></i> Konfirmasi Penugasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAssignModal(orderId, customerName, equipmentName) {
    document.getElementById('modal_order_id').value = orderId;
    document.getElementById('modal_order_badge').textContent = '#ORD-' + String(orderId).padStart(4, '0');
    document.getElementById('modal_customer_name').textContent = customerName;
    document.getElementById('modal_equipment_name').textContent = equipmentName;
    document.getElementById('modal_technician_select').value = '';
    
    var modal = new bootstrap.Modal(document.getElementById('assignModal'));
    modal.show();
}

function confirmAssignment() {
    const select = document.getElementById('modal_technician_select');
    const selectedOption = select.options[select.selectedIndex];
    if (!select.value) {
        alert('Silakan pilih teknisi terlebih dahulu.');
        return false;
    }
    const techName = selectedOption.getAttribute('data-name') || selectedOption.text;
    const orderId = document.getElementById('modal_order_id').value;
    return confirm(`Apakah Anda yakin ingin menugaskan teknisi "${techName}" untuk Order #ORD-${String(orderId).padStart(4, '0')}? Status akan diubah menjadi ASSIGNED.`);
}
</script>
