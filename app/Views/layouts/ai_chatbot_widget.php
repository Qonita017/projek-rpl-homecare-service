<?php
/**
 * HomeCare Service — Floating AI Chatbot Widget (Icon Shape)
 * Accessible across all pages, provides instant interactive AI assistance.
 */
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['user_role'] ?? 'GUEST';
$userName = $_SESSION['user_name'] ?? 'Pelanggan';
?>

<!-- ── Floating AI Chatbot Launcher Icon & Speech Bubble ── -->
<div id="aiChatbotLauncherContainer" class="ai-chatbot-launcher-wrap">
    
    <!-- Greeting Tooltip Bubble (Disappears on click or close) -->
    <div id="aiChatbotGreetingBubble" class="ai-chatbot-greeting shadow-lg">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 cursor-pointer" onclick="toggleAiChatWidget(true)">
                <span class="fs-5">🤖</span>
                <div>
                    <strong class="d-block text-dark small fw-bold">Tanya HomeCare AI</strong>
                    <span class="text-muted" style="font-size: 0.72rem;">Konsultasi servis & pesanan</span>
                </div>
            </div>
            <button type="button" class="btn-close" style="font-size: 0.65rem;" onclick="dismissAiGreeting(event)" aria-label="Tutup"></button>
        </div>
    </div>

    <!-- The Floating Icon Button -->
    <button type="button" id="aiChatbotFloatingBtn" class="ai-chatbot-btn shadow" onclick="toggleAiChatWidget()" title="Buka Asisten HomeCare AI" aria-label="HomeCare AI Chatbot">
        <div class="ai-icon-inner">
            <!-- Robot Icon (when closed) -->
            <i class="bi bi-robot ai-icon-bot"></i>
            <!-- Close Icon (when open) -->
            <i class="bi bi-x-lg ai-icon-close"></i>
        </div>
        <!-- Online Pulse Indicator -->
        <span class="ai-status-pulse" title="AI Siap Membantu 24/7"></span>
    </button>
</div>

<!-- ── The Floating Chatbot Window / Popup Widget ── -->
<div id="aiChatbotWidget" class="ai-chatbot-window shadow-xl" style="display: none;" aria-hidden="true">
    
    <!-- Widget Header -->
    <div class="ai-chatbot-header d-flex justify-content-between align-items-center text-white">
        <div class="d-flex align-items-center gap-2">
            <div class="ai-header-avatar">
                <span class="fs-4">🤖</span>
                <span class="ai-header-dot" title="Online"></span>
            </div>
            <div>
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-1.5" style="font-size: 0.95rem;">
                    <span>HomeCare AI</span>
                    <span class="badge bg-light text-primary rounded-pill px-2 py-0.5" style="font-size: 0.62rem;">Virtual CS</span>
                </h6>
                <small class="text-white-50 d-block" style="font-size: 0.72rem;">
                    <i class="bi bi-database-check text-warning me-1"></i>Terhubung Database Sistem
                </small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-1">
            <!-- Reset / New Session Button -->
            <?php if ($isLoggedIn): ?>
            <button type="button" class="btn btn-sm text-white opacity-75 hover-opacity-100 p-1" onclick="resetWidgetSession()" title="Mulai Percakapan Baru">
                <i class="bi bi-arrow-clockwise fs-6"></i>
            </button>
            <!-- Full Page Link -->
            <?php
            $chatFullPageUrl = BASE_URL . '/customer/ai-chat';
            if ($userRole === 'ADMIN') $chatFullPageUrl = BASE_URL . '/admin/ai-chat';
            elseif ($userRole === 'TEKNISI') $chatFullPageUrl = BASE_URL . '/technician/ai-chat';
            ?>
            <a href="<?= $chatFullPageUrl ?>" class="btn btn-sm text-white opacity-75 hover-opacity-100 p-1" title="Buka Halaman Penuh">
                <i class="bi bi-arrows-fullscreen fs-6"></i>
            </a>
            <?php endif; ?>
            <!-- Minimize / Close Button -->
            <button type="button" class="btn btn-sm text-white opacity-75 hover-opacity-100 p-1" onclick="toggleAiChatWidget(false)" title="Kecilkan">
                <i class="bi bi-dash-lg fs-5"></i>
            </button>
        </div>
    </div>

    <!-- Widget Body / Chat Messages Feed -->
    <div class="ai-chatbot-body p-3 overflow-auto" id="widgetChatFeed">
        
        <?php if ($isLoggedIn): ?>
            <!-- Initial Welcome Message (Role-Aware) -->
            <div class="d-flex gap-2 mb-3 ai-widget-bubble ai-bubble">
                <div class="flex-shrink-0">
                    <div class="ai-avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                        🤖
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="card border-0 shadow-xs rounded-3 p-2.5 bg-white text-dark small">
                        <div class="fw-bold text-primary mb-1 d-flex justify-content-between align-items-center" style="font-size: 0.75rem;">
                            <span>HomeCare AI Assistant</span>
                            <span class="badge bg-primary-subtle text-primary rounded-pill"><?= htmlspecialchars($userRole) ?></span>
                        </div>

                        <?php if ($userRole === 'ADMIN'): ?>
                        <div class="ai-msg-content" style="line-height: 1.55;">
                            Halo, <strong><?= htmlspecialchars($userName) ?></strong> (Administrator)! 👑<br>
                            Sistem AI terhubung langsung ke database live platform. Anda dapat memantau:
                            <ul class="mb-2 ps-3 mt-1" style="font-size: 0.8rem;">
                                <li>📊 <strong>Laporan omset & transaksi pesanan</strong></li>
                                <li>⏳ <strong>Daftar pesanan PENDING butuh teknisi</strong></li>
                                <li>👥 <strong>Jumlah & daftar pelanggan terdaftar</strong></li>
                                <li>👨‍🔧 <strong>Ketersediaan seluruh tim teknisi</strong></li>
                            </ul>
                        </div>
                        <div class="mt-2 pt-2 border-top">
                            <span class="d-block text-muted fw-semibold mb-1" style="font-size: 0.7rem;">Pertanyaan Cepat Admin:</span>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2" style="font-size: 0.72rem;" onclick="sendWidgetQuickPrompt('Berapa total omset dan pendapatan servis di sistem?')">
                                    📊 Cek Omset
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2" style="font-size: 0.72rem;" onclick="sendWidgetQuickPrompt('Tampilkan daftar pesanan pending yang belum dapat teknisi')">
                                    ⏳ Order Pending
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2" style="font-size: 0.72rem;" onclick="sendWidgetQuickPrompt('Berapa total customer terdaftar dan siapa saja yang terbaru?')">
                                    👥 Data Customer
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2" style="font-size: 0.72rem;" onclick="sendWidgetQuickPrompt('Bagaimana ketersediaan tim teknisi saat ini?')">
                                    👨‍🔧 Status Teknisi
                                </button>
                            </div>
                        </div>

                        <?php elseif ($userRole === 'TEKNISI'): ?>
                        <div class="ai-msg-content" style="line-height: 1.55;">
                            Halo, Pak/Ibu Teknisi <strong><?= htmlspecialchars($userName) ?></strong>! 🔧<br>
                            Sistem AI siap membantu mengecek database tugas kerja Anda:
                            <ul class="mb-2 ps-3 mt-1" style="font-size: 0.8rem;">
                                <li>🛠️ <strong>Daftar pekerjaan aktif yang ditugaskan</strong></li>
                                <li>📍 <strong>Alamat & kontak pelanggan yang harus dikunjungi</strong></li>
                                <li>📅 <strong>Jadwal kunjungan servis</strong></li>
                                <li>⭐ <strong>Rating & ulasan dari pelanggan Anda</strong></li>
                            </ul>
                        </div>
                        <div class="mt-2 pt-2 border-top">
                            <span class="d-block text-muted fw-semibold mb-1" style="font-size: 0.7rem;">Pertanyaan Cepat Teknisi:</span>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2" style="font-size: 0.72rem;" onclick="sendWidgetQuickPrompt('Apa saja daftar pekerjaan aktif yang ditugaskan kepada saya?')">
                                    🛠️ Pekerjaan Saya
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2" style="font-size: 0.72rem;" onclick="sendWidgetQuickPrompt('Di mana alamat dan nomor telepon customer dari order aktif saya?')">
                                    📍 Alamat Customer
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2" style="font-size: 0.72rem;" onclick="sendWidgetQuickPrompt('Berapa rating kinerja saya dan apa ulasan dari pelanggan?')">
                                    ⭐ Rating Saya
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2" style="font-size: 0.72rem;" onclick="sendWidgetQuickPrompt('Berapa total pekerjaan yang sudah saya selesaikan?')">
                                    💰 Servis Selesai
                                </button>
                            </div>
                        </div>

                        <?php else: ?>
                        <div class="ai-msg-content" style="line-height: 1.55;">
                            Halo, <strong><?= htmlspecialchars($userName) ?></strong>! 👋<br>
                            Ada yang bisa saya bantu hari ini? Anda dapat bertanya seputar:
                            <ul class="mb-2 ps-3 mt-1" style="font-size: 0.8rem;">
                                <li>📦 <strong>Status pesanan servis aktif Anda</strong></li>
                                <li>👨‍🔧 <strong>Informasi daftar teknisi & rating</strong></li>
                                <li>📋 <strong>Katalog peralatan & merk yang didukung</strong></li>
                                <li>🔍 <strong>Diagnosis awal keluhan alat elektronik</strong></li>
                            </ul>
                        </div>
                        <div class="mt-2 pt-2 border-top">
                            <span class="d-block text-muted fw-semibold mb-1" style="font-size: 0.7rem;">Pertanyaan Cepat:</span>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2" style="font-size: 0.72rem;" onclick="sendWidgetQuickPrompt('Cek status pesanan servis saya yang ada di sistem')">
                                    📦 Status Pesanan
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2" style="font-size: 0.72rem;" onclick="sendWidgetQuickPrompt('Siapa saja daftar tim teknisi HomeCare Service dan ratingnya?')">
                                    👨‍🔧 Tim Teknisi
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2" style="font-size: 0.72rem;" onclick="sendWidgetQuickPrompt('Peralatan apa saja yang dapat diservis di HomeCare?')">
                                    📋 Katalog Alat
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="text-muted text-end mt-1" style="font-size: 0.65rem;">
                            <i class="bi bi-shield-check text-success"></i> Database Connected
                        </div>
                    </div>
                </div>
            </div>

            <!-- Typing Indicator (Hidden by default) -->
            <div id="widgetTypingIndicator" class="d-none gap-2 mb-3 ai-widget-bubble">
                <div class="flex-shrink-0">
                    <div class="ai-avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                        🤖
                    </div>
                </div>
                <div class="card border-0 shadow-xs rounded-3 p-2 bg-white text-muted small d-flex align-items-center flex-row gap-2">
                    <span class="spinner-grow spinner-grow-sm text-primary" role="status" style="width: 0.7rem; height: 0.7rem;"></span>
                    <span style="font-size: 0.75rem;">HomeCare AI sedang merespons...</span>
                </div>
            </div>

        <?php else: ?>
            <!-- Welcome Guest Mode: Prompt to login or try public diagnosis -->
            <div class="d-flex gap-2 mb-3 ai-widget-bubble ai-bubble">
                <div class="flex-shrink-0">
                    <div class="ai-avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                        🤖
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="card border-0 shadow-xs rounded-3 p-3 bg-white text-dark small">
                        <div class="fw-bold text-primary mb-1" style="font-size: 0.8rem;">HomeCare AI Assistant</div>
                        <p class="mb-2" style="line-height: 1.55;">
                            Halo! Selamat datang di <strong>HomeCare Service</strong>. Saya asisten cerdas yang siap membantu Anda memeriksa status pesanan, mencari teknisi, dan mendiagnosa kerusakan alat rumah tangga.
                        </p>
                        <p class="mb-3 text-secondary" style="font-size: 0.78rem;">
                            Untuk menikmati fitur chat interaktif lengkap dan mengecek data akun, silakan masuk ke akun Anda.
                        </p>
                        <div class="d-grid gap-2">
                            <a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-sm fw-bold rounded-pill">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                            </a>
                            <a href="<?= BASE_URL ?>/register" class="btn btn-outline-primary btn-sm rounded-pill">
                                <i class="bi bi-person-plus me-1"></i> Daftar Akun Baru
                            </a>
                            <a href="<?= BASE_URL ?>/ai-diagnosis" class="btn btn-light btn-sm border text-primary rounded-pill">
                                <i class="bi bi-cpu me-1"></i> Coba AI Diagnosis (Publik)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Widget Footer / Input Area -->
    <?php if ($isLoggedIn): ?>
    <div class="ai-chatbot-footer p-2.5 bg-white border-top">
        <!-- Quick Suggestions Horizontal Scroll (Role-Aware) -->
        <div class="d-flex gap-1 overflow-auto pb-1 mb-1.5 text-nowrap widget-quick-scroll" style="scrollbar-width: none;">
            <?php if ($userRole === 'ADMIN'): ?>
            <button type="button" class="btn btn-xs btn-light border rounded-pill py-0.5 px-2 text-secondary" style="font-size: 0.7rem;" onclick="sendWidgetQuickPrompt('Berapa total omset dan pendapatan servis?')">
                📊 Cek Omset
            </button>
            <button type="button" class="btn btn-xs btn-light border rounded-pill py-0.5 px-2 text-secondary" style="font-size: 0.7rem;" onclick="sendWidgetQuickPrompt('Tampilkan daftar pesanan pending butuh teknisi')">
                ⏳ Order Pending
            </button>
            <button type="button" class="btn btn-xs btn-light border rounded-pill py-0.5 px-2 text-secondary" style="font-size: 0.7rem;" onclick="sendWidgetQuickPrompt('Berapa customer terdaftar saat ini?')">
                👥 Total Customer
            </button>
            <button type="button" class="btn btn-xs btn-light border rounded-pill py-0.5 px-2 text-secondary" style="font-size: 0.7rem;" onclick="sendWidgetQuickPrompt('Tampilkan rekapitulasi status seluruh pesanan sistem')">
                📈 Rekap Pesanan
            </button>
            <?php elseif ($userRole === 'TEKNISI'): ?>
            <button type="button" class="btn btn-xs btn-light border rounded-pill py-0.5 px-2 text-secondary" style="font-size: 0.7rem;" onclick="sendWidgetQuickPrompt('Apa saja daftar pekerjaan aktif saya?')">
                🛠️ Tugas Aktif
            </button>
            <button type="button" class="btn btn-xs btn-light border rounded-pill py-0.5 px-2 text-secondary" style="font-size: 0.7rem;" onclick="sendWidgetQuickPrompt('Di mana alamat dan nomor customer pekerjaan saya?')">
                📍 Alamat Customer
            </button>
            <button type="button" class="btn btn-xs btn-light border rounded-pill py-0.5 px-2 text-secondary" style="font-size: 0.7rem;" onclick="sendWidgetQuickPrompt('Berapa rating dan ulasan pelanggan untuk saya?')">
                ⭐ Rating Saya
            </button>
            <button type="button" class="btn btn-xs btn-light border rounded-pill py-0.5 px-2 text-secondary" style="font-size: 0.7rem;" onclick="sendWidgetQuickPrompt('Berapa banyak servis yang telah saya selesaikan?')">
                💰 Servis Selesai
            </button>
            <?php else: ?>
            <button type="button" class="btn btn-xs btn-light border rounded-pill py-0.5 px-2 text-secondary" style="font-size: 0.7rem;" onclick="sendWidgetQuickPrompt('Bagaimana cara memesan teknisi servis?')">
                💡 Cara Pesan
            </button>
            <button type="button" class="btn btn-xs btn-light border rounded-pill py-0.5 px-2 text-secondary" style="font-size: 0.7rem;" onclick="sendWidgetQuickPrompt('AC saya tidak dingin dan berbau, apa penyebabnya?')">
                ❄️ AC Tidak Dingin
            </button>
            <button type="button" class="btn btn-xs btn-light border rounded-pill py-0.5 px-2 text-secondary" style="font-size: 0.7rem;" onclick="sendWidgetQuickPrompt('Mesin cuci tidak mau berputar mode spin')">
                🌀 Mesin Cuci Macet
            </button>
            <button type="button" class="btn btn-xs btn-light border rounded-pill py-0.5 px-2 text-secondary" style="font-size: 0.7rem;" onclick="sendWidgetQuickPrompt('Kulkas freezer dingin tapi bawah hangat')">
                🧊 Kulkas Bawah Hangat
            </button>
            <?php endif; ?>
        </div>

        <!-- Chat Form -->
        <form id="widgetChatForm" data-ajax="true" data-no-prevent-double-submit="true" onsubmit="handleWidgetChatSubmit(event)">
            <input type="hidden" id="widgetActiveSessionId" value="0">
            <div class="input-group input-group-sm rounded-pill border overflow-hidden shadow-xs">
                <input type="text" id="widgetChatInput" class="form-control border-0 px-3 py-2" placeholder="Tanya AI seputar pesanan/servis..." autocomplete="off">
                <button type="submit" id="widgetSendBtn" data-no-loading-text="true" class="btn btn-primary px-3 fw-bold d-flex align-items-center">
                    <i class="bi bi-send-fill" style="font-size: 0.85rem;"></i>
                </button>
            </div>
        </form>
    </div>
    <?php else: ?>
    <div class="ai-chatbot-footer p-2.5 bg-light border-top text-center">
        <small class="text-muted" style="font-size: 0.72rem;">
            Silakan <a href="<?= BASE_URL ?>/login" class="fw-bold text-primary">login</a> untuk mulai chat dengan AI.
        </small>
    </div>
    <?php endif; ?>

</div>

<style>
/* ── Floating AI Chatbot Launcher Styles ── */
.ai-chatbot-launcher-wrap {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1060;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

/* Greeting Tooltip Bubble */
.ai-chatbot-greeting {
    background: #ffffff;
    border-radius: 12px;
    padding: 8px 12px;
    margin-bottom: 12px;
    border: 1px solid rgba(13, 110, 253, 0.15);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
    max-width: 230px;
    animation: fadeInSlideUp 0.4s ease-out;
}

@keyframes fadeInSlideUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Floating Action Button (Icon shape) */
.ai-chatbot-btn {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0d6efd 0%, #0052cc 100%);
    border: 3px solid #ffffff;
    cursor: pointer;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4) !important;
    outline: none;
    color: #ffffff;
}

.ai-chatbot-btn:hover {
    transform: scale(1.08) translateY(-2px);
    box-shadow: 0 10px 28px rgba(13, 110, 253, 0.55) !important;
}

.ai-chatbot-btn:active {
    transform: scale(0.96);
}

.ai-icon-inner {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-icon-bot {
    font-size: 2.3rem;
    transition: transform 0.2s ease, opacity 0.2s ease;
}

.ai-icon-close {
    font-size: 1.85rem;
    display: none;
    transition: transform 0.2s ease, opacity 0.2s ease;
}

/* Open State transforms button */
.ai-chatbot-launcher-wrap.is-open .ai-icon-bot {
    display: none;
}

.ai-chatbot-launcher-wrap.is-open .ai-icon-close {
    display: block;
    transform: rotate(90deg);
}

.ai-chatbot-launcher-wrap.is-open .ai-chatbot-btn {
    background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4) !important;
}

/* Online status pulse on the button */
.ai-status-pulse {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background-color: #22c55e;
    border: 2px solid #ffffff;
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
    animation: statusPulse 2s infinite;
}

@keyframes statusPulse {
    0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
    70% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
    100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
}

/* ── Floating Chatbot Window / Modal Popup ── */
.ai-chatbot-window {
    position: fixed;
    bottom: 108px;
    right: 24px;
    width: 380px;
    max-width: calc(100vw - 32px);
    height: 540px;
    max-height: calc(100vh - 120px);
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 16px 48px rgba(15, 76, 138, 0.22) !important;
    z-index: 1060;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: popupScaleUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes popupScaleUp {
    from {
        opacity: 0;
        transform: translateY(18px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Header */
.ai-chatbot-header {
    background: linear-gradient(135deg, #0f4c8a 0%, #1d6fd8 100%);
    padding: 12px 16px;
    flex-shrink: 0;
}

.ai-header-avatar {
    position: relative;
    width: 38px;
    height: 38px;
    background: rgba(255, 255, 255, 0.18);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ai-header-dot {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 10px;
    height: 10px;
    background-color: #22c55e;
    border: 2px solid #ffffff;
    border-radius: 50%;
}

/* Body / Feed */
.ai-chatbot-body {
    flex-grow: 1;
    background-color: #f8fafc;
    overflow-y: auto;
}

.ai-chatbot-body::-webkit-scrollbar {
    width: 5px;
}

.ai-chatbot-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.ai-avatar-sm {
    width: 30px;
    height: 30px;
    font-size: 0.95rem;
}

.ai-widget-bubble {
    animation: msgFadeIn 0.2s ease-out;
}

@keyframes msgFadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Footer & Buttons */
.btn-xs {
    padding: 0.2rem 0.5rem;
    font-size: 0.72rem;
    line-height: 1.2;
}

.widget-quick-scroll::-webkit-scrollbar {
    display: none;
}

.cursor-pointer {
    cursor: pointer;
}

.hover-opacity-100:hover {
    opacity: 1 !important;
}

/* Responsive adjustment for Mobile screens */
@media (max-width: 576px) {
    .ai-chatbot-launcher-wrap {
        bottom: 16px;
        right: 16px;
    }
    .ai-chatbot-btn {
        width: 64px;
        height: 64px;
    }
    .ai-icon-bot {
        font-size: 2rem;
    }
    .ai-chatbot-window {
        bottom: 92px;
        right: 12px;
        width: calc(100vw - 24px);
        height: calc(100vh - 110px);
        max-height: 580px;
    }
}
</style>

<script>
// Global toggle for Floating AI Chatbot Widget
function toggleAiChatWidget(forcedState) {
    const widget = document.getElementById('aiChatbotWidget');
    const launcher = document.getElementById('aiChatbotLauncherContainer');
    const bubble = document.getElementById('aiChatbotGreetingBubble');
    if (!widget || !launcher) return;

    let shouldOpen;
    if (typeof forcedState === 'boolean') {
        shouldOpen = forcedState;
    } else {
        shouldOpen = (widget.style.display === 'none' || widget.style.display === '');
    }

    if (shouldOpen) {
        widget.style.display = 'flex';
        widget.setAttribute('aria-hidden', 'false');
        launcher.classList.add('is-open');
        if (bubble) bubble.style.display = 'none';

        // Auto focus input
        setTimeout(() => {
            const input = document.getElementById('widgetChatInput');
            if (input) input.focus();
            scrollWidgetToBottom();
        }, 150);
    } else {
        widget.style.display = 'none';
        widget.setAttribute('aria-hidden', 'true');
        launcher.classList.remove('is-open');
    }
}

// Dismiss greeting bubble
function dismissAiGreeting(e) {
    if (e) e.stopPropagation();
    const bubble = document.getElementById('aiChatbotGreetingBubble');
    if (bubble) bubble.style.display = 'none';
}

// Scroll chat feed to bottom
function scrollWidgetToBottom() {
    const feed = document.getElementById('widgetChatFeed');
    if (feed) {
        feed.scrollTop = feed.scrollHeight;
    }
}

// Reset / New Session in Widget
function resetWidgetSession() {
    const sessionIdInput = document.getElementById('widgetActiveSessionId');
    if (sessionIdInput) sessionIdInput.value = '0';
    try {
        sessionStorage.removeItem('homecare_ai_widget_session');
    } catch (e) {}

    // Reload widget greeting content
    const feed = document.getElementById('widgetChatFeed');
    if (feed) {
        // Keep initial welcome message and typing indicator, remove appended messages
        const userBubbles = feed.querySelectorAll('.user-widget-bubble, .ai-dynamic-bubble');
        userBubbles.forEach(el => el.remove());
    }
    const input = document.getElementById('widgetChatInput');
    if (input) {
        input.value = '';
        input.focus();
    }
}

// Quick Prompt Sender for Widget
function sendWidgetQuickPrompt(text) {
    const input = document.getElementById('widgetChatInput');
    if (input) {
        input.value = text;
        const form = document.getElementById('widgetChatForm');
        if (form) form.dispatchEvent(new Event('submit'));
    }
}

// Format message markdown to clean HTML
function formatWidgetMessage(text) {
    let esc = String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    esc = esc.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    esc = esc.replace(/\*(.*?)\*/g, '<em>$1</em>');
    esc = esc.replace(/\n/g, '<br>');
    return esc;
}

// Handle submit in Widget
function handleWidgetChatSubmit(e) {
    if (e && e.preventDefault) e.preventDefault();
    const input = document.getElementById('widgetChatInput');
    const sendBtn = document.getElementById('widgetSendBtn');
    const sessionIdInput = document.getElementById('widgetActiveSessionId');
    const typingIndicator = document.getElementById('widgetTypingIndicator');
    const feed = document.getElementById('widgetChatFeed');
    const originalWidgetBtnHtml = '<i class="bi bi-send-fill" style="font-size: 0.85rem;"></i>';

    if (!input || !feed) return;
    const message = input.value.trim();
    if (!message) return;

    const sessionId = sessionIdInput ? sessionIdInput.value : '0';
    const nowTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    // 1. Render User Message Bubble
    const userBubble = document.createElement('div');
    userBubble.className = 'd-flex justify-content-end mb-3 ai-widget-bubble user-widget-bubble';
    userBubble.innerHTML = `
        <div class="card border-0 shadow-xs rounded-3 p-2 bg-primary text-white small" style="max-width: 82%;">
            <div style="line-height: 1.5;">${formatWidgetMessage(message)}</div>
            <div class="text-white-50 text-end mt-1" style="font-size: 0.65rem;">${nowTime}</div>
        </div>
    `;

    if (typingIndicator) {
        feed.insertBefore(userBubble, typingIndicator);
    } else {
        feed.appendChild(userBubble);
    }

    input.value = '';
    scrollWidgetToBottom();

    // 2. Show Typing Indicator & disable controls
    if (typingIndicator) {
        typingIndicator.style.display = 'flex';
        typingIndicator.classList.remove('d-none');
        typingIndicator.classList.add('d-flex');
    }
    if (sendBtn) {
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width: 0.85rem; height: 0.85rem;" role="status"></span>';
    }
    input.disabled = true;
    scrollWidgetToBottom();

    // 3. Send AJAX Request to backend
    const formData = new FormData();
    formData.append('session_id', sessionId);
    formData.append('message', message);

    const baseUrl = "<?= BASE_URL ?>";

    fetch(`${baseUrl}/customer/ai-chat/send`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.success) {
            if (data.session_id && sessionIdInput) {
                sessionIdInput.value = data.session_id;
                try {
                    sessionStorage.setItem('homecare_ai_widget_session', data.session_id);
                } catch (e) {}
            }

            // Optional CTA order recommendation
            let ctaHtml = '';
            if (data.recommend_order && data.order_params) {
                const equip = encodeURIComponent(data.order_params.equipment || '');
                const comp = encodeURIComponent(data.order_params.complaint || '');
                const ctaUrl = `${baseUrl}/customer/order/create?equipment=${equip}&complaint=${comp}`;

                ctaHtml = `
                    <div class="alert alert-primary border-primary border-start border-3 bg-primary-subtle p-2 mt-2 mb-1 rounded-2 shadow-xs">
                        <strong class="d-block text-primary" style="font-size: 0.75rem;">🔧 Rekomendasi Teknisi:</strong>
                        <p class="mb-1.5 text-dark" style="font-size: 0.72rem;">Sebaiknya masalah ini diperiksa teknisi secara fisik.</p>
                        <a href="${ctaUrl}" class="btn btn-primary btn-xs fw-bold rounded-pill shadow-xs">
                            <i class="bi bi-plus-circle me-1"></i> Buat Pesanan Servis
                        </a>
                    </div>
                `;
            }

            const replyText = (data.ai_message && typeof data.ai_message.message === 'string')
                ? data.ai_message.message 
                : (data.message || 'Respons telah diterima.');
            const replyTime = (data.ai_message && data.ai_message.time) ? data.ai_message.time : nowTime;

            // Append AI response bubble
            const aiBubble = document.createElement('div');
            aiBubble.className = 'd-flex gap-2 mb-3 ai-widget-bubble ai-dynamic-bubble';
            aiBubble.innerHTML = `
                <div class="flex-shrink-0">
                    <div class="ai-avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                        🤖
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="card border-0 shadow-xs rounded-3 p-2.5 bg-white text-dark small" style="max-width: 90%;">
                        <div class="fw-bold text-primary mb-1" style="font-size: 0.75rem;">HomeCare AI</div>
                        <div style="line-height: 1.55;">${formatWidgetMessage(replyText)}</div>
                        ${ctaHtml}
                        <div class="text-muted text-end mt-1" style="font-size: 0.65rem;">${replyTime}</div>
                    </div>
                </div>
            `;

            if (typingIndicator) {
                feed.insertBefore(aiBubble, typingIndicator);
            } else {
                feed.appendChild(aiBubble);
            }
            scrollWidgetToBottom();

        } else {
            // Error handling
            const errText = (data && data.message) ? data.message : 'Maaf, terjadi kendala koneksi ke server.';
            const errBubble = document.createElement('div');
            errBubble.className = 'd-flex gap-2 mb-3 ai-widget-bubble ai-dynamic-bubble';
            errBubble.innerHTML = `
                <div class="flex-shrink-0">
                    <div class="ai-avatar-sm bg-danger text-white rounded-circle d-flex align-items-center justify-content-center">
                        ⚠️
                    </div>
                </div>
                <div class="card border-0 shadow-xs rounded-3 p-2 bg-danger-subtle text-danger small">
                    <div>${formatWidgetMessage(errText)}</div>
                </div>
            `;
            if (typingIndicator) {
                feed.insertBefore(errBubble, typingIndicator);
            } else {
                feed.appendChild(errBubble);
            }
            scrollWidgetToBottom();
        }
    })
    .catch(err => {
        const errBubble = document.createElement('div');
        errBubble.className = 'd-flex gap-2 mb-3 ai-widget-bubble ai-dynamic-bubble';
        errBubble.innerHTML = `
            <div class="card border-0 shadow-xs rounded-3 p-2 bg-danger-subtle text-danger small">
                <div>Gagal terhubung dengan asisten AI. Silakan periksa koneksi Anda.</div>
            </div>
        `;
        if (typingIndicator) {
            feed.insertBefore(errBubble, typingIndicator);
        } else {
            feed.appendChild(errBubble);
        }
        scrollWidgetToBottom();
    })
    .finally(() => {
        // Guaranteed cleanup of widget typing indicator and send button
        if (typingIndicator) {
            typingIndicator.style.display = 'none';
            typingIndicator.classList.add('d-none');
            typingIndicator.classList.remove('d-flex');
        }
        if (sendBtn) {
            sendBtn.disabled = false;
            sendBtn.innerHTML = originalWidgetBtnHtml;
        }
        if (input) {
            input.disabled = false;
            input.focus();
        }
        scrollWidgetToBottom();
    });
}
</script>
