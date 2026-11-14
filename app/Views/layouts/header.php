<header class="navbar navbar-dark sticky-top flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6 fw-bold d-flex align-items-center gap-2" href="<?= BASE_URL ?>/">
        <i class="bi bi-house-heart-fill text-warning"></i>
        <span>HomeCare Service</span>
    </a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="navbar-nav w-100 d-flex flex-row justify-content-end align-items-center px-3 gap-2">
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php 
            $roleColors = ['ADMIN' => 'bg-warning text-dark', 'CUSTOMER' => 'bg-success', 'TEKNISI' => 'bg-info text-dark'];
            $roleIcons = ['ADMIN' => 'bi-shield-check', 'CUSTOMER' => 'bi-person-circle', 'TEKNISI' => 'bi-tools'];
            $role = $_SESSION['user_role'] ?? '';
            $roleColor = $roleColors[$role] ?? 'bg-secondary';
            $roleIcon = $roleIcons[$role] ?? 'bi-person';
            ?>
            <div class="nav-item d-flex align-items-center gap-2 text-nowrap">
                <!-- AI Chatbot Quick Launcher Icon -->
                <button type="button" class="btn btn-sm btn-outline-light rounded-circle d-flex align-items-center justify-content-center position-relative shadow-sm" style="width: 34px; height: 34px;" onclick="if(window.toggleAiChatWidget) toggleAiChatWidget(true)" title="HomeCare AI Assistant">
                    <i class="bi bi-robot fs-6"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle" style="transform: translate(-30%, 30%) !important;">
                        <span class="visually-hidden">AI Online</span>
                    </span>
                </button>

                <!-- ── Notification Bell ── -->
                <div class="dropdown" id="notifDropdownWrapper">
                    <button class="btn btn-sm btn-outline-light rounded-circle d-flex align-items-center justify-content-center position-relative shadow-sm"
                            style="width: 34px; height: 34px;"
                            id="notifBellBtn"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            title="Notifikasi"
                            onclick="notifMarkAllReadOnOpen()">
                        <i class="bi bi-bell-fill fs-6"></i>
                        <span id="notifBadge"
                              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-dark"
                              style="font-size: 0.6rem; min-width: 16px; padding: 2px 4px; transform: translate(-30%, 30%) !important; display: none;">
                            0
                        </span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0"
                         id="notifDropdownMenu"
                         style="width: 360px; max-width: 95vw; border-radius: 14px; overflow: hidden;">

                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center px-3 py-2"
                             style="background: linear-gradient(135deg,#0d6efd,#0052cc); color:#fff;">
                            <span class="fw-bold fs-6"><i class="bi bi-bell-fill me-2"></i>Notifikasi</span>
                            <button class="btn btn-sm btn-link text-white text-decoration-none p-0 fw-semibold"
                                    id="notifMarkAllBtn"
                                    onclick="notifMarkAllRead(event)"
                                    style="font-size:0.78rem;">
                                Tandai semua dibaca
                            </button>
                        </div>

                        <!-- Notification List -->
                        <div id="notifList"
                             style="max-height: 340px; overflow-y: auto;">
                            <div class="text-center text-muted py-4" id="notifEmpty">
                                <i class="bi bi-bell-slash fs-2 d-block mb-2 opacity-50"></i>
                                <small>Belum ada notifikasi</small>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="text-center py-2 border-top bg-light">
                            <small class="text-muted" id="notifFooterText">Memuat notifikasi...</small>
                        </div>
                    </div>
                </div>
                <!-- ── End Notification Bell ── -->

                <span class="badge <?= $roleColor ?> d-none d-sm-inline-flex align-items-center gap-1">
                    <i class="bi <?= $roleIcon ?>"></i>
                    <?= htmlspecialchars($role) ?>
                </span>
                <a class="btn btn-sm btn-outline-light rounded-pill px-3 d-flex align-items-center gap-1" href="<?= BASE_URL ?>/account/settings" title="Pengaturan Akun & Profil">
                    <i class="bi bi-person-gear"></i>
                    <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Akun') ?></span>
                </a>
                <a class="btn btn-sm btn-danger rounded-pill px-3" href="<?= BASE_URL ?>/logout" title="Keluar dari Sistem">
                    <i class="bi bi-box-arrow-right me-1"></i>Keluar
                </a>
            </div>
        <?php else: ?>
            <div class="nav-item text-nowrap d-flex align-items-center gap-2">
                <!-- AI Chatbot Quick Launcher Icon for Guest -->
                <button type="button" class="btn btn-sm btn-outline-light rounded-circle d-flex align-items-center justify-content-center position-relative shadow-sm" style="width: 34px; height: 34px;" onclick="if(window.toggleAiChatWidget) toggleAiChatWidget(true)" title="HomeCare AI Assistant">
                    <i class="bi bi-robot fs-6"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle" style="transform: translate(-30%, 30%) !important;">
                        <span class="visually-hidden">AI Online</span>
                    </span>
                </button>

                <a class="btn btn-sm btn-outline-light rounded-pill px-3" href="<?= BASE_URL ?>/login">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                </a>
                <a class="btn btn-sm btn-warning rounded-pill px-3 text-dark fw-semibold" href="<?= BASE_URL ?>/register">
                    <i class="bi bi-person-plus me-1"></i>Daftar
                </a>
            </div>
        <?php endif; ?>
    </div>
</header>

<?php if(isset($_SESSION['user_id'])): ?>
<style>
/* ── Notification Dropdown Styles ── */
#notifList .notif-item {
    border-bottom: 1px solid #f0f0f0;
    padding: 10px 14px;
    transition: background 0.15s;
    cursor: default;
    position: relative;
}
#notifList .notif-item:last-child { border-bottom: none; }
#notifList .notif-item.unread {
    background: linear-gradient(90deg, #eff6ff 0%, #fff 100%);
    border-left: 3px solid #0d6efd;
}
#notifList .notif-item.unread:hover { background: #dbeafe; }
#notifList .notif-item.read:hover   { background: #f8f9fa; }
#notifList .notif-icon {
    width: 34px; height: 34px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.notif-icon-ACCEPTED   { background: #d1fae5; color: #059669; }
.notif-icon-ON_THE_WAY { background: #e0e7ff; color: #4338ca; }
.notif-icon-IN_PROGRESS{ background: #fef3c7; color: #d97706; }
.notif-icon-COMPLETED  { background: #dcfce7; color: #16a34a; }
.notif-icon-DEFAULT    { background: #f1f5f9; color: #64748b; }
#notifList .notif-delete-btn {
    display: none; position: absolute; top: 8px; right: 8px;
    background: none; border: none; color: #94a3b8;
    font-size: 0.75rem; padding: 2px 5px; border-radius: 4px;
    line-height: 1;
}
#notifList .notif-item:hover .notif-delete-btn { display: block; }
#notifList .notif-delete-btn:hover { background: #fee2e2; color: #dc2626; }
#notifList::-webkit-scrollbar { width: 4px; }
#notifList::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
</style>

<script>
(function() {
    const BASE = '<?= BASE_URL ?>';
    let _notifData = [];

    const iconMap = {
        ACCEPTED:    { cls: 'notif-icon-ACCEPTED',    icon: 'bi-check-circle-fill' },
        ON_THE_WAY:  { cls: 'notif-icon-ON_THE_WAY',  icon: 'bi-truck'             },
        IN_PROGRESS: { cls: 'notif-icon-IN_PROGRESS',  icon: 'bi-tools'             },
        COMPLETED:   { cls: 'notif-icon-COMPLETED',    icon: 'bi-trophy-fill'       },
    };

    /** Render notifikasi ke dropdown */
    function renderNotifications(data) {
        _notifData = data.notifications || [];
        const unread = data.unread || 0;
        const badge  = document.getElementById('notifBadge');
        const list   = document.getElementById('notifList');
        const footer = document.getElementById('notifFooterText');
        const empty  = document.getElementById('notifEmpty');

        // Badge
        if (unread > 0) {
            badge.textContent = unread > 99 ? '99+' : unread;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }

        if (_notifData.length === 0) {
            list.innerHTML = '';
            list.appendChild(empty);
            footer.textContent = 'Tidak ada notifikasi';
            return;
        }

        // Build list HTML
        let html = '';
        _notifData.forEach(function(n) {
            const ic    = iconMap[n.type] || { cls: 'notif-icon-DEFAULT', icon: 'bi-bell-fill' };
            const state = n.is_read == 1 ? 'read' : 'unread';
            const link  = n.order_id ? BASE + '/customer/order/detail?id=' + n.order_id : '#';
            html += '<div class="notif-item ' + state + '" data-id="' + n.id + '">';
            html += '  <button class="notif-delete-btn" onclick="notifDelete(event,' + n.id + ')" title="Hapus notifikasi"><i class="bi bi-x-lg"></i></button>';
            html += '  <a href="' + link + '" class="d-flex align-items-start gap-2 text-decoration-none text-dark" onclick="notifMarkRead(event,' + n.id + ',\'' + link + '\')">';
            html += '    <div class="notif-icon ' + ic.cls + '"><i class="bi ' + ic.icon + '"></i></div>';
            html += '    <div class="flex-grow-1" style="min-width:0;">';
            html += '      <div class="fw-semibold" style="font-size:0.82rem;line-height:1.3;">' + n.title + '</div>';
            html += '      <div class="text-muted mt-1" style="font-size:0.75rem;line-height:1.4;">' + n.message + '</div>';
            html += '      <div class="text-muted mt-1" style="font-size:0.7rem;opacity:.7;"><i class="bi bi-clock me-1"></i>' + n.time_ago + '</div>';
            html += '    </div>';
            html += '  </a>';
            html += '</div>';
        });

        list.innerHTML = html;
        footer.textContent = _notifData.length + ' notifikasi terbaru';
    }

    /** Fetch notifikasi dari server */
    function fetchNotifications() {
        fetch(BASE + '/notifications/fetch', { credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(renderNotifications)
            .catch(function() {});
    }

    /** Tandai semua dibaca — dipanggil saat dropdown dibuka */
    window.notifMarkAllReadOnOpen = function() {
        const badge = document.getElementById('notifBadge');
        const hasUnread = badge && badge.style.display !== 'none';
        if (!hasUnread) return;
        fetch(BASE + '/notifications/mark-all-read', { method: 'POST', credentials: 'same-origin' })
            .then(function() { fetchNotifications(); })
            .catch(function() {});
    };

    /** Tombol "Tandai semua dibaca" */
    window.notifMarkAllRead = function(e) {
        e.stopPropagation();
        fetch(BASE + '/notifications/mark-all-read', { method: 'POST', credentials: 'same-origin' })
            .then(function() { fetchNotifications(); })
            .catch(function() {});
    };

    /** Tandai satu notif dibaca lalu navigasi */
    window.notifMarkRead = function(e, id, link) {
        e.preventDefault();
        var fd = new FormData(); fd.append('id', id);
        fetch(BASE + '/notifications/mark-read', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function() { if (link && link !== '#') { window.location.href = link; } else { fetchNotifications(); } })
            .catch(function() { if (link && link !== '#') window.location.href = link; });
    };

    /** Hapus satu notif */
    window.notifDelete = function(e, id) {
        e.preventDefault(); e.stopPropagation();
        var fd = new FormData(); fd.append('id', id);
        fetch(BASE + '/notifications/delete', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function() { fetchNotifications(); })
            .catch(function() {});
    };

    // Inisialisasi: fetch langsung, lalu polling setiap 30 detik
    fetchNotifications();
    setInterval(fetchNotifications, 30000);
})();
</script>
<?php endif; ?>