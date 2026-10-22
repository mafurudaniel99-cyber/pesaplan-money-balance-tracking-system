/* =====================================================================
 * PesaPlan — Main JavaScript (vanilla, no frameworks)
 * Handles: sidebar toggle, modal open/close, password toggle, CSRF fetch,
 * toast notifications, and reduced-motion respect.
 * ===================================================================== */
(function() {
    'use strict';

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Sidebar (mobile) ---------- */
    const sidebar = document.getElementById('sidebar');
    const hamburger = document.getElementById('hamburger');
    const sidebarClose = document.getElementById('sidebarClose');
    const overlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('open');
        if (overlay) overlay.hidden = false;
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('open');
        if (overlay) overlay.hidden = true;
        document.body.style.overflow = '';
    }
    if (hamburger) hamburger.addEventListener('click', openSidebar);
    if (sidebarClose) sidebarClose.addEventListener('click', closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);

    /* ---------- Modals ---------- */
    function openModal(id) {
        const m = document.getElementById(id);
        if (!m) return;
        m.hidden = false;
        document.body.style.overflow = 'hidden';
        // focus first input
        const focusable = m.querySelector('input, select, textarea, button');
        if (focusable) setTimeout(() => focusable.focus(), reduceMotion ? 0 : 100);
    }
    function closeModal(m) {
        if (!m) return;
        m.hidden = true;
        document.body.style.overflow = '';
    }
    document.addEventListener('click', function(e) {
        const opener = e.target.closest('[data-modal-open]');
        if (opener) {
            e.preventDefault();
            openModal(opener.getAttribute('data-modal-open'));
            return;
        }
        const closer = e.target.closest('[data-modal-close]');
        if (closer) {
            e.preventDefault();
            const m = closer.closest('.modal');
            closeModal(m);
            return;
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal:not([hidden])').forEach(closeModal);
            if (sidebar && sidebar.classList.contains('open')) closeSidebar();
        }
    });

    /* ---------- Password toggle ---------- */
    document.querySelectorAll('.password-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const input = btn.parentElement.querySelector('input');
            if (!input) return;
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) icon.className = 'fa-solid fa-eye-slash';
            } else {
                input.type = 'password';
                if (icon) icon.className = 'fa-solid fa-eye';
            }
        });
    });

    /* ---------- Toast ---------- */
    function toast(message, type) {
        type = type || 'info';
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:200;display:flex;flex-direction:column;gap:8px;';
            document.body.appendChild(container);
        }
        const el = document.createElement('div');
        const colors = {
            success: 'background:#2f8a4f;color:#fff',
            error:   'background:#c0392b;color:#fff',
            info:    'background:#1f4f6f;color:#fff',
            warning: 'background:#d68910;color:#fff',
        };
        el.style.cssText = 'padding:12px 16px;border-radius:8px;font-size:14px;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,.2);max-width:320px;' + (colors[type] || colors.info);
        el.textContent = message;
        container.appendChild(el);
        if (!reduceMotion) {
            el.style.opacity = '0';
            el.style.transform = 'translateY(8px)';
            el.style.transition = 'opacity .2s, transform .2s';
            requestAnimationFrame(function() {
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            });
        }
        setTimeout(function() {
            el.style.opacity = '0';
            setTimeout(function() { el.remove(); }, 300);
        }, 4000);
    }
    window.pesaToast = toast;

    /* ---------- CSRF fetch helper ---------- */
    window.pesaFetch = function(url, options) {
        options = options || {};
        options.headers = options.headers || {};
        options.headers['X-CSRF-Token'] = document.querySelector('meta[name="csrf-token"]')?.content || '';
        if (options.body && !(options.body instanceof FormData) && !options.headers['Content-Type']) {
            options.headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }
        return fetch(url, options);
    };

    /* ---------- Auto-format amount inputs (TZS, no decimals) ---------- */
    document.querySelectorAll('input[type="number"][data-format="money"]').forEach(function(input) {
        input.addEventListener('blur', function() {
            const v = parseFloat(input.value);
            if (!isNaN(v)) {
                input.value = Math.round(v);
            }
        });
    });

    /* ---------- Confirm dialogs on destructive actions ---------- */
    document.querySelectorAll('form[data-confirm]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm(form.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    /* ---------- Auto-dismiss flash messages after 6 seconds ---------- */
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(el) {
            if (!reduceMotion) {
                el.style.transition = 'opacity .4s';
                el.style.opacity = '0';
                setTimeout(function() { el.remove(); }, 400);
            } else {
                el.remove();
            }
        });
    }, 6000);

    /* ---------- Highlight active nav based on body data-page ---------- */
    const page = document.body.getAttribute('data-page');
    if (page) {
        document.querySelectorAll('.nav-link').forEach(function(link) {
            if (link.getAttribute('href') === '/' + page.replace('_', '-')) {
                link.classList.add('active');
            }
        });
    }
})();
