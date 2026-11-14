<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/customer/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pembayaran & Tagihan</li>
            </ol>
        </nav>
        <h1 class="h2">
            <i class="bi bi-wallet2 text-success me-2"></i>Pembayaran & Tagihan Saya
        </h1>
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

<!-- Guidance Notice -->
<div class="card border-0 shadow-sm mb-4 border-start border-4 border-primary bg-white">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex align-items-start">
            <div class="p-2 bg-primary-subtle text-primary rounded-circle me-3 mt-1 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; flex-shrink: 0;">
                <i class="bi bi-shield-check fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-1">Pembayaran Bebas & Mudah oleh Customer</h5>
                <p class="text-muted small mb-0">
                    Pembayaran dilakukan setelah teknisi menyelesaikan perbaikan dan menerbitkan rincian tagihan akhir. 
                    <strong>Pilihan metode pembayaran sepenuhnya terserah Anda:</strong> Anda bebas memilih membayar secara 
                    <span class="badge bg-secondary">Tunai (Cash)</span> langsung ke teknisi di lokasi, atau melalui 
                    <span class="badge bg-primary">Transfer M-Banking / E-Wallet</span> (BRIMO, BCA, Mandiri, ShopeePay, DANA, GoPay, atau bank lainnya).
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-pills mb-4" id="paymentTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold position-relative" id="unpaid-tab" data-bs-toggle="pill" data-bs-target="#unpaid" type="button" role="tab">
            <i class="bi bi-hourglass-split me-1 text-warning"></i> Menunggu Pembayaran
            <?php if(!empty($unpaidOrders)): ?>
                <span class="badge bg-danger ms-1"><?= count($unpaidOrders) ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold" id="paid-tab" data-bs-toggle="pill" data-bs-target="#paid" type="button" role="tab">
            <i class="bi bi-check-circle-fill me-1 text-success"></i> Riwayat Pembayaran Lunas (<?= count($paidOrders) ?>)
        </button>
    </li>
</ul>

<div class="tab-content" id="paymentTabContent">
    
    <!-- Tab 1: Menunggu Pembayaran -->
    <div class="tab-pane fade show active" id="unpaid" role="tabpanel" aria-labelledby="unpaid-tab">
        <?php if(!empty($unpaidOrders)): ?>
            <div class="row g-4">
                <?php foreach($unpaidOrders as $order): ?>
                <div class="col-md-6 col-lg-6">
                    <div class="card shadow-sm border-0 h-100 border-top border-4 border-warning">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-warning text-dark px-2 py-1">
                                    <i class="bi bi-clock-history me-1"></i>Belum Dibayar
                                </span>
                            </div>
                            <span class="fw-bold text-primary">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($order['equipment_name']) ?></h5>
                            <div class="small text-muted mb-3">
                                <?= htmlspecialchars($order['equipment_brand'] ?? '-') ?> &bull; <?= htmlspecialchars($order['equipment_category'] ?? 'Umum') ?>
                            </div>

                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Total Tagihan:</span>
                                    <span class="fs-4 fw-bold text-success">
                                        Rp <?= number_format($order['final_cost'] ?? $order['report_cost'] ?? 0, 0, ',', '.') ?>
                                    </span>
                                </div>
                            </div>

                            <div class="small mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Teknisi:</span>
                                    <span class="fw-semibold"><?= htmlspecialchars($order['technician_name'] ?? 'Teknisi HomeCare') ?></span>
                                </div>
                                <?php if(!empty($order['inspection_result'])): ?>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Hasil Servis:</span>
                                    <span class="text-end text-truncate" style="max-width: 200px;"><?= htmlspecialchars($order['inspection_result']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="p-2 bg-info-subtle border border-info-subtle rounded small mb-3 text-secondary">
                                <i class="bi bi-info-circle me-1"></i>Pilihan cara bayar: <strong>Tunai (Cash)</strong> atau <strong>Transfer (BRIMO, BCA, Mandiri, ShopeePay, DANA, GoPay, Lainnya)</strong>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="<?= BASE_URL ?>/customer/order/detail?id=<?= $order['id'] ?>#paymentCard" class="btn btn-success fw-bold py-2 shadow-sm">
                                    <i class="bi bi-credit-card me-1"></i> Pilih Cara Bayar & Lunasi
                                </a>
                                <a href="<?= BASE_URL ?>/customer/order/detail?id=<?= $order['id'] ?>" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-eye me-1"></i> Lihat Rincian Servis
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="card shadow-sm border-0 text-center py-5">
                <div class="card-body">
                    <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">Tidak Ada Tagihan yang Tertunda!</h5>
                    <p class="text-muted mb-3">Semua pesanan servis Anda yang telah selesai sudah lunas terbayar.</p>
                    <a href="<?= BASE_URL ?>/customer/order" class="btn btn-outline-primary">
                        <i class="bi bi-list-task me-1"></i> Lihat Pesanan Saya
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tab 2: Riwayat Pembayaran Lunas -->
    <div class="tab-pane fade" id="paid" role="tabpanel" aria-labelledby="paid-tab">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">No. Order</th>
                                <th>Peralatan</th>
                                <th>Teknisi</th>
                                <th>Tanggal Bayar</th>
                                <th>Metode Pembayaran</th>
                                <th>Total Dibayar</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Kuitansi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($paidOrders)): ?>
                                <?php foreach($paidOrders as $p): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-primary">#ORD-<?= str_pad($p['order_id'], 4, '0', STR_PAD_LEFT) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['equipment_name']) ?></strong>
                                        <div class="small text-muted"><?= htmlspecialchars($p['equipment_brand'] ?? '-') ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($p['technician_name'] ?? '-') ?></td>
                                    <td>
                                        <div><i class="bi bi-calendar-check me-1 text-muted"></i><?= date('d M Y', strtotime($p['paid_at'])) ?></div>
                                        <div class="small text-muted"><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($p['paid_at'])) ?> WIB</div>
                                    </td>
                                    <td>
                                        <?php if($p['payment_method'] === 'CASH'): ?>
                                            <span class="badge bg-secondary px-2 py-1">
                                                <i class="bi bi-cash-stack me-1"></i>Tunai (Cash)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary px-2 py-1">
                                                <i class="bi bi-phone-fill me-1"></i>Transfer (<?= htmlspecialchars($p['payment_provider'] ?? 'M-Banking') ?>)
                                            </span>
                                        <?php endif; ?>
                                        <?php if(!empty($p['sender_name'])): ?>
                                            <div class="small text-muted mt-1">Oleh: <?= htmlspecialchars($p['sender_name']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong class="text-success fs-6">Rp <?= number_format($p['amount'], 0, ',', '.') ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                            <i class="bi bi-check-circle-fill me-1"></i>LUNAS
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/customer/payment/receipt?order_id=<?= $p['order_id'] ?>" target="_blank" class="btn btn-outline-success" title="Cetak Kuitansi">
                                                <i class="bi bi-printer me-1"></i>Kuitansi
                                            </a>
                                            <a href="<?= BASE_URL ?>/customer/order/detail?id=<?= $p['order_id'] ?>" class="btn btn-outline-primary" title="Detail Pesanan">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-receipt fs-1 d-block mb-3 text-secondary"></i>
                                        <h6 class="fw-bold">Belum Ada Riwayat Pembayaran</h6>
                                        <p class="mb-0">Pesanan yang telah Anda bayar akan tercatat rapi di sini.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
