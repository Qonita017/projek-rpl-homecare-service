<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/customer/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Buat Pesanan</li>
            </ol>
        </nav>
        <h1 class="h2"><i class="bi bi-plus-circle text-primary me-2"></i>Buat Pesanan Servis Baru</h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="<?= BASE_URL ?>/ai-diagnosis" class="btn btn-outline-info">
            <i class="bi bi-robot me-1"></i> Konsultasi AI Terlebih Dahulu
        </a>
        <a href="<?= BASE_URL ?>/customer/order" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Pesanan Saya
        </a>
    </div>
</div>

<?php if(!empty($ai_diagnosis)): ?>
    <div class="alert alert-primary shadow-sm mb-4 border-primary-subtle bg-white">
        <div class="d-flex align-items-start">
            <div class="p-2 bg-primary-subtle text-primary rounded-circle me-3 mt-1 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                <i class="bi bi-robot fs-5"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h5 class="alert-heading fw-bold mb-0 text-primary">Hasil AI Diagnosis Terlampir</h5>
                    <span class="badge bg-primary text-white">AI Assistant</span>
                </div>
                <p class="mb-2 small text-muted">Hasil konsultasi AI telah otomatis terlampir pada pesanan ini untuk membantu teknisi mempersiapkan tindakan servis yang tepat.</p>
                <?php if(!empty($ai_estimated_cost)): ?>
                    <div class="d-inline-flex align-items-center bg-success-subtle text-success px-3 py-1 rounded-pill small fw-bold mb-2 border border-success-subtle">
                        <i class="bi bi-cash-stack me-1"></i>Estimasi Biaya AI: <?= htmlspecialchars($ai_estimated_cost) ?>
                    </div>
                <?php endif; ?>
                <div class="bg-light p-3 rounded-3 border small text-dark text-break" style="max-height: 180px; overflow-y: auto; line-height: 1.7;">
                    <?= nl2br(htmlspecialchars($ai_diagnosis)) ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="bi bi-clipboard2-check me-2"></i>Formulir Permintaan Servis
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/customer/order/store" method="POST" enctype="multipart/form-data" id="orderForm">
                    
                    <!-- Hidden field for AI Diagnosis -->
                    <input type="hidden" name="ai_diagnosis" value="<?= htmlspecialchars($ai_diagnosis ?? ($old['ai_diagnosis'] ?? '')) ?>">

                    <!-- 1. Equipment Selection -->
                    <div class="mb-4">
                        <label for="equipment_choice" class="form-label fw-bold">Pilih Peralatan yang Akan Diservis <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg shadow-sm" id="equipment_choice" name="equipment_choice" required onchange="toggleCustomEquipment(this.value)">
                            <option value="">-- Pilih dari Daftar Peralatan --</option>
                            <?php 
                            $selectedChoice = $old['equipment_choice'] ?? (!empty($initial_equipment_name) ? 'new' : '');
                            foreach($equipments as $eq): 
                            ?>
                                <option value="<?= $eq['id'] ?>" <?= ($selectedChoice == $eq['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($eq['name']) ?> - <?= htmlspecialchars($eq['brand'] ?? 'Universal') ?> (<?= htmlspecialchars($eq['category'] ?? 'Umum') ?>)
                                </option>
                            <?php endforeach; ?>
                            <option value="new" <?= ($selectedChoice === 'new') ? 'selected' : '' ?>>+ Peralatan Lainnya (Input Jenis & Merk Baru)</option>
                        </select>
                        <div class="form-text">Pilih jenis peralatan dari katalog atau pilih "Peralatan Lainnya" jika tidak ada dalam daftar.</div>
                    </div>

                    <!-- Custom Equipment Fields (Shown if 'new' is selected) -->
                    <div id="custom_equipment_fields" class="p-3 bg-light rounded-3 mb-4 border <?= ($selectedChoice === 'new') ? '' : 'd-none' ?>">
                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-pencil-square me-1"></i>Detail Peralatan Baru</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="custom_name" class="form-label fw-semibold">Jenis / Nama Peralatan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="custom_name" name="custom_name" placeholder="Contoh: AC Split, Mesin Cuci, Kulkas" value="<?= htmlspecialchars($old['custom_name'] ?? ($initial_equipment_name ?? '')) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="custom_brand" class="form-label fw-semibold">Merk / Brand <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="custom_brand" name="custom_brand" placeholder="Contoh: Panasonic, Samsung, Sharp" value="<?= htmlspecialchars($old['custom_brand'] ?? ($initial_brand ?? '')) ?>">
                            </div>
                        </div>
                    </div>

                    <!-- 2. Complaint -->
                    <div class="mb-4">
                        <label for="complaint" class="form-label fw-bold">Keluhan / Masalah Kerusakan <span class="text-danger">*</span></label>
                        <textarea class="form-control shadow-sm" id="complaint" name="complaint" rows="4" placeholder="Jelaskan secara detail gejala kerusakan atau masalah yang dialami oleh peralatan Anda..." required><?= htmlspecialchars($old['complaint'] ?? ($initial_complaint ?? '')) ?></textarea>
                        <div class="form-text">Deskripsi yang detail membantu teknisi menyiapkan peralatan dan suku cadang yang tepat.</div>
                    </div>

                    <!-- 3. Photo Upload -->
                    <div class="mb-4">
                        <label for="photo" class="form-label fw-bold">Foto Kerusakan <span class="badge bg-light text-secondary border">Opsional</span></label>
                        <input class="form-control shadow-sm" type="file" id="photo" name="photo" accept="image/*" onchange="previewImage(this)">
                        <div class="form-text">Format didukung: JPG, PNG, WEBP (Maksimal 5MB).</div>
                        <div id="image_preview_container" class="mt-3 d-none">
                            <p class="small text-muted mb-1">Pratinjau Foto:</p>
                            <img id="image_preview" src="#" alt="Preview" class="img-thumbnail rounded" style="max-height: 200px; object-fit: contain;">
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Jadwal & Lokasi Servis</h5>

                    <!-- 4. Service Date -->
                    <div class="mb-3">
                        <label for="service_date" class="form-label fw-bold">Tanggal & Waktu Servis yang Diinginkan <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control form-control-lg shadow-sm" id="service_date" name="service_date" min="<?= date('Y-m-d\TH:i') ?>" value="<?= htmlspecialchars($old['service_date'] ?? date('Y-m-d\TH:i', strtotime('+1 day 09:00'))) ?>" required>
                        <div class="form-text">Teknisi akan menyesuaikan kunjungan dengan jadwal yang Anda tentukan.</div>
                    </div>

                    <!-- 5. Phone & Address -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="phone" class="form-label fw-bold">Nomor Telepon / WhatsApp Aktif <span class="text-danger">*</span></label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="Contoh: 08123456789" value="<?= htmlspecialchars($old['phone'] ?? $user['phone'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="col-md-12 mb-4">
                            <label for="address" class="form-label fw-bold">Alamat Lengkap Servis <span class="text-danger">*</span></label>
                            <textarea class="form-control shadow-sm" id="address" name="address" rows="3" placeholder="Masukkan alamat lengkap rumah/kantor beserta patokan lokasi..." required><?= htmlspecialchars($old['address'] ?? $user['address'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-bold py-3">
                            <i class="bi bi-send-fill me-2"></i>Kirim Permintaan Servis
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar Information Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body">
                <h5 class="fw-bold text-primary mb-3">
                    <i class="bi bi-info-circle-fill me-2"></i>Alur Proses Pemesanan
                </h5>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex mb-3">
                        <div class="me-3 text-center">
                            <span class="badge rounded-circle bg-warning text-dark p-2" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">1</span>
                        </div>
                        <div>
                            <strong class="d-block">Status Awal: PENDING</strong>
                            <small class="text-muted">Pesanan Anda akan masuk ke sistem dengan status menunggu review admin/penugasan teknisi.</small>
                        </div>
                    </li>
                    <li class="d-flex mb-3">
                        <div class="me-3 text-center">
                            <span class="badge rounded-circle bg-info text-dark p-2" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">2</span>
                        </div>
                        <div>
                            <strong class="d-block">Penugasan Teknisi</strong>
                            <small class="text-muted">Admin akan memilih teknisi yang ahli sesuai dengan jenis peralatan Anda.</small>
                        </div>
                    </li>
                    <li class="d-flex mb-3">
                        <div class="me-3 text-center">
                            <span class="badge rounded-circle bg-primary text-white p-2" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">3</span>
                        </div>
                        <div>
                            <strong class="d-block">Kunjungan & Servis</strong>
                            <small class="text-muted">Teknisi datang ke lokasi Anda, melakukan diagnosa, dan memperbaiki peralatan.</small>
                        </div>
                    </li>
                    <li class="d-flex">
                        <div class="me-3 text-center">
                            <span class="badge rounded-circle bg-success text-white p-2" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">4</span>
                        </div>
                        <div>
                            <strong class="d-block">Selesai & Pembayaran</strong>
                            <small class="text-muted">Pekerjaan selesai, laporan dibuat, dan Anda dapat memberikan ulasan kepuasan.</small>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card shadow-sm border-0 border-start border-4 border-warning">
            <div class="card-body">
                <h6 class="fw-bold mb-2"><i class="bi bi-shield-check text-warning me-2"></i>Garansi Layanan</h6>
                <p class="text-muted small mb-0">Setiap perbaikan peralatan melalui HomeCare Service dilindungi oleh garansi perbaikan dan teknisi profesional bersertifikasi.</p>
            </div>
        </div>
    </div>
</div>

<script>
function toggleCustomEquipment(val) {
    const customFields = document.getElementById('custom_equipment_fields');
    const customName = document.getElementById('custom_name');
    const customBrand = document.getElementById('custom_brand');
    
    if (val === 'new') {
        customFields.classList.remove('d-none');
        customName.setAttribute('required', 'required');
        customBrand.setAttribute('required', 'required');
    } else {
        customFields.classList.add('d-none');
        customName.removeAttribute('required');
        customBrand.removeAttribute('required');
    }
}

function previewImage(input) {
    const previewContainer = document.getElementById('image_preview_container');
    const previewImage = document.getElementById('image_preview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            previewContainer.classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        previewContainer.classList.add('d-none');
    }
}
</script>
