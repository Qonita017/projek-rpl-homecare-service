<?php
$badgeClass = function($status) {
    switch($status) {
        case 'ASSIGNED': return 'bg-warning text-dark';
        case 'ACCEPTED': return 'bg-info text-dark';
        case 'ON_THE_WAY': return 'bg-secondary text-white';
        case 'IN_PROGRESS': return 'bg-primary text-white';
        case 'COMPLETED': return 'bg-success text-white';
        case 'CANCELLED': return 'bg-danger text-white';
        default: return 'bg-light text-dark';
    }
};

$statusLabel = function($status) {
    switch($status) {
        case 'ASSIGNED': return 'Tugas Baru (Assigned)';
        case 'ACCEPTED': return 'Pekerjaan Diterima (Accepted)';
        case 'ON_THE_WAY': return 'Sedang Menuju Lokasi Customer (On The Way)';
        case 'IN_PROGRESS': return 'Servis Sedang Dikerjakan (In Progress)';
        case 'COMPLETED': return 'Pekerjaan Selesai (Completed)';
        case 'CANCELLED': return 'Pesanan Dibatalkan (Cancelled)';
        default: return $status;
    }
};

// Workflow step calculation
$currentStep = 1;
switch($order['status']) {
    case 'ASSIGNED': $currentStep = 1; break;
    case 'ACCEPTED': $currentStep = 2; break;
    case 'ON_THE_WAY': $currentStep = 3; break;
    case 'IN_PROGRESS': $currentStep = 4; break;
    case 'COMPLETED': $currentStep = 5; break;
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/technician/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/technician/jobs">Pekerjaan Saya</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail #ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></li>
            </ol>
        </nav>
        <h1 class="h2">
            <i class="bi bi-tools text-primary me-2"></i>Kelola Pekerjaan Servis
            <span class="text-muted fs-4">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></span>
        </h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="<?= BASE_URL ?>/technician/jobs" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pekerjaan
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

<!-- Workflow Steps Banner -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center mb-3">
            <div class="col-md-7">
                <span class="text-muted small text-uppercase fw-bold">Status Pekerjaan:</span>
                <div class="mt-1">
                    <span class="badge <?= $badgeClass($order['status']) ?> fs-6 px-3 py-2">
                        <?= $statusLabel($order['status']) ?>
                    </span>
                </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <span class="text-muted small d-block">Jadwal Permintaan Servis:</span>
                <span class="fw-bold text-primary fs-6">
                    <i class="bi bi-calendar-check me-1"></i><?= date('d M Y, H:i', strtotime($order['service_date'])) ?> WIB
                </span>
            </div>
        </div>

        <?php if($order['status'] !== 'CANCELLED'): ?>
        <div class="mt-4 pt-3 border-top">
            <div class="row text-center">
                <div class="col">
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center text-white <?= ($currentStep >= 1) ? 'bg-warning text-dark' : 'bg-light text-muted border' ?>" style="width: 40px; height: 40px;">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <span class="small fw-semibold <?= ($currentStep >= 1) ? 'text-dark' : 'text-muted' ?>">1. Ditugaskan</span>
                </div>
                <div class="col">
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center text-white <?= ($currentStep >= 2) ? 'bg-info text-dark' : 'bg-light text-muted border' ?>" style="width: 40px; height: 40px;">
                        <i class="bi bi-hand-thumbs-up-fill"></i>
                    </div>
                    <span class="small fw-semibold <?= ($currentStep >= 2) ? 'text-primary' : 'text-muted' ?>">2. Diterima</span>
                </div>
                <div class="col">
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center text-white <?= ($currentStep >= 3) ? 'bg-secondary' : 'bg-light text-muted border' ?>" style="width: 40px; height: 40px;">
                        <i class="bi bi-truck"></i>
                    </div>
                    <span class="small fw-semibold <?= ($currentStep >= 3) ? 'text-secondary' : 'text-muted' ?>">3. Menuju Lokasi</span>
                </div>
                <div class="col">
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center text-white <?= ($currentStep >= 4) ? 'bg-primary' : 'bg-light text-muted border' ?>" style="width: 40px; height: 40px;">
                        <i class="bi bi-tools"></i>
                    </div>
                    <span class="small fw-semibold <?= ($currentStep >= 4) ? 'text-primary' : 'text-muted' ?>">4. Dikerjakan</span>
                </div>
                <div class="col">
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center text-white <?= ($currentStep >= 5) ? 'bg-success' : 'bg-light text-muted border' ?>" style="width: 40px; height: 40px;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <span class="small fw-semibold <?= ($currentStep >= 5) ? 'text-success' : 'text-muted' ?>">5. Selesai</span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- Left Column: Customer, Location & Equipment Details -->
    <div class="col-lg-7 mb-4">
        <!-- Customer & Location Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="bi bi-person-lines-fill me-2"></i>Informasi Customer & Lokasi Servis
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Nama Customer</div>
                    <div class="col-sm-8 fw-bold fs-6 text-dark"><?= htmlspecialchars($order['customer_name']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Nomor WhatsApp / HP</div>
                    <div class="col-sm-8">
                        <strong class="text-dark me-2"><?= htmlspecialchars($order['customer_phone'] ?? '-') ?></strong>
                        <?php if(!empty($order['customer_phone'])): ?>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $order['customer_phone']) ?>" target="_blank" class="btn btn-sm btn-success py-1 px-3">
                                <i class="bi bi-whatsapp me-1"></i> Chat WhatsApp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Alamat Lengkap</div>
                    <div class="col-sm-8 bg-light p-3 rounded">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                        <span id="customer_address_text"><?= nl2br(htmlspecialchars($order['address'])) ?></span>
                        <div class="mt-2">
                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($order['address']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-map me-1"></i> Buka di Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipment & Complaint Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="bi bi-cpu me-2"></i>Peralatan & Keluhan Masalah
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Nama Peralatan</div>
                    <div class="col-sm-8 fw-bold fs-6"><?= htmlspecialchars($order['equipment_name']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Merk / Brand</div>
                    <div class="col-sm-8"><?= htmlspecialchars($order['equipment_brand'] ?? '-') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Kategori</div>
                    <div class="col-sm-8"><span class="badge bg-light text-dark border"><?= htmlspecialchars($order['equipment_category'] ?? 'Umum') ?></span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Keluhan Kerusakan</div>
                    <div class="col-sm-8 text-break bg-light p-3 rounded">
                        <?= nl2br(htmlspecialchars($order['complaint'])) ?>
                    </div>
                </div>

                <?php if(!empty($order['photo'])): ?>
                <div class="row">
                    <div class="col-sm-4 text-muted">Foto Kerusakan</div>
                    <div class="col-sm-8">
                        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($order['photo']) ?>" target="_blank" title="Klik untuk memperbesar">
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($order['photo']) ?>" alt="Foto Kerusakan" class="img-thumbnail rounded" style="max-height: 200px;">
                        </a>
                        <div class="small text-muted mt-1"><i class="bi bi-zoom-in me-1"></i>Klik foto untuk memperbesar</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Workflow Actions & Service Report -->
    <div class="col-lg-5 mb-4">
        
        <!-- Action Workflow Card based on current status -->
        <div class="card shadow-sm border-0 mb-4 border-top border-4 border-primary">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="bi bi-lightning-charge-fill me-2"></i>Aksi Workflow Pekerjaan
                </h5>
            </div>
            <div class="card-body p-4">

                <!-- STEP 1: ASSIGNED -> ACCEPTED -->
                <?php if($order['status'] === 'ASSIGNED'): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-bell-fill me-1"></i> Anda telah ditugaskan untuk pekerjaan ini. Silakan klik tombol di bawah untuk menerima tugas.
                    </div>
                    <form action="<?= BASE_URL ?>/technician/jobs/status" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menerima pekerjaan ini?')">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <input type="hidden" name="new_status" value="ACCEPTED">
                        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold py-3 shadow-sm">
                            <i class="bi bi-check2-circle me-2"></i> Terima Pekerjaan (ACCEPTED)
                        </button>
                    </form>

                <!-- STEP 2: ACCEPTED -> ON_THE_WAY -->
                <?php elseif($order['status'] === 'ACCEPTED'): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-1"></i> Pekerjaan telah Anda terima. Saat Anda siap berangkat menuju lokasi customer, ubah status menjadi <strong>ON_THE_WAY</strong>.
                    </div>
                    <form action="<?= BASE_URL ?>/technician/jobs/status" method="POST" onsubmit="return confirm('Mulai perjalanan menuju lokasi customer sekarang?')">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <input type="hidden" name="new_status" value="ON_THE_WAY">
                        <button type="submit" class="btn btn-secondary btn-lg w-100 fw-bold py-3 shadow-sm">
                            <i class="bi bi-truck me-2"></i> Berangkat ke Lokasi (ON_THE_WAY)
                        </button>
                    </form>

                <!-- STEP 3: ON_THE_WAY -> IN_PROGRESS -->
                <?php elseif($order['status'] === 'ON_THE_WAY'): ?>
                    <div class="alert alert-primary">
                        <i class="bi bi-geo-alt-fill me-1"></i> Anda sedang dalam perjalanan. Saat tiba di lokasi customer dan mulai melakukan pengecekan/servis, klik tombol di bawah.
                    </div>
                    <form action="<?= BASE_URL ?>/technician/jobs/status" method="POST" onsubmit="return confirm('Tiba di lokasi dan mulai proses pengerjaan servis?')">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <input type="hidden" name="new_status" value="IN_PROGRESS">
                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 shadow-sm">
                            <i class="bi bi-tools me-2"></i> Mulai Pengerjaan (IN_PROGRESS)
                        </button>
                    </form>

                <!-- STEP 4: IN_PROGRESS -> COMPLETED (Form Report) -->
                <?php elseif($order['status'] === 'IN_PROGRESS'): ?>
                    <div class="alert alert-primary">
                        <i class="bi bi-tools me-1"></i> Servis sedang berjalan. Setelah pekerjaan selesai, silakan isi <strong>Laporan Servis & Biaya Akhir</strong> di bawah ini untuk menyelesaikan pesanan.
                    </div>
                    <a href="#reportCard" class="btn btn-success btn-lg w-100 fw-bold py-3 shadow-sm">
                        <i class="bi bi-clipboard2-check-fill me-2"></i> Isi Laporan & Selesaikan Servis
                    </a>

                <!-- STEP 5: COMPLETED -->
                <?php elseif($order['status'] === 'COMPLETED'): ?>
                    <div class="alert alert-success mb-0 text-center py-3">
                        <i class="bi bi-check-circle-fill fs-1 d-block mb-2 text-success"></i>
                        <h5 class="fw-bold mb-1">Pekerjaan Telah Selesai</h5>
                        <p class="small text-muted mb-0">Laporan servis dan biaya akhir telah tercatat di sistem.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Service Report Completion Card (Visible if IN_PROGRESS or COMPLETED) -->
        <?php if($order['status'] === 'IN_PROGRESS'): ?>
        <div class="card shadow-sm border-0 mb-4 border-top border-4 border-success" id="reportCard">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-success">
                    <i class="bi bi-clipboard-check me-2"></i>Form Laporan Servis (Service Report)
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/technician/jobs/complete" method="POST" onsubmit="return confirmComplete()">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                    <!-- 1. Inspection Result -->
                    <div class="mb-3">
                        <label for="inspection_result" class="form-label fw-bold">Hasil Pemeriksaan / Diagnosa <span class="text-danger">*</span></label>
                        <textarea class="form-control shadow-sm" id="inspection_result" name="inspection_result" rows="3" placeholder="Jelaskan kondisi kerusakan yang ditemukan pada peralatan..." required></textarea>
                    </div>

                    <!-- 2. Repair Action -->
                    <div class="mb-3">
                        <label for="repair_action" class="form-label fw-bold">Tindakan Perbaikan / Suku Cadang <span class="text-danger">*</span></label>
                        <textarea class="form-control shadow-sm" id="repair_action" name="repair_action" rows="3" placeholder="Jelaskan tindakan servis yang dilakukan dan komponen yang diganti (jika ada)..." required></textarea>
                    </div>

                    <!-- 3. Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-bold">Catatan Tambahan untuk Customer <span class="badge bg-light text-muted border">Opsional</span></label>
                        <textarea class="form-control shadow-sm" id="notes" name="notes" rows="2" placeholder="Saran pemakaian, masa garansi servis, dll..."></textarea>
                    </div>

                    <!-- 4. Final Cost -->
                    <div class="mb-4">
                        <label for="cost" class="form-label fw-bold">Total Biaya Servis Akhir (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text fw-bold">Rp</span>
                            <input type="number" class="form-control fw-bold fs-5 text-success" id="cost" name="cost" placeholder="0" min="0" step="1000" required>
                        </div>
                        <div class="form-text">Biaya ini mencakup jasa perbaikan dan penggantian suku cadang.</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg shadow-sm fw-bold py-3">
                            <i class="bi bi-check2-all me-2"></i>Simpan Laporan & Selesaikan Servis (COMPLETED)
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if($order['status'] === 'COMPLETED' && !empty($order['inspection_result'])): ?>
        <!-- Completed Service Report View Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-success">
                    <i class="bi bi-file-earmark-check-fill me-2"></i>Laporan Servis yang Tersimpan
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <span class="text-muted small d-block">Hasil Pemeriksaan:</span>
                    <strong><?= nl2br(htmlspecialchars($order['inspection_result'])) ?></strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">Tindakan Perbaikan:</span>
                    <span><?= nl2br(htmlspecialchars($order['repair_action'] ?? '-')) ?></span>
                </div>
                <?php if(!empty($order['report_notes'])): ?>
                <div class="mb-3">
                    <span class="text-muted small d-block">Catatan Tambahan:</span>
                    <span class="text-muted"><?= nl2br(htmlspecialchars($order['report_notes'])) ?></span>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <span class="fw-bold">Total Biaya Akhir:</span>
                    <span class="fw-bold fs-4 text-success">
                        Rp <?= number_format($order['final_cost'] ?? $order['report_cost'] ?? 0, 0, ',', '.') ?>
                    </span>
                </div>
                <?php if(!empty($order['payment_id'])): ?>
                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Pembayaran Customer:</span>
                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1 fw-bold">
                        <i class="bi bi-check-circle-fill me-1"></i>Lunas (<?= ($order['payment_method'] === 'CASH') ? 'Tunai di Tempat' : 'Transfer ' . htmlspecialchars($order['payment_provider']) ?>)
                    </span>
                </div>
                <?php else: ?>
                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Pembayaran Customer:</span>
                    <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1 fw-bold">
                        <i class="bi bi-hourglass-split me-1"></i>Menunggu Pembayaran
                    </span>
                </div>
                <?php endif; ?>
                <?php if(!empty($order['report_completed_at'])): ?>
                <div class="text-end small text-muted mt-2">
                    Diselesaikan pada: <?= date('d M Y, H:i', strtotime($order['report_completed_at'])) ?> WIB
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<script>
function confirmComplete() {
    const cost = document.getElementById('cost').value;
    if (!cost || cost <= 0) {
        alert('Silakan masukkan total biaya servis yang valid.');
        return false;
    }
    return confirm(`Konfirmasi Penyelesaian Servis:\nApakah Anda yakin pekerjaan telah selesai dan laporan servis sudah benar?\n\nTotal Biaya: Rp ${Number(cost).toLocaleString('id-ID')}\nStatus akan diubah menjadi COMPLETED.`);
}
</script>
