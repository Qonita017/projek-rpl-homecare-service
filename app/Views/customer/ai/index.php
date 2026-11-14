<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/">Beranda</a></li>
                <?php if($is_logged_in && $user_role === 'CUSTOMER'): ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/customer/dashboard">Dashboard</a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active" aria-current="page">AI Assistant Diagnosis</li>
            </ol>
        </nav>
        <h1 class="h2">
            <i class="bi bi-robot text-primary me-2"></i>AI Assistant Diagnosis Kerusakan
        </h1>
    </div>
</div>

<div class="row">
    <!-- Form Input Section -->
    <div class="col-lg-5 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-chat-left-dots-fill me-2"></i>Konsultasi Gejala Kerusakan
                </h5>
            </div>
            <div class="card-body p-4">
                <form id="aiDiagnosisForm" data-ajax="true" data-no-prevent-double-submit="true" onsubmit="handleDiagnosisSubmit(event)">
                    
                    <!-- 1. Equipment Type -->
                    <div class="mb-3">
                        <label for="equipment_type" class="form-label fw-bold">Jenis Peralatan <span class="text-danger">*</span></label>
                        <select class="form-select shadow-sm" id="equipment_type" name="equipment_type" required onchange="handleEquipmentChange(this.value)">
                            <option value="">-- Pilih Jenis Peralatan --</option>
                            <option value="AC (Air Conditioner)">AC (Air Conditioner)</option>
                            <option value="Mesin Cuci">Mesin Cuci</option>
                            <option value="Kulkas / Lemari Es">Kulkas / Lemari Es</option>
                            <option value="Televisi (TV)">Televisi (TV)</option>
                            <option value="Microwave / Oven">Microwave / Oven</option>
                            <option value="Pompa Air">Pompa Air</option>
                            <option value="Kompor Gas / Listrik">Kompor Gas / Listrik</option>
                            <option value="Dispenser Air">Dispenser Air</option>
                            <option value="Lainnya">Peralatan Lainnya...</option>
                        </select>
                    </div>

                    <!-- Custom Equipment Input if 'Lainnya' selected -->
                    <div id="custom_eq_container" class="mb-3 d-none">
                        <label for="custom_equipment_type" class="form-label fw-semibold">Nama Peralatan Kustom</label>
                        <input type="text" class="form-control" id="custom_equipment_type" placeholder="Misal: Blender, Setrika, Vacuum Cleaner">
                    </div>

                    <!-- 2. Brand -->
                    <div class="mb-3">
                        <label for="brand" class="form-label fw-bold">Merk / Brand <span class="text-muted small fw-normal">(Opsional)</span></label>
                        <input type="text" class="form-control shadow-sm" id="brand" name="brand" placeholder="Contoh: Panasonic, Samsung, LG, Sharp, Daikin">
                    </div>

                    <!-- 3. Complaint -->
                    <div class="mb-3">
                        <label for="complaint" class="form-label fw-bold">Keluhan / Masalah Utama <span class="text-danger">*</span></label>
                        <textarea class="form-control shadow-sm" id="complaint" name="complaint" rows="3" placeholder="Contoh: AC saya menyala tetapi tidak dingin dan mengeluarkan tetesan air..." required></textarea>
                    </div>

                    <!-- 4. Symptoms -->
                    <div class="mb-3">
                        <label for="symptoms" class="form-label fw-bold">Gejala Tambahan <span class="text-muted small fw-normal">(Opsional)</span></label>
                        <textarea class="form-control shadow-sm" id="symptoms" name="symptoms" rows="2" placeholder="Contoh: Ada bunyi mendesis di unit outdoor, lampu indikator berkedip 3 kali..."></textarea>
                    </div>

                    <!-- Quick Example Chips -->
                    <div class="mb-4">
                        <small class="text-muted fw-bold d-block mb-1">Contoh Cepat:</small>
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="fillExample('AC (Air Conditioner)', 'Daikin', 'AC menyala tapi tidak dingin dan bocor air', 'Angin yang keluar kecil dan berbau apek')">
                                AC Bocor & Tidak Dingin
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="fillExample('Mesin Cuci', 'LG', 'Mesin cuci tidak mau berputar saat mode pengering', 'Ada suara mendengung dari motor')">
                                Mesin Cuci Tidak Berputar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="fillExample('Kulkas / Lemari Es', 'Sharp', 'Bagian bawah kulkas tidak dingin tetapi freezer beku', 'Bunyi klik berulang dari belakang')">
                                Kulkas Kurang Dingin
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" id="btnDiagnose" data-no-loading-text="true" class="btn btn-primary btn-lg shadow-sm fw-bold py-3">
                            <i class="bi bi-cpu me-2"></i>Analisis dengan AI
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- AI Output & Result Section -->
    <div class="col-lg-7 mb-4">
        
        <!-- Initial Placeholder State -->
        <div id="aiPlaceholder" class="card shadow-sm border-0 h-100 bg-light">
            <div class="card-body p-5 text-center d-flex flex-column justify-content-center align-items-center">
                <div class="bg-white p-4 rounded-circle shadow-sm mb-3 text-primary">
                    <i class="bi bi-robot fs-1"></i>
                </div>
                <h4 class="fw-bold text-dark">Siap Mendiagnosa Kerusakan</h4>
                <p class="text-muted" style="max-width: 480px;">
                    Isi formulir di sebelah kiri dan klik tombol <strong>"Analisis dengan AI"</strong> untuk mendapatkan perkiraan penyebab masalah, tingkat prioritas, dan saran penanganan awal.
                </p>
                <div class="row g-3 text-start mt-3 w-100" style="max-width: 540px;">
                    <div class="col-sm-6">
                        <div class="p-3 bg-white rounded shadow-sm h-100 border-start border-3 border-primary">
                            <h6 class="fw-bold mb-1 text-primary"><i class="bi bi-lightning-charge me-1"></i>Cepat & Instan</h6>
                            <small class="text-muted">Hasil analisis cerdas langsung dalam hitungan detik.</small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-white rounded shadow-sm h-100 border-start border-3 border-success">
                            <h6 class="fw-bold mb-1 text-success"><i class="bi bi-shield-check me-1"></i>Aman & Terarah</h6>
                            <small class="text-muted">Dapatkan panduan tindakan aman sebelum teknisi datang.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="aiLoading" class="card shadow-sm border-0 h-100 bg-light d-none">
            <div class="card-body p-5 text-center d-flex flex-column justify-content-center align-items-center">
                <div class="spinner-border text-primary mb-3" style="width: 3.5rem; height: 3.5rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="fw-bold text-dark mb-1">AI Sedang Menganalisis Gejala Kerusakan...</h5>
                <p class="text-muted small">Memeriksa kemungkinan penyebab teknis dan merumuskan rekomendasi penanganan awal.</p>
            </div>
        </div>

        <!-- Result State -->
        <div id="aiResultContainer" class="card shadow-sm border-0 d-none overflow-hidden">
            
            <!-- Result Header Banner -->
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-primary-subtle text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-robot fs-5"></i>
                    </div>
                    <div>
                        <h5 class="card-title mb-0 fw-bold text-dark">Hasil Diagnosis AI HomeCare</h5>
                        <small class="text-muted" id="summaryEquipmentInfo">Peralatan Rumah Tangga</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="priorityBadge" class="badge bg-warning text-dark fs-6 px-3 py-2 shadow-xs">Prioritas: Sedang</span>
                </div>
            </div>

            <div class="card-body p-4 bg-light-subtle">
                
                <!-- 1. Analysis Summary Block -->
                <div class="card border border-primary-subtle shadow-xs mb-3 bg-white rounded-3">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center mb-2 pb-2 border-bottom border-light-subtle">
                            <i class="bi bi-file-earmark-medical-fill text-primary fs-5 me-2"></i>
                            <h6 class="fw-bold text-dark mb-0">1. Analisis Gejala & Kondisi Peralatan</h6>
                        </div>
                        <div id="analysisContainer" class="text-secondary" style="line-height: 1.75; font-size: 0.95rem;">
                            <!-- Injected via JS as clean readable paragraphs -->
                        </div>
                    </div>
                </div>

                <!-- 2. Technical Causes Block -->
                <div class="card border border-warning-subtle shadow-xs mb-3 bg-white rounded-3">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center mb-2 pb-2 border-bottom border-light-subtle">
                            <i class="bi bi-cpu-fill text-warning fs-5 me-2"></i>
                            <h6 class="fw-bold text-dark mb-0">2. Kemungkinan Penyebab & Komponen Terkait</h6>
                        </div>
                        <div id="causesContainer" class="text-secondary" style="line-height: 1.75; font-size: 0.95rem;">
                            <!-- Injected via JS as clean readable paragraphs -->
                        </div>
                    </div>
                </div>

                <!-- 3. Estimated Cost Block -->
                <div class="card border border-success-subtle shadow-xs mb-3 bg-white rounded-3">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-light-subtle">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-cash-stack text-success fs-5 me-2"></i>
                                <h6 class="fw-bold text-dark mb-0">3. Estimasi Perkiraan Biaya Perbaikan</h6>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small fw-semibold d-none d-sm-inline-block">
                                <i class="bi bi-shield-check me-1"></i>Estimasi AI
                            </span>
                        </div>

                        <!-- Price Highlight Box -->
                        <div class="d-flex flex-wrap align-items-center justify-content-between p-3 rounded-3 mb-3 border border-success-subtle" style="background-color: #f0fdf4;">
                            <div>
                                <span class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">Kisaran Biaya Servis & Suku Cadang</span>
                                <h4 id="costRangeHighlight" class="fw-bold text-success mb-0">Rp 100.000 - Rp 250.000</h4>
                            </div>
                            <div class="mt-2 mt-sm-0">
                                <span class="badge bg-success text-white px-3 py-2 fs-6 shadow-xs">
                                    <i class="bi bi-tag-fill me-1"></i>Transparan & Terjangkau
                                </span>
                            </div>
                        </div>

                        <!-- Price Details Narrative -->
                        <div id="costDetailContainer" class="text-secondary mb-3" style="line-height: 1.75; font-size: 0.95rem;">
                            <!-- Injected via JS as clean readable paragraphs -->
                        </div>

                        <!-- Note Disclaimer -->
                        <div class="small text-muted d-flex align-items-start pt-2 border-top border-light-subtle">
                            <i class="bi bi-info-circle text-primary me-2 mt-1 fs-6"></i>
                            <span>*Estimasi ini merupakan perkiraan berdasarkan standar jasa HomeCare Service dan suku cadang terkait. Biaya final akan disepakati bersama setelah teknisi melakukan pemeriksaan langsung di tempat.</span>
                        </div>
                    </div>
                </div>

                <!-- 4. Safe Action Guide Block -->
                <div class="card border border-info-subtle shadow-xs mb-3 bg-white rounded-3">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center mb-2 pb-2 border-bottom border-light-subtle">
                            <i class="bi bi-shield-check text-info fs-5 me-2"></i>
                            <h6 class="fw-bold text-dark mb-0">4. Panduan Penanganan Awal yang Aman di Rumah</h6>
                        </div>
                        <div id="recommendationsContainer" class="text-secondary" style="line-height: 1.75; font-size: 0.95rem;">
                            <!-- Injected via JS as clean readable paragraphs -->
                        </div>
                    </div>
                </div>

                <!-- 5. Technician Recommendation Block -->
                <div class="card border border-primary-subtle shadow-xs mb-3 bg-primary-subtle bg-opacity-25 rounded-3">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-tools text-primary fs-5 me-2"></i>
                            <h6 class="fw-bold text-primary-emphasis mb-0">5. Saran & Rekomendasi Pemeriksaan Teknisi</h6>
                        </div>
                        <div id="technicianAdviceText" class="text-dark" style="line-height: 1.75; font-size: 0.95rem;">
                            -
                        </div>
                    </div>
                </div>

                <!-- 6. Disclaimer Notice -->
                <div class="alert alert-light border text-muted py-2 px-3 small mb-4 rounded-3 d-flex align-items-start">
                    <i class="bi bi-info-circle text-secondary me-2 mt-1 fs-6"></i>
                    <span id="disclaimerText">Hasil ini merupakan diagnosis awal dari AI dan bukan diagnosis teknisi pasti. Pemeriksaan fisik langsung oleh teknisi tetap diperlukan.</span>
                </div>

                <!-- 7. Proceed to Order Button -->
                <form action="<?= BASE_URL ?>/ai-diagnosis/proceed" method="POST" id="proceedForm">
                    <input type="hidden" name="raw_diagnosis" id="proceed_raw_diagnosis" value="">
                    <input type="hidden" name="equipment_type" id="proceed_equipment_type" value="">
                    <input type="hidden" name="brand" id="proceed_brand" value="">
                    <input type="hidden" name="complaint" id="proceed_complaint" value="">
                    <input type="hidden" name="estimated_cost" id="proceed_estimated_cost" value="">

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg shadow-sm fw-bold py-3 fs-6">
                            <i class="bi bi-calendar-plus me-2"></i>Pesan Teknisi dengan Hasil Diagnosa Ini
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

<script>
function handleEquipmentChange(val) {
    const customContainer = document.getElementById('custom_eq_container');
    const customInput = document.getElementById('custom_equipment_type');
    if (val === 'Lainnya') {
        customContainer.classList.remove('d-none');
        customInput.setAttribute('required', 'required');
    } else {
        customContainer.classList.add('d-none');
        customInput.removeAttribute('required');
    }
}

function fillExample(eq, brand, complaint, symptoms) {
    const eqSelect = document.getElementById('equipment_type');
    eqSelect.value = eq;
    handleEquipmentChange(eq);
    document.getElementById('brand').value = brand;
    document.getElementById('complaint').value = complaint;
    document.getElementById('symptoms').value = symptoms;
}

async function handleDiagnosisSubmit(e) {
    e.preventDefault();

    let eqType = document.getElementById('equipment_type').value;
    if (eqType === 'Lainnya') {
        eqType = document.getElementById('custom_equipment_type').value.trim();
    }
    const brand = document.getElementById('brand').value.trim();
    const complaint = document.getElementById('complaint').value.trim();
    const symptoms = document.getElementById('symptoms').value.trim();

    if (!eqType || !complaint) {
        alert('Silakan pilih jenis peralatan dan isi keluhan kerusakan.');
        return;
    }

    // UI Loading state
    document.getElementById('aiPlaceholder').classList.add('d-none');
    document.getElementById('aiResultContainer').classList.add('d-none');
    document.getElementById('aiLoading').classList.remove('d-none');
    
    const btnDiagnose = document.getElementById('btnDiagnose');
    btnDiagnose.disabled = true;
    btnDiagnose.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Menganalisis...';

    const formData = new FormData();
    formData.append('equipment_type', eqType);
    formData.append('brand', brand);
    formData.append('complaint', complaint);
    formData.append('symptoms', symptoms);

    try {
        const response = await fetch('<?= BASE_URL ?>/ai-diagnosis/diagnose', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success && result.data) {
            renderDiagnosisResult(result.data, eqType, brand, complaint);
        } else {
            alert(result.message || 'Gagal memproses diagnosis AI.');
            document.getElementById('aiPlaceholder').classList.remove('d-none');
        }
    } catch (err) {
        alert('Terjadi kesalahan koneksi saat menghubungi server.');
        document.getElementById('aiPlaceholder').classList.remove('d-none');
    } finally {
        document.getElementById('aiLoading').classList.add('d-none');
        btnDiagnose.disabled = false;
        btnDiagnose.innerHTML = '<i class="bi bi-cpu me-2"></i>Analisis dengan AI';
    }
}

function renderDiagnosisResult(data, eqType, brand, complaint) {
    // 1. Summary info & Priority badge
    const summaryInfo = document.getElementById('summaryEquipmentInfo');
    summaryInfo.textContent = eqType + (brand ? ' (' + brand + ')' : '');

    const priorityBadge = document.getElementById('priorityBadge');
    const priority = (data.tingkat_prioritas || 'Sedang').toUpperCase();
    priorityBadge.textContent = 'Prioritas: ' + priority;
    
    if (priority === 'TINGGI' || priority === 'MENDESAK') {
        priorityBadge.className = 'badge bg-danger text-white fs-6 px-3 py-2 shadow-xs';
    } else if (priority === 'SEDANG') {
        priorityBadge.className = 'badge bg-warning text-dark fs-6 px-3 py-2 shadow-xs';
    } else {
        priorityBadge.className = 'badge bg-success text-white fs-6 px-3 py-2 shadow-xs';
    }

    // 2. Analysis & Causes Blocks
    const analysisText = data.penjelasan_analisis || data.kemungkinan_penyebab || '';
    let causesText = data.kemungkinan_penyebab || '';
    
    // If analysis and causes are identical or causes is empty, provide appropriate display
    if (!data.penjelasan_analisis && data.kemungkinan_penyebab) {
        document.getElementById('analysisContainer').innerHTML = formatReadableParagraphs(data.kemungkinan_penyebab);
        document.getElementById('causesContainer').innerHTML = formatReadableParagraphs("Berdasarkan gejala di atas, kerusakan berkaitan dengan keausan atau penurunan performa komponen internal perangkat.");
    } else {
        document.getElementById('analysisContainer').innerHTML = formatReadableParagraphs(analysisText);
        document.getElementById('causesContainer').innerHTML = formatReadableParagraphs(causesText);
    }

    // 3. Estimated Cost Block
    const costRange = data.estimasi_biaya || 'Rp 100.000 - Rp 250.000';
    const costDetail = data.rincian_biaya || 'Estimasi biaya mencakup jasa pemeriksaan teknisi dan kisaran penggantian komponen suku cadang standar.';
    document.getElementById('costRangeHighlight').textContent = costRange;
    document.getElementById('costDetailContainer').innerHTML = formatReadableParagraphs(costDetail);

    // 4. Recommendations Block
    const recText = data.rekomendasi_tindakan || '';
    document.getElementById('recommendationsContainer').innerHTML = formatReadableParagraphs(recText);

    // 5. Technician Advice & Disclaimer
    const techText = data.perlu_teknisi || 'Disarankan melakukan pemeriksaan langsung oleh teknisi profesional untuk penanganan yang tepat dan aman.';
    document.getElementById('technicianAdviceText').innerHTML = formatReadableParagraphs(techText);
    document.getElementById('disclaimerText').textContent = data.disclaimer || 'Hasil ini merupakan diagnosis awal dari AI dan bukan diagnosis teknisi pasti. Pemeriksaan fisik langsung oleh teknisi tetap diperlukan.';

    // 6. Setup Proceed Form
    document.getElementById('proceed_raw_diagnosis').value = data.raw_text || '';
    document.getElementById('proceed_equipment_type').value = eqType;
    document.getElementById('proceed_brand').value = brand;
    document.getElementById('proceed_complaint').value = complaint;
    document.getElementById('proceed_estimated_cost').value = costRange;

    // Show result container and smooth scroll
    const resContainer = document.getElementById('aiResultContainer');
    resContainer.classList.remove('d-none');
    resContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Format raw text into comfortable, bite-sized, readable paragraphs
 */
function formatReadableParagraphs(content) {
    if (!content) return '<p class="mb-0 text-muted fst-italic">Tidak ada informasi tambahan.</p>';
    
    // If array, clean each element and render as distinct paragraphs
    if (Array.isArray(content)) {
        return content.map((item, idx) => {
            const isLast = idx === content.length - 1;
            return `<p class="${isLast ? 'mb-0' : 'mb-2'} text-dark" style="line-height: 1.75;">${escapeHtml(item)}</p>`;
        }).join('');
    }
    
    const text = String(content).trim();

    // If explicit double line-breaks exist
    let parts = text.split(/\n\s*\n/).map(p => p.trim()).filter(p => p.length > 0);
    
    // If it's a single giant paragraph (> 240 chars), split by sentences into 2 lighter paragraphs
    if (parts.length === 1 && text.length > 240) {
        // Split on period followed by space and uppercase letter
        const sentences = text.match(/[^.!?]+[.!?]+(?:\s|$)/g) || [text];
        if (sentences.length >= 3) {
            const mid = Math.ceil(sentences.length / 2);
            const p1 = sentences.slice(0, mid).join('').trim();
            const p2 = sentences.slice(mid).join('').trim();
            parts = [p1, p2];
        }
    }
    
    return parts.map((p, idx) => {
        const isLast = idx === parts.length - 1;
        return `<p class="${isLast ? 'mb-0' : 'mb-2'} text-dark" style="line-height: 1.75;">${escapeHtml(p).replace(/\n/g, '<br>')}</p>`;
    }).join('');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
