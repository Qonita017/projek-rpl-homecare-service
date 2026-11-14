<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Ulasan & Rating</li>
            </ol>
        </nav>
        <h1 class="h2"><i class="bi bi-star-fill text-warning me-2"></i>Daftar Ulasan & Rating Customer</h1>
    </div>
</div>

<!-- Search Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/admin/reviews" class="row g-2">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari ulasan berdasarkan ID Order, nama customer, nama teknisi, atau isi komentar..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Cari</button>
            </div>
        </form>
    </div>
</div>

<!-- Reviews Table -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No. Order</th>
                        <th>Customer</th>
                        <th>Teknisi yang Dinilai</th>
                        <th>Peralatan</th>
                        <th>Rating Bintang</th>
                        <th>Ulasan / Komentar</th>
                        <th>Tanggal Ulasan</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($reviews)): ?>
                        <?php foreach($reviews as $rev): ?>
                            <tr>
                                <td class="ps-3 fw-bold text-primary">#ORD-<?= str_pad($rev['order_id'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($rev['customer_name']) ?></strong>
                                    <div class="small text-muted"><?= htmlspecialchars($rev['customer_email']) ?></div>
                                </td>
                                <td>
                                    <strong class="text-dark"><i class="bi bi-person-fill text-primary me-1"></i><?= htmlspecialchars($rev['technician_name']) ?></strong>
                                    <div class="small text-muted"><?= htmlspecialchars($rev['technician_specialization'] ?? 'Teknisi') ?></div>
                                </td>
                                <td>
                                    <?= htmlspecialchars($rev['equipment_name']) ?>
                                    <?php if(!empty($rev['equipment_brand'])): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($rev['equipment_brand']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-warning fs-6">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi <?= ($i <= $rev['rating']) ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                        <?php endfor; ?>
                                        <span class="ms-1 fw-bold text-dark">(<?= $rev['rating'] ?>.0)</span>
                                    </div>
                                </td>
                                <td>
                                    <?php if(!empty($rev['comment'])): ?>
                                        <span class="fst-italic text-break">"<?= htmlspecialchars($rev['comment']) ?>"</span>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Tanpa komentar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small text-muted"><i class="bi bi-clock me-1"></i><?= date('d M Y, H:i', strtotime($rev['created_at'])) ?> WIB</div>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/admin/orders/detail?id=<?= $rev['order_id'] ?>" class="btn btn-sm btn-outline-primary" title="Lihat Order">
                                        <i class="bi bi-eye me-1"></i>Detail Order
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-chat-square-quote fs-1 d-block mb-3 text-secondary"></i>
                                <h6 class="fw-bold">Belum ada ulasan customer yang masuk</h6>
                                <p class="mb-0">Ulasan yang diberikan oleh customer setelah pekerjaan selesai akan muncul di sini.</p>
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
        <span class="small text-muted">Menampilkan halaman <?= $page ?> dari <?= $total_pages ?> (Total <?= $total_count ?> ulasan)</span>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/admin/reviews?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
