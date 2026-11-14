<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse" style="min-height: calc(100vh - 56px);">
    <div class="position-sticky pt-3 sidebar-sticky">
        <ul class="nav flex-column px-2">

            <li class="nav-item">
                <a class="nav-link" aria-current="page" href="<?= BASE_URL ?>/">
                    <i class="bi bi-house-door"></i> Beranda
                </a>
            </li>
            
            <?php if(!isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/login">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/register">
                    <i class="bi bi-person-plus"></i> Daftar Akun
                </a>
            </li>
            <?php else: ?>
            
            <?php if($_SESSION['user_role'] === 'ADMIN'): ?>

            <h6 class="sidebar-heading px-1 mt-3 mb-1 text-uppercase">
                <i class="bi bi-grid-1x2 me-1"></i> Menu Utama
            </h6>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/orders">
                    <i class="bi bi-card-checklist"></i> Service Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/reviews">
                    <i class="bi bi-star-fill text-warning"></i> Ulasan & Rating
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/reports">
                    <i class="bi bi-graph-up-arrow text-primary"></i> Laporan & Statistik
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'ai-chat') !== false ? 'active text-primary fw-bold' : '' ?>" href="<?= BASE_URL ?>/admin/ai-chat">
                    <i class="bi bi-robot text-primary"></i> AI Chatbot Assistant
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'ai-setting') !== false ? 'active text-primary fw-bold' : '' ?>" href="<?= BASE_URL ?>/admin/ai-setting">
                    <i class="bi bi-sliders2-vertical text-warning"></i> Konfigurasi AI & Model
                </a>
            </li>

            <h6 class="sidebar-heading px-1 mt-3 mb-1 text-uppercase">
                <i class="bi bi-database me-1"></i> Master Data
            </h6>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/customer">
                    <i class="bi bi-people"></i> Customer
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/technician">
                    <i class="bi bi-tools"></i> Teknisi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/admin/equipment">
                    <i class="bi bi-pc-display"></i> Peralatan
                </a>
            </li>

            <?php elseif($_SESSION['user_role'] === 'CUSTOMER'): ?>

            <h6 class="sidebar-heading px-1 mt-3 mb-1 text-uppercase">
                <i class="bi bi-grid-1x2 me-1"></i> Menu Saya
            </h6>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/customer/dashboard">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item d-flex align-items-center px-2 py-1">
                <button type="button" onclick="if(window.toggleAiChatWidget){ toggleAiChatWidget(true); }" class="btn btn-primary d-flex align-items-center justify-content-center rounded-circle shadow position-relative p-0" style="width: 36px; height: 36px; flex-shrink: 0;" title="AI Chatbot Assistant">
                    <i class="bi bi-robot fs-6"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-white rounded-circle" style="width: 10px; height: 10px; transform: translate(-40%, 40%) !important;"></span>
                </button>
                <span class="ms-2 small fw-semibold text-muted" style="font-size: 0.82rem;">AI Chatbot</span>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/ai-diagnosis">
                    <i class="bi bi-robot"></i> AI Diagnosis
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/customer/order/create">
                    <i class="bi bi-plus-circle-fill text-success"></i> Buat Pesanan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/customer/order">
                    <i class="bi bi-clock-history"></i> Pesanan Aktif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/customer/payment') !== false ? 'active text-primary fw-bold' : '' ?>" href="<?= BASE_URL ?>/customer/payment">
                    <i class="bi bi-wallet2 text-success"></i> Pembayaran
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/customer/order/history">
                    <i class="bi bi-journal-check"></i> Riwayat Pesanan
                </a>
            </li>

            <?php elseif($_SESSION['user_role'] === 'TEKNISI'): ?>

            <h6 class="sidebar-heading px-1 mt-3 mb-1 text-uppercase">
                <i class="bi bi-grid-1x2 me-1"></i> Menu Teknisi
            </h6>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/technician/dashboard">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/technician/jobs">
                    <i class="bi bi-briefcase-fill"></i> Pekerjaan Saya
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= BASE_URL ?>/technician/jobs?status=COMPLETED">
                    <i class="bi bi-check2-circle text-success"></i> Riwayat Selesai
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'ai-chat') !== false ? 'active text-primary fw-bold' : '' ?>" href="<?= BASE_URL ?>/technician/ai-chat">
                    <i class="bi bi-robot text-primary"></i> AI Chatbot Assistant
                </a>
            </li>
            <?php endif; ?>
            
            <h6 class="sidebar-heading px-1 mt-3 mb-1 text-uppercase">
                <i class="bi bi-person-circle me-1"></i> Akun Saya
            </h6>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/account/settings') !== false ? 'active text-primary fw-bold' : '' ?>" href="<?= BASE_URL ?>/account/settings">
                    <i class="bi bi-gear-fill text-primary"></i> Pengaturan Akun
                </a>
            </li>
            <hr class="my-2 mx-1 opacity-25">
            <li class="nav-item">
                <a class="nav-link text-danger" href="<?= BASE_URL ?>/logout">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>