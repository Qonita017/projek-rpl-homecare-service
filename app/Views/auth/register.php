<div class="row justify-content-center align-items-center" style="min-height: 100vh; padding: 2rem 0;">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg border-0 rounded-4 p-4">
            <div class="text-center mb-4">
                <h3 class="text-primary fw-bold">Daftar Akun</h3>
                <p class="text-muted">Bergabung dengan HomeCare Service</p>
            </div>
            
            <?php if(isset($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['flash_error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/register" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Nama Lengkap</label>
                    <input type="text" class="form-control rounded-3 bg-light border-0" id="name" name="name" required placeholder="John Doe">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control rounded-3 bg-light border-0" id="email" name="email" required placeholder="name@example.com">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <input type="password" class="form-control rounded-3 bg-light border-0" id="password" name="password" required placeholder="Minimal 8 karakter">
                </div>
                <div class="mb-4">
                    <label for="role" class="form-label fw-semibold">Daftar Sebagai</label>
                    <select class="form-select rounded-3 bg-light border-0" id="role" name="role" required>
                        <option value="customer">👤 Customer (Pencari Jasa)</option>
                        <option value="technician">🔧 Teknisi (Penyedia Jasa)</option>
                        <option value="admin">🛡️ Admin (Pengelola Sistem)</option>
                    </select>
                </div>
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">Daftar Sekarang</button>
                </div>
            </form>
            <div class="text-center mt-4">
                <p class="mb-2 text-muted">Sudah punya akun? <a href="<?= BASE_URL ?>/login" class="text-decoration-none fw-bold">Login di sini</a></p>
                <a href="<?= BASE_URL ?>/" class="text-decoration-none text-secondary small"><i class="bi bi-arrow-left"></i> Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</div>