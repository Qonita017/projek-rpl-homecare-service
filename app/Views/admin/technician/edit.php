<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Teknisi</h1>
    <a href="<?= BASE_URL ?>/admin/technician" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form action="<?= BASE_URL ?>/admin/technician/update" method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($technician['id']) ?>">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($technician['user_id']) ?>">
            
            <h5 class="mb-3 border-bottom pb-2">Informasi Akun</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($technician['name']) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($technician['email']) ?>" readonly>
                    <div class="form-text">Email tidak dapat diubah.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password Baru (Opsional)</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">No. Telepon</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($technician['phone'] ?? '') ?>">
                </div>
                <div class="col-12 mb-3">
                    <label for="address" class="form-label">Alamat Lengkap</label>
                    <textarea class="form-control" id="address" name="address" rows="2"><?= htmlspecialchars($technician['address'] ?? '') ?></textarea>
                </div>
            </div>
            
            <h5 class="mb-3 mt-4 border-bottom pb-2">Informasi Teknisi</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="specialization" class="form-label">Spesialisasi</label>
                    <input type="text" class="form-control" id="specialization" name="specialization" value="<?= htmlspecialchars($technician['specialization'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="experience" class="form-label">Pengalaman (Tahun)</label>
                    <input type="number" class="form-control" id="experience" name="experience" min="0" value="<?= htmlspecialchars($technician['experience'] ?? 0) ?>">
                </div>
                <div class="col-12 mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="availability" name="availability" <?= $technician['availability'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="availability">Tersedia untuk menerima pekerjaan (Availability)</label>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Update Data Teknisi</button>
            </div>
        </form>
    </div>
</div>
