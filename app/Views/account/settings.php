<?php
$role = $user['role'] ?? 'CUSTOMER';
$roleColors = [
    'ADMIN' => 'bg-warning text-dark border-warning',
    'CUSTOMER' => 'bg-success text-white border-success',
    'TEKNISI' => 'bg-info text-dark border-info'
];
$roleLabels = [
    'ADMIN' => 'Administrator',
    'CUSTOMER' => 'Pelanggan (Customer)',
    'TEKNISI' => 'Teknisi Profesional'
];
$roleColor = $roleColors[$role] ?? 'bg-secondary text-white';
$roleLabel = $roleLabels[$role] ?? $role;

// Get initials for avatar
$nameParts = explode(' ', trim($user['name'] ?? 'User'));
$initials = '';
foreach (array_slice($nameParts, 0, 2) as $part) {
    $initials .= strtoupper(mb_substr($part, 0, 1));
}
if (empty($initials)) $initials = 'U';

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? $_SESSION['success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? $_SESSION['error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['success'], $_SESSION['flash_error'], $_SESSION['error']);
?>

<div class="container-fluid px-0 px-md-2 py-2">
    <!-- Header Title Bar -->
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item">
                        <a href="<?= BASE_URL ?>/<?= strtolower($role === 'TEKNISI' ? 'technician' : ($role === 'ADMIN' ? 'admin' : 'customer')) ?>/dashboard">
                            Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Pengaturan Akun</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 d-flex align-items-center gap-2">
                <span class="fs-2">⚙️</span>
                <span>Pengaturan Akun & Profil</span>
                <span class="badge <?= $roleColor ?> rounded-pill fs-6 fw-normal px-3 py-1.5">
                    <?= htmlspecialchars($roleLabel) ?>
                </span>
            </h1>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/<?= strtolower($role === 'TEKNISI' ? 'technician' : ($role === 'ADMIN' ? 'admin' : 'customer')) ?>/dashboard" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Flash Alerts -->
    <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-4 border-success d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div><?= htmlspecialchars($flashSuccess) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 border-start border-4 border-danger d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
            <div><?= htmlspecialchars($flashError) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Left Column: Profile Card & Quick Info -->
        <div class="col-lg-4 col-md-5">
            <!-- Profile Identity Card -->
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="bg-primary text-white p-4 text-center position-relative" style="background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);">
                    <div class="avatar bg-white text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center shadow-lg mb-3" style="width: 84px; height: 84px; font-size: 2rem; font-weight: 700;">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['name']) ?></h5>
                    <p class="text-white-50 small mb-2"><?= htmlspecialchars($user['email']) ?></p>
                    <span class="badge bg-white text-dark rounded-pill px-3 py-1 shadow-sm font-monospace fw-bold">
                        <i class="bi bi-shield-check text-primary me-1"></i><?= htmlspecialchars($role) ?>
                    </span>
                </div>

                <div class="card-body p-3">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                            <span class="text-muted"><i class="bi bi-telephone me-2 text-primary"></i>No. Telepon / WA:</span>
                            <span class="fw-semibold text-dark"><?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : '<em class="text-muted">Belum diisi</em>' ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                            <span class="text-muted"><i class="bi bi-geo-alt me-2 text-danger"></i>Alamat:</span>
                            <span class="fw-semibold text-dark text-truncate text-end" style="max-width: 180px;" title="<?= htmlspecialchars($user['address'] ?? '') ?>">
                                <?= !empty($user['address']) ? htmlspecialchars($user['address']) : '<em class="text-muted">Belum diisi</em>' ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                            <span class="text-muted"><i class="bi bi-calendar-check me-2 text-success"></i>Bergabung Sejak:</span>
                            <span class="fw-semibold text-dark"><?= date('d M Y', strtotime($user['created_at'])) ?></span>
                        </li>

                        <?php if ($role === 'TEKNISI' && !empty($technician)): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                                <span class="text-muted"><i class="bi bi-star-fill me-2 text-warning"></i>Rating Teknisi:</span>
                                <span class="badge bg-warning-subtle text-dark fw-bold px-2 py-1">
                                    ⭐ <?= number_format($technician['rating'] ?? 0, 2) ?> / 5.0
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2.5">
                                <span class="text-muted"><i class="bi bi-circle-fill me-2 <?= $technician['availability'] ? 'text-success' : 'text-secondary' ?>"></i>Status Ketersediaan:</span>
                                <span class="badge <?= $technician['availability'] ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                                    <?= $technician['availability'] ? 'Siap Menerima Order' : 'Sedang Sibuk / Libur' ?>
                                </span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Tab Navigation Menu Cards -->
            <div class="list-group shadow-sm border-0 rounded-4 overflow-hidden" id="settingsNav">
                <a href="#profile" class="list-group-item list-group-item-action active p-3 d-flex align-items-center justify-content-between border-0" id="tab-profile-link" onclick="switchTab('profile', event)">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-person-lines-fill fs-5"></i>
                        <div>
                            <div class="fw-bold">Profil & Data Diri</div>
                            <small class="opacity-75">Perbarui nama, kontak, dan alamat</small>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right opacity-75"></i>
                </a>

                <a href="#security" class="list-group-item list-group-item-action p-3 d-flex align-items-center justify-content-between border-0 border-top" id="tab-security-link" onclick="switchTab('security', event)">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-shield-lock-fill fs-5"></i>
                        <div>
                            <div class="fw-bold">Keamanan & Password</div>
                            <small class="opacity-75">Ubah password & perlindungan akun</small>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right opacity-75"></i>
                </a>

                <a href="#activity" class="list-group-item list-group-item-action p-3 d-flex align-items-center justify-content-between border-0 border-top" id="tab-activity-link" onclick="switchTab('activity', event)">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-bar-chart-fill fs-5"></i>
                        <div>
                            <div class="fw-bold">Ringkasan Aktivitas</div>
                            <small class="opacity-75">Statistik & histori akun Anda</small>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right opacity-75"></i>
                </a>
            </div>
        </div>

        <!-- Right Column: Settings Content Panels -->
        <div class="col-lg-8 col-md-7">
            
            <!-- PANEL 1: PROFIL & DATA DIRI -->
            <div class="tab-panel" id="panel-profile">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-circle fs-4 text-primary"></i>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Informasi Profil Pribadi</h5>
                                <small class="text-muted">Kelola data identitas dan kontak akun Anda di HomeCare Service</small>
                            </div>
                        </div>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">Akun Aktif</span>
                    </div>

                    <div class="card-body p-4">
                        <form action="<?= BASE_URL ?>/account/settings/profile" method="POST" id="profileForm">
                            
                            <div class="row g-3">
                                <!-- Nama Lengkap -->
                                <div class="col-md-12">
                                    <label for="nameInput" class="form-label fw-semibold text-dark">
                                        Nama Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-secondary"></i></span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="nameInput" name="name" value="<?= htmlspecialchars($user['name']) ?>" required placeholder="Masukkan nama lengkap Anda">
                                    </div>
                                    <div class="form-text text-muted">Nama ini akan digunakan pada faktur servis, laporan teknisi, dan interaksi sistem.</div>
                                </div>

                                <!-- Email (Read Only) -->
                                <div class="col-md-6">
                                    <label for="emailInput" class="form-label fw-semibold text-dark">
                                        Alamat Email <span class="badge bg-secondary-subtle text-secondary ms-1">Permanen</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light-subtle border-end-0"><i class="bi bi-envelope-fill text-muted"></i></span>
                                        <input type="email" class="form-control bg-light-subtle border-start-0 ps-0" id="emailInput" value="<?= htmlspecialchars($user['email']) ?>" readonly disabled>
                                    </div>
                                    <div class="form-text text-muted"><i class="bi bi-lock-fill me-1"></i>Email utama login tidak dapat diubah secara mandiri.</div>
                                </div>

                                <!-- Nomor Telepon / WA -->
                                <div class="col-md-6">
                                    <label for="phoneInput" class="form-label fw-semibold text-dark">
                                        Nomor Telepon / WhatsApp
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-whatsapp text-success"></i></span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="phoneInput" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Contoh: 081234567890">
                                    </div>
                                    <div class="form-text text-muted">Digunakan untuk koordinasi jadwal servis oleh tim teknisi.</div>
                                </div>

                                <!-- Alamat Lengkap -->
                                <div class="col-md-12">
                                    <label for="addressInput" class="form-label fw-semibold text-dark">
                                        Alamat Lengkap Domisili / Lokasi Servis
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 align-items-start pt-2"><i class="bi bi-geo-alt-fill text-danger"></i></span>
                                        <textarea class="form-control border-start-0 ps-0" id="addressInput" name="address" rows="3" placeholder="Masukkan nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, dan patokan alamat..."><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                    </div>
                                    <div class="form-text text-muted">Alamat default saat Anda membuat pesanan servis baru ke lokasi rumah.</div>
                                </div>

                                <!-- KHUSUS ROLE TEKNISI: PENGATURAN KEAHLIAN & KETERSEDIAAN -->
                                <?php if ($role === 'TEKNISI'): ?>
                                    <div class="col-12 mt-4">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <h6 class="fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                                                <i class="bi bi-tools"></i> Pengaturan Khusus Teknisi
                                            </h6>

                                            <div class="row g-3">
                                                <!-- Spesialisasi -->
                                                <div class="col-md-8">
                                                    <label for="specInput" class="form-label fw-semibold text-dark">
                                                        Spesialisasi Keahlian Peralatan
                                                    </label>
                                                    <input type="text" class="form-control" id="specInput" name="specialization" value="<?= htmlspecialchars($technician['specialization'] ?? '') ?>" placeholder="Contoh: Air Conditioner (AC), Kulkas, Mesin Cuci">
                                                    <div class="form-text text-muted">Keahlian utama Anda dalam menangani peralatan rumah tangga.</div>
                                                </div>

                                                <!-- Pengalaman Kerja -->
                                                <div class="col-md-4">
                                                    <label for="expInput" class="form-label fw-semibold text-dark">
                                                        Pengalaman (Tahun)
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control" id="expInput" name="experience" min="0" max="50" value="<?= (int)($technician['experience'] ?? 1) ?>">
                                                        <span class="input-group-text bg-light">Tahun</span>
                                                    </div>
                                                </div>

                                                <!-- Status Ketersediaan -->
                                                <div class="col-md-12">
                                                    <label class="form-label fw-semibold text-dark d-block">
                                                        Status Ketersediaan Menerima Pekerjaan
                                                    </label>
                                                    <div class="d-flex gap-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="availability" id="avail1" value="1" <?= (!isset($technician['availability']) || $technician['availability'] == 1) ? 'checked' : '' ?>>
                                                            <label class="form-check-label text-success fw-semibold" for="avail1">
                                                                <i class="bi bi-check-circle-fill me-1"></i> Siap & Tersedia (Ready to Work)
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="availability" id="avail0" value="0" <?= (isset($technician['availability']) && $technician['availability'] == 0) ? 'checked' : '' ?>>
                                                            <label class="form-check-label text-secondary fw-semibold" for="avail0">
                                                                <i class="bi bi-pause-circle-fill me-1"></i> Sedang Sibuk / Libur
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2">
                                    <i class="bi bi-check-lg fs-5"></i> Simpan Perubahan Profil
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <!-- PANEL 2: KEAMANAN & PASSWORD -->
            <div class="tab-panel d-none" id="panel-security">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock-fill fs-4 text-warning"></i>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Ganti Kata Sandi (Password)</h5>
                                <small class="text-muted">Pastikan akun Anda tetap aman dengan menggunakan kata sandi yang kuat</small>
                            </div>
                        </div>
                        <span class="badge bg-warning-subtle text-dark rounded-pill px-3 py-1"><i class="bi bi-shield-shaded me-1"></i>Keamanan Akun</span>
                    </div>

                    <div class="card-body p-4">
                        <form action="<?= BASE_URL ?>/account/settings/password" method="POST" id="passwordForm" onsubmit="return validatePasswordForm(event)">
                            
                            <div class="row g-3">
                                <!-- Password Saat Ini -->
                                <div class="col-md-12">
                                    <label for="currentPasswordInput" class="form-label fw-semibold text-dark">
                                        Password Saat Ini <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-secondary"></i></span>
                                        <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="currentPasswordInput" name="current_password" required placeholder="Masukkan password Anda saat ini">
                                        <button type="button" class="btn btn-outline-secondary border-start-0" onclick="togglePasswordVisibility('currentPasswordInput', this)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text text-muted">Dibutuhkan untuk memverifikasi kepemilikan akun sebelum mengubah kata sandi.</div>
                                </div>

                                <!-- Password Baru -->
                                <div class="col-md-6">
                                    <label for="newPasswordInput" class="form-label fw-semibold text-dark">
                                        Password Baru <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-primary"></i></span>
                                        <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="newPasswordInput" name="new_password" minlength="6" required placeholder="Minimal 6 karakter" oninput="checkPasswordStrength()">
                                        <button type="button" class="btn btn-outline-secondary border-start-0" onclick="togglePasswordVisibility('newPasswordInput', this)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <!-- Password Strength Meter -->
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div id="strengthBar" class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <small id="strengthText" class="text-muted" style="font-size: 0.75rem;">Kekuatan password: Belum diisi</small>
                                </div>

                                <!-- Konfirmasi Password Baru -->
                                <div class="col-md-6">
                                    <label for="confirmPasswordInput" class="form-label fw-semibold text-dark">
                                        Ulangi Password Baru <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock-fill text-success"></i></span>
                                        <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="confirmPasswordInput" name="confirm_password" minlength="6" required placeholder="Ketik ulang password baru" oninput="checkPasswordMatch()">
                                        <button type="button" class="btn btn-outline-secondary border-start-0" onclick="togglePasswordVisibility('confirmPasswordInput', this)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <small id="matchText" class="text-muted" style="font-size: 0.75rem;"></small>
                                </div>
                            </div>

                            <!-- Tips Keamanan Box -->
                            <div class="alert alert-info border-info border-start border-4 bg-info-subtle mt-4 p-3 rounded-3 small">
                                <h6 class="fw-bold text-dark mb-1"><i class="bi bi-lightbulb text-warning me-1"></i>Tips Keamanan Password yang Kuat:</h6>
                                <ul class="mb-0 ps-3 text-secondary">
                                    <li>Gunakan minimal 6 hingga 8 karakter.</li>
                                    <li>Kombinasikan huruf besar, huruf kecil, angka, dan simbol unik (misal: <em>!@#$%</em>).</li>
                                    <li>Hindari menggunakan tanggal lahir, nama diri, atau nomor telepon sebagai kata sandi.</li>
                                </ul>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-outline-secondary rounded-pill px-4" onclick="resetPasswordStrength()">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Batal
                                </button>
                                <button type="submit" id="savePasswordBtn" class="btn btn-warning text-dark fw-bold rounded-pill px-4 shadow-sm d-flex align-items-center gap-2">
                                    <i class="bi bi-shield-check fs-5"></i> Simpan Password Baru
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <!-- PANEL 3: RINGKASAN AKTIVITAS AKUN -->
            <div class="tab-panel d-none" id="panel-activity">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-activity fs-4 text-success"></i>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Ringkasan Aktivitas & Layanan</h5>
                                <small class="text-muted">Statistik keterlibatan dan histori akun Anda di sistem HomeCare Service</small>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">Histori Akun</span>
                    </div>

                    <div class="card-body p-4">
                        
                        <?php if ($role === 'CUSTOMER'): ?>
                            <!-- Customer Stats Overview -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="card border-0 bg-primary-subtle rounded-4 p-3 text-center">
                                        <span class="fs-2 text-primary">📦</span>
                                        <h3 class="fw-bold mb-0 text-primary"><?= (int)($stats['total_pesanan'] ?? 0) ?></h3>
                                        <small class="text-muted fw-semibold">Total Pesanan Servis</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 bg-warning-subtle rounded-4 p-3 text-center">
                                        <span class="fs-2 text-warning">⏳</span>
                                        <h3 class="fw-bold mb-0 text-dark"><?= (int)($stats['pesanan_aktif'] ?? 0) ?></h3>
                                        <small class="text-muted fw-semibold">Pesanan Sedang Proses</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 bg-success-subtle rounded-4 p-3 text-center">
                                        <span class="fs-2 text-success">✅</span>
                                        <h3 class="fw-bold mb-0 text-success"><?= (int)($stats['pesanan_selesai'] ?? 0) ?></h3>
                                        <small class="text-muted fw-semibold">Pesanan Selesai</small>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-4 border">
                                <h6 class="fw-bold text-dark mb-2">Akses Cepat Fitur Pelanggan:</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="<?= BASE_URL ?>/customer/order/create" class="btn btn-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-plus-circle me-1"></i> Buat Pesanan Servis Baru
                                    </a>
                                    <a href="<?= BASE_URL ?>/customer/order" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-clock-history me-1"></i> Lihat Pesanan Aktif
                                    </a>
                                    <a href="<?= BASE_URL ?>/customer/ai-chat" class="btn btn-outline-success btn-sm rounded-pill px-3">
                                        <i class="bi bi-robot me-1"></i> Konsultasi AI Assistant
                                    </a>
                                </div>
                            </div>

                        <?php elseif ($role === 'TEKNISI'): ?>
                            <!-- Technician Stats Overview -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="card border-0 bg-primary-subtle rounded-4 p-3 text-center">
                                        <span class="fs-2 text-primary">⭐</span>
                                        <h3 class="fw-bold mb-0 text-primary"><?= number_format($technician['rating'] ?? 0, 2) ?></h3>
                                        <small class="text-muted fw-semibold">Rating Rata-rata</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 bg-success-subtle rounded-4 p-3 text-center">
                                        <span class="fs-2 text-success">🔧</span>
                                        <h3 class="fw-bold mb-0 text-success"><?= (int)($stats['pekerjaan_selesai'] ?? 0) ?></h3>
                                        <small class="text-muted fw-semibold">Pekerjaan Selesai</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 bg-warning-subtle rounded-4 p-3 text-center">
                                        <span class="fs-2 text-warning">📋</span>
                                        <h3 class="fw-bold mb-0 text-dark"><?= (int)($stats['pekerjaan_aktif'] ?? 0) ?></h3>
                                        <small class="text-muted fw-semibold">Pekerjaan Sedang Berjalan</small>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-4 border">
                                <h6 class="fw-bold text-dark mb-2">Akses Cepat Fitur Teknisi:</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="<?= BASE_URL ?>/technician/jobs" class="btn btn-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-briefcase me-1"></i> Kelola Pekerjaan Saya
                                    </a>
                                    <a href="<?= BASE_URL ?>/technician/jobs?status=COMPLETED" class="btn btn-outline-success btn-sm rounded-pill px-3">
                                        <i class="bi bi-check2-circle me-1"></i> Histori Pekerjaan Selesai
                                    </a>
                                </div>
                            </div>

                        <?php elseif ($role === 'ADMIN'): ?>
                            <!-- Admin Stats Overview -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <div class="card border-0 bg-primary-subtle rounded-4 p-3 text-center">
                                        <span class="fs-2 text-primary">👥</span>
                                        <h3 class="fw-bold mb-0 text-primary"><?= (int)($stats['total_customer'] ?? 0) ?></h3>
                                        <small class="text-muted fw-semibold">Total Customer</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 bg-info-subtle rounded-4 p-3 text-center">
                                        <span class="fs-2 text-info">👨‍🔧</span>
                                        <h3 class="fw-bold mb-0 text-dark"><?= (int)($stats['total_teknisi'] ?? 0) ?></h3>
                                        <small class="text-muted fw-semibold">Total Teknisi</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 bg-success-subtle rounded-4 p-3 text-center">
                                        <span class="fs-2 text-success">📋</span>
                                        <h3 class="fw-bold mb-0 text-success"><?= (int)($stats['pesanan_selesai'] ?? 0) ?></h3>
                                        <small class="text-muted fw-semibold">Servis Selesai</small>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-4 border">
                                <h6 class="fw-bold text-dark mb-2">Akses Cepat Administrasi:</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="<?= BASE_URL ?>/admin/orders" class="btn btn-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-card-checklist me-1"></i> Manajemen Pesanan
                                    </a>
                                    <a href="<?= BASE_URL ?>/admin/reports" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-graph-up me-1"></i> Laporan & Rekap
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Tab switcher handler
function switchTab(tabId, event) {
    if (event) event.preventDefault();

    // Hide all panels
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.add('d-none');
    });

    // Deactivate all links
    document.querySelectorAll('#settingsNav .list-group-item').forEach(link => {
        link.classList.remove('active');
    });

    // Show target panel
    const targetPanel = document.getElementById(`panel-${tabId}`);
    const targetLink = document.getElementById(`tab-${tabId}-link`);

    if (targetPanel) targetPanel.classList.remove('d-none');
    if (targetLink) targetLink.classList.add('active');

    // Update URL Hash
    window.location.hash = tabId;
}

// Check Hash on Load
document.addEventListener('DOMContentLoaded', function() {
    const hash = window.location.hash.replace('#', '');
    if (hash && ['profile', 'security', 'activity'].includes(hash)) {
        switchTab(hash);
    }
});

// Toggle show/hide password text
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Password strength indicator
function checkPasswordStrength() {
    const val = document.getElementById('newPasswordInput').value;
    const bar = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');

    let score = 0;
    if (val.length >= 6) score += 25;
    if (val.length >= 8) score += 25;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score += 25;
    if (/[0-9]/.test(val) && /[^A-Za-z0-9]/.test(val)) score += 25;

    bar.style.width = score + '%';

    if (score === 0) {
        bar.className = 'progress-bar bg-danger';
        text.textContent = 'Kekuatan password: Belum diisi';
        text.className = 'text-muted';
    } else if (score <= 25) {
        bar.className = 'progress-bar bg-danger';
        text.textContent = 'Kekuatan password: Lemah (tambahkan huruf besar & angka)';
        text.className = 'text-danger fw-semibold';
    } else if (score <= 50) {
        bar.className = 'progress-bar bg-warning';
        text.textContent = 'Kekuatan password: Cukup';
        text.className = 'text-warning fw-semibold';
    } else if (score <= 75) {
        bar.className = 'progress-bar bg-info';
        text.textContent = 'Kekuatan password: Bagus';
        text.className = 'text-info fw-semibold';
    } else {
        bar.className = 'progress-bar bg-success';
        text.textContent = 'Kekuatan password: Sangat Kuat! 🛡️';
        text.className = 'text-success fw-semibold';
    }

    checkPasswordMatch();
}

// Check confirmation match
function checkPasswordMatch() {
    const newPass = document.getElementById('newPasswordInput').value;
    const confirmPass = document.getElementById('confirmPasswordInput').value;
    const matchText = document.getElementById('matchText');

    if (!confirmPass) {
        matchText.textContent = '';
        return;
    }

    if (newPass === confirmPass) {
        matchText.textContent = '✓ Konfirmasi password cocok!';
        matchText.className = 'text-success fw-semibold';
    } else {
        matchText.textContent = '✗ Konfirmasi password belum sama.';
        matchText.className = 'text-danger fw-semibold';
    }
}

function resetPasswordStrength() {
    setTimeout(() => {
        const bar = document.getElementById('strengthBar');
        const text = document.getElementById('strengthText');
        const matchText = document.getElementById('matchText');
        if (bar) bar.style.width = '0%';
        if (text) text.textContent = 'Kekuatan password: Belum diisi';
        if (matchText) matchText.textContent = '';
    }, 50);
}

// Form validation before submit
function validatePasswordForm(e) {
    const newPass = document.getElementById('newPasswordInput').value;
    const confirmPass = document.getElementById('confirmPasswordInput').value;

    if (newPass.length < 6) {
        alert('Password baru harus memiliki minimal 6 karakter.');
        e.preventDefault();
        return false;
    }

    if (newPass !== confirmPass) {
        alert('Konfirmasi password tidak cocok dengan password baru.');
        e.preventDefault();
        return false;
    }

    return true;
}
</script>
