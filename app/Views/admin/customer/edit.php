<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Customer</h1>
    <a href="<?= BASE_URL ?>/admin/customer" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form action="<?= BASE_URL ?>/admin/customer/update" method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($customer['id']) ?>">
            
            <div class="mb-3">
                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($customer['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($customer['email']) ?>" readonly>
                <div class="form-text">Email tidak dapat diubah.</div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password Baru (Opsional)</label>
                <input type="password" class="form-control" id="password" name="password">
                <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">No. Telepon</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Alamat Lengkap</label>
                <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($customer['address'] ?? '') ?></textarea>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>
