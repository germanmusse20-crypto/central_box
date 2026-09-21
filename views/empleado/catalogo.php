<?php
/**
 * UH-29 — Catálogo de productos
 */
$busquedaAct   = $_GET['busqueda']   ?? '';
$categoriaAct  = (int)($_GET['categoria'] ?? 0);
?>

<div class="emp-page-header emp-animate-in">
    <div>
        <h1 class="emp-page-title">Catalogo de <span>Productos</span></h1>
        <p class="emp-page-subtitle"><?= count($productos) ?> producto(s) disponibles.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=nuevaVenta"
       class="emp-btn emp-btn-primary">
        <i data-lucide="plus-circle"></i> Nueva venta
    </a>
</div>

<!-- Filtros -->
<div class="emp-card emp-animate-in" style="margin-bottom:20px;">
    <form method="GET" action="<?= BASE_URL ?>/index.php"
          style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="controller" value="empleado">
        <input type="hidden" name="action" value="catalogo">

        <div style="flex:1;min-width:220px;">
            <label class="emp-label">Buscar producto</label>
            <div class="emp-searchbar">
                <span class="emp-searchbar-icon"><i data-lucide="search"></i></span>
                <input type="text" name="busqueda" class="emp-input"
                       placeholder="Nombre del producto..."
                       value="<?= e($busquedaAct) ?>">
            </div>
        </div>

        <div style="width:200px;">
            <label class="emp-label">Categoria</label>
            <select name="categoria" class="emp-select">
                <option value="0">Todas las categorias</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoriaAct == $cat['id'] ? 'selected' : '' ?>>
                        <?= e($cat['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="emp-btn emp-btn-primary" style="align-self:flex-end;">
            <i data-lucide="filter"></i> Filtrar
        </button>
        <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=catalogo"
           class="emp-btn emp-btn-ghost" style="align-self:flex-end;">
            <i data-lucide="x"></i> Limpiar
        </a>
    </form>
</div>

<!-- Chips de categoría -->
<div class="emp-filter-chips">
    <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=catalogo"
       class="emp-chip <?= !$categoriaAct ? 'active' : '' ?>">
        Todos
    </a>
    <?php foreach ($categorias as $cat): ?>
        <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=catalogo&categoria=<?= $cat['id'] ?>"
           class="emp-chip <?= $categoriaAct == $cat['id'] ? 'active' : '' ?>">
            <?= e($cat['nombre']) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Grid de productos -->
<?php if (empty($productos)): ?>
    <div class="emp-card emp-empty">
        <div class="emp-empty-icon">📦</div>
        <div class="emp-empty-title">No se encontraron productos</div>
        <p class="emp-empty-text">Intenta con otros filtros o terminos de busqueda.</p>
        <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=catalogo"
           class="emp-btn emp-btn-secondary">Limpiar filtros</a>
    </div>
<?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;">
        <?php foreach ($productos as $p):
            $stockPct  = ($p['stock_minimo'] > 0) ? min(100, round($p['stock'] / max($p['stock_minimo'] * 2, 1) * 100)) : 100;
            $stockClass= $p['stock'] <= 0 ? 'low' : ($p['stock'] <= $p['stock_minimo'] ? 'medium' : 'high');
            $stockLabel= $p['stock'] <= 0 ? 'Sin stock' : ($p['stock'] <= $p['stock_minimo'] ? 'Stock bajo' : 'Disponible');
            $badgeClass= $p['stock'] <= 0 ? 'emp-badge-red' : ($p['stock'] <= $p['stock_minimo'] ? 'emp-badge-amber' : 'emp-badge-green');
        ?>
        <div class="emp-card emp-animate-in" style="padding:0;overflow:hidden;cursor:pointer;transition:transform .18s,box-shadow .18s;"
             onclick="empAbrirDetalle(<?= htmlspecialchars(json_encode([
                 'id'          => $p['id'],
                 'nombre'      => $p['nombre'],
                 'precio'      => $p['precio'],
                 'stock'       => $p['stock'],
                 'descripcion' => $p['descripcion'] ?? '',
                 'categoria'   => $p['categoria_nombre'] ?? '—',
                 'imagen'      => $p['imagen'] ?? '',
             ], JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)">

            <!-- Imagen -->
            <div style="height:130px;background:#F9FAFB;display:flex;align-items:center;justify-content:center;position:relative;">
                <?php if (!empty($p['imagen'])): ?>
                    <img src="<?= IMG_URL ?>/productos/<?= e($p['imagen']) ?>"
                         alt="<?= e($p['nombre']) ?>"
                         style="width:100%;height:130px;object-fit:cover;"
                         onerror="this.style.display='none'">
                <?php else: ?>
                    <i data-lucide="package" style="width:40px;height:40px;color:#D1D5DB;"></i>
                <?php endif; ?>
                <!-- Badge stock -->
                <span class="emp-badge <?= $badgeClass ?>"
                      style="position:absolute;top:8px;right:8px;">
                    <?= $stockLabel ?>
                </span>
            </div>

            <!-- Info -->
            <div style="padding:14px;">
                <div style="font-size:.72rem;color:var(--emp-text-sec);margin-bottom:4px;">
                    <?= e($p['categoria_nombre'] ?? '—') ?>
                </div>
                <div style="font-size:.9rem;font-weight:700;color:var(--emp-text);margin-bottom:6px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                    <?= e($p['nombre']) ?>
                </div>
                <div style="font-size:1rem;font-weight:800;color:#10B981;">
                    <?= formatPrice((float)$p['precio']) ?>
                </div>
                <!-- Barra de stock -->
                <div class="emp-stock-bar" style="margin-top:8px;">
                    <div class="emp-stock-fill <?= $stockClass ?>" style="width:<?= $stockPct ?>%"></div>
                </div>
                <div style="font-size:.68rem;color:var(--emp-text-sec);margin-top:3px;">
                    <?= $p['stock'] ?> unidades en stock
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal detalle de producto -->
<div class="emp-modal-overlay" id="modalDetalle">
    <div class="emp-modal" style="max-width:480px;">
        <div class="emp-modal-header">
            <h3 class="emp-modal-title" id="detNombre">—</h3>
            <button class="emp-modal-close" onclick="document.getElementById('modalDetalle').classList.remove('active')">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div id="detBody">
            <p id="detCategoria" style="font-size:.78rem;color:var(--emp-text-sec);margin-bottom:12px;"></p>
            <div id="detImgWrap" style="height:180px;background:#F9FAFB;border-radius:12px;margin-bottom:16px;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                <img id="detImg" src="" alt="" style="width:100%;height:180px;object-fit:cover;">
            </div>
            <p id="detDesc" style="font-size:.875rem;color:var(--emp-text-sec);line-height:1.7;margin-bottom:16px;"></p>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px;background:#F9FAFB;border-radius:10px;margin-bottom:16px;">
                <div>
                    <div style="font-size:.72rem;color:var(--emp-text-sec);">Precio</div>
                    <div style="font-size:1.3rem;font-weight:800;color:#10B981;" id="detPrecio"></div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:.72rem;color:var(--emp-text-sec);">Stock</div>
                    <div style="font-size:1.1rem;font-weight:700;" id="detStock"></div>
                </div>
            </div>
            <a id="detAgregar" href="#" class="emp-btn emp-btn-primary emp-btn-block emp-btn-lg">
                <i data-lucide="plus-circle"></i> Agregar a venta
            </a>
        </div>
    </div>
</div>

<script>
function empAbrirDetalle(p) {
    document.getElementById('detNombre').textContent    = p.nombre;
    document.getElementById('detCategoria').textContent = p.categoria;
    document.getElementById('detDesc').textContent      = p.descripcion || 'Sin descripcion disponible.';
    document.getElementById('detPrecio').textContent    = '$ ' + Number(p.precio).toLocaleString('es-CO');
    document.getElementById('detStock').textContent     = p.stock > 0 ? p.stock + ' unidades' : 'Sin stock';
    document.getElementById('detStock').style.color     = p.stock > 0 ? '#10B981' : '#EF4444';

    const img = document.getElementById('detImg');
    if (p.imagen) {
        img.src = '<?= IMG_URL ?>/productos/' + p.imagen;
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
    }

    const btn = document.getElementById('detAgregar');
    if (p.stock > 0) {
        btn.href    = '<?= BASE_URL ?>/index.php?controller=empleado&action=nuevaVenta';
        btn.style.opacity = '1';
        btn.style.pointerEvents = 'auto';
    } else {
        btn.href = '#';
        btn.style.opacity = '.4';
        btn.style.pointerEvents = 'none';
    }

    document.getElementById('modalDetalle').classList.add('active');
    lucide.createIcons();
}

document.getElementById('modalDetalle')?.addEventListener('click', function (e) {
    if (e.target === this) this.classList.remove('active');
});
</script>
