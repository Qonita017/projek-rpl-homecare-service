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
        case 'PENDING': return 'Menunggu Konfirmasi & Penugasan';
        case 'ASSIGNED': return 'Teknisi Telah Ditugaskan';
        case 'ACCEPTED': return 'Diterima oleh Teknisi';
        case 'ON_THE_WAY': return 'Teknisi Sedang Menuju Lokasi';
        case 'IN_PROGRESS': return 'Servis Sedang Dikerjakan';
        case 'COMPLETED': return 'Servis Telah Selesai';
        case 'CANCELLED': return 'Pesanan Dibatalkan';
        default: return $status;
    }
};
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/orders">Service Orders</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail #ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></li>
            </ol>
        </nav>
        <h1 class="h2">
            <i class="bi bi-file-earmark-medical text-primary me-2"></i>Detail Service Order
            <span class="text-muted fs-4">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></span>
        </h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
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

<!-- Top Status Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <span class="text-muted small text-uppercase fw-bold">Status Pesanan:</span>
                <div class="mt-1">
                    <span class="badge <?= $badgeClass($order['status']) ?> fs-6 px-3 py-2">
                        <i class="bi bi-circle-fill me-1" style="font-size: 0.6rem;"></i> <?= $statusLabel($order['status']) ?>
                    </span>
                </div>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <span class="text-muted small d-block">Waktu Pemesanan Dibuat:</span>
                <span class="fw-bold text-dark"><i class="bi bi-calendar-event me-1"></i><?= date('d M Y, H:i', strtotime($order['created_at'])) ?> WIB</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column: Customer, Equipment, Complaint, AI Diagnosis -->
    <div class="col-lg-7 mb-4">
        <!-- Customer Info Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="bi bi-person-circle me-2"></i>Informasi Customer & Lokasi
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Nama Customer</div>
                    <div class="col-sm-8 fw-bold fs-6 text-dark"><?= htmlspecialchars($order['customer_name']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Nomor Telepon</div>
                    <div class="col-sm-8">
                        <a href="tel:<?= htmlspecialchars($order['customer_phone'] ?? '') ?>" class="text-decoration-none fw-semibold">
                            <i class="bi bi-telephone-fill text-success me-1"></i><?= htmlspecialchars($order['customer_phone'] ?? '-') ?>
                        </a>
                        <?php if(!empty($order['customer_phone'])): ?>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $order['customer_phone']) ?>" target="_blank" class="btn btn-sm btn-outline-success ms-2 py-0 px-2">
                                <i class="bi bi-whatsapp"></i> Chat
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Email</div>
                    <div class="col-sm-8"><?= htmlspecialchars($order['customer_email'] ?? '-') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Jadwal Servis</div>
                    <div class="col-sm-8 fw-bold text-primary">
                        <i class="bi bi-calendar-check me-1"></i><?= date('l, d F Y', strtotime($order['service_date'])) ?> 
                        <span class="badge bg-secondary ms-1"><?= date('H:i', strtotime($order['service_date'])) ?> WIB</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4 text-muted">Alamat Servis</div>
                    <div class="col-sm-8 text-break bg-light p-3 rounded">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= nl2br(htmlspecialchars($order['address'])) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipment & Problem Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="bi bi-tools me-2"></i>Peralatan & Keluhan Kerusakan
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Nama Peralatan</div>
                    <div class="col-sm-8 fw-semibold fs-6"><?= htmlspecialchars($order['equipment_name']) ?></div>
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
                    <div class="col-sm-4 text-muted">Keluhan Masalah</div>
                    <div class="col-sm-8 text-break bg-light p-3 rounded">
                        <?= nl2br(htmlspecialchars($order['complaint'])) ?>
                    </div>
                </div>

                <?php if(!empty($order['photo'])): ?>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Foto Kerusakan</div>
                    <div class="col-sm-8">
                        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($order['photo']) ?>" target="_blank" title="Klik untuk memperbesar">
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($order['photo']) ?>" alt="Foto Kerusakan" class="img-thumbnail rounded" style="max-height: 180px;">
                        </a>
                        <div class="small text-muted mt-1"><i class="bi bi-zoom-in me-1"></i>Klik gambar untuk memperbesar</div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- AI Diagnosis Section -->
                <div class="row mt-4 pt-3 border-top">
                    <div class="col-sm-4 text-muted">
                        <span class="d-flex align-items-center">
                            <i class="bi bi-robot text-info me-1"></i> <strong>AI Diagnosis</strong>
                        </span>
                    </div>
                    <div class="col-sm-8">
                        <?php if(!empty($order['ai_diagnosis'])): ?>
                            <div class="p-3 bg-light rounded-3 border small text-dark" style="line-height: 1.7; text-align: justify;">
                                <div class="d-flex align-items-center text-primary fw-bold mb-2">
                                    <i class="bi bi-stars text-warning me-1"></i> Catatan Diagnosis AI
                                </div>
                                <?= nl2br(htmlspecialchars($order['ai_diagnosis'])) ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small fst-italic">
                                <i class="bi bi-dash-circle me-1"></i>Belum ada diagnosa otomatis dari AI
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Technician Assignment & Costs -->
    <div class="col-lg-5 mb-4">
        <!-- Technician Assignment Card -->
        <div class="card shadow-sm border-0 mb-4 border-top border-4 border-primary">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="bi bi-person-gear me-2"></i>Penugasan Teknisi
                </h5>
            </div>
            <div class="card-body p-4">
                <?php if(!empty($order['technician_name'])): ?>
                    <div class="alert alert-success border-success mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-check-circle-fill fs-4 text-success me-2"></i>
                            <div>
                                <strong class="d-block text-success">Teknisi Saat Ini Ditugaskan:</strong>
                                <span class="fw-bold fs-6 text-dark"><?= htmlspecialchars($order['technician_name']) ?></span>
                            </div>
                        </div>
                        <div class="small mt-2 pt-2 border-top border-success-subtle">
                            <div><strong>Spesialisasi:</strong> <?= htmlspecialchars($order['technician_specialization'] ?? 'Elektronik') ?></div>
                            <div><strong>Rating:</strong> ★ <?= number_format($order['technician_rating'] ?? 5.0, 1) ?> (<?= $order['technician_experience'] ?? 0 ?> thn pengalaman)</div>
                            <div><strong>Kontak:</strong> <?= htmlspecialchars($order['technician_phone'] ?? '-') ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning border-warning mb-3">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                        <strong>Belum Ada Teknisi Ditugaskan</strong>
                        <p class="small mb-0 mt-1">Pilih salah satu teknisi yang berstatus <strong>Tersedia (Available)</strong> di bawah ini untuk menangani order ini.</p>
                    </div>
                <?php endif; ?>

                <?php if($order['status'] !== 'COMPLETED' && $order['status'] !== 'CANCELLED'): ?>
                <form action="<?= BASE_URL ?>/admin/orders/assign" method="POST" id="assignForm" onsubmit="return confirmShowAssignment()">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <input type="hidden" name="redirect" value="/admin/orders/detail?id=<?= $order['id'] ?>">

                    <div class="mb-3">
                        <label for="technician_id" class="form-label fw-bold">
                            <?= !empty($order['technician_name']) ? 'Ubah / Alihkan ke Teknisi Lain:' : 'Tugaskan Teknisi:' ?>
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-lg shadow-sm" name="technician_id" id="technician_id" required>
                            <option value="">-- Pilih Teknisi yang Tersedia --</option>
                            <?php if(!empty($availableTechnicians)): ?>
                                <?php foreach($availableTechnicians as $tech): ?>
                                    <option value="<?= $tech['id'] ?>" data-name="<?= htmlspecialchars($tech['name']) ?>" <?= ($order['technician_id'] == $tech['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tech['name']) ?> - <?= htmlspecialchars($tech['specialization'] ?? 'Elektronik') ?> (★ <?= number_format($tech['rating'] ?? 5.0, 1) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>Tidak ada teknisi berstatus Tersedia saat ini</option>
                            <?php endif; ?>
                        </select>
                        <div class="form-text small">Hanya teknisi dengan status ketersediaan aktif yang ditampilkan.</div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-bold" <?= empty($availableTechnicians) ? 'disabled' : '' ?>>
                            <i class="bi bi-check2-circle me-1"></i>
                            <?= !empty($order['technician_name']) ? 'Update Penugasan Teknisi' : 'Tugaskan Teknisi Sekarang' ?>
                        </button>
                    </div>
                </form>
                <?php else: ?>
                <div class="text-muted small">
                    <i class="bi bi-lock me-1"></i>Pesanan telah selesai/dibatalkan sehingga penugasan teknisi tidak dapat diubah.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cost & Report Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="bi bi-cash-coin me-2"></i>Biaya & Laporan Servis
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="text-muted">Estimasi Biaya:</span>
                    <span class="fw-semibold fs-5">
                        <?= !empty($order['estimated_cost']) ? 'Rp ' . number_format($order['estimated_cost'], 0, ',', '.') : '<span class="text-muted fs-6">Belum Ditentukan</span>' ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="text-muted fw-bold">Biaya Akhir (Final):</span>
                    <span class="fw-bold fs-4 text-success">
                        <?= !empty($order['final_cost']) ? 'Rp ' . number_format($order['final_cost'], 0, ',', '.') : '<span class="text-muted fs-6">Belum Selesai</span>' ?>
                    </span>
                </div>

                <?php if(!empty($order['payment_id'])): ?>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="text-muted fw-bold">Status Pembayaran:</span>
                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1 fw-bold fs-6">
                        <i class="bi bi-check-circle-fill me-1"></i>Lunas (<?= ($order['payment_method'] === 'CASH') ? 'Tunai' : 'Transfer ' . htmlspecialchars($order['payment_provider']) ?>)
                    </span>
                </div>
                <?php if(!empty($order['payment_proof_image'])): ?>
                <div class="mb-3 text-end">
                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($order['payment_proof_image']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-image me-1"></i>Lihat Bukti Transfer
                    </a>
                </div>
                <?php endif; ?>
                <?php elseif(!empty($order['final_cost'])): ?>
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="text-muted fw-bold">Status Pembayaran:</span>
                    <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1 fw-bold fs-6">
                        <i class="bi bi-hourglass-split me-1"></i>Menunggu Pembayaran
                    </span>
                </div>
                <?php endif; ?>

                <?php if(!empty($order['inspection_result'])): ?>
                <div class="mt-3 p-3 bg-light rounded">
                    <h6 class="fw-bold text-success mb-2"><i class="bi bi-clipboard-check me-1"></i>Laporan Teknisi:</h6>
                    <div class="small mb-2"><strong>Hasil Pemeriksaan:</strong> <?= nl2br(htmlspecialchars($order['inspection_result'])) ?></div>
                    <?php if(!empty($order['repair_action'])): ?>
                        <div class="small mb-2"><strong>Tindakan Perbaikan:</strong> <?= nl2br(htmlspecialchars($order['repair_action'])) ?></div>
                    <?php endif; ?>
                    <?php if(!empty($order['report_notes'])): ?>
                        <div class="small text-muted"><strong>Catatan:</strong> <?= nl2br(htmlspecialchars($order['report_notes'])) ?></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function confirmShowAssignment() {
    const select = document.getElementById('technician_id');
    const selectedOption = select.options[select.selectedIndex];
    if (!select.value) {
        alert('Silakan pilih teknisi terlebih dahulu.');
        return false;
    }
    const techName = selectedOption.getAttribute('data-name') || selectedOption.text;
    return confirm(`Konfirmasi Penugasan:\nApakah Anda yakin ingin menugaskan "${techName}" untuk Order #ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?>?\n\nStatus pesanan akan diubah menjadi ASSIGNED.`);
}
</script>
