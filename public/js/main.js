/**
 * HomeCare Service — Main JavaScript
 * Global UX utilities: sidebar active state, auto-alerts, confirmation modals, form validation
 */

/* ── Sidebar Active Link Detection ── */
(function () {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (!href) return;
        
        // Extract path from href (strip query string)
        const linkPath = href.split('?')[0];
        
        // Exact match or starts-with match for sub-routes
        if (currentPath === linkPath || 
            (linkPath !== '/' && currentPath.startsWith(linkPath))) {
            link.classList.add('active');
        }
    });
})();

/* ── Auto-dismiss Alerts after 5 seconds ── */
(function () {
    const alerts = document.querySelectorAll('.alert.alert-success, .alert.alert-info');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
})();

/* ── Form Validation Enhancement ── */
(function () {
    // Add Bootstrap's native validation styles
    const forms = document.querySelectorAll('form[data-validate="true"]');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

/* ── Password Show/Hide Toggle ── */
function togglePasswordVisibility(buttonEl) {
    const wrapper = buttonEl.closest('.password-wrapper');
    if (!wrapper) return;
    const input = wrapper.querySelector('input[type="password"], input[type="text"]');
    if (!input) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        buttonEl.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
        input.type = 'password';
        buttonEl.innerHTML = '<i class="bi bi-eye"></i>';
    }
}

/* ── Global Confirm Delete (Bootstrap Modal-based) ── */
function confirmDeleteAction(message, formId) {
    if (confirm(message || 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.')) {
        if (formId) {
            document.getElementById(formId).submit();
        }
        return true;
    }
    return false;
}

/* ── Toast Notification (lightweight) ── */
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) return;
    
    const id = 'toast-' + Date.now();
    const iconMap = {
        success: 'bi-check-circle-fill text-success',
        danger: 'bi-exclamation-triangle-fill text-danger',
        warning: 'bi-exclamation-circle-fill text-warning',
        info: 'bi-info-circle-fill text-info'
    };
    
    const html = `
        <div id="${id}" class="toast align-items-center" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi ${iconMap[type] || iconMap.success}"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', html);
    const toastEl = document.getElementById(id);
    const bsToast = new bootstrap.Toast(toastEl, { delay: 4000 });
    bsToast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

/* ── Format currency (Rupiah) ── */
function formatRupiah(amount) {
    return 'Rp ' + Number(amount).toLocaleString('id-ID');
}

/* ── Prevent double-submit on standard (non-AJAX) forms ── */
document.addEventListener('DOMContentLoaded', function () {
    const submitButtons = document.querySelectorAll('form button[type="submit"]');
    submitButtons.forEach(btn => {
        const form = btn.closest('form');
        if (!form) return;
        form.addEventListener('submit', function (e) {
            // Ignore forms that handle submission via AJAX / JavaScript
            if (e.defaultPrevented || btn.dataset.noLoadingText === 'true' || form.dataset.ajax === 'true') {
                return;
            }
            setTimeout(() => {
                if (e.defaultPrevented) return;
                btn.disabled = true;
                if (!btn.dataset.noLoadingText || btn.dataset.noLoadingText !== 'true') {
                    const originalText = btn.innerHTML;
                    btn.dataset.originalText = originalText;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Memproses...';
                }
            }, 0);
        });
    });
});

console.log('HomeCare Service App Loaded v2.0');