/**
 * central_box — POS (Punto de Venta) JavaScript
 */

/* ── Estado ── */
let posCarrito = [];
let posDescuentoGlobal = 0;
let posClienteId = null;

document.addEventListener('DOMContentLoaded', () => {
    initCatalogSearch();
    initCatalogFilter();
    initViewToggle();
    initClienteSearch();
    initPaymentChange();
    initDiscount();
    initFormValidation();
    initKeyboardShortcuts();
    renderPosCart();
});

/* ════════════════════════════
   CATÁLOGO
   ════════════════════════════ */
function initCatalogSearch() {
    document.getElementById('posBuscar')?.addEventListener('input', function () {
        filterProducts(this.value, document.getElementById('posCatFilter')?.value || 'Todas');
    });
}

function initCatalogFilter() {
    document.getElementById('posCatFilter')?.addEventListener('change', function () {
        filterProducts(document.getElementById('posBuscar')?.value || '', this.value);
    });
}

function filterProducts(q, cat) {
    const cards = document.querySelectorAll('.pos-product-card');
    const ql = q.toLowerCase();
    cards.forEach(card => {
        const nombre = card.dataset.nombre?.toLowerCase() || '';
        const cardCat = card.dataset.cat || '';
        const matchQ   = !ql || nombre.includes(ql);
        const matchCat = cat === 'Todas' || cardCat === cat;
        card.style.display = matchQ && matchCat ? '' : 'none';
    });
}

function initViewToggle() {
    const grid = document.getElementById('posProductGrid');
    document.getElementById('btnGrid')?.addEventListener('click', function () {
        grid?.classList.remove('pos-list-view');
        this.classList.add('active');
        document.getElementById('btnList')?.classList.remove('active');
    });
    document.getElementById('btnList')?.addEventListener('click', function () {
        grid?.classList.add('pos-list-view');
        this.classList.add('active');
        document.getElementById('btnGrid')?.classList.remove('active');
    });
}

/* ════════════════════════════
   AGREGAR PRODUCTO
   ════════════════════════════ */
function posAgregarProducto(id) {
    const p = PRODUCTOS_DATA.find(x => x.id === id);
    if (!p) return;
    if (p.stock <= 0) { posToast('Sin stock disponible', 'warning'); return; }

    const existente = posCarrito.find(x => x.id === id);
    if (existente) {
        if (existente.qty >= p.stock) { posToast('Stock máximo alcanzado', 'warning'); return; }
        existente.qty++;
    } else {
        posCarrito.push({ id: p.id, nombre: p.nombre, precio: p.precio, stock: p.stock, qty: 1 });
    }
    renderPosCart();
    posToast(p.nombre + ' agregado', 'success');
}

/* ════════════════════════════
   RENDER CARRITO
   ════════════════════════════ */
function renderPosCart() {
    const tbody = document.getElementById('posCartBody');
    if (!tbody) return;

    if (posCarrito.length === 0) {
        tbody.innerHTML = `<tr id="posEmptyRow">
            <td colspan="5" class="pos-cart-empty">
                <i data-lucide="shopping-cart"></i><p>Carrito vacío</p>
            </td></tr>`;
        if (window.lucide) lucide.createIcons();
        updateTotals();
        return;
    }

    tbody.innerHTML = posCarrito.map((item, i) => `
        <tr>
            <td>
                <div class="pos-cart-item-name">${escHtml(item.nombre)}</div>
                <input type="hidden" name="items[${i}][producto_id]" value="${item.id}">
                <input type="hidden" name="items[${i}][precio]" value="${item.precio}">
            </td>
            <td class="pos-cart-price">$${fmtNum(item.precio)}</td>
            <td>
                <div class="pos-qty-ctrl">
                    <button type="button" class="pos-qty-btn" onclick="posQty(${i},-1)">−</button>
                    <input type="number" class="pos-qty-input" name="items[${i}][cantidad]"
                           value="${item.qty}" min="1" max="${item.stock}"
                           onchange="posSetQty(${i},this.value)">
                    <button type="button" class="pos-qty-btn" onclick="posQty(${i},1)">+</button>
                </div>
            </td>
            <td class="pos-cart-total"><strong>$${fmtNum(item.precio * item.qty)}</strong></td>
            <td>
                <button type="button" class="pos-del-btn" onclick="posRemove(${i})">
                    <i data-lucide="trash-2"></i>
                </button>
            </td>
        </tr>`).join('');

    if (window.lucide) lucide.createIcons();
    updateTotals();
}

function posQty(i, d) {
    const item = posCarrito[i];
    const n = item.qty + d;
    if (n < 1) return;
    if (n > item.stock) { posToast('Stock máximo', 'warning'); return; }
    item.qty = n;
    renderPosCart();
}

function posSetQty(i, v) {
    const item = posCarrito[i];
    item.qty = Math.max(1, Math.min(item.stock, parseInt(v) || 1));
    renderPosCart();
}

function posRemove(i) {
    posCarrito.splice(i, 1);
    renderPosCart();
}

document.getElementById('btnLimpiar')?.addEventListener('click', () => {
    if (posCarrito.length && confirm('¿Vaciar carrito?')) {
        posCarrito = [];
        renderPosCart();
    }
});

/* ════════════════════════════
   TOTALES
   ════════════════════════════ */
function updateTotals() {
    const subtotal = posCarrito.reduce((s, i) => s + i.precio * i.qty, 0);
    const desc     = subtotal * (posDescuentoGlobal / 100);
    const base     = subtotal - desc;
    const igv      = base * IGV;
    const total    = base + igv;

    setText('posSubtotal',  '$' + fmtNum(subtotal));
    setText('posDescuento', desc > 0 ? '-$' + fmtNum(desc) : '$0.00');
    setText('posImpuesto',  '$' + fmtNum(igv));
    setText('posTotal',     '$' + fmtNum(total));

    calcCambio(total);
}

/* ════════════════════════════
   DESCUENTO GLOBAL
   ════════════════════════════ */
function initDiscount() {
    document.getElementById('btnDescuento')?.addEventListener('click', () => {
        const panel = document.getElementById('discountPanel');
        const chev  = document.getElementById('discChev');
        panel.classList.toggle('hidden');
        chev?.setAttribute('data-lucide', panel.classList.contains('hidden') ? 'chevron-down' : 'chevron-up');
        if (window.lucide) lucide.createIcons();
    });

    document.getElementById('btnAplicarDesc')?.addEventListener('click', () => {
        const v = parseFloat(document.getElementById('descuentoGlobal')?.value) || 0;
        posDescuentoGlobal = Math.max(0, Math.min(100, v));
        updateTotals();
        posToast('Descuento ' + posDescuentoGlobal + '% aplicado', 'success');
    });
}

/* ════════════════════════════
   PAGO EN EFECTIVO
   ════════════════════════════ */
function initPaymentChange() {
    document.getElementById('posMetodoPago')?.addEventListener('change', function () {
        const box = document.getElementById('posPagoBox');
        if (box) box.style.display = this.value === 'efectivo' ? '' : 'none';
    });

    document.getElementById('posPagoRecibido')?.addEventListener('input', () => {
        const totalStr = document.getElementById('posTotal')?.textContent.replace(/[^0-9.]/g, '') || '0';
        calcCambio(parseFloat(totalStr));
    });
}

function calcCambio(total) {
    const recibido = parseFloat(document.getElementById('posPagoRecibido')?.value) || 0;
    const cambio   = Math.max(0, recibido - total);
    const el = document.getElementById('posCambio');
    if (el) el.textContent = '$' + fmtNum(cambio);
}

/* ════════════════════════════
   BÚSQUEDA DE CLIENTE
   ════════════════════════════ */
function initClienteSearch() {
    const input    = document.getElementById('posClienteSearch');
    const dropdown = document.getElementById('posClienteDropdown');
    const selected = document.getElementById('posClienteSelected');
    const hiddenId = document.getElementById('posClienteId');

    if (!input) return;

    input.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        dropdown.innerHTML = '';
        if (!q) { dropdown.classList.add('hidden'); return; }

        const res = CLIENTES_DATA.filter(c =>
            c.nombre.toLowerCase().includes(q) || c.email.toLowerCase().includes(q)
        ).slice(0, 6);

        if (!res.length) {
            dropdown.innerHTML = '<div class="pv-dropdown-empty">Sin resultados</div>';
            dropdown.classList.remove('hidden');
            return;
        }

        res.forEach(c => {
            const div = document.createElement('div');
            div.className = 'pv-dropdown-item';
            div.innerHTML = `<div class="pv-dd-info"><strong>${escHtml(c.nombre)}</strong><span>${escHtml(c.email)}</span></div>`;
            div.addEventListener('click', () => {
                hiddenId.value = c.id;
                posClienteId = c.id;
                input.value = c.nombre;
                dropdown.classList.add('hidden');
                selected.textContent = c.nombre + ' — ' + c.email;
                selected.classList.remove('hidden');
            });
            dropdown.appendChild(div);
        });
        dropdown.classList.remove('hidden');
    });

    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !dropdown.contains(e.target))
            dropdown.classList.add('hidden');
    });
}

/* ════════════════════════════
   VALIDACIÓN
   ════════════════════════════ */
function initFormValidation() {
    document.getElementById('formVenta')?.addEventListener('submit', function (e) {
        if (posCarrito.length === 0) {
            e.preventDefault();
            posToast('Agrega al menos un producto al carrito.', 'error');
            return;
        }
    });
}

/* ════════════════════════════
   ATAJOS DE TECLADO
   ════════════════════════════ */
function initKeyboardShortcuts() {
    document.addEventListener('keydown', e => {
        if (['INPUT','TEXTAREA','SELECT'].includes(e.target.tagName)) return;
        switch (e.key) {
            case 'F2': e.preventDefault(); document.getElementById('posBuscar')?.focus(); break;
            case 'F3': e.preventDefault(); document.getElementById('posBuscar')?.focus(); break;
            case 'F4': e.preventDefault(); document.getElementById('btnDescuento')?.click(); break;
            case 'F5': e.preventDefault(); document.getElementById('btnRegistrar')?.click(); break;
            case 'Escape': e.preventDefault();
                if (posCarrito.length && confirm('¿Limpiar carrito?')) { posCarrito = []; renderPosCart(); }
                break;
        }
    });
}

/* ════════════════════════════
   CAJÓN DE DINERO
   ════════════════════════════ */
function posCajonDinero() {
    posToast('Cajón abierto', 'success');
}

/* ════════════════════════════
   HELPERS
   ════════════════════════════ */
function fmtNum(n) {
    return Number(n).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function posToast(msg, type) {
    if (typeof showToast === 'function') showToast(msg, type);
}
