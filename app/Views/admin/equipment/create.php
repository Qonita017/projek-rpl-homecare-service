<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tambah Equipment</h1>
    <a href="<?= BASE_URL ?>/admin/equipment" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form action="<?= BASE_URL ?>/admin/equipment/store" method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Nama/Model Equipment <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Kategori</label>
                <input type="text" class="form-control" id="category" name="category" placeholder="Contoh: AC, Mesin Cuci, Kulkas">
            </div>
            <div class="mb-3">
                <label for="brand" class="form-label">Brand/Merk</label>
                <input type="text" class="form-control" id="brand" name="brand">
            </div>
            <div class="mb-3">
                <label for="repair_price" class="form-label">Estimasi Harga Perbaikan (Rp)</label>
                <input type="number" class="form-control" id="repair_price" name="repair_price" placeholder="Contoh: 150000" min="0" step="5000">
                <div class="form-text">Estimasi biaya servis awal untuk AI Chatbot & katalog pelanggan.</div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan Equipment</button>
            </div>
        </form>
    </div>
</div>
