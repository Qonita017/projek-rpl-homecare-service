<?php
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Helper to mask API key for security preview
$maskedKey = '';
if (!empty($active_api_key)) {
    $len = strlen($active_api_key);
    if ($len > 8) {
        $maskedKey = substr($active_api_key, 0, 6) . str_repeat('•', min(18, $len - 10)) . substr($active_api_key, -4);
    } else {
        $maskedKey = str_repeat('•', $len);
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Konfigurasi AI Chatbot</li>
            </ol>
        </nav>
        <h1 class="h2 fw-bold text-dark mb-0">
            <i class="bi bi-sliders2-vertical text-primary me-2"></i>Konfigurasi AI Chatbot & Model
        </h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="<?= BASE_URL ?>/admin/ai-chat" class="btn btn-outline-primary">
            <i class="bi bi-robot me-1"></i>Buka Chatbot Assistant
        </a>
    </div>
</div>

<?php if ($flashSuccess): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i>
        <span><?= $flashSuccess ?></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($flashError): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5 align-middle"></i>
        <span><?= $flashError ?></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Kolom Kiri: Form Konfigurasi Utama -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold text-primary d-flex align-items-center gap-2">
                    <i class="bi bi-gear-wide-connected"></i>
                    <span>Pengaturan API Key & Pemilihan Model</span>
                </h5>
                <small class="text-muted">Ganti API key, pilih provider, dan tentukan model LLM yang akan digunakan oleh Chatbot dan Fitur AI Diagnosis.</small>
            </div>
            <div class="card-body p-4">
                <form id="aiConfigForm" action="<?= BASE_URL ?>/admin/ai-setting/update" method="POST">
                    
                    <!-- 1. Pilihan Provider AI -->
                    <div class="mb-4">
                        <label class="form-label fw-bold d-block mb-2">
                            1. Pilih Provider AI:
                        </label>
                        <div class="row g-2">
                            <!-- NVIDIA NIM -->
                            <div class="col-sm-6 col-md-3">
                                <input type="radio" class="btn-check" name="provider" id="provider_nvidia" value="nvidia" <?= $active_provider === 'nvidia' ? 'checked' : '' ?> autocomplete="off">
                                <label class="btn btn-outline-primary w-100 text-start p-3 h-100 rounded-3 provider-card" for="provider_nvidia">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="d-block">NVIDIA NIM</strong>
                                        <span class="badge bg-success bg-opacity-75">Bawaan</span>
                                    </div>
                                    <small class="text-muted d-block" style="font-size: 0.78rem;">Llama 3.2 Vision, Nemotron, Mixtral</small>
                                </label>
                            </div>

                            <!-- Google Gemini -->
                            <div class="col-sm-6 col-md-3">
                                <input type="radio" class="btn-check" name="provider" id="provider_gemini" value="gemini" <?= $active_provider === 'gemini' ? 'checked' : '' ?> autocomplete="off">
                                <label class="btn btn-outline-primary w-100 text-start p-3 h-100 rounded-3 provider-card" for="provider_gemini">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="d-block">Google Gemini</strong>
                                        <span class="badge bg-primary bg-opacity-75">Gratis</span>
                                    </div>
                                    <small class="text-muted d-block" style="font-size: 0.78rem;">Gemini 1.5 Flash, 1.5 Pro, 2.0</small>
                                </label>
                            </div>

                            <!-- OpenAI -->
                            <div class="col-sm-6 col-md-3">
                                <input type="radio" class="btn-check" name="provider" id="provider_openai" value="openai" <?= $active_provider === 'openai' ? 'checked' : '' ?> autocomplete="off">
                                <label class="btn btn-outline-primary w-100 text-start p-3 h-100 rounded-3 provider-card" for="provider_openai">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="d-block">OpenAI</strong>
                                        <span class="badge bg-dark bg-opacity-75">ChatGPT</span>
                                    </div>
                                    <small class="text-muted d-block" style="font-size: 0.78rem;">GPT-4o Mini, GPT-4o, GPT-3.5</small>
                                </label>
                            </div>

                            <!-- Groq Cloud -->
                            <div class="col-sm-6 col-md-3">
                                <input type="radio" class="btn-check" name="provider" id="provider_groq" value="groq" <?= $active_provider === 'groq' ? 'checked' : '' ?> autocomplete="off">
                                <label class="btn btn-outline-primary w-100 text-start p-3 h-100 rounded-3 provider-card" for="provider_groq">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="d-block">Groq LPU</strong>
                                        <span class="badge bg-warning text-dark">Super Cepat</span>
                                    </div>
                                    <small class="text-muted d-block" style="font-size: 0.78rem;">Llama 3.3 70B, Llama 3.1 8B</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Input API Key -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="apiKeyInput" class="form-label fw-bold mb-0">
                                2. API Key Chatbot:
                            </label>
                            <span id="providerGuideLink">
                                <!-- Link dinamis sesuai provider -->
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-key-fill"></i></span>
                            <input type="password" class="form-control font-monospace" id="apiKeyInput" name="api_key" value="<?= htmlspecialchars($active_api_key) ?>" placeholder="Masukkan API Key (misal: nvapi-... atau AIzaSy... atau sk-...)" required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleApiKeyBtn" title="Lihat/Sembunyikan API Key">
                                <i class="bi bi-eye" id="toggleEyeIcon"></i>
                            </button>
                            <button class="btn btn-primary" type="button" id="btnFetchModels" title="Hubungkan ke API dan ambil model yang tersedia">
                                <span class="spinner-border spinner-border-sm d-none" id="fetchSpinner" role="status"></span>
                                <i class="bi bi-cloud-arrow-down-fill me-1" id="fetchIcon"></i>
                                <span>Cek Model API</span>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted" id="keyHelperText">
                                Masukkan API key baru untuk mengganti, atau biarkan yang lama jika tidak ingin mengubah.
                            </small>
                            <span class="badge bg-light text-secondary border" id="fetchStatusBadge" style="display: none;"></span>
                        </div>
                        <div id="keyFormatWarning" class="alert alert-warning py-2 px-3 small mt-2 d-none shadow-sm" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <span id="keyFormatWarningText"></span>
                        </div>
                    </div>

                    <!-- 3. Pemilihan Model (Modeling) -->
                    <div class="mb-4">
                        <label for="modelSelect" class="form-label fw-bold d-flex justify-content-between align-items-center">
                            <span>3. Pilih Modeling (Model AI):</span>
                            <span class="badge bg-info text-dark" id="modelSourceBadge">Model Standar Rekomendasi</span>
                        </label>
                        <select class="form-select form-select-lg" id="modelSelect" name="model_choice">
                            <!-- Diisi secara dinamis oleh JavaScript -->
                        </select>
                        <div class="form-text">
                            Model ini akan memproses percakapan tanya-jawab dengan pelanggan, memberikan konsultasi kerusakan, serta mendiagnosis peralatan.
                        </div>

                        <!-- Input Model Kustom (Jika memilih custom) -->
                        <div class="mt-3" id="customModelGroup" style="display: none;">
                            <label for="customModelInput" class="form-label fw-semibold text-secondary small">
                                <i class="bi bi-pencil-square me-1"></i>Ketik Nama Model Kustom Secara Manual:
                            </label>
                            <input type="text" class="form-control font-monospace" id="customModelInput" name="custom_model" placeholder="Contoh: meta/llama-3.1-70b-instruct atau gemini-1.5-pro">
                        </div>
                    </div>

                    <!-- 4. Parameter Lanjutan (Opsional) -->
                    <div class="accordion mb-4" id="accordionAdvanced">
                        <div class="accordion-item border rounded-3 overflow-hidden">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-2 px-3 bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvanced">
                                    <i class="bi bi-sliders me-2 text-primary"></i>
                                    <span class="fw-semibold">Pengaturan Tingkat Lanjut (Temperature & Token Limit)</span>
                                </button>
                            </h2>
                            <div id="collapseAdvanced" class="accordion-collapse collapse" data-bs-parent="#accordionAdvanced">
                                <div class="accordion-body p-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="tempInput" class="form-label fw-semibold small d-flex justify-content-between">
                                                <span>Temperature (Kreativitas AI):</span>
                                                <span class="badge bg-primary" id="tempValueBadge"><?= htmlspecialchars($temperature) ?></span>
                                            </label>
                                            <input type="range" class="form-range" id="tempInput" name="temperature" min="0" max="1" step="0.05" value="<?= htmlspecialchars($temperature) ?>" oninput="document.getElementById('tempValueBadge').innerText = this.value">
                                            <div class="d-flex justify-content-between text-muted" style="font-size: 0.7rem;">
                                                <span>0.0 (Presisi/Faktual)</span>
                                                <span>0.3 (Default Disarankan)</span>
                                                <span>1.0 (Kreatif)</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="tokenInput" class="form-label fw-semibold small">Maksimal Output Tokens:</label>
                                            <input type="number" class="form-control" id="tokenInput" name="max_tokens" min="200" max="4000" step="50" value="<?= htmlspecialchars($max_tokens ?? $maxTokens ?? '900') ?>">
                                            <small class="text-muted" style="font-size: 0.75rem;">Membatasi panjang respon teks (standar: 900 tokens).</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Simpan & Tes -->
                    <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-check-circle-fill me-2"></i>Simpan Konfigurasi
                        </button>
                        <button type="button" class="btn btn-outline-success px-4 py-2 fw-semibold" id="btnTestConnection">
                            <span class="spinner-border spinner-border-sm d-none" id="testSpinner" role="status"></span>
                            <i class="bi bi-lightning-charge-fill me-1" id="testIcon"></i>
                            <span>Tes Koneksi AI</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Status & Panduan Dapatkan Key -->
    <div class="col-lg-4">
        <!-- Card Status Aktif Saat Ini -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-info"></i>
                    <span>Status Engine AI Saat Ini</span>
                </h6>
            </div>
            <div class="card-body p-3">
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted small">Provider Aktif:</span>
                        <span class="badge bg-primary text-uppercase"><?= htmlspecialchars($active_provider) ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted small">Model Aktif:</span>
                        <code class="text-dark small text-truncate" style="max-width: 170px;" title="<?= htmlspecialchars($active_model) ?>">
                            <?= htmlspecialchars($active_model) ?>
                        </code>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted small">Status Key:</span>
                        <?php if(!empty($active_api_key)): ?>
                            <span class="badge bg-success"><i class="bi bi-shield-check me-1"></i>Tersimpan</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i>Belum Diisi</span>
                        <?php endif; ?>
                    </li>
                    <?php if(!empty($maskedKey)): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted small">Preview Key:</span>
                        <span class="font-monospace small text-muted"><?= htmlspecialchars($maskedKey) ?></span>
                    </li>
                    <?php endif; ?>
                </ul>

                <!-- Box Hasil Tes Koneksi (Dinamis via JS) -->
                <div id="testResultBox" class="p-3 rounded-3 bg-light border" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="small" id="testResultTitle">Hasil Uji Respon:</strong>
                        <span class="badge" id="testResultBadge">-</span>
                    </div>
                    <div class="small text-muted mb-1" id="testLatencyText"></div>
                    <div class="p-2 bg-white rounded border small font-monospace text-break" id="testReplyText" style="max-height: 120px; overflow-y: auto;">
                        -
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Panduan Cara Mendapatkan API Key Gratis -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-patch-question-fill text-warning"></i>
                    <span>Panduan Dapatkan API Key</span>
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="accordion accordion-flush" id="accordionKeyGuides">
                    <!-- Google Gemini -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-0 fw-semibold text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#guideGemini">
                                <i class="bi bi-google me-2"></i>Google Gemini (Gratis)
                            </button>
                        </h2>
                        <div id="guideGemini" class="accordion-collapse collapse show" data-bs-parent="#accordionKeyGuides">
                            <div class="accordion-body px-0 py-2 small text-muted">
                                <ol class="ps-3 mb-2">
                                    <li>Kunjungi <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener">Google AI Studio <i class="bi bi-box-arrow-up-right"></i></a>.</li>
                                    <li>Login dengan akun Google Anda.</li>
                                    <li>Klik tombol <strong>"Create API key"</strong>.</li>
                                    <li>Salin kuncinya dan tempel pada form di samping.</li>
                                </ol>
                                <span class="badge bg-success bg-opacity-75">Sangat Direkomendasikan & Gratis Kuota Banyak</span>
                            </div>
                        </div>
                    </div>

                    <!-- NVIDIA NIM -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-0 fw-semibold text-success" type="button" data-bs-toggle="collapse" data-bs-target="#guideNvidia">
                                <i class="bi bi-cpu me-2"></i>NVIDIA NIM (1000 Credits)
                            </button>
                        </h2>
                        <div id="guideNvidia" class="accordion-collapse collapse" data-bs-parent="#accordionKeyGuides">
                            <div class="accordion-body px-0 py-2 small text-muted">
                                <ol class="ps-3 mb-2">
                                    <li>Buka <a href="https://build.nvidia.com" target="_blank" rel="noopener">build.nvidia.com <i class="bi bi-box-arrow-up-right"></i></a>.</li>
                                    <li>Pilih model (misal: <code>meta/llama-3.2-11b-vision-instruct</code>).</li>
                                    <li>Klik <strong>"Get API Key"</strong> dan salin token <code>nvapi-...</code>.</li>
                                </ol>
                                <span class="badge bg-secondary">Akurasi tinggi untuk diagnosis servis</span>
                            </div>
                        </div>
                    </div>

                    <!-- Groq -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-0 fw-semibold text-warning" type="button" data-bs-toggle="collapse" data-bs-target="#guideGroq">
                                <i class="bi bi-lightning-charge me-2"></i>Groq LPU (Ultra Fast)
                            </button>
                        </h2>
                        <div id="guideGroq" class="accordion-collapse collapse" data-bs-parent="#accordionKeyGuides">
                            <div class="accordion-body px-0 py-2 small text-muted">
                                <ol class="ps-3 mb-2">
                                    <li>Kunjungi <a href="https://console.groq.com/keys" target="_blank" rel="noopener">Groq Console <i class="bi bi-box-arrow-up-right"></i></a>.</li>
                                    <li>Buat akun gratis dan klik <strong>"Create API Key"</strong>.</li>
                                    <li>Salin token <code>gsk_...</code> lalu pilih model <code>llama-3.3-70b-versatile</code>.</li>
                                </ol>
                                <span class="badge bg-warning text-dark">Kecepatan 500+ token per detik</span>
                            </div>
                        </div>
                    </div>

                    <!-- OpenAI -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-2 px-0 fw-semibold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#guideOpenAI">
                                <i class="bi bi-chat-square-text me-2"></i>OpenAI ChatGPT
                            </button>
                        </h2>
                        <div id="guideOpenAI" class="accordion-collapse collapse" data-bs-parent="#accordionKeyGuides">
                            <div class="accordion-body px-0 py-2 small text-muted">
                                <ol class="ps-3 mb-2">
                                    <li>Kunjungi <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener">platform.openai.com <i class="bi bi-box-arrow-up-right"></i></a>.</li>
                                    <li>Buat secret key baru (berawalan <code>sk-proj-...</code>).</li>
                                    <li>Model rekomendasi hemat & cerdas: <code>gpt-4o-mini</code>.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Data Preset Models dari Backend PHP
const PRESET_MODELS = <?= json_encode($preset_models) ?>;
const CURRENT_ACTIVE_MODEL = <?= json_encode($active_model) ?>;
const PROVIDER_KEYS = <?= json_encode($provider_keys ?? []) ?>;
const BASE_URL = '<?= BASE_URL ?>';

document.addEventListener('DOMContentLoaded', function() {
    const providerRadios = document.querySelectorAll('input[name="provider"]');
    const modelSelect = document.getElementById('modelSelect');
    const customModelGroup = document.getElementById('customModelGroup');
    const customModelInput = document.getElementById('customModelInput');
    const apiKeyInput = document.getElementById('apiKeyInput');
    const toggleApiKeyBtn = document.getElementById('toggleApiKeyBtn');
    const toggleEyeIcon = document.getElementById('toggleEyeIcon');
    const btnFetchModels = document.getElementById('btnFetchModels');
    const fetchSpinner = document.getElementById('fetchSpinner');
    const fetchIcon = document.getElementById('fetchIcon');
    const fetchStatusBadge = document.getElementById('fetchStatusBadge');
    const modelSourceBadge = document.getElementById('modelSourceBadge');
    const providerGuideLink = document.getElementById('providerGuideLink');
    const btnTestConnection = document.getElementById('btnTestConnection');
    const testSpinner = document.getElementById('testSpinner');
    const testIcon = document.getElementById('testIcon');
    const testResultBox = document.getElementById('testResultBox');
    const keyFormatWarning = document.getElementById('keyFormatWarning');
    const keyFormatWarningText = document.getElementById('keyFormatWarningText');

    let currentSelectedProvider = document.querySelector('input[name="provider"]:checked')?.value || 'nvidia';

    // 1. Toggle Show/Hide API Key
    toggleApiKeyBtn.addEventListener('click', function() {
        if (apiKeyInput.type === 'password') {
            apiKeyInput.type = 'text';
            toggleEyeIcon.classList.remove('bi-eye');
            toggleEyeIcon.classList.add('bi-eye-slash');
        } else {
            apiKeyInput.type = 'password';
            toggleEyeIcon.classList.remove('bi-eye-slash');
            toggleEyeIcon.classList.add('bi-eye');
        }
    });

    // 2. Deteksi & Peringatan Format Key
    function validateKeyPrefix(provider, key) {
        if (!key || key.length === 0) {
            keyFormatWarning.classList.add('d-none');
            return null;
        }

        let detected = null;
        if (key.startsWith('nvapi-')) detected = 'NVIDIA NIM';
        else if (key.startsWith('gsk_')) detected = 'Groq Cloud';
        else if (key.startsWith('sk-')) detected = 'OpenAI';
        else if (key.startsWith('AIza') || key.startsWith('AQ.')) detected = 'Google Gemini';

        if (detected) {
            if (provider === 'nvidia' && detected !== 'NVIDIA NIM') {
                return `Perhatian: Key yang Anda ketik/tempel terdeteksi sebagai format API Key <strong>${detected}</strong>, bukan untuk NVIDIA NIM. Pastikan memasukkan key yang diawali dengan <code>nvapi-...</code>.`;
            }
            if (provider === 'gemini' && detected !== 'Google Gemini') {
                return `Perhatian: Key yang Anda ketik/tempel terdeteksi sebagai format API Key <strong>${detected}</strong>, bukan untuk Google Gemini. Pastikan memasukkan key Gemini dari Google AI Studio (diawali <code>AIzaSy...</code> atau <code>AQ.</code>).`;
            }
            if (provider === 'groq' && detected !== 'Groq Cloud') {
                return `Perhatian: Key yang Anda ketik/tempel terdeteksi sebagai format API Key <strong>${detected}</strong>, bukan untuk Groq LPU. Key Groq diawali dengan <code>gsk_...</code>.`;
            }
            if (provider === 'openai' && detected !== 'OpenAI') {
                return `Perhatian: Key yang Anda ketik/tempel terdeteksi sebagai format API Key <strong>${detected}</strong>, bukan untuk OpenAI. Key OpenAI diawali dengan <code>sk-...</code>.`;
            }
        }

        keyFormatWarning.classList.add('d-none');
        return null;
    }

    function updateFormatWarning() {
        const warning = validateKeyPrefix(currentSelectedProvider, apiKeyInput.value.trim());
        if (warning) {
            keyFormatWarningText.innerHTML = warning;
            keyFormatWarning.classList.remove('d-none');
        } else {
            keyFormatWarning.classList.add('d-none');
        }
    }

    apiKeyInput.addEventListener('input', updateFormatWarning);

    // 3. Fungsi render dropdown pilihan model
    function renderModelDropdown(modelsList, selectedValue = null, isApiSource = false) {
        modelSelect.innerHTML = '';

        const optGroup = document.createElement('optgroup');
        optGroup.label = isApiSource ? 'Daftar Model dari API Key Anda' : 'Model Rekomendasi Teruji';

        let hasSelected = false;
        modelsList.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = m.name || m.id;
            if (m.badge) {
                opt.textContent += ` [${m.badge}]`;
            }
            if (selectedValue && m.id === selectedValue) {
                opt.selected = true;
                hasSelected = true;
            }
            optGroup.appendChild(opt);
        });

        modelSelect.appendChild(optGroup);

        // Tambahkan opsi kustom di bagian bawah
        const customGroup = document.createElement('optgroup');
        customGroup.label = 'Opsi Tambahan';
        const customOpt = document.createElement('option');
        customOpt.value = 'custom';
        customOpt.textContent = '✍️ Input Nama Model Lain Secara Manual...';
        
        if (!hasSelected && selectedValue) {
            customOpt.selected = true;
            customModelInput.value = selectedValue;
            customModelGroup.style.display = 'block';
        } else {
            customModelGroup.style.display = 'none';
        }
        customGroup.appendChild(customOpt);
        modelSelect.appendChild(customGroup);

        if (isApiSource) {
            modelSourceBadge.className = 'badge bg-success';
            modelSourceBadge.innerHTML = '<i class="bi bi-cloud-check-fill me-1"></i>' + modelsList.length + ' Model Langsung dari API';
        } else {
            modelSourceBadge.className = 'badge bg-info text-dark';
            modelSourceBadge.textContent = 'Model Standar Rekomendasi';
        }
    }

    // 4. Update tampilan ketika provider berganti
    function updateProviderView(provider, retainModel = true) {
        // Reset box hasil uji lama saat berganti provider
        testResultBox.style.display = 'none';

        const presets = PRESET_MODELS[provider] || [];
        const currentModel = retainModel ? CURRENT_ACTIVE_MODEL : (presets[0]?.id || '');
        renderModelDropdown(presets, currentModel, false);

        // Update placeholder & isi API key sesuai provider
        if (PROVIDER_KEYS[provider] !== undefined) {
            apiKeyInput.value = PROVIDER_KEYS[provider];
        }

        let guideHtml = '';
        if (provider === 'gemini') {
            guideHtml = '<a href="https://aistudio.google.com/app/apikey" target="_blank" class="small text-decoration-none fw-semibold"><i class="bi bi-box-arrow-up-right me-1"></i>Dapatkan Key Gemini Gratis</a>';
            apiKeyInput.placeholder = "Masukkan Gemini API Key (misal: AIzaSy... atau AQ....)";
        } else if (provider === 'nvidia') {
            guideHtml = '<a href="https://build.nvidia.com" target="_blank" class="small text-decoration-none fw-semibold"><i class="bi bi-box-arrow-up-right me-1"></i>Dapatkan Key di NVIDIA Build</a>';
            apiKeyInput.placeholder = "Masukkan NVIDIA NIM API Key (misal: nvapi-...)";
        } else if (provider === 'groq') {
            guideHtml = '<a href="https://console.groq.com/keys" target="_blank" class="small text-decoration-none fw-semibold"><i class="bi bi-box-arrow-up-right me-1"></i>Dapatkan Key di Groq Console</a>';
            apiKeyInput.placeholder = "Masukkan Groq API Key (misal: gsk_...)";
        } else {
            guideHtml = '<a href="https://platform.openai.com/api-keys" target="_blank" class="small text-decoration-none fw-semibold"><i class="bi bi-box-arrow-up-right me-1"></i>Dapatkan Key di OpenAI</a>';
            apiKeyInput.placeholder = "Masukkan OpenAI API Key (misal: sk-...)";
        }
        providerGuideLink.innerHTML = guideHtml;
        fetchStatusBadge.style.display = 'none';

        updateFormatWarning();
    }

    // Event listener pergantian provider radio
    providerRadios.forEach(r => {
        r.addEventListener('change', function() {
            if (this.checked) {
                // Simpan key yang sedang diketik untuk provider sebelumnya
                PROVIDER_KEYS[currentSelectedProvider] = apiKeyInput.value.trim();
                currentSelectedProvider = this.value;
                updateProviderView(this.value, false);
            }
        });
    });

    // Event listener pilihan model dropdown
    modelSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customModelGroup.style.display = 'block';
            customModelInput.focus();
        } else {
            customModelGroup.style.display = 'none';
        }
    });

    // Inisialisasi awal
    updateProviderView(currentSelectedProvider, true);

    // 5. Fitur Cek & Ambil Model Langsung dari API Key
    btnFetchModels.addEventListener('click', function() {
        const apiKey = apiKeyInput.value.trim();

        const warning = validateKeyPrefix(currentSelectedProvider, apiKey);
        if (warning) {
            keyFormatWarningText.innerHTML = warning;
            keyFormatWarning.classList.remove('d-none');
        }

        fetchSpinner.classList.remove('d-none');
        fetchIcon.classList.add('d-none');
        btnFetchModels.disabled = true;
        fetchStatusBadge.style.display = 'inline-block';
        fetchStatusBadge.className = 'badge bg-warning text-dark';
        fetchStatusBadge.textContent = 'Menghubungi API ' + currentSelectedProvider.toUpperCase() + '...';

        const formData = new FormData();
        formData.append('provider', currentSelectedProvider);
        formData.append('api_key', apiKey);

        fetch(BASE_URL + '/admin/ai-setting/fetch-models', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            fetchSpinner.classList.add('d-none');
            fetchIcon.classList.remove('d-none');
            btnFetchModels.disabled = false;

            if (data.success && data.models && data.models.length > 0) {
                renderModelDropdown(data.models, data.models[0].id, data.source === 'api');
                fetchStatusBadge.className = 'badge bg-success';
                fetchStatusBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i>' + data.message;
            } else {
                fetchStatusBadge.className = 'badge bg-danger';
                fetchStatusBadge.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>' + (data.message || 'Gagal memuat model');
                if (data.fallback_models) {
                    renderModelDropdown(data.fallback_models, null, false);
                }
            }
        })
        .catch(err => {
            fetchSpinner.classList.add('d-none');
            fetchIcon.classList.remove('d-none');
            btnFetchModels.disabled = false;
            fetchStatusBadge.className = 'badge bg-danger';
            fetchStatusBadge.textContent = 'Koneksi error: ' + err.message;
        });
    });

    // 6. Fitur Tes Koneksi AI Langsung
    btnTestConnection.addEventListener('click', function() {
        const apiKey = apiKeyInput.value.trim();
        let selectedModel = modelSelect.value;
        if (selectedModel === 'custom') {
            selectedModel = customModelInput.value.trim();
        }

        const warning = validateKeyPrefix(currentSelectedProvider, apiKey);
        if (warning) {
            keyFormatWarningText.innerHTML = warning;
            keyFormatWarning.classList.remove('d-none');
        }

        testSpinner.classList.remove('d-none');
        testIcon.classList.add('d-none');
        btnTestConnection.disabled = true;
        testResultBox.style.display = 'block';
        document.getElementById('testResultTitle').textContent = 'Menguji Respon AI...';
        document.getElementById('testResultBadge').className = 'badge bg-warning text-dark';
        document.getElementById('testResultBadge').textContent = 'Loading...';
        document.getElementById('testLatencyText').textContent = 'Mengirim prompt uji ke server provider...';
        document.getElementById('testReplyText').innerHTML = 'Menghubungi <strong>' + currentSelectedProvider.toUpperCase() + '</strong> (' + selectedModel + ')...';

        const formData = new FormData();
        formData.append('provider', currentSelectedProvider);
        formData.append('api_key', apiKey);
        formData.append('model', selectedModel);

        fetch(BASE_URL + '/admin/ai-setting/test-connection', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            testSpinner.classList.add('d-none');
            testIcon.classList.remove('d-none');
            btnTestConnection.disabled = false;

            if (data.success) {
                document.getElementById('testResultTitle').textContent = 'Koneksi AI Berhasil!';
                document.getElementById('testResultBadge').className = 'badge bg-success';
                document.getElementById('testResultBadge').innerHTML = '<i class="bi bi-check-circle me-1"></i>OK (' + data.latency_ms + ' ms)';
                document.getElementById('testLatencyText').innerHTML = 'Provider: <strong>' + data.provider + '</strong> | Model: <code>' + data.model + '</code> | Kecepatan: <strong>' + data.latency_ms + ' ms</strong>';
                document.getElementById('testReplyText').innerHTML = '<strong>Balasan AI:</strong> ' + data.reply;
            } else {
                document.getElementById('testResultTitle').textContent = 'Koneksi Gagal!';
                document.getElementById('testResultBadge').className = 'badge bg-danger';
                document.getElementById('testResultBadge').innerHTML = '<i class="bi bi-x-circle me-1"></i>Error' + (data.latency_ms > 0 ? ' (' + data.latency_ms + ' ms)' : '');
                document.getElementById('testLatencyText').innerHTML = 'Provider: <strong>' + data.provider + '</strong> | Model: <code>' + data.model + '</code>';
                document.getElementById('testReplyText').innerHTML = '<span class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle-fill me-1"></i>' + data.message + '</span>';
            }
        })
        .catch(err => {
            testSpinner.classList.add('d-none');
            testIcon.classList.remove('d-none');
            btnTestConnection.disabled = false;
            document.getElementById('testResultTitle').textContent = 'Koneksi Error';
            document.getElementById('testResultBadge').className = 'badge bg-danger';
            document.getElementById('testResultBadge').textContent = 'Gagal';
            document.getElementById('testReplyText').innerHTML = '<span class="text-danger">Request gagal: ' + err.message + '</span>';
        });
    });
});
</script>

