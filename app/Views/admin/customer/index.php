<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Master Data Customer</h1>
    <a href="<?= BASE_URL ?>/admin/customer/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Customer</a>
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
        <h5 class="mb-0 text-primary fw-bold">Daftar Customer</h5>
        <form class="d-flex" method="GET" action="<?= BASE_URL ?>/admin/customer">
            <input class="form-control me-2" type="search" name="search" placeholder="Cari nama atau email..." value="<?= htmlspecialchars($search) ?>">
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
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($customers)): ?>
                        <?php foreach($customers as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['id']) ?></td>
                                <td><?= htmlspecialchars($c['name']) ?></td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                                <td><?= htmlspecialchars($c['phone'] ?? '-') ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/customer/edit?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                                    <form action="<?= BASE_URL ?>/admin/customer/delete" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus customer ini?');">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Data customer tidak ditemukan.</td>
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
                        <a class="page-link" href="<?= BASE_URL ?>/admin/customer?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>
