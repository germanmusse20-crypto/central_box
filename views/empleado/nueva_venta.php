<?php
/**
 * UH-30+32+35 — Nueva venta / POS con promociones
 */
?>

<div class="emp-page-header emp-animate-in">
    <div>
        <h1 class="emp-page-title">🛒 Nueva <span>Venta</span></h1>
        <p class="emp-page-subtitle">Registra una venta de forma rapida y sencilla.</p>
    </div>
    <a href="<?= e($historialUrl ?? BASE_URL . '/index.php?controller=empleado&action=historial') ?>"
       class="emp-btn emp-btn-ghost">
        <i data-lucide="clock"></i> Historial
    </a>
</div>

<div class="ventas-pos-context" aria-label="Controles de la venta">
    <div><i data-lucide="receipt"></i><span><strong>Registro</strong><small>Genera el comprobante</small></span></div>
    <div><i data-lucide="package-check"></i><span><strong>Stock protegido</strong><small>Descuento automático</small></span></div>
    <div><i data-lucide="shield-check"></i><span><strong>Validación</strong><small>Evita ventas imposibles</small></span></div>
    <div><i data-lucide="history"></i><span><strong>Historial</strong><small>Consulta cada operación</small></span></div>
</div>

<form action="<?= e($guardarVentaUrl ?? BASE_URL . '/index.php?controller=empleado&action=confirmarVenta') ?>"
      method="POST" id="empVentaForm" class="ventas-pos-page">
    <?= csrfField() ?>
    <input type="hidden" name="promo_pct" id="promoPct" value="0">
    <input type="hidden" name="cliente_id" id="clienteIdHidden" value="">

<div class="emp-pos-layout">

    <!-- ══ COL 1: CARRITO ══ -->
    <div>
        <div class="emp-pos-section">
            <div class="emp-pos-section-title">
                <span class="emp-pos-section-num">1</span>
                <i data-lucide="shopping-cart" style="width:16px;height:16px;color:#10B981;"></i>
                Carrito de venta
            </div>

            <div id="empCartEmpty" style="text-align:center;padding:24px;color:var(--emp-text-sec);font-size:.85rem;">
                <i data-lucide="shopping-cart" style="width:28px;height:28px;opacity:.25;display:block;margin:0 auto 8px;"></i>
                Agrega productos desde el catalogo
            </div>
            <div id="empCartItems" style="min-height:80px; display:none;">
            </div>

            <!-- Descuento / promo -->
            <div style="border-top:1px solid var(--emp-border);padding-top:12px;margin-top:8px;">
                <button type="button" class="emp-btn emp-btn-ghost emp-btn-sm"
                        id="togglePromo"
                        style="font-size:.78rem;color:#6366F1;border-color:#C7D2FE;">
                    <i data-lucide="tag"></i> Aplicar promocion
                </button>
                <div id="promoPanel" style="display:none;margin-top:12px;">
                    <?php if (empty($promociones)): ?>
                        <p style="font-size:.78rem;color:var(--emp-text-sec);text-align:center;padding:10px;">
                            No hay promociones activas disponibles.
                        </p>
                    <?php else: ?>
                        <div class="emp-promo-list">
                            <?php foreach ($promociones as $promo): ?>
                                <div class="emp-promo-item"
                                     data-pct="<?= $promo['descuento'] ?>"
                                     data-nombre="<?= e($promo['nombre']) ?>"
                                     onclick="empAplicarPromo(this)">
                                    <div>
                                        <div class="emp-promo-name"><?= e($promo['nombre']) ?></div>
                                        <div class="emp-promo-desc">
                                            Valido hasta <?= date('d/m/Y', strtotime($promo['fecha_fin'])) ?>
                                        </div>
                                    </div>
                                    <div class="emp-promo-pct">-<?= $promo['descuento'] ?>%</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <button type="button" onclick="empQuitarPromo()"
                            class="emp-btn emp-btn-ghost emp-btn-sm"
                            style="margin-top:8px;color:#EF4444;border-color:#FECACA;font-size:.72rem;">
                        <i data-lucide="x"></i> Quitar promocion
                    </button>
                </div>

                <div id="promoAplicada" style="display:none;margin-top:10px;"></div>
            </div>

            <!-- Totales -->
            <div class="emp-cart-totals">
                <div class="emp-total-row">
                    <span>Subtotal</span>
                    <span id="empSubtotal">$ 0</span>
                </div>
                <div class="emp-total-row" id="descRow" style="display:none;color:#6366F1;font-weight:600;">
                    <span id="descLabel">Descuento</span>
                    <span id="empDescuento">- $ 0</span>
                </div>
                <div class="emp-total-final">
                    <span>Total</span>
                    <span id="empTotal">$ 0</span>
                </div>
            </div>

            <!-- Botones -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px;">
                <button type="button" onclick="empLimpiarCarrito()"
                        class="emp-btn emp-btn-ghost">
                    <i data-lucide="trash-2"></i> Limpiar
                </button>
                <button type="submit" id="btnConfirmar"
                        class="emp-btn emp-btn-primary" disabled>
                    <i data-lucide="check-circle"></i> Registrar venta
                </button>
            </div>
        </div>

        <!-- Método de pago -->
        <div class="emp-pos-section" style="margin-top:16px;">
            <div class="emp-pos-section-title">
                <span class="emp-pos-section-num">3</span>
                <i data-lucide="credit-card" style="width:16px;height:16px;color:#10B981;"></i>
                Metodo de pago
            </div>
            <select name="metodo_pago" class="emp-select">
                <option value="efectivo">💵 Efectivo</option>
                <option value="tarjeta">💳 Tarjeta de credito / debito</option>
                <option value="transferencia">🏦 Transferencia bancaria</option>
            </select>

            <div style="margin-top:14px;" id="montoWrap">
                <label class="emp-label" style="margin-bottom:6px;">Monto recibido (efectivo)</label>
                <input type="number" id="montoRec" class="emp-input"
                       placeholder="$ 0.00" step="0.01" min="0">
                <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:.85rem;">
                    <span style="color:var(--emp-text-sec);">Cambio</span>
                    <span id="cambio" style="font-weight:800;color:#10B981;">$ 0</span>
                </div>
            </div>
        </div>

        <!-- Notas -->
        <div class="emp-pos-section" style="margin-top:16px;">
            <div class="emp-pos-section-title">
                <span class="emp-pos-section-num">4</span>
                <i data-lucide="file-text" style="width:16px;height:16px;color:#10B981;"></i>
                Observaciones
            </div>
            <textarea name="notas" class="emp-input" rows="3"
                      placeholder="Notas adicionales sobre la venta..."
                      style="resize:vertical;"></textarea>
        </div>
    </div>

    <!-- ══ COL 2: CATÁLOGO ══ -->
    <div class="emp-pos-section">
        <div class="emp-pos-section-title">
            <span class="emp-pos-section-num">2</span>
            <i data-lucide="grid-2x2" style="width:16px;height:16px;color:#10B981;"></i>
            Seleccionar productos
        </div>

        <!-- Buscador + filtro -->
        <div style="display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap;">
            <div class="emp-searchbar" style="flex:1;min-width:160px;">
                <span class="emp-searchbar-icon"><i data-lucide="search"></i></span>
                <input type="text" id="posBuscar" class="emp-input"
                       placeholder="Buscar producto...">
            </div>
            <select id="posCatFil" class="emp-select" style="width:160px;">
                <option value="">Todas</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= e($cat['nombre']) ?>"><?= e($cat['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Grid de productos -->
        <?php if (empty($productos)): ?>
            <div class="emp-empty" style="min-height:220px;padding:32px 20px;">
                <div class="emp-empty-icon"><i data-lucide="package-x"></i></div>
                <div class="emp-empty-title">Catálogo sin productos disponibles</div>
                <p class="emp-empty-text">Registra productos con precio y stock inicial para habilitar las ventas.</p>
                <a href="<?= BASE_URL ?>/index.php?controller=productos&action=crear" class="emp-btn emp-btn-primary emp-btn-sm">
                    <i data-lucide="plus"></i> Registrar producto
                </a>
            </div>
        <?php else: ?>
        <div class="emp-product-grid" id="posGrid">
            <?php foreach ($productos as $p):
                $so = $p['stock'] <= 0;
                $sl = !$so && $p['stock'] <= $p['stock_minimo'];
            ?>
            <div class="emp-product-card <?= $so ? 'out-stock' : '' ?>"
                 data-nombre="<?= e(strtolower($p['nombre'])) ?>"
                 data-cat="<?= e($p['categoria_nombre'] ?? '') ?>"
                 onclick="<?= $so ? "empToast('Sin stock disponible para este producto.','error')" : "empAgregar({$p['id']},'" . e(addslashes($p['nombre'])) . "',{$p['precio']},{$p['stock']})" ?>">
                <div class="emp-product-img">
                    <?php if (!empty($p['imagen'])): ?>
                        <img src="<?= IMG_URL ?>/productos/<?= e($p['imagen']) ?>"
                             onerror="this.style.display='none'">
                    <?php else: ?>
                        <i data-lucide="package"></i>
                    <?php endif; ?>
                </div>
                <div class="emp-product-info">
                    <div class="emp-product-name"><?= e($p['nombre']) ?></div>
                    <div class="emp-product-price"><?= formatPrice((float)$p['precio']) ?></div>
                    <div class="emp-product-stock <?= $so ? 'emp-stock-out' : ($sl ? 'emp-stock-low' : 'emp-stock-ok') ?>">
                        <?= $so ? '⛔ Sin stock' : ($sl ? '⚠️ Stock bajo (' . $p['stock'] . ')' : '✓ ' . $p['stock'] . ' uds.') ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ══ COL 3: RESUMEN ══ -->
    <div>
        <div class="emp-pos-section" style="position:sticky;top:88px;">
            <div class="emp-pos-section-title">
                <i data-lucide="file-text" style="width:16px;height:16px;color:#10B981;"></i>
                Resumen de venta
            </div>

            <div style="font-size:.78rem;color:var(--emp-text-sec);margin-bottom:6px;">
                Folio
            </div>
            <div style="font-size:1rem;font-weight:800;color:#6366F1;margin-bottom:16px;">
                VTA-<?= strtoupper(substr(uniqid(), -5)) ?>
            </div>

            <div style="font-size:.78rem;color:var(--emp-text-sec);margin-bottom:4px;">Empleado</div>
            <div style="font-size:.88rem;font-weight:700;margin-bottom:16px;">
                <?= e(getUser()['nombre'] ?? '') ?>
            </div>

            <div style="font-size:.78rem;color:var(--emp-text-sec);margin-bottom:4px;">Fecha y hora</div>
            <div style="font-size:.82rem;margin-bottom:16px;">
                <?= date('d/m/Y H:i') ?>
            </div>

            <!-- Cliente opcional -->
            <div class="emp-form-group">
                <label class="emp-label">Cliente (opcional)</label>
                <div class="emp-searchbar">
                    <span class="emp-searchbar-icon"><i data-lucide="user"></i></span>
                    <input type="text" id="clienteBuscar" class="emp-input"
                           placeholder="Buscar cliente..."
                           autocomplete="off">
                </div>
                <div id="clienteDrop" style="display:none;position:absolute;z-index:99;background:white;border:1px solid var(--emp-border);border-radius:10px;box-shadow:var(--emp-shadow-md);width:100%;max-height:180px;overflow-y:auto;"></div>
                <div id="clienteSel" style="display:none;margin-top:6px;font-size:.78rem;color:#10B981;font-weight:600;"></div>
            </div>

            <!-- Items resumen -->
            <div id="resumItems" style="margin-bottom:14px;font-size:.8rem;color:var(--emp-text-sec);">
                <p style="text-align:center;opacity:.5;">Sin productos</p>
            </div>

            <div style="height:1px;background:var(--emp-border);margin-bottom:12px;"></div>

            <div style="display:flex;justify-content:space-between;font-size:.85rem;color:var(--emp-text-sec);margin-bottom:4px;">
                <span>Subtotal</span><span id="res-sub">$ 0</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:.85rem;color:#6366F1;font-weight:700;margin-bottom:4px;" id="res-desc-row" style="display:none;">
                <span>Descuento</span><span id="res-desc">- $ 0</span>
            </div>

            <div style="height:1px;background:var(--emp-border);margin:10px 0;"></div>

            <div style="display:flex;justify-content:space-between;align-items:center;font-size:1.1rem;font-weight:800;">
                <span>Total</span>
                <span style="background:linear-gradient(135deg,#10B981,#6366F1);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;" id="res-total">$ 0</span>
            </div>

            <button type="submit" id="btnConfirmar2" disabled
                    class="emp-btn emp-btn-primary emp-btn-block emp-btn-lg"
                    style="margin-top:16px;">
                <i data-lucide="check-circle"></i> Registrar venta
            </button>

            <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=dashboard"
               class="emp-btn emp-btn-ghost emp-btn-block" style="margin-top:8px;">
                Cancelar
            </a>
        </div>
    </div>

</div><!-- /.emp-pos-layout -->
</form>

<!-- Toast container para este módulo -->
<div class="emp-toast-container" id="empToasts"></div>

<script>
// ── Datos de productos ──
const EMP_PRODUCTS = <?= json_encode(array_map(fn($p) => [
    'id'       => $p['id'],
    'nombre'   => $p['nombre'],
    'precio'   => (float) $p['precio'],
    'stock'    => (int)   $p['stock'],
    'cat'      => $p['categoria_nombre'] ?? '',
], $productos), JSON_UNESCAPED_UNICODE) ?>;

let empCart = [];      // {id, nombre, precio, stock, qty}
let empPromo = 0;      // porcentaje de descuento

// ── Agregar producto ──
function empAgregar(id, nombre, precio, stock) {
    const ex = empCart.find(i => i.id === id);
    if (ex) {
        if (ex.qty >= stock) { empToast('Stock maximo alcanzado para este producto.','warning'); return; }
        ex.qty++;
    } else {
        empCart.push({id, nombre, precio, stock, qty: 1});
    }
    empRenderCart();
    empToast(nombre + ' agregado al carrito', 'success');
}

// ── Render carrito ──
function empRenderCart() {
    const wrap = document.getElementById('empCartItems');
    const empty = document.getElementById('empCartEmpty');

    if (empCart.length === 0) {
        empty.style.display = '';
        wrap.style.display = 'none';
        wrap.innerHTML = '';
        document.getElementById('btnConfirmar').disabled = true;
        document.getElementById('btnConfirmar2').disabled = true;
        empActualizar();
        return;
    }

    empty.style.display = 'none';
    wrap.style.display = 'block';
    wrap.innerHTML = empCart.map((item, i) => `
        <div class="emp-cart-item">
            <input type="hidden" name="items[${i}][producto_id]" value="${item.id}">
            <input type="hidden" name="items[${i}][precio]" value="${item.precio}">
            <div class="emp-cart-item-name">${escHtml(item.nombre)}</div>
            <div class="emp-cart-item-price">$ ${item.precio.toLocaleString('es-CO')}</div>
            <div class="emp-qty-ctrl">
                <button type="button" class="emp-qty-btn" onclick="empQty(${i},-1)">−</button>
                <input type="number" class="emp-qty-input" name="items[${i}][cantidad]"
                       value="${item.qty}" min="1" max="${item.stock}"
                       onchange="empSetQty(${i},this.value)">
                <button type="button" class="emp-qty-btn" onclick="empQty(${i},1)">+</button>
            </div>
            <button type="button" class="emp-cart-del" onclick="empRemove(${i})">
                <i data-lucide="trash-2"></i>
            </button>
        </div>`).join('');

    document.getElementById('btnConfirmar').disabled = false;
    document.getElementById('btnConfirmar2').disabled = false;
    if (window.lucide) lucide.createIcons();
    empActualizar();
}

function empQty(i, d) {
    const item = empCart[i];
    const n = item.qty + d;
    if (n < 1) return;
    if (n > item.stock) { empToast('Sin stock suficiente.','warning'); return; }
    item.qty = n;
    empRenderCart();
}

function empSetQty(i, v) {
    empCart[i].qty = Math.max(1, Math.min(empCart[i].stock, parseInt(v)||1));
    empRenderCart();
}

function empRemove(i) { empCart.splice(i,1); empRenderCart(); }
function empLimpiarCarrito() { if (empCart.length && confirm('Vaciar carrito?')) { empCart=[]; empRenderCart(); } }

// ── Calcular totales ──
function empActualizar() {
    const subtotal = empCart.reduce((s,i) => s + i.precio * i.qty, 0);
    const descAmt  = subtotal * (empPromo / 100);
    const total    = subtotal - descAmt;
    const fmt = v => '$ ' + v.toLocaleString('es-CO', {minimumFractionDigits:0, maximumFractionDigits:0});

    document.getElementById('empSubtotal').textContent = fmt(subtotal);
    document.getElementById('empTotal').textContent    = fmt(total);
    document.getElementById('res-sub').textContent     = fmt(subtotal);
    document.getElementById('res-total').textContent   = fmt(total);

    const descRow  = document.getElementById('descRow');
    const descRow2 = document.getElementById('res-desc-row');

    if (descAmt > 0) {
        document.getElementById('empDescuento').textContent = '- ' + fmt(descAmt);
        document.getElementById('res-desc').textContent     = '- ' + fmt(descAmt);
        descRow.style.display  = 'flex';
        descRow2.style.display = 'flex';
    } else {
        descRow.style.display  = 'none';
        descRow2.style.display = 'none';
    }

    // Resumen lateral
    const resumEl = document.getElementById('resumItems');
    if (empCart.length === 0) {
        resumEl.innerHTML = '<p style="text-align:center;opacity:.5;font-size:.8rem;">Sin productos</p>';
    } else {
        resumEl.innerHTML = empCart.map(i =>
            `<div style="display:flex;justify-content:space-between;padding:3px 0;font-size:.8rem;">
                <span>${escHtml(i.nombre)} ×${i.qty}</span>
                <span>$ ${(i.precio*i.qty).toLocaleString('es-CO',{minimumFractionDigits:0})}</span>
            </div>`
        ).join('');
    }

    // Cambio efectivo
    const recibido = parseFloat(document.getElementById('montoRec')?.value) || 0;
    const cambio = Math.max(0, recibido - total);
    document.getElementById('cambio').textContent = '$ ' + cambio.toLocaleString('es-CO',{minimumFractionDigits:0});
}

document.getElementById('montoRec')?.addEventListener('input', empActualizar);

// ── Promociones ──
document.getElementById('togglePromo')?.addEventListener('click', function () {
    const panel = document.getElementById('promoPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
});

function empAplicarPromo(el) {
    if (el.classList.contains('expired') || el.classList.contains('unauthorized')) return;
    document.querySelectorAll('.emp-promo-item').forEach(i => i.classList.remove('selected'));
    el.classList.add('selected');
    empPromo = parseFloat(el.dataset.pct) || 0;
    document.getElementById('promoPct').value = empPromo;
    document.getElementById('promoPanel').style.display = 'none';
    document.getElementById('promoAplicada').style.display = 'block';
    document.getElementById('promoAplicada').innerHTML =
        `<span class="emp-badge emp-badge-green"><i data-lucide="tag" style="width:11px;height:11px;"></i> ${el.dataset.nombre} (${empPromo}% dto.)</span>`;
    if (window.lucide) lucide.createIcons();
    empActualizar();
    empToast('Promocion aplicada: ' + empPromo + '% de descuento', 'success');
}

function empQuitarPromo() {
    empPromo = 0;
    document.getElementById('promoPct').value = 0;
    document.getElementById('promoAplicada').style.display = 'none';
    document.querySelectorAll('.emp-promo-item').forEach(i => i.classList.remove('selected'));
    empActualizar();
}

// ── Filtros catálogo ──
function filtrarProductos() {
    const q   = document.getElementById('posBuscar')?.value.toLowerCase() || '';
    const cat = document.getElementById('posCatFil')?.value || '';
    document.querySelectorAll('#posGrid .emp-product-card').forEach(function (c) {
        const n = c.dataset.nombre || '';
        const k = c.dataset.cat   || '';
        const matchQ   = !q   || n.includes(q);
        const matchCat = !cat || k === cat;
        c.style.display = matchQ && matchCat ? '' : 'none';
    });
}

document.getElementById('posBuscar')?.addEventListener('input', filtrarProductos);
document.getElementById('posCatFil')?.addEventListener('change', filtrarProductos);

// Método de pago toggle
document.querySelector('select[name="metodo_pago"]')?.addEventListener('change', function () {
    document.getElementById('montoWrap').style.display = this.value === 'efectivo' ? '' : 'none';
});

// ── Validación antes de enviar ──
document.getElementById('empVentaForm')?.addEventListener('submit', function (e) {
    if (empCart.length === 0) {
        e.preventDefault();
        empToast('Agrega al menos un producto al carrito.', 'error');
    }
});

// ── Toast ──
function empToast(msg, type) {
    type = type || 'info';
    const icons = {success:'check-circle', error:'alert-circle', warning:'alert-triangle', info:'info'};
    const colors = {success:'#10B981', error:'#EF4444', warning:'#F59E0B', info:'#6366F1'};
    const c = document.getElementById('empToasts');
    if (!c) return;
    const t = document.createElement('div');
    t.className = 'emp-toast ' + type;
    t.innerHTML = `<i data-lucide="${icons[type]||'info'}" class="emp-toast-icon" style="color:${colors[type]};"></i>
        <span style="flex:1;">${msg}</span>
        <button class="emp-toast-close" onclick="this.parentElement.remove()">×</button>`;
    c.appendChild(t);
    if (window.lucide) lucide.createIcons();
    setTimeout(function () {
        t.style.opacity = '0'; t.style.transition = 'opacity .3s';
        setTimeout(() => t.remove(), 300);
    }, 3500);
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
