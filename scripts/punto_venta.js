/**
 * central_box — Punto de Venta JS
 * Lógica de carrito, búsqueda de cliente/producto y resumen
 */

/* ────────────────────────────────────────
   Estado del carrito
   ──────────────────────────────────────── */
let carrito = [];       // [{id, nombre, categoria, precio, descuento, cantidad}]
let clienteSeleccionado = null;

/* ────────────────────────────────────────
   Inicialización
   ──────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    initClienteSearch();
    initProductoSearch();
    initPaymentToggle();
    initFormValidation();
    initClienteList();
});

/* ════════════════════════════════════════
   BÚSQUEDA DE CLIENTE
   ════════════════════════════════════════ */
function initClienteSearch() {
    const input    = document.getElementById('clienteSearch');
    const dropdown = document.getElementById('clienteDropdown');
    const lista    = document.getElementById('clienteListaAll');

    if (!input) return;

    input.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();

        // Filtrar lista visible
        const items = lista.querySelectorAll('.pv-client-item');
        let found = 0;
        items.forEach(item => {
            const match = item.dataset.nombre.toLowerCase().includes(q)
                       || item.dataset.email.toLowerCase().includes(q);
            item.style.display = match ? '' : 'none';
            if (match) found++;
        });
    });
}

function initClienteList() {
    const items = document.querySelectorAll('.pv-client-item');
    items.forEach(item => {
        item.addEventListener('click', function () {
            seleccionarCliente({
                id:        this.dataset.id,
                nombre:    this.dataset.nombre,
                email:     this.dataset.email,
                telefono:  this.dataset.telefono,
                direccion: this.dataset.direccion,
            });
        });
    });
}

function seleccionarCliente(c) {
    clienteSeleccionado = c;

    document.getElementById('clienteId').value        = c.id;
    document.getElementById('clienteNombre').value    = c.nombre;
    document.getElementById('clienteEmail').value     = c.email;
    document.getElementById('clienteTelefono').value  = c.telefono;
    document.getElementById('clienteDireccion').value = c.direccion;

    document.getElementById('clienteInfo').classList.remove('hidden');
    document.getElementById('clienteListaAll').style.display = 'none';

    // Marcar seleccionado
    document.querySelectorAll('.pv-client-item').forEach(el => {
        el.classList.toggle('selected', el.dataset.id == c.id);
    });

    document.getElementById('clienteSearch').value = c.nombre;
}

/* ════════════════════════════════════════
   BÚSQUEDA DE PRODUCTO
   ════════════════════════════════════════ */
function initProductoSearch() {
    const input    = document.getElementById('productoSearch');
    const dropdown = document.getElementById('productoDropdown');

    if (!input) return;

    input.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        dropdown.innerHTML = '';

        if (!q) { dropdown.classList.add('hidden'); return; }

        const resultados = PRODUCTOS_DATA.filter(p =>
            p.nombre.toLowerCase().includes(q) && p.stock > 0
        ).slice(0, 8);

        if (!resultados.length) {
            dropdown.innerHTML = '<div class="pv-dropdown-empty">Sin resultados</div>';
            dropdown.classList.remove('hidden');
            return;
        }

        resultados.forEach(p => {
            const div = document.createElement('div');
            div.className = 'pv-dropdown-item';
            div.innerHTML = `
                <div class="pv-dd-info">
                    <strong>${p.nombre}</strong>
                    <span class="pv-dd-cat">${p.categoria_nombre}</span>
                </div>
                <div class="pv-dd-right">
                    <span class="pv-dd-price">$${p.precio.toLocaleString('es-CO')}</span>
                    <span class="pv-dd-stock">Stock: ${p.stock}</span>
                </div>`;
            div.addEventListener('click', () => {
                agregarProducto(p);
                input.value = '';
                dropdown.classList.add('hidden');
            });
            dropdown.appendChild(div);
        });

        dropdown.classList.remove('hidden');
    });

    // Botón agregar producto abre buscador
    document.getElementById('btnAddProduct')?.addEventListener('click', () => {
        input.focus();
    });

    // Cerrar dropdown al hacer click fuera
    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

/* ════════════════════════════════════════
   CARRITO
   ════════════════════════════════════════ */
function agregarProducto(p) {
    const existente = carrito.find(i => i.id === p.id);
    if (existente) {
        if (existente.cantidad < p.stock) existente.cantidad++;
        else { showToast('Stock insuficiente para más unidades', 'warning'); return; }
    } else {
        carrito.push({ id: p.id, nombre: p.nombre, categoria: p.categoria_nombre,
                       precio: p.precio, descuento: 0, cantidad: 1, stock: p.stock });
    }
    renderCarrito();
}

function renderCarrito() {
    const tbody   = document.getElementById('carritoBody');
    const emptyRow = document.getElementById('emptyRow');

    if (carrito.length === 0) {
        tbody.innerHTML = `
            <tr id="emptyRow">
                <td colspan="7" class="table-empty">
                    <div class="table-empty-icon">📦</div>
                    <p>Agrega productos a la venta</p>
                </td>
            </tr>`;
        actualizarResumen();
        return;
    }

    tbody.innerHTML = carrito.map((item, idx) => {
        const descAmt = item.precio * (item.descuento / 100);
        const subtotal = (item.precio - descAmt) * item.cantidad;
        return `
        <tr>
            <td>
                <div class="pv-prod-cell">
                    <strong>${escHtml(item.nombre)}</strong>
                    <span class="pv-sku">Stock: ${item.stock}</span>
                </div>
                <input type="hidden" name="items[${idx}][producto_id]" value="${item.id}">
                <input type="hidden" name="items[${idx}][precio]"      value="${item.precio}">
            </td>
            <td><span class="badge badge-secondary">${escHtml(item.categoria)}</span></td>
            <td>$${item.precio.toLocaleString('es-CO')}</td>
            <td>
                <div class="pv-discount-cell">
                    <input type="number" class="form-control pv-input-sm"
                           value="${item.descuento}" min="0" max="100" step="1"
                           onchange="setDescuento(${idx}, this.value)">
                    <span>%</span>
                    ${descAmt > 0 ? `<span class="pv-disc-amt text-danger">-$${descAmt.toLocaleString('es-CO', {minimumFractionDigits:2})}</span>` : ''}
                </div>
            </td>
            <td>
                <div class="pv-qty-cell">
                    <button type="button" class="pv-qty-btn" onclick="cambiarCantidad(${idx}, -1)">−</button>
                    <input type="number" class="form-control pv-input-sm pv-qty-input"
                           name="items[${idx}][cantidad]"
                           value="${item.cantidad}" min="1" max="${item.stock}"
                           onchange="setCantidad(${idx}, this.value)">
                    <button type="button" class="pv-qty-btn" onclick="cambiarCantidad(${idx}, 1)">+</button>
                </div>
            </td>
            <td><strong>$${subtotal.toLocaleString('es-CO', {minimumFractionDigits:2})}</strong></td>
            <td>
                <button type="button" class="btn btn-ghost btn-sm"
                        onclick="eliminarProducto(${idx})" title="Eliminar">
                    <i data-lucide="trash-2"></i>
                </button>
            </td>
        </tr>`;
    }).join('');

    if (window.lucide) lucide.createIcons();
    actualizarResumen();
}

function cambiarCantidad(idx, delta) {
    const item = carrito[idx];
    const nueva = item.cantidad + delta;
    if (nueva < 1) return;
    if (nueva > item.stock) { showToast('Sin stock suficiente', 'warning'); return; }
    item.cantidad = nueva;
    renderCarrito();
}

function setCantidad(idx, val) {
    const n = Math.max(1, Math.min(carrito[idx].stock, parseInt(val) || 1));
    carrito[idx].cantidad = n;
    renderCarrito();
}

function setDescuento(idx, val) {
    carrito[idx].descuento = Math.max(0, Math.min(100, parseFloat(val) || 0));
    renderCarrito();
}

function eliminarProducto(idx) {
    carrito.splice(idx, 1);
    renderCarrito();
}

document.getElementById('btnVaciar')?.addEventListener('click', () => {
    if (carrito.length && confirm('¿Vaciar carrito?')) {
        carrito = [];
        renderCarrito();
    }
});

/* ════════════════════════════════════════
   RESUMEN DE VENTA
   ════════════════════════════════════════ */
function actualizarResumen() {
    let subtotal   = 0;
    let descTotal  = 0;

    carrito.forEach(item => {
        const d = item.precio * (item.descuento / 100) * item.cantidad;
        subtotal  += item.precio * item.cantidad;
        descTotal += d;
    });

    const total = subtotal - descTotal;

    const fmt = v => '$ ' + v.toLocaleString('es-CO', { minimumFractionDigits: 2 });

    document.getElementById('sumSubtotal').textContent  = fmt(subtotal);
    document.getElementById('sumDescuento').textContent = '- ' + fmt(descTotal);
    document.getElementById('sumSubtotal2').textContent = fmt(subtotal);
    document.getElementById('sumDescuento2').textContent= '- ' + fmt(descTotal);
    document.getElementById('sumTotal').textContent     = fmt(total);

    // Items del resumen
    const summaryEl = document.getElementById('summaryItems');
    if (carrito.length === 0) {
        summaryEl.innerHTML = '<div class="pv-summary-empty"><p>No hay productos agregados</p></div>';
    } else {
        summaryEl.innerHTML = carrito.map(item => {
            const sub = item.precio * item.cantidad;
            return `<div class="pv-summary-item">
                <span>${item.nombre} ×${item.cantidad}</span>
                <span>$${sub.toLocaleString('es-CO', {minimumFractionDigits:2})}</span>
            </div>`;
        }).join('');
    }

    // Cambio (efectivo)
    calcularCambio();
}

/* ════════════════════════════════════════
   PAGO EN EFECTIVO — CAMBIO
   ════════════════════════════════════════ */
function initPaymentToggle() {
    const radios = document.querySelectorAll('input[name="metodo_pago"]');
    const efectDiv = document.getElementById('montoEfectivo');

    radios.forEach(r => r.addEventListener('change', () => {
        efectDiv.style.display = r.value === 'efectivo' ? '' : 'none';
    }));

    document.getElementById('montoRecibido')?.addEventListener('input', calcularCambio);
}

function calcularCambio() {
    const monto    = parseFloat(document.getElementById('montoRecibido')?.value) || 0;
    const totalStr = document.getElementById('sumTotal')?.textContent
                           .replace(/[^0-9,]/g, '').replace(',', '.') || '0';
    const total    = parseFloat(totalStr) || 0;
    const cambio   = Math.max(0, monto - total);
    const el = document.getElementById('cambio');
    if (el) el.value = '$ ' + cambio.toLocaleString('es-CO', { minimumFractionDigits: 2 });
}

/* ════════════════════════════════════════
   VALIDACIÓN DEL FORMULARIO
   ════════════════════════════════════════ */
function initFormValidation() {
    document.getElementById('formVenta')?.addEventListener('submit', function (e) {
        if (!clienteSeleccionado) {
            e.preventDefault();
            showToast('Selecciona un cliente antes de guardar.', 'error');
            document.getElementById('clienteSearch').focus();
            return;
        }
        if (carrito.length === 0) {
            e.preventDefault();
            showToast('Agrega al menos un producto.', 'error');
            document.getElementById('productoSearch').focus();
            return;
        }
        // Mostrar nota factura
        document.getElementById('invNote').style.display = 'flex';
        document.getElementById('invEstado').textContent = 'Completada';
        document.getElementById('invEstado').className   = 'badge badge-success';
        document.getElementById('invNumero').textContent =
            'FV-' + String(Date.now()).slice(-6);
    });
}

/* ════════════════════════════════════════
   HELPERS
   ════════════════════════════════════════ */
function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function showToast(msg, type) {
    if (typeof window.showToast === 'function') {
        window.showToast(msg, type);
    } else {
        alert(msg);
    }
}
