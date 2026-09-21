/**
 * central_box — empleado.js
 * Funciones globales del módulo empleado (toast, helpers)
 */

/**
 * Mostrar un toast de notificación
 * @param {string} msg
 * @param {'success'|'error'|'warning'|'info'} type
 */
function empToast(msg, type) {
    type = type || 'info';
    const icons  = { success:'check-circle', error:'alert-circle', warning:'alert-triangle', info:'info' };
    const colors = { success:'#10B981', error:'#EF4444', warning:'#F59E0B', info:'#6366F1' };

    let container = document.getElementById('empToasts');
    if (!container) {
        container = document.createElement('div');
        container.id = 'empToasts';
        container.className = 'emp-toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'emp-toast ' + type;
    toast.innerHTML = [
        `<i data-lucide="${icons[type]||'info'}" class="emp-toast-icon" style="color:${colors[type]};flex-shrink:0;"></i>`,
        `<span style="flex:1;">${msg}</span>`,
        `<button class="emp-toast-close" onclick="this.parentElement.remove()">×</button>`,
    ].join('');

    container.appendChild(toast);
    if (window.lucide) lucide.createIcons();

    setTimeout(function () {
        toast.style.opacity    = '0';
        toast.style.transition = 'opacity .3s, transform .3s';
        toast.style.transform  = 'translateX(20px)';
        setTimeout(function () { toast.remove(); }, 300);
    }, 3500);
}

/**
 * Escapa HTML básico
 * @param {string} s
 * @returns {string}
 */
function escHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

/**
 * Formatea precio en pesos colombianos
 * @param {number} value
 * @returns {string}
 */
function empFmt(value) {
    return '$ ' + Number(value).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

// Auto-dismiss alerts en el layout
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
        document.querySelectorAll('.emp-alert').forEach(function (a) {
            a.style.opacity    = '0';
            a.style.transition = 'opacity .3s';
            setTimeout(function () { a.remove(); }, 300);
        });
    }, 5000);

    // Sidebar toggle responsive
    document.getElementById('empToggle')?.addEventListener('click', function () {
        const sidebar = document.getElementById('empSidebar');
        const overlay = document.getElementById('empOverlay');
        if (sidebar) sidebar.classList.toggle('open');
        if (overlay) overlay.classList.toggle('active');
    });

    document.getElementById('empOverlay')?.addEventListener('click', function () {
        document.getElementById('empSidebar')?.classList.remove('open');
        this.classList.remove('active');
    });
});
