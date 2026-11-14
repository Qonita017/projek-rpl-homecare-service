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

<!-- Chart.js CDN for lightweight charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Laporan & Statistik</li>
            </ol>
        </nav>
        <h1 class="h2"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Laporan Layanan & Kinerja</h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak / Export Laporan
        </button>
    </div>
</div>

<!-- Filter Section -->
<div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASE_URL ?>/admin/reports" class="row g-2 align-items-end">
            
            <div class="col-md-2">
                <label for="start_date" class="form-label small fw-bold mb-1">Tanggal Mulai</label>
                <input type="date" class="form-control form-control-sm" id="start_date" name="start_date" value="<?= htmlspecialchars($filters['start_date']) ?>">
            </div>

            <div class="col-md-2">
                <label for="end_date" class="form-label small fw-bold mb-1">Tanggal Akhir</label>
                <input type="date" class="form-control form-control-sm" id="end_date" name="end_date" value="<?= htmlspecialchars($filters['end_date']) ?>">
            </div>

            <div class="col-md-2">
                <label for="status" class="form-label small fw-bold mb-1">Status Order</label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="ALL">-- Semua Status --</option>
                    <option value="PENDING" <?= ($filters['status'] === 'PENDING') ? 'selected' : '' ?>>Pending</option>
                    <option value="ASSIGNED" <?= ($filters['status'] === 'ASSIGNED') ? 'selected' : '' ?>>Assigned</option>
                    <option value="ACCEPTED" <?= ($filters['status'] === 'ACCEPTED') ? 'selected' : '' ?>>Accepted</option>
                    <option value="ON_THE_WAY" <?= ($filters['status'] === 'ON_THE_WAY') ? 'selected' : '' ?>>On The Way</option>
                    <option value="IN_PROGRESS" <?= ($filters['status'] === 'IN_PROGRESS') ? 'selected' : '' ?>>In Progress</option>
                    <option value="COMPLETED" <?= ($filters['status'] === 'COMPLETED') ? 'selected' : '' ?>>Completed</option>
                    <option value="CANCELLED" <?= ($filters['status'] === 'CANCELLED') ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>

            <div class="col-md-2">
                <label for="equipment_id" class="form-label small fw-bold mb-1">Jenis Peralatan</label>
                <select class="form-select form-select-sm" id="equipment_id" name="equipment_id">
                    <option value="ALL">-- Semua Peralatan --</option>
                    <?php foreach($equipments as $eq): ?>
                        <option value="<?= $eq['id'] ?>" <?= ($filters['equipment_id'] == $eq['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($eq['name']) ?> (<?= htmlspecialchars($eq['brand'] ?? 'Universal') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="technician_id" class="form-label small fw-bold mb-1">Teknisi</label>
                <select class="form-select form-select-sm" id="technician_id" name="technician_id">
                    <option value="ALL">-- Semua Teknisi --</option>
                    <?php foreach($technicians as $tech): ?>
                        <option value="<?= $tech['id'] ?>" <?= ($filters['technician_id'] == $tech['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tech['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-filter"></i> Filter
                </button>
                <a href="<?= BASE_URL ?>/admin/reports" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>

        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0 border-start border-4 border-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Total Service Order</div>
                        <h2 class="fw-bold text-primary mb-0 mt-1"><?= number_format($summary['total_orders'] ?? 0) ?></h2>
                    </div>
                    <div class="bg-primary text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-card-checklist fs-4"></i>
                    </div>
                </div>
                <small class="text-muted mt-2 d-block">
                    Aktif: <strong><?= $summary['total_active'] ?? 0 ?></strong> | Pending: <strong><?= $summary['total_pending'] ?? 0 ?></strong>
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0 border-start border-4 border-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Total Servis Selesai</div>
                        <h2 class="fw-bold text-success mb-0 mt-1"><?= number_format($summary['total_completed'] ?? 0) ?></h2>
                    </div>
                    <div class="bg-success text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
                <small class="text-muted mt-2 d-block">
                    Tingkat Selesai: <strong><?= ($summary['total_orders'] > 0) ? round(($summary['total_completed'] / $summary['total_orders']) * 100, 1) : 0 ?>%</strong>
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0 border-start border-4 border-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Total Biaya / Omset</div>
                        <h2 class="fw-bold text-info mb-0 mt-1 fs-4">Rp <?= number_format($summary['total_revenue'] ?? 0, 0, ',', '.') ?></h2>
                    </div>
                    <div class="bg-info text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                </div>
                <small class="text-muted mt-2 d-block">Dari total pekerjaan yang selesai</small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0 border-start border-4 border-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Rata-rata Rating Teknisi</div>
                        <h2 class="fw-bold text-warning mb-0 mt-1">★ <?= number_format($summary['avg_technician_rating'] ?? 0, 1) ?></h2>
                    </div>
                    <div class="bg-warning text-dark rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-star-fill fs-4"></i>
                    </div>
                </div>
                <small class="text-muted mt-2 d-block">Evaluasi kepuasan pelanggan</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-5 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold text-primary">
                    <i class="bi bi-pie-chart-fill me-2"></i>Order Berdasarkan Status
                </h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" style="position: relative; height: 280px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-7 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold text-primary">
                    <i class="bi bi-bar-chart-line-fill me-2"></i>Order Berdasarkan Jenis Peralatan
                </h6>
            </div>
            <div class="card-body" style="position: relative; height: 280px;">
                <canvas id="equipmentChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Technician Workload & Rating Table -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="card-title mb-0 fw-bold text-primary">
            <i class="bi bi-people-fill me-2"></i>Jumlah Pekerjaan & Rating per Teknisi
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nama Teknisi</th>
                        <th>Spesialisasi</th>
                        <th>Pengalaman</th>
                        <th>Rating</th>
                        <th class="text-center">Total Ditugaskan</th>
                        <th class="text-center">Selesai</th>
                        <th class="text-center">Sedang Berjalan</th>
                        <th class="text-end pe-3">Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($technicianJobs)): ?>
                        <?php foreach($technicianJobs as $tjob): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-dark">
                                    <i class="bi bi-person-badge text-primary me-1"></i><?= htmlspecialchars($tjob['technician_name']) ?>
                                </td>
                                <td><?= htmlspecialchars($tjob['specialization'] ?? 'Elektronik') ?></td>
                                <td><?= $tjob['experience'] ?? 0 ?> tahun</td>
                                <td>
                                    <span class="badge bg-warning text-dark">
                                        ★ <?= number_format($tjob['rating'] ?? 0, 1) ?>
                                    </span>
                                </td>
                                <td class="text-center fw-bold"><?= $tjob['total_assigned'] ?? 0 ?></td>
                                <td class="text-center text-success fw-bold"><?= $tjob['total_completed'] ?? 0 ?></td>
                                <td class="text-center text-primary"><?= $tjob['total_in_progress'] ?? 0 ?></td>
                                <td class="text-end pe-3 fw-bold text-success">
                                    Rp <?= number_format($tjob['total_revenue'] ?? 0, 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada data teknisi yang tercatat.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detailed Filtered Orders Table -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold text-primary">
            <i class="bi bi-table me-2"></i>Data Detail Service Orders (<?= $total_count ?> data)
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No. Order</th>
                        <th>Jadwal Servis</th>
                        <th>Customer</th>
                        <th>Peralatan</th>
                        <th>Teknisi</th>
                        <th>Status</th>
                        <th>Biaya Akhir</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($orders)): ?>
                        <?php foreach($orders as $ord): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#ORD-<?= str_pad($ord['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td><?= date('d M Y H:i', strtotime($ord['service_date'])) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($ord['customer_name']) ?></strong>
                                    <div class="small text-muted"><?= htmlspecialchars($ord['customer_phone'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($ord['equipment_name']) ?></strong>
                                    <?php if(!empty($ord['equipment_brand'])): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($ord['equipment_brand']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(!empty($ord['technician_name'])): ?>
                                        <span class="text-dark fw-semibold"><?= htmlspecialchars($ord['technician_name']) ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">Belum Ada</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass($ord['status']) ?>">
                                        <?= $statusLabel($ord['status']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold <?= (!empty($ord['final_cost'])) ? 'text-success' : 'text-muted' ?>">
                                    <?= (!empty($ord['final_cost'])) ? 'Rp ' . number_format($ord['final_cost'], 0, ',', '.') : '-' ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/admin/orders/detail?id=<?= $ord['id'] ?>" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-clipboard-x fs-1 d-block mb-3 text-secondary"></i>
                                <h6 class="fw-bold">Tidak ada data order pada rentang filter ini</h6>
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
                <?php 
                $queryParams = $filters;
                for($i = 1; $i <= $total_pages; $i++): 
                    $queryParams['page'] = $i;
                ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/admin/reports?<?= http_build_query($queryParams) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<script>
// Chart 1: Status Distribution Doughnut Chart
const statusData = <?= json_encode($statusDistribution) ?>;
const statusLabels = Object.keys(statusData);
const statusCounts = Object.values(statusData);

const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: statusLabels.length ? statusLabels : ['Belum Ada Data'],
        datasets: [{
            data: statusCounts.length ? statusCounts : [1],
            backgroundColor: [
                '#ffc107', // PENDING
                '#0dcaf0', // ASSIGNED
                '#0d6efd', // ACCEPTED
                '#6c757d', // ON_THE_WAY
                '#6610f2', // IN_PROGRESS
                '#198754', // COMPLETED
                '#dc3545'  // CANCELLED
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Chart 2: Equipment Distribution Bar Chart
const equipmentData = <?= json_encode($equipmentDistribution) ?>;
const eqLabels = equipmentData.map(item => item.name);
const eqCounts = equipmentData.map(item => item.count);

const eqCtx = document.getElementById('equipmentChart').getContext('2d');
new Chart(eqCtx, {
    type: 'bar',
    data: {
        labels: eqLabels.length ? eqLabels : ['Belum Ada Data'],
        datasets: [{
            label: 'Jumlah Order',
            data: eqCounts.length ? eqCounts : [0],
            backgroundColor: '#0d6efd',
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>
