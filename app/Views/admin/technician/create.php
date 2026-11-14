<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tambah Teknisi</h1>
    <a href="<?= BASE_URL ?>/admin/technician" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form action="<?= BASE_URL ?>/admin/technician/store" method="POST">
            <h5 class="mb-3 border-bottom pb-2">Informasi Akun</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">No. Telepon</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>
                <div class="col-12 mb-3">
                    <label for="address" class="form-label">Alamat Lengkap</label>
                    <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                </div>
            </div>
            
            <h5 class="mb-3 mt-4 border-bottom pb-2">Informasi Teknisi</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="specialization" class="form-label">Spesialisasi</label>
                    <input type="text" class="form-control" id="specialization" name="specialization" placeholder="Contoh: AC, Kulkas, Mesin Cuci">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="experience" class="form-label">Pengalaman (Tahun)</label>
                    <input type="number" class="form-control" id="experience" name="experience" min="0" value="0">
                </div>
                <div class="col-12 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="availability" name="availability" checked>
                        <label class="form-check-label" for="availability">Tersedia untuk menerima pekerjaan (Availability)</label>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan Data Teknisi</button>
            </div>
        </form>
    </div>
</div>
