<?php
// Helper to format chat message text (Markdown-like bold & newlines)
function formatChatMessage($text) {
    $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    // Convert **bold**
    $text = preg_replace('/\*\*(.*?)\*\*/su', '<strong>$1</strong>', $text);
    // Convert *italic*
    $text = preg_replace('/\*(.*?)\*/su', '<em>$1</em>', $text);
    // Convert lists starting with • or - safely with unicode modifier
    $text = preg_replace('/^[\x{2022}\-]\s*(.*)$/um', '• $1', $text);
    // Convert newlines to <br>
    return nl2br($text);
}
?>

<?php
$currentRole = $_SESSION['user_role'] ?? 'CUSTOMER';
$currentUserName = $_SESSION['user_name'] ?? 'Pengguna';
$dashUrl = ($currentRole === 'ADMIN') ? BASE_URL . '/admin/dashboard' : (($currentRole === 'TEKNISI') ? BASE_URL . '/technician/dashboard' : BASE_URL . '/customer/dashboard');
$dashLabel = ($currentRole === 'ADMIN') ? 'Dashboard Admin' : (($currentRole === 'TEKNISI') ? 'Dashboard Teknisi' : 'Dashboard');
$badgeRole = ($currentRole === 'ADMIN') ? 'Admin Assistant' : (($currentRole === 'TEKNISI') ? 'Teknisi Copilot' : 'Virtual CS & Diagnosis');
?>

<div class="container-fluid px-0 px-md-2 py-2">
    <!-- Header Title Bar -->
    <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= $dashUrl ?>"><?= $dashLabel ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">AI Chat Assistant</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 d-flex align-items-center gap-2">
                <span class="fs-2">🤖</span>
                <span>HomeCare AI Assistant</span>
                <span class="badge bg-primary text-white fs-6 fw-normal rounded-pill"><?= $badgeRole ?></span>
            </h1>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#chatHistoryOffcanvas">
                <i class="bi bi-clock-history me-1"></i> Riwayat Chat
            </button>
            <button type="button" class="btn btn-success fw-semibold rounded-pill px-3 shadow-sm" onclick="createNewSession()">
                <i class="bi bi-plus-lg me-1"></i> Chat Baru
            </button>
        </div>
    </div>

    <!-- Main Chat Grid -->
    <div class="row g-3">
        <!-- Sidebar Chat History (Desktop) -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="card shadow-sm border-0 h-100" style="min-height: 580px; max-height: calc(100vh - 180px);">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-chat-left-text me-2 text-primary"></i>Riwayat Chat Saya</h6>
                    <button class="btn btn-sm btn-outline-success rounded-circle" onclick="createNewSession()" title="Chat Baru">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
                <div class="card-body p-2 overflow-auto" id="sessionListDesktop">
                    <?php if (!empty($sessions)): ?>
                        <div class="list-group list-group-flush gap-1">
                            <?php foreach ($sessions as $sess): ?>
                                <div class="list-group-item list-group-item-action border-0 rounded-3 p-2.5 position-relative <?= ($sess['id'] == $activeSessionId) ? 'active bg-primary-subtle text-primary fw-bold' : '' ?>" id="session_item_<?= $sess['id'] ?>">
                                    <a href="<?= BASE_URL ?>/customer/ai-chat?session_id=<?= $sess['id'] ?>" class="text-decoration-none text-reset d-block pe-4">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <i class="bi bi-chat-dots fs-5 <?= ($sess['id'] == $activeSessionId) ? 'text-primary' : 'text-secondary' ?>"></i>
                                            <span class="text-truncate session-title-text" style="max-width: 170px;"><?= htmlspecialchars($sess['title']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center small opacity-75">
                                            <span><i class="bi bi-clock me-1"></i><?= date('d M, H:i', strtotime($sess['updated_at'])) ?></span>
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill"><?= $sess['message_count'] ?? 0 ?> pesan</span>
                                        </div>
                                    </a>
                                    <button type="button" class="btn btn-sm text-danger position-absolute top-50 end-0 translate-middle-y me-1 opacity-50 hover-opacity-100 border-0 bg-transparent" onclick="deleteSession(<?= $sess['id'] ?>, event)" title="Hapus Chat">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-chat-square-dots fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <p class="small mb-0">Belum ada riwayat percakapan.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Offcanvas Sidebar Chat History (Mobile & Tablet) -->
        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="chatHistoryOffcanvas" aria-labelledby="chatHistoryOffcanvasLabel">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title fw-bold" id="chatHistoryOffcanvasLabel"><i class="bi bi-chat-left-text me-2 text-primary"></i>Riwayat Chat Saya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-2" id="sessionListMobile">
                <?php if (!empty($sessions)): ?>
                    <div class="list-group list-group-flush gap-1">
                        <?php foreach ($sessions as $sess): ?>
                            <div class="list-group-item list-group-item-action border-0 rounded-3 p-2.5 position-relative <?= ($sess['id'] == $activeSessionId) ? 'active bg-primary-subtle text-primary fw-bold' : '' ?>">
                                <a href="<?= BASE_URL ?>/customer/ai-chat?session_id=<?= $sess['id'] ?>" class="text-decoration-none text-reset d-block pe-4">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i class="bi bi-chat-dots fs-5 text-primary"></i>
                                        <span class="text-truncate" style="max-width: 200px;"><?= htmlspecialchars($sess['title']) ?></span>
                                    </div>
                                    <div class="small opacity-75"><?= date('d M Y, H:i', strtotime($sess['updated_at'])) ?></div>
                                </a>
                                <button type="button" class="btn btn-sm text-danger position-absolute top-50 end-0 translate-middle-y me-2 border-0 bg-transparent" onclick="deleteSession(<?= $sess['id'] ?>, event)" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-4">Belum ada riwayat percakapan.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Active Chat Area -->
        <div class="col-lg-9 col-12">
            <div class="card shadow-sm border-0 d-flex flex-column" style="height: calc(100vh - 180px); min-height: 580px;">
                <!-- Chat Top Header -->
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="position-relative">
                            <span class="fs-2">🤖</span>
                            <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-light rounded-circle" title="Online"></span>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark" id="activeChatTitle">
                                <?= !empty($activeSession) ? htmlspecialchars($activeSession['title']) : 'Konsultasi Peralatan Rumah Tangga' ?>
                            </h6>
                            <small class="text-success fw-medium">
                                <i class="bi bi-dot"></i> HomeCare Virtual Assistant (Aktif)
                            </small>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <a href="<?= BASE_URL ?>/ai-diagnosis" class="btn btn-sm btn-outline-secondary rounded-pill d-none d-sm-inline-flex align-items-center gap-1">
                            <i class="bi bi-cpu"></i> Mode Smart Diagnosis
                        </a>
                    </div>
                </div>

                <!-- Chat Messages Feed Box -->
                <div class="card-body p-3 p-md-4 overflow-auto flex-grow-1 bg-light-subtle" id="chatFeed" style="background-color: #f8f9fa;">
                    
                    <!-- Welcome Initial Message -->
                    <div class="d-flex gap-3 mb-4 message-bubble ai-bubble">
                        <div class="flex-shrink-0">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; font-size: 1.2rem;">
                                🤖
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-dark" style="max-width: 85%;">
                                <div class="fw-bold mb-1 text-primary small d-flex align-items-center gap-1 justify-content-between">
                                    <span>HomeCare AI</span>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill" style="font-size: 0.65rem;"><?= $badgeRole ?></span>
                                </div>

                                <?php if ($currentRole === 'ADMIN'): ?>
                                <div class="message-text">
                                    Halo, Administrator <strong><?= htmlspecialchars($currentUserName) ?></strong>! 👑<br><br>
                                    Saya terhubung langsung ke database sistem HomeCare Service. Anda dapat memantau:<br>
                                    • 📊 <strong>Laporan omset & seluruh transaksi servis selesai</strong><br>
                                    • ⏳ <strong>Daftar pesanan berstatus PENDING yang butuh penugasan teknisi</strong><br>
                                    • 👥 <strong>Data jumlah & profil seluruh pelanggan terdaftar</strong><br>
                                    • 👨‍🔧 <strong>Ketersediaan tim teknisi (Ready / Sibuk)</strong>
                                </div>
                                <div class="mt-3 pt-2 border-top">
                                    <div class="small fw-semibold text-secondary mb-2"><i class="bi bi-lightbulb-fill text-warning me-1"></i>Pertanyaan Cepat Admin:</div>
                                    <div class="d-flex flex-wrap gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 small" onclick="sendQuickPrompt('Berapa total omset dan pendapatan servis saat ini?')">
                                            📊 Cek Omset Sistem
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 small" onclick="sendQuickPrompt('Tampilkan daftar pesanan pending yang perlu teknisi')">
                                            ⏳ Pesanan Pending
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 small" onclick="sendQuickPrompt('Berapa total customer terdaftar dan siapa saja yang terbaru?')">
                                            👥 Total Pelanggan
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 small" onclick="sendQuickPrompt('Tampilkan rekapitulasi status seluruh pesanan sistem')">
                                            📈 Rekap Status Pesanan
                                        </button>
                                    </div>
                                </div>

                                <?php elseif ($currentRole === 'TEKNISI'): ?>
                                <div class="message-text">
                                    Halo, Pak/Ibu Teknisi <strong><?= htmlspecialchars($currentUserName) ?></strong>! 🔧<br><br>
                                    Saya siap membantu Anda mengakses database pekerjaan servis Anda:<br>
                                    • 🛠️ <strong>Daftar pekerjaan aktif yang ditugaskan kepada Anda</strong><br>
                                    • 📍 <strong>Alamat lengkap & nomor telepon pelanggan untuk dikunjungi</strong><br>
                                    • 📅 <strong>Jadwal kunjungan servis dan keluhan peralatan</strong><br>
                                    • ⭐ <strong>Laporan rating kinerja dan ulasan dari pelanggan Anda</strong>
                                </div>
                                <div class="mt-3 pt-2 border-top">
                                    <div class="small fw-semibold text-secondary mb-2"><i class="bi bi-lightbulb-fill text-warning me-1"></i>Pertanyaan Cepat Teknisi:</div>
                                    <div class="d-flex flex-wrap gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 small" onclick="sendQuickPrompt('Apa saja daftar pekerjaan aktif saya?')">
                                            🛠️ Pekerjaan Aktif Saya
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 small" onclick="sendQuickPrompt('Di mana alamat dan kontak pelanggan dari pekerjaan aktif saya?')">
                                            📍 Alamat & No Customer
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 small" onclick="sendQuickPrompt('Berapa rating saya dan ulasan dari pelanggan?')">
                                            ⭐ Rating & Ulasan Saya
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 small" onclick="sendQuickPrompt('Berapa total pekerjaan yang sudah saya selesaikan?')">
                                            💰 Servis Selesai
                                        </button>
                                    </div>
                                </div>

                                <?php else: ?>
                                <div class="message-text">
                                    Halo! Saya <strong>HomeCare AI</strong>, asisten virtual resmi HomeCare Service. 😊<br><br>
                                    Saya memiliki akses ke database sistem dan siap membantu Anda:<br>
                                    • 📦 <strong>Mengecek status & riwayat pesanan servis Anda</strong><br>
                                    • 👨‍🔧 <strong>Melihat daftar teknisi, spesialisasi, rating & ketersediaan</strong><br>
                                    • 📋 <strong>Katalog peralatan & merk yang didukung di database</strong><br>
                                    • 🔍 <strong>Konsultasi diagnosis kerusakan & rekomendasi perbaikan</strong>
                                </div>
                                <div class="mt-3 pt-2 border-top">
                                    <div class="small fw-semibold text-secondary mb-2"><i class="bi bi-lightbulb-fill text-warning me-1"></i>Pertanyaan Cepat seputar Data Database:</div>
                                    <div class="d-flex flex-wrap gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 small" onclick="sendQuickPrompt('Berapa pesanan servis saya dan bagaimana statusnya?')">
                                            📦 Status Pesanan Saya
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 small" onclick="sendQuickPrompt('Siapa saja daftar tim teknisi yang ada dan berapa rating mereka?')">
                                            👨‍🔧 Daftar Teknisi
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 small" onclick="sendQuickPrompt('Katalog peralatan apa saja yang didukung di database?')">
                                            📋 Katalog Peralatan
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5 small" onclick="sendQuickPrompt('Bagaimana ulasan pelanggan dan rating layanan?')">
                                            ⭐ Ulasan Pelanggan
                                        </button>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="text-muted text-end mt-2" style="font-size: 0.7rem;">
                                    <i class="bi bi-check-all text-primary"></i> Database Connected
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Render Existing Messages -->
                    <?php if (!empty($messages)): ?>
                        <?php foreach ($messages as $msg): ?>
                            <?php if ($msg['sender'] === 'USER'): ?>
                                <!-- User Message -->
                                <div class="d-flex justify-content-end mb-4 message-bubble user-bubble">
                                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-primary text-white" style="max-width: 80%;">
                                        <div class="message-text">
                                            <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                        </div>
                                        <div class="text-white-50 text-end mt-1" style="font-size: 0.7rem;">
                                            <?= date('H:i', strtotime($msg['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- AI Message -->
                                <div class="d-flex gap-3 mb-4 message-bubble ai-bubble">
                                    <div class="flex-shrink-0">
                                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; font-size: 1.2rem;">
                                            🤖
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-dark" style="max-width: 85%;">
                                            <div class="fw-bold mb-1 text-primary small d-flex align-items-center gap-1">
                                                <span>HomeCare AI</span>
                                            </div>
                                            <div class="message-text">
                                                <?= formatChatMessage($msg['message']) ?>
                                            </div>
                                            
                                            <!-- Render CTA if order recommendation is detected in text -->
                                            <?php if (strpos($msg['message'], 'layanan cuci/servis') !== false || strpos($msg['message'], 'pemeriksaan fisik') !== false || strpos($msg['message'], 'pemesanan') !== false || strpos($msg['message'], 'teknisi') !== false): ?>
                                                <div class="alert alert-primary border-primary border-start border-4 bg-primary-subtle mt-3 mb-1 p-3 rounded-3">
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <i class="bi bi-tools fs-5 text-primary"></i>
                                                        <strong class="text-primary">Butuh Pemeriksaan Langsung oleh Teknisi?</strong>
                                                    </div>
                                                    <p class="small mb-2 text-dark">Tim teknisi profesional HomeCare Service siap datang ke lokasi Anda untuk pengecekan dan perbaikan.</p>
                                                    <a href="<?= BASE_URL ?>/customer/order/create" class="btn btn-primary btn-sm fw-bold rounded-pill px-3 shadow-sm">
                                                        <i class="bi bi-plus-circle me-1"></i> Buat Pesanan Servis
                                                    </a>
                                                </div>
                                            <?php endif; ?>

                                            <div class="text-muted text-end mt-2" style="font-size: 0.7rem;">
                                                <?= date('H:i', strtotime($msg['created_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Typing Indicator Container (Hidden by default) -->
                    <div id="typingIndicator" class="d-none gap-3 mb-4 message-bubble">
                        <div class="flex-shrink-0">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; font-size: 1.2rem;">
                                🤖
                            </div>
                        </div>
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-dark">
                            <div class="d-flex align-items-center gap-2 text-muted small fw-semibold">
                                <span class="spinner-grow spinner-grow-sm text-primary" role="status"></span>
                                <span>HomeCare AI sedang mengetik...</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Input Footer -->
                <div class="card-footer bg-white py-3 border-top">
                    <!-- Quick Prompt Chips -->
                    <div class="d-flex gap-1.5 overflow-auto pb-2 mb-1 text-nowrap" style="scrollbar-width: thin;">
                        <span class="badge bg-light text-secondary border align-self-center py-1.5 px-2"><i class="bi bi-chat-left-dots me-1"></i>Tanya Cepat:</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-1 px-2.5 small bg-light-subtle" onclick="sendQuickPrompt('Cek status pesanan servis saya yang ada di sistem')">
                            📦 Pesanan Saya
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-1 px-2.5 small bg-light-subtle" onclick="sendQuickPrompt('Siapa saja daftar tim teknisi yang ada di HomeCare Service?')">
                            👨‍🔧 Tim Teknisi
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-1 px-2.5 small bg-light-subtle" onclick="sendQuickPrompt('Katalog peralatan apa saja yang terdaftar di database?')">
                            📋 Katalog Alat
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-1 px-2.5 small bg-light-subtle" onclick="sendQuickPrompt('Bagaimana ulasan pelanggan dan rating layanan?')">
                            ⭐ Rating & Ulasan
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-1 px-2.5 small bg-light-subtle" onclick="sendQuickPrompt('Tolong cek data profil akun saya')">
                            👤 Profil Saya
                        </button>
                    </div>

                    <form id="chatForm" data-ajax="true" data-no-prevent-double-submit="true" onsubmit="handleChatSubmit(event)">
                        <input type="hidden" id="activeSessionIdInput" value="<?= $activeSessionId ?>">
                        <div class="input-group input-group-lg shadow-sm rounded-4 overflow-hidden border">
                            <textarea id="chatInput" class="form-control border-0 px-3 py-2 fs-6" rows="1" placeholder="Tanyakan seputar pesanan, teknisi, peralatan, atau kendala alat rumah tangga..." style="resize: none;" onkeydown="handleKeyDown(event)" oninput="updateCharCount()"></textarea>
                            <button type="submit" id="sendBtn" data-no-loading-text="true" class="btn btn-primary px-4 d-flex align-items-center gap-2 fw-bold">
                                <span>Kirim</span>
                                <i class="bi bi-send-fill fs-5"></i>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 px-1 text-muted" style="font-size: 0.75rem;">
                            <span><i class="bi bi-database-check text-success me-1"></i>Terhubung langsung dengan Database & Sistem HomeCare.</span>
                            <span id="charCounter">0 / 1000</span>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL = "<?= BASE_URL ?>";

// Quick prompt sender helper
function sendQuickPrompt(text) {
    const input = document.getElementById('chatInput');
    if (input) {
        input.value = text;
        updateCharCount();
        document.getElementById('chatForm').dispatchEvent(new Event('submit'));
    }
}

// Auto-scroll chat feed to bottom
function scrollToBottom() {
    const chatFeed = document.getElementById('chatFeed');
    if (chatFeed) {
        chatFeed.scrollTop = chatFeed.scrollHeight;
    }
}

// Initial scroll on load
document.addEventListener("DOMContentLoaded", function() {
    scrollToBottom();
    const chatInput = document.getElementById('chatInput');
    if (chatInput) chatInput.focus();
});

// Update character counter
function updateCharCount() {
    const input = document.getElementById('chatInput');
    const counter = document.getElementById('charCounter');
    if (input && counter) {
        const len = input.value.length;
        counter.textContent = `${len} / 1000`;
        if (len > 1000) {
            counter.classList.add('text-danger');
        } else {
            counter.classList.remove('text-danger');
        }
    }
}

// Handle Enter vs Shift+Enter
function handleKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('chatForm').dispatchEvent(new Event('submit'));
    }
}

// Format markdown text on JS side
function formatJsText(text) {
    let esc = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    esc = esc.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    esc = esc.replace(/\*(.*?)\*/g, '<em>$1</em>');
    esc = esc.replace(/\n/g, '<br>');
    return esc;
}

// Handle sending message via AJAX
function handleChatSubmit(e) {
    if (e && e.preventDefault) e.preventDefault();
    const input = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendBtn');
    const sessionIdInput = document.getElementById('activeSessionIdInput');
    const typingIndicator = document.getElementById('typingIndicator');
    const chatFeed = document.getElementById('chatFeed');
    const originalBtnHtml = '<span>Kirim</span><i class="bi bi-send-fill fs-5"></i>';

    const message = input.value.trim();
    if (!message) return;

    if (message.length > 1000) {
        alert('Pesan terlalu panjang. Maksimal 1000 karakter.');
        return;
    }

    const sessionId = sessionIdInput.value;
    const nowTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    // 1. Append User Bubble immediately to UI
    const userHtml = `
        <div class="d-flex justify-content-end mb-4 message-bubble user-bubble">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-primary text-white" style="max-width: 80%;">
                <div class="message-text">${formatJsText(message)}</div>
                <div class="text-white-50 text-end mt-1" style="font-size: 0.7rem;">${nowTime}</div>
            </div>
        </div>
    `;
    
    // Insert before typing indicator
    typingIndicator.insertAdjacentHTML('beforebegin', userHtml);
    input.value = '';
    updateCharCount();
    scrollToBottom();

    // 2. Show Typing Indicator & Disable Input
    typingIndicator.style.display = 'flex';
    typingIndicator.classList.remove('d-none');
    typingIndicator.classList.add('d-flex');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span><span>Kirim</span>';
    input.disabled = true;
    scrollToBottom();

    // 3. Send AJAX Request
    const formData = new FormData();
    formData.append('session_id', sessionId);
    formData.append('message', message);

    fetch(`${BASE_URL}/customer/ai-chat/send`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.success) {
            // Update active session ID if a new session was created
            if (data.session_id) {
                sessionIdInput.value = data.session_id;
                document.getElementById('activeChatTitle').textContent = data.session_title;
            }

            // Build CTA block if recommendation detected
            let ctaHtml = '';
            if (data.recommend_order && data.order_params) {
                const equip = encodeURIComponent(data.order_params.equipment || '');
                const comp = encodeURIComponent(data.order_params.complaint || '');
                const ctaUrl = `${BASE_URL}/customer/order/create?equipment=${equip}&complaint=${comp}`;

                ctaHtml = `
                    <div class="alert alert-primary border-primary border-start border-4 bg-primary-subtle mt-3 mb-1 p-3 rounded-3 shadow-sm">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-tools fs-5 text-primary"></i>
                            <strong class="text-primary">🔧 Masalah ini sebaiknya diperiksa oleh teknisi.</strong>
                        </div>
                        <p class="small mb-2 text-dark">Tim teknisi profesional HomeCare Service siap melakukan inspeksi langsung ke lokasi Anda.</p>
                        <a href="${ctaUrl}" class="btn btn-primary btn-sm fw-bold rounded-pill px-3 shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Buat Pesanan Servis
                        </a>
                    </div>
                `;
            }

            const replyText = (data.ai_message && typeof data.ai_message.message === 'string')
                ? data.ai_message.message 
                : (data.message || 'Respons telah diterima.');
            const replyTime = (data.ai_message && data.ai_message.time) ? data.ai_message.time : nowTime;

            // Append AI Bubble
            const aiHtml = `
                <div class="d-flex gap-3 mb-4 message-bubble ai-bubble">
                    <div class="flex-shrink-0">
                        <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; font-size: 1.2rem;">
                            🤖
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-dark" style="max-width: 85%;">
                            <div class="fw-bold mb-1 text-primary small d-flex align-items-center gap-1">
                                <span>HomeCare AI</span>
                            </div>
                            <div class="message-text">${formatJsText(replyText)}</div>
                            ${ctaHtml}
                            <div class="text-muted text-end mt-2" style="font-size: 0.7rem;">${replyTime}</div>
                        </div>
                    </div>
                </div>
            `;
            
            typingIndicator.insertAdjacentHTML('beforebegin', aiHtml);
            scrollToBottom();

        } else {
            // Show Error Message from backend or default fallback
            const errText = (data && data.message) ? data.message : "Maaf, AI sedang mengalami gangguan. Silakan coba beberapa saat lagi.";
            const errHtml = `
                <div class="d-flex gap-3 mb-4 message-bubble ai-bubble">
                    <div class="flex-shrink-0">
                        <div class="avatar bg-danger text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; font-size: 1.2rem;">
                            🤖
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-danger-subtle text-danger" style="max-width: 85%;">
                            <div class="fw-bold mb-1 small"><i class="bi bi-exclamation-circle me-1"></i>Pemberitahuan Sistem</div>
                            <div>${formatJsText(errText)}</div>
                        </div>
                    </div>
                </div>
            `;
            typingIndicator.insertAdjacentHTML('beforebegin', errHtml);
            scrollToBottom();
        }
    })
    .catch(err => {
        const errHtml = `
            <div class="d-flex gap-3 mb-4 message-bubble">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-danger-subtle text-danger" style="max-width: 85%;">
                    <div>Maaf, AI sedang mengalami gangguan. Silakan coba beberapa saat lagi.</div>
                </div>
            </div>
        `;
        typingIndicator.insertAdjacentHTML('beforebegin', errHtml);
        scrollToBottom();
    })
    .finally(() => {
        // Guaranteed cleanup of any processing indicator or button lock
        if (typingIndicator) {
            typingIndicator.style.display = 'none';
            typingIndicator.classList.add('d-none');
            typingIndicator.classList.remove('d-flex');
        }
        if (sendBtn) {
            sendBtn.disabled = false;
            sendBtn.innerHTML = originalBtnHtml;
        }
        if (input) {
            input.disabled = false;
            input.focus();
        }
        scrollToBottom();
    });
}

// Create new Chat Session
function createNewSession() {
    fetch(`${BASE_URL}/customer/ai-chat/session/create`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = `${BASE_URL}/customer/ai-chat?session_id=${data.session_id}`;
        } else {
            alert(data.message || 'Gagal membuat sesi percakapan baru.');
        }
    })
    .catch(() => {
        window.location.href = `${BASE_URL}/customer/ai-chat`;
    });
}

// Delete Chat Session
function deleteSession(sessionId, event) {
    if (event) event.stopPropagation();
    if (!confirm('Apakah Anda yakin ingin menghapus riwayat percakapan ini?')) return;

    const formData = new FormData();
    formData.append('session_id', sessionId);

    fetch(`${BASE_URL}/customer/ai-chat/session/delete`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = `${BASE_URL}/customer/ai-chat`;
        } else {
            alert(data.message || 'Gagal menghapus sesi.');
        }
    });
}
</script>
