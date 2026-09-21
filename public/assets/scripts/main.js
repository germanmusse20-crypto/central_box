/**
 * central_box — Main JavaScript
 * Sidebar toggle, toast notifications, global helpers
 */

document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initAlertDismiss();
});

/* ============================================================
   Sidebar Toggle
   ============================================================ */
function initSidebar() {
    const sidebar       = document.getElementById('sidebar');
    const appMain       = document.getElementById('appMain');
    const appHeader     = document.getElementById('appHeader');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileOverlay = document.getElementById('mobileOverlay');

    if (!sidebar || !sidebarToggle) return;

    // Check saved state
    const isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
    if (isCollapsed && window.innerWidth > 768) {
        sidebar.classList.add('collapsed');
        appMain?.classList.add('sidebar-collapsed');
        appHeader?.classList.add('sidebar-collapsed');
    }

    sidebarToggle.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            // Mobile: slide in/out
            sidebar.classList.toggle('mobile-open');
            mobileOverlay?.classList.toggle('active');
        } else {
            // Desktop: collapse/expand
            sidebar.classList.toggle('collapsed');
            appMain?.classList.toggle('sidebar-collapsed');
            appHeader?.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
        }
    });

    // Close mobile sidebar on overlay click
    mobileOverlay?.addEventListener('click', function() {
        sidebar.classList.remove('mobile-open');
        mobileOverlay.classList.remove('active');
    });

    // Handle resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('mobile-open');
            mobileOverlay?.classList.remove('active');
        }
    });
}

/* ============================================================
   Auto-dismiss alerts
   ============================================================ */
function initAlertDismiss() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.3s, transform 0.3s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function() { alert.remove(); }, 300);
        }, 5000);
    });
}

/* ============================================================
   Toast Notifications
   ============================================================ */
function showToast(message, type) {
    type = type || 'info';
    var container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    var icons = {
        success: 'check-circle',
        error: 'alert-circle',
        warning: 'alert-triangle',
        info: 'info'
    };

    var toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML =
        '<i data-lucide="' + (icons[type] || 'info') + '" style="color:var(--color-' + type + ');flex-shrink:0;"></i>' +
        '<span>' + message + '</span>' +
        '<button onclick="this.parentElement.remove()" style="margin-left:auto;background:none;border:none;color:var(--color-text-muted);cursor:pointer;font-size:1.1rem;">&times;</button>';

    container.appendChild(toast);

    // Re-render icons
    if (window.lucide) lucide.createIcons();

    // Auto remove
    setTimeout(function() {
        toast.style.transition = 'opacity 0.3s, transform 0.3s';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(function() { toast.remove(); }, 300);
    }, 4000);
}

/* ============================================================
   Confirm Delete
   ============================================================ */
function confirmDelete(message, url) {
    message = message || '¿Estás seguro de que deseas eliminar este elemento?';
    if (confirm(message)) {
        window.location.href = url;
    }
}

/* ============================================================
   Format price helper
   ============================================================ */
function formatPrice(price) {
    return '$ ' + Number(price).toLocaleString('es-CO', { minimumFractionDigits: 0 });
}
