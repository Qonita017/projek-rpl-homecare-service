<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Master Data Teknisi</h1>
    <a href="<?= BASE_URL ?>/admin/technician/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Teknisi</a>
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
        <h5 class="mb-0 text-primary fw-bold">Daftar Teknisi</h5>
        <form class="d-flex" method="GET" action="<?= BASE_URL ?>/admin/technician">
            <input class="form-control me-2" type="search" name="search" placeholder="Cari nama, email, atau spesialisasi..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline-primary" type="submit">Cari</button>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Spesialisasi</th>
                        <th>Pengalaman</th>
                        <th>Status</th>
                        <th>Rating</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($technicians)): ?>
                        <?php foreach($technicians as $t): ?>
                            <tr>
                                <td><?= htmlspecialchars($t['id']) ?></td>
                                <td>
                                    <?= htmlspecialchars($t['name']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($t['email']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($t['specialization'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($t['experience'] ?? 0) ?> Tahun</td>
                                <td>
                                    <?php if($t['availability']): ?>
                                        <span class="badge bg-success">Tersedia</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tidak Tersedia</span>
                                    <?php endif; ?>
                                </td>
                                <td><i class="bi bi-star-fill text-warning"></i> <?= number_format($t['rating'] ?? 0, 1) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/technician/edit?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                                    <form action="<?= BASE_URL ?>/admin/technician/delete" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus teknisi ini? Semua data terkait (termasuk user) akan terhapus.');">
                                        <input type="hidden" name="user_id" value="<?= $t['user_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Data teknisi tidak ditemukan.</td>
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
                        <a class="page-link" href="<?= BASE_URL ?>/admin/technician?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
