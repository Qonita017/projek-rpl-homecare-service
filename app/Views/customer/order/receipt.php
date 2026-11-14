<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Kuitansi Pembayaran - HomeCare Service') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333;
        }
        .receipt-card {
            max-width: 720px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .receipt-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            padding: 30px;
        }
        .paid-stamp {
            border: 3px solid #198754;
            color: #198754;
            display: inline-block;
            padding: 6px 18px;
            text-transform: uppercase;
            font-weight: 900;
            letter-spacing: 2px;
            border-radius: 6px;
            transform: rotate(-4deg);
        }
        @media print {
            body { background: white; }
            .no-print { display: none !important; }
            .receipt-card { box-shadow: none; border: none; margin: 0; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="no-print text-center mb-4">
        <a href="<?= BASE_URL ?>/customer/order/detail?id=<?= $order['id'] ?>" class="btn btn-outline-secondary me-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail Pesanan
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
        </button>
    </div>

    <div class="receipt-card">
        <!-- Header -->
        <div class="receipt-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold mb-0"><i class="bi bi-tools me-2"></i>HomeCare Service</h3>
                    <small class="opacity-75">Solusi Cepat & Terpercaya Peralatan Rumah Tangga</small>
                </div>
                <div class="text-end">
                    <div class="fs-6 fw-bold">TANDA TERIMA RESMI</div>
                    <small class="opacity-75">No: #PAY-ORD<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?>-<?= str_pad($order['payment_id'] ?? 1, 3, '0', STR_PAD_LEFT) ?></small>
                </div>
            </div>
        </div>

        <div class="p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <span class="text-muted small d-block">Nomor Pesanan</span>
                    <strong class="fs-5 text-primary">#ORD-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?></strong>
                    <div class="small text-muted mt-1">Tanggal Bayar: <?= date('d M Y, H:i', strtotime($order['payment_paid_at'] ?? 'now')) ?> WIB</div>
                </div>
                <div>
                    <span class="paid-stamp">LUNAS / PAID</span>
                </div>
            </div>

            <!-- Info Customer & Teknisi -->
            <div class="row mb-4">
                <div class="col-6">
                    <h6 class="text-uppercase text-muted fw-bold small">Diterima Dari (Customer):</h6>
                    <div class="fw-bold"><?= htmlspecialchars($order['customer_name'] ?? '-') ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($order['customer_phone'] ?? '-') ?></div>
                    <div class="small text-muted"><?= nl2br(htmlspecialchars($order['address'] ?? '-')) ?></div>
                </div>
                <div class="col-6 text-end">
                    <h6 class="text-uppercase text-muted fw-bold small">Teknisi Penanggung Jawab:</h6>
                    <div class="fw-bold"><?= htmlspecialchars($order['technician_name'] ?? 'Teknisi HomeCare') ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($order['technician_specialization'] ?? 'Spesialis Servis') ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($order['technician_phone'] ?? '-') ?></div>
                </div>
            </div>

            <!-- Service Details Table -->
            <table class="table table-bordered mb-4">
                <thead class="table-light">
                    <tr>
                        <th>Deskripsi Layanan & Peralatan</th>
                        <th class="text-center" style="width: 140px;">Metode Bayar</th>
                        <th class="text-end" style="width: 160px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>Servis Peralatan: <?= htmlspecialchars($order['equipment_name']) ?></strong>
                            <div class="small text-muted">Merk: <?= htmlspecialchars($order['equipment_brand'] ?? '-') ?> | Kategori: <?= htmlspecialchars($order['equipment_category'] ?? 'Umum') ?></div>
                            <?php if(!empty($order['inspection_result'])): ?>
                                <div class="small text-secondary mt-1"><em>Perbaikan: <?= htmlspecialchars($order['repair_action'] ?? $order['inspection_result']) ?></em></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center align-middle">
                            <?php if(($order['payment_method'] ?? '') === 'CASH'): ?>
                                <span class="badge bg-secondary">Tunai (Cash)</span>
                            <?php else: ?>
                                <span class="badge bg-primary">Transfer (<?= htmlspecialchars($order['payment_provider'] ?? 'M-Banking') ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end align-middle fw-bold">
                            Rp <?= number_format($order['payment_amount'] ?? $order['final_cost'] ?? 0, 0, ',', '.') ?>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th colspan="2" class="text-end">Total Pembayaran:</th>
                        <th class="text-end text-success fs-5">Rp <?= number_format($order['payment_amount'] ?? $order['final_cost'] ?? 0, 0, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>

            <?php if(!empty($order['payment_sender_name']) || !empty($order['payment_sender_account'])): ?>
            <div class="p-3 bg-light rounded-3 mb-4 small">
                <div class="row">
                    <div class="col-sm-4 text-muted">Data Pengirim Transfer:</div>
                    <div class="col-sm-8 fw-semibold">
                        <?= htmlspecialchars($order['payment_sender_name'] ?? '-') ?> 
                        <?= !empty($order['payment_sender_account']) ? ' - Rek/No HP: ' . htmlspecialchars($order['payment_sender_account']) : '' ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="row mt-4 pt-3 border-top align-items-center">
                <div class="col-8">
                    <small class="text-muted d-block">Terima kasih telah mempercayakan perbaikan peralatan Anda kepada <strong>HomeCare Service</strong>.</small>
                    <small class="text-muted">Kuitansi ini adalah bukti pembayaran yang sah dan diterbitkan secara elektronik oleh sistem.</small>
                </div>
                <div class="col-4 text-end">
                    <small class="text-muted d-block">Status Kuitansi:</small>
                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fw-bold">
                        <i class="bi bi-shield-check me-1"></i>TERVERIFIKASI LUNAS
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
