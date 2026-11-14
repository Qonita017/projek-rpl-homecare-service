<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Master Data Equipment</h1>
    <a href="<?= BASE_URL ?>/admin/equipment/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Equipment</a>
</div>

<?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom pt-3 pb-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary fw-bold">Daftar Equipment</h5>
        <form class="d-flex" method="GET" action="<?= BASE_URL ?>/admin/equipment">
            <input class="form-control me-2" type="search" name="search" placeholder="Cari nama, brand, atau kategori..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline-primary" type="submit">Cari</button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Kategori</th>
                        <th>Nama/Model</th>
                        <th>Brand</th>
                        <th>Estimasi Biaya Servis</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($equipments)): ?>
                        <?php foreach($equipments as $e): ?>
                            <tr>
                                <td><?= htmlspecialchars($e['id']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($e['category'] ?? 'Uncategorized') ?></span></td>
                                <td><?= htmlspecialchars($e['name']) ?></td>
                                <td><?= htmlspecialchars($e['brand'] ?? '-') ?></td>
                                <td><span class="text-success fw-bold">Rp <?= number_format($e['repair_price'] ?? 0, 0, ',', '.') ?></span></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/equipment/edit?id=<?= $e['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                                    <form action="<?= BASE_URL ?>/admin/equipment/delete" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus equipment ini?');">
                                        <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Data equipment tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if($total_pages > 1): ?>
    <div class="card-footer bg-white pt-3 pb-3">
        <nav aria-label="Page navigation">
            <ul class="pagination mb-0 justify-content-center">
                <?php for($i=1; $i<=$total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/admin/equipment?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
