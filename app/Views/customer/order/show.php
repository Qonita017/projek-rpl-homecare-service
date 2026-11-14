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
        case 'ACCEPTED': return 'Pesanan Diterima oleh Teknisi';
        case 'ON_THE_WAY': return 'Teknisi Sedang Menuju Lokasi';
        case 'IN_PROGRESS': return 'Servis Sedang Dikerjakan';
        case 'COMPLETED': return 'Servis Telah Selesai';
        case 'CANCELLED': return 'Pesanan Dibatalkan';
        default: return $status;
    }
};

// Determine step index for progress tracker
$currentStep = 1;
switch($order['status']) {
    case 'PENDING': $currentStep = 1; break;
    case 'ASSIGNED': 
    case 'ACCEPTED': $currentStep = 2; break;
    case 'ON_THE_WAY': $currentStep = 3; break;
    case 'IN_PROGRESS': $currentStep = 4; break;
    case 'COMPLETED': $currentStep = 5; break;
    case 'CANCELLED': $currentStep = -1; break;
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/customer/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/customer/order">Pesanan Saya</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail #ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></li>
            </ol>
        </nav>
        <h1 class="h2">
            <i class="bi bi-file-earmark-text text-primary me-2"></i>Detail Pesanan Servis
            <span class="text-muted fs-4">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></span>
        </h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Bukti
        </button>
        <a href="<?= BASE_URL ?>/customer/order" class="btn btn-outline-primary">
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

<!-- Order Status Banner -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center mb-3">
            <div class="col-md-7">
                <span class="text-muted small text-uppercase fw-bold">Status Pesanan Saat Ini:</span>
                <div class="mt-1">
                    <span class="badge <?= $badgeClass($order['status']) ?> fs-6 px-3 py-2">
                        <i class="bi bi-info-circle me-1"></i> <?= $statusLabel($order['status']) ?>
                    </span>
                </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <span class="text-muted small d-block">Tanggal Pemesanan:</span>
                <span class="fw-bold text-dark"><i class="bi bi-clock-history me-1"></i><?= date('d M Y, H:i', strtotime($order['created_at'])) ?> WIB</span>
            </div>
        </div>

        <?php if($order['status'] !== 'CANCELLED'): ?>
        <!-- Progress Steps -->
        <div class="mt-4 pt-3 border-top">
            <div class="row text-center position-relative">
                <div class="col">
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center text-white <?= ($currentStep >= 1) ? 'bg-primary' : 'bg-light text-muted border' ?>" style="width: 40px; height: 40px;">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <span class="small fw-semibold <?= ($currentStep >= 1) ? 'text-primary' : 'text-muted' ?>">Pending</span>
                </div>
                <div class="col">
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center text-white <?= ($currentStep >= 2) ? 'bg-primary' : 'bg-light text-muted border' ?>" style="width: 40px; height: 40px;">
                        <i class="bi bi-person-gear"></i>
                    </div>
                    <span class="small fw-semibold <?= ($currentStep >= 2) ? 'text-primary' : 'text-muted' ?>">Ditugaskan</span>
                </div>
                <div class="col">
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center text-white <?= ($currentStep >= 3) ? 'bg-primary' : 'bg-light text-muted border' ?>" style="width: 40px; height: 40px;">
                        <i class="bi bi-truck"></i>
                    </div>
                    <span class="small fw-semibold <?= ($currentStep >= 3) ? 'text-primary' : 'text-muted' ?>">Menuju Lokasi</span>
                </div>
                <div class="col">
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center text-white <?= ($currentStep >= 4) ? 'bg-primary' : 'bg-light text-muted border' ?>" style="width: 40px; height: 40px;">
                        <i class="bi bi-tools"></i>
                    </div>
                    <span class="small fw-semibold <?= ($currentStep >= 4) ? 'text-primary' : 'text-muted' ?>">Dikerjakan</span>
                </div>
                <div class="col">
                    <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center text-white <?= ($currentStep >= 5) ? 'bg-success' : 'bg-light text-muted border' ?>" style="width: 40px; height: 40px;">
                        <i class="bi bi-trophy"></i>
                    </div>
                    <span class="small fw-semibold <?= ($currentStep >= 5) ? 'text-success' : 'text-muted' ?>">Selesai</span>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-danger mb-0 mt-3">
            <i class="bi bi-x-circle-fill me-2"></i> Pesanan ini telah dibatalkan.
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- Left Column: Equipment & Complaint Details -->
    <div class="col-lg-7 mb-4">
        <!-- Equipment Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="bi bi-cpu me-2"></i>Informasi Peralatan & Kerusakan
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Nomor Order</div>
                    <div class="col-sm-8 fw-bold text-dark">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Nama Peralatan</div>
                    <div class="col-sm-8 fw-semibold"><?= htmlspecialchars($order['equipment_name']) ?></div>
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
                    <div class="col-sm-4 text-muted">Keluhan / Masalah</div>
                    <div class="col-sm-8 text-break bg-light p-3 rounded">
                        <?= nl2br(htmlspecialchars($order['complaint'])) ?>
                    </div>
                </div>

                <?php if(!empty($order['photo'])): ?>
                <div class="row mt-3">
                    <div class="col-sm-4 text-muted">Foto Kerusakan</div>
                    <div class="col-sm-8">
                        <div class="p-2 border rounded bg-light d-inline-block">
                            <a href="<?= BASE_URL ?>/<?= htmlspecialchars($order['photo']) ?>" target="_blank" title="Klik untuk memperbesar">
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($order['photo']) ?>" alt="Foto Kerusakan" class="img-fluid rounded" style="max-height: 180px;">
                            </a>
                            <div class="small text-muted mt-1 text-center"><i class="bi bi-zoom-in me-1"></i>Klik foto untuk memperbesar</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Service Location & Schedule -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="bi bi-geo-alt me-2"></i>Jadwal & Lokasi Servis
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Jadwal Kunjungan</div>
                    <div class="col-sm-8 fw-semibold text-primary">
                        <i class="bi bi-calendar-event me-1"></i><?= date('l, d F Y', strtotime($order['service_date'])) ?>
                        <span class="badge bg-secondary ms-2"><?= date('H:i', strtotime($order['service_date'])) ?> WIB</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 text-muted">Alamat Lengkap</div>
                    <div class="col-sm-8"><?= nl2br(htmlspecialchars($order['address'])) ?></div>
                </div>
                <div class="row">
                    <div class="col-sm-4 text-muted">Nomor Kontak</div>
                    <div class="col-sm-8">
                        <a href="tel:<?= htmlspecialchars($order['customer_phone'] ?? '') ?>" class="text-decoration-none">
                            <i class="bi bi-telephone-fill me-1"></i><?= htmlspecialchars($order['customer_phone'] ?? '-') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Technician & Cost Information -->
    <div class="col-lg-5 mb-4">
        <!-- Technician Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="bi bi-person-badge me-2"></i>Teknisi yang Menangani
                </h5>
            </div>
            <div class="card-body p-4">
                <?php if(!empty($order['technician_name'])): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 54px; height: 54px; font-size: 1.5rem;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($order['technician_name']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($order['technician_specialization'] ?? 'Spesialis Servis Elektronik') ?></small>
                            <div class="mt-1">
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-star-fill me-1"></i><?= number_format($order['technician_rating'] ?? 5.0, 1) ?>
                                </span>
                                <?php if(!empty($order['technician_experience'])): ?>
                                    <span class="small text-muted ms-2"><?= $order['technician_experience'] ?> thn pengalaman</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if(!empty($order['technician_phone'])): ?>
                    <div class="d-grid mt-3">
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $order['technician_phone']) ?>" target="_blank" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-whatsapp me-1"></i> Hubungi Teknisi
                        </a>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-3 text-muted">
                        <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary"></i>
                        <h6 class="fw-bold mb-1">Belum Ada Teknisi Ditugaskan</h6>
                        <small>Admin sedang mencarikan teknisi terbaik yang sesuai dengan jenis peralatan Anda.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cost Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="bi bi-cash-coin me-2"></i>Rincian Biaya
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="text-muted">Estimasi Biaya:</span>
                    <span class="fw-semibold fs-5">
                        <?php if(!empty($order['estimated_cost'])): ?>
                            Rp <?= number_format($order['estimated_cost'], 0, ',', '.') ?>
                        <?php else: ?>
                            <span class="text-muted fs-6 fst-italic">Menunggu Diagnosa</span>
                        <?php endif; ?>
                    </span>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark">Biaya Akhir (Final):</span>
                    <span class="fw-bold fs-4 text-success">
                        <?php if(!empty($order['final_cost'])): ?>
                            Rp <?= number_format($order['final_cost'], 0, ',', '.') ?>
                        <?php else: ?>
                            <span class="text-muted fs-6 fst-italic">Belum Selesai</span>
                        <?php endif; ?>
                    </span>
                </div>
                <small class="text-muted d-block mt-2">
                    <i class="bi bi-info-circle me-1"></i>Biaya akhir mencakup jasa servis teknisi dan penggantian komponen (jika ada).
                </small>

                <?php if(!empty($order['payment_id'])): ?>
                <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Status Pembayaran:</span>
                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1 fw-bold">
                        <i class="bi bi-check-circle-fill me-1"></i>Lunas (<?= ($order['payment_method'] === 'CASH') ? 'Tunai' : htmlspecialchars($order['payment_provider']) ?>)
                    </span>
                </div>
                <?php elseif($order['status'] === 'COMPLETED'): ?>
                <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Status Pembayaran:</span>
                    <span class="badge bg-warning-subtle text-warning border border-warning px-2 py-1 fw-bold">
                        <i class="bi bi-hourglass-split me-1"></i>Menunggu Pembayaran
                    </span>
                </div>
                <?php else: ?>
                <div class="mt-3 pt-2 border-top">
                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Pembayaran (Tunai atau Transfer M-Banking) dapat dilakukan setelah servis selesai.</small>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if(!empty($order['inspection_result'])): ?>
        <!-- Inspection / Service Report Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-success">
                    <i class="bi bi-check2-circle me-2"></i>Laporan Hasil Perbaikan
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <span class="text-muted small d-block">Hasil Pemeriksaan:</span>
                    <strong><?= nl2br(htmlspecialchars($order['inspection_result'])) ?></strong>
                </div>
                <?php if(!empty($order['repair_action'])): ?>
                <div class="mb-3">
                    <span class="text-muted small d-block">Tindakan Perbaikan:</span>
                    <span><?= nl2br(htmlspecialchars($order['repair_action'])) ?></span>
                </div>
                <?php endif; ?>
                <?php if(!empty($order['report_notes'])): ?>
                <div>
                    <span class="text-muted small d-block">Catatan Tambahan:</span>
                    <span class="text-muted"><?= nl2br(htmlspecialchars($order['report_notes'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- PAYMENT SECTION (Hanya untuk pesanan COMPLETED) -->
        <?php if($order['status'] === 'COMPLETED'): ?>
            <?php if(!empty($order['payment_id'])): ?>
            <!-- State 1: Pembayaran Sudah Lunas -->
            <div class="card shadow-sm border-0 mb-4 border-top border-4 border-success" id="paymentCard">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-success">
                        <i class="bi bi-patch-check-fill me-2"></i>Status Pembayaran: LUNAS
                    </h5>
                    <span class="badge bg-success px-3 py-2 fs-6">
                        <i class="bi bi-check-circle-fill me-1"></i>PAID
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="p-3 bg-success-subtle rounded-3 mb-3 border border-success-subtle">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small text-uppercase fw-bold d-block">Total Telah Dibayar</span>
                                <span class="fs-4 fw-bold text-success">Rp <?= number_format($order['payment_amount'] ?? $order['final_cost'], 0, ',', '.') ?></span>
                            </div>
                            <div class="text-end">
                                <span class="badge <?= ($order['payment_method'] === 'CASH') ? 'bg-secondary' : 'bg-primary' ?> fs-6 px-3 py-2">
                                    <?= ($order['payment_method'] === 'CASH') ? '💵 Tunai (Cash)' : '📱 ' . htmlspecialchars($order['payment_provider']) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3 small">
                        <div class="col-sm-5 text-muted">Metode Pembayaran</div>
                        <div class="col-sm-7 fw-semibold text-dark">
                            <?php if($order['payment_method'] === 'CASH'): ?>
                                <i class="bi bi-cash-stack text-success me-1"></i>Tunai (Cash langsung ke Teknisi)
                            <?php else: ?>
                                <i class="bi bi-phone-fill text-primary me-1"></i>Transfer M-Banking (<?= htmlspecialchars($order['payment_provider']) ?>)
                            <?php endif; ?>
                        </div>

                        <?php if(!empty($order['payment_sender_name']) || !empty($order['payment_sender_account'])): ?>
                        <div class="col-sm-5 text-muted">Data Pengirim</div>
                        <div class="col-sm-7 text-dark">
                            <?= htmlspecialchars($order['payment_sender_name'] ?? '-') ?>
                            <?= !empty($order['payment_sender_account']) ? ' (' . htmlspecialchars($order['payment_sender_account']) . ')' : '' ?>
                        </div>
                        <?php endif; ?>

                        <div class="col-sm-5 text-muted">Waktu Pembayaran</div>
                        <div class="col-sm-7 text-dark">
                            <i class="bi bi-clock me-1"></i><?= date('d M Y, H:i', strtotime($order['payment_paid_at'])) ?> WIB
                        </div>

                        <?php if(!empty($order['payment_notes'])): ?>
                        <div class="col-sm-5 text-muted">Catatan</div>
                        <div class="col-sm-7 text-dark"><?= htmlspecialchars($order['payment_notes']) ?></div>
                        <?php endif; ?>
                    </div>

                    <?php if(!empty($order['payment_proof_image'])): ?>
                    <div class="mb-3 pt-2 border-top">
                        <span class="text-muted small d-block mb-1">Bukti Transfer:</span>
                        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($order['payment_proof_image']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-image me-1"></i>Lihat Bukti Foto Transfer
                        </a>
                    </div>
                    <?php endif; ?>

                    <div class="d-grid mt-3">
                        <a href="<?= BASE_URL ?>/customer/payment/receipt?order_id=<?= $order['id'] ?>" target="_blank" class="btn btn-outline-success">
                            <i class="bi bi-printer me-1"></i> Cetak / Simpan Kuitansi Resmi
                        </a>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <!-- State 2: Belum Bayar - Formulir Pembayaran -->
            <div class="card shadow-sm border-0 mb-4 border-top border-4 border-primary" id="paymentCard">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="bi bi-wallet2 me-2"></i>Pembayaran Servis
                    </h5>
                    <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                        <i class="bi bi-hourglass-split me-1"></i>Menunggu Pembayaran
                    </span>
                </div>
                <div class="card-body p-4">
                    <!-- Tagihan Amount Box -->
                    <div class="p-3 bg-light rounded-3 mb-4 border text-center">
                        <span class="text-muted small text-uppercase fw-bold d-block mb-1">Total Tagihan Servis</span>
                        <div class="fs-3 fw-bold text-success" id="payableAmountDisplay">
                            Rp <?= number_format($order['final_cost'] ?? $order['estimated_cost'] ?? 0, 0, ',', '.') ?>
                        </div>
                        <small class="text-muted">Biaya akhir perbaikan oleh teknisi</small>
                    </div>

                    <!-- Payment Form -->
                    <form action="<?= BASE_URL ?>/customer/payment/store" method="POST" enctype="multipart/form-data" id="paymentForm">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                        <!-- Notice to Customer -->
                        <div class="alert alert-primary-subtle border border-primary-subtle mb-3 p-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle-fill text-primary fs-4 me-2"></i>
                                <div class="small">
                                    <strong>Pembayaran dilakukan oleh Anda (Customer):</strong> Pilihan metode pembayaran sepenuhnya terserah Anda. Anda bebas memilih membayar secara <strong>Tunai (Cash)</strong> langsung ke teknisi atau menggunakan <strong>Transfer M-Banking / E-Wallet</strong>.
                                </div>
                            </div>
                        </div>

                        <label class="form-label fw-bold mb-2">Pilih Cara Pembayaran yang Anda Inginkan <span class="text-danger">*</span></label>
                        
                        <div class="row g-2 mb-3">
                            <!-- Cash Option -->
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="payment_method" id="payMethodCash" value="CASH" onchange="switchPaymentMethod('CASH')" checked>
                                <label class="btn btn-outline-success w-100 p-3 h-100 text-start rounded-3 border-2" for="payMethodCash">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-cash-stack fs-2 me-2 text-success"></i>
                                        <div>
                                            <div class="fw-bold">1. Tunai (Cash)</div>
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Bayar di tempat ke teknisi</small>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <!-- Transfer M-Banking Option -->
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="payment_method" id="payMethodTransfer" value="TRANSFER" onchange="switchPaymentMethod('TRANSFER')">
                                <label class="btn btn-outline-primary w-100 p-3 h-100 text-start rounded-3 border-2" for="payMethodTransfer">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-phone-fill fs-2 me-2 text-primary"></i>
                                        <div>
                                            <div class="fw-bold">2. M-Banking</div>
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Transfer / E-Wallet</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Cash Notice Block -->
                        <div id="cashNoticeBlock" class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle-fill text-success fs-5 me-2 mt-1"></i>
                                <div>
                                    <strong class="text-dark d-block mb-1">Pembayaran Tunai (Cash di Lokasi)</strong>
                                    <p class="small text-muted mb-0">
                                        Silakan serahkan uang tunai pas sebesar <strong>Rp <?= number_format($order['final_cost'] ?? $order['estimated_cost'] ?? 0, 0, ',', '.') ?></strong> langsung kepada teknisi <strong><?= htmlspecialchars($order['technician_name'] ?? 'Teknisi') ?></strong>. Setelah diserahkan, klik tombol konfirmasi di bawah untuk mencatat pelunasan servis.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Transfer Block -->
                        <div id="transferBlock" class="d-none">
                            <label class="form-label fw-bold mb-2">Pilih Layanan M-Banking / E-Wallet yang Digunakan <span class="text-danger">*</span></label>
                            
                            <div class="row g-2 mb-3">
                                <?php 
                                $providers = [
                                    ['id' => 'BRIMO', 'name' => 'BRIMO', 'sub' => 'Bank BRI', 'color' => '#00529C', 'icon' => 'bi-bank'],
                                    ['id' => 'BCA', 'name' => 'BCA', 'sub' => 'BCA Mobile', 'color' => '#003399', 'icon' => 'bi-credit-card-2-front'],
                                    ['id' => 'MANDIRI', 'name' => 'MANDIRI', 'sub' => "Livin' Mandiri", 'color' => '#002D62', 'icon' => 'bi-bank2'],
                                    ['id' => 'SHOPEEPAY', 'name' => 'SHOPEEPAY', 'sub' => 'ShopeePay', 'color' => '#EE4D2D', 'icon' => 'bi-bag-check-fill'],
                                    ['id' => 'DANA', 'name' => 'DANA', 'sub' => 'Dompet DANA', 'color' => '#118EEA', 'icon' => 'bi-wallet2'],
                                    ['id' => 'GOPAY', 'name' => 'GOPAY', 'sub' => 'GoPay', 'color' => '#00AA13', 'icon' => 'bi-send-check-fill'],
                                    ['id' => 'OTHER', 'name' => 'Lainnya', 'sub' => 'Bank / E-Wallet Lain', 'color' => '#6f42c1', 'icon' => 'bi-grid-fill'],
                                ];
                                foreach($providers as $p):
                                ?>
                                <div class="col-4 col-sm-4">
                                    <input type="radio" class="btn-check" name="payment_provider" id="prov_<?= $p['id'] ?>" value="<?= $p['id'] ?>" onchange="selectProvider('<?= $p['id'] ?>')">
                                    <label class="btn btn-outline-secondary w-100 p-2 text-center rounded-3 provider-choice-btn h-100" for="prov_<?= $p['id'] ?>" style="border-width: 1.5px;">
                                        <i class="bi <?= $p['icon'] ?> d-block fs-4 mb-1" style="color: <?= $p['color'] ?>;"></i>
                                        <span class="fw-bold d-block small" style="font-size: 0.8rem;"><?= $p['name'] ?></span>
                                        <span class="text-muted d-block" style="font-size: 0.65rem;"><?= $p['sub'] ?></span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Custom Provider Input (shown if OTHER selected) -->
                            <div id="customProviderInput" class="mb-3 d-none">
                                <label for="custom_provider" class="form-label small fw-bold">Ketik Nama Bank atau E-Wallet Anda <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="custom_provider" name="custom_provider" placeholder="Contoh: BNI, BSI, OVO, LinkAja, CIMB Niaga">
                            </div>

                            <!-- Bank / E-Wallet Instruction Box -->
                            <div id="providerDetailBox" class="p-3 rounded-3 mb-3 border bg-white shadow-sm d-none">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <span class="fw-bold text-dark" id="boxProviderName">Rekening Tujuan</span>
                                    <span class="badge bg-primary" id="boxProviderBadge">M-Banking</span>
                                </div>
                                <div class="mb-2">
                                    <span class="text-muted small d-block">Nomor Rekening / No. HP Tujuan:</span>
                                    <div class="input-group input-group-sm mt-1">
                                        <input type="text" class="form-control fw-bold font-monospace bg-light" id="boxAccountNumber" readonly>
                                        <button class="btn btn-outline-primary" type="button" id="copyAccBtn" onclick="copyText('boxAccountNumber', 'copyAccBtn')">
                                            <i class="bi bi-clipboard me-1"></i>Salin
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <span class="text-muted small d-block">Atas Nama Penerima:</span>
                                    <strong class="text-dark" id="boxAccountName">PT HomeCare Solusi Indonesia</strong>
                                </div>
                                <div>
                                    <span class="text-muted small d-block">Nominal Pas yang Harus Ditransfer:</span>
                                    <div class="input-group input-group-sm mt-1">
                                        <input type="text" class="form-control fw-bold text-success font-monospace bg-light" id="boxTransferAmount" readonly value="Rp <?= number_format($order['final_cost'] ?? $order['estimated_cost'] ?? 0, 0, ',', '.') ?>">
                                        <button class="btn btn-outline-success" type="button" id="copyAmtBtn" onclick="copyValue('<?= (int)($order['final_cost'] ?? $order['estimated_cost'] ?? 0) ?>', 'copyAmtBtn')">
                                            <i class="bi bi-clipboard me-1"></i>Salin Nominal
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Sender info (optional) -->
                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <label for="sender_name" class="form-label small fw-semibold">Nama Pengirim / Pemilik Akun</label>
                                    <input type="text" class="form-control form-control-sm" id="sender_name" name="sender_name" placeholder="Nama di rekening/aplikasi">
                                </div>
                                <div class="col-sm-6">
                                    <label for="sender_account" class="form-label small fw-semibold">No. Rekening / No. HP Pengirim</label>
                                    <input type="text" class="form-control form-control-sm" id="sender_account" name="sender_account" placeholder="Nomor rekening pengirim">
                                </div>
                            </div>

                            <!-- Proof Upload -->
                            <div class="mb-3">
                                <label for="proof_image" class="form-label small fw-semibold">
                                    <i class="bi bi-upload me-1"></i>Unggah Bukti Transfer <span class="badge bg-light text-muted border">Disarankan</span>
                                </label>
                                <input type="file" class="form-control form-control-sm" id="proof_image" name="proof_image" accept="image/*,application/pdf">
                                <div class="form-text" style="font-size: 0.75rem;">Mendukung: JPG, PNG, WEBP, PDF (Maksimal 5MB)</div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="payment_notes" class="form-label small fw-semibold">Catatan Pembayaran <span class="badge bg-light text-muted border">Opsional</span></label>
                            <input type="text" class="form-control form-control-sm" id="payment_notes" name="notes" placeholder="Catatan untuk teknisi atau admin...">
                        </div>

                        <!-- Submit -->
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-success btn-lg shadow fw-bold py-2" id="submitPaymentBtn">
                                <i class="bi bi-shield-lock-fill me-2"></i>Konfirmasi & Simpan Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
            const providerDetails = {
                'BRIMO': {
                    name: 'Bank BRI (BRIMO)',
                    badge: 'M-Banking BRI',
                    account: '0341-01-002345-50-8',
                    holder: 'PT HomeCare Solusi Indonesia'
                },
                'BCA': {
                    name: 'Bank Central Asia (BCA)',
                    badge: 'BCA Mobile / myBCA',
                    account: '8820-9182-34',
                    holder: 'PT HomeCare Solusi Indonesia'
                },
                'MANDIRI': {
                    name: 'Bank Mandiri (Livin\')',
                    badge: 'Livin\' by Mandiri',
                    account: '137-00-1928374-1',
                    holder: 'PT HomeCare Solusi Indonesia'
                },
                'SHOPEEPAY': {
                    name: 'ShopeePay',
                    badge: 'E-Wallet ShopeePay',
                    account: '0812-9876-5432',
                    holder: 'HomeCare Service Official'
                },
                'DANA': {
                    name: 'Dompet Digital DANA',
                    badge: 'E-Wallet DANA',
                    account: '0812-9876-5432',
                    holder: 'HomeCare Service Official'
                },
                'GOPAY': {
                    name: 'GoPay',
                    badge: 'E-Wallet GoPay',
                    account: '0812-9876-5432',
                    holder: 'HomeCare Service Official'
                },
                'OTHER': {
                    name: 'Bank / E-Wallet Lainnya',
                    badge: 'Transfer Antar Bank',
                    account: '8820-9182-34 (BCA) / 0341-01-002345-50-8 (BRI)',
                    holder: 'PT HomeCare Solusi Indonesia'
                }
            };

            function switchPaymentMethod(method) {
                const cashNotice = document.getElementById('cashNoticeBlock');
                const transferBlock = document.getElementById('transferBlock');
                const customProvider = document.getElementById('custom_provider');

                if (method === 'CASH') {
                    cashNotice.classList.remove('d-none');
                    transferBlock.classList.add('d-none');
                    if (customProvider) customProvider.removeAttribute('required');
                } else {
                    cashNotice.classList.add('d-none');
                    transferBlock.classList.remove('d-none');
                }
            }

            function selectProvider(provId) {
                const detailBox = document.getElementById('providerDetailBox');
                const customInput = document.getElementById('customProviderInput');
                const customField = document.getElementById('custom_provider');

                const data = providerDetails[provId];
                if (data) {
                    document.getElementById('boxProviderName').textContent = data.name;
                    document.getElementById('boxProviderBadge').textContent = data.badge;
                    document.getElementById('boxAccountNumber').value = data.account;
                    document.getElementById('boxAccountName').textContent = data.holder;
                    detailBox.classList.remove('d-none');
                }

                if (provId === 'OTHER') {
                    customInput.classList.remove('d-none');
                    customField.setAttribute('required', 'required');
                    customField.focus();
                } else {
                    customInput.classList.add('d-none');
                    customField.removeAttribute('required');
                }
            }

            function copyText(elemId, btnId) {
                const input = document.getElementById(elemId);
                if (input && input.value) {
                    navigator.clipboard.writeText(input.value).then(() => {
                        const btn = document.getElementById(btnId);
                        const orig = btn.innerHTML;
                        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Disalin!';
                        btn.classList.replace('btn-outline-primary', 'btn-success');
                        setTimeout(() => {
                            btn.innerHTML = orig;
                            btn.classList.replace('btn-success', 'btn-outline-primary');
                        }, 2000);
                    });
                }
            }

            function copyValue(val, btnId) {
                navigator.clipboard.writeText(val).then(() => {
                    const btn = document.getElementById(btnId);
                    const orig = btn.innerHTML;
                    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Disalin!';
                    btn.classList.replace('btn-outline-success', 'btn-success');
                    setTimeout(() => {
                        btn.innerHTML = orig;
                        btn.classList.replace('btn-success', 'btn-outline-success');
                    }, 2000);
                });
            }
            </script>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Rating & Review Section (Only for COMPLETED orders) -->
        <?php if($order['status'] === 'COMPLETED'): ?>
            <?php if(!empty($order['review_rating'])): ?>
            <!-- Review Already Submitted Card -->
            <div class="card shadow-sm border-0 mb-4 border-top border-4 border-warning">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 fw-bold text-warning">
                        <i class="bi bi-star-fill me-2"></i>Ulasan & Penilaian Anda
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="text-warning fs-5 mb-2">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="bi <?= ($i <= $order['review_rating']) ? 'bi-star-fill' : 'bi-star' ?>"></i>
                        <?php endfor; ?>
                        <span class="fw-bold text-dark fs-6 ms-2">(<?= $order['review_rating'] ?>.0 / 5.0)</span>
                    </div>
                    <?php if(!empty($order['review_comment'])): ?>
                        <blockquote class="blockquote fs-6 text-muted bg-light p-3 rounded mb-2 fst-italic">
                            "<?= htmlspecialchars($order['review_comment']) ?>"
                        </blockquote>
                    <?php endif; ?>
                    <small class="text-muted d-block">
                        <i class="bi bi-clock me-1"></i>Diberikan pada: <?= date('d M Y, H:i', strtotime($order['review_created_at'])) ?> WIB
                    </small>
                </div>
            </div>
            <?php else: ?>
            <!-- Review Form Card -->
            <div class="card shadow-sm border-0 mb-4 border-top border-4 border-warning" id="reviewFormCard">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 fw-bold text-warning">
                        <i class="bi bi-star-fill me-2"></i>Beri Penilaian & Ulasan Teknisi
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="small text-muted mb-3">Bagikan pengalaman servis Anda bersama teknisi <strong><?= htmlspecialchars($order['technician_name'] ?? 'Teknisi') ?></strong>.</p>
                    
                    <form action="<?= BASE_URL ?>/customer/review/store" method="POST">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                        <!-- Interactive Star Rating -->
                        <div class="mb-3 text-center p-3 bg-light rounded">
                            <label class="form-label fw-bold d-block mb-2">Pilih Rating Kepuasan <span class="text-danger">*</span></label>
                            <div class="d-flex justify-content-center gap-3 fs-3 text-warning my-1">
                                <?php for($i = 5; $i >= 1; $i--): ?>
                                    <div class="form-check form-check-inline m-0 p-0 position-relative">
                                        <input class="btn-check" type="radio" name="rating" id="rating_<?= $i ?>" value="<?= $i ?>" required <?= ($i === 5) ? 'checked' : '' ?> onchange="updateRatingLabel(this.value)">
                                        <label class="btn btn-outline-warning rounded-circle p-2" for="rating_<?= $i ?>" style="width: 46px; height: 46px; display: inline-flex; align-items: center; justify-content: center;">
                                            <?= $i ?>★
                                        </label>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            <small class="fw-bold text-primary mt-2 d-block" id="ratingLabelText">5 Bintang - Sangat Puas</small>
                        </div>

                        <!-- Comment Textarea -->
                        <div class="mb-3">
                            <label for="review_comment" class="form-label fw-bold">Ulasan / Komentar <span class="badge bg-light text-muted border">Opsional</span></label>
                            <textarea class="form-control shadow-sm" id="review_comment" name="comment" rows="3" placeholder="Tuliskan ulasan mengenai kerapian kerja, keramahan, atau kecepatan teknisi..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning btn-lg shadow-sm fw-bold py-2 text-dark">
                                <i class="bi bi-send-fill me-1"></i>Kirim Ulasan & Rating
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
            function updateRatingLabel(val) {
                const labels = {
                    '5': '5 Bintang - Sangat Puas & Sangat Direkomendasikan',
                    '4': '4 Bintang - Puas dengan Hasil Servis',
                    '3': '3 Bintang - Cukup Baik',
                    '2': '2 Bintang - Kurang Memuaskan',
                    '1': '1 Bintang - Sangat Mengecewakan'
                };
                document.getElementById('ratingLabelText').textContent = labels[val] || (val + ' Bintang');
            }
            </script>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</div>
