<?php
/**
 * UH-31 — Consulta de stock / inventario
 */
$busquedaAct = $_GET['busqueda'] ?? '';
$totalProd   = count($productos);
$sinStock    = count(array_filter($productos, fn($p) => $p['stock'] <= 0));
$bajoStock   = count(array_filter($productos, fn($p) => $p['stock'] > 0 && $p['stock'] <= $p['stock_minimo']));
$conStock    = $totalProd - $sinStock - $bajoStock;
?>

<div class="emp-page-header emp-animate-in">
    <div>
        <h1 class="emp-page-title">Inventario / <span>Stock</span></h1>
        <p class="emp-page-subtitle">Consulta la disponibilidad de productos en tiempo real.</p>
    </div>
</div>

<!-- Stats rápidas -->
<div class="emp-stats">
    <div class="emp-stat emp-animate-in">
        <div class="emp-stat-header">
            <div class="emp-stat-icon green"><i data-lucide="package"></i></div>
        </div>
        <div class="emp-stat-label">Total productos</div>
        <div class="emp-stat-value"><?= $totalProd ?></div>
    </div>
    <div class="emp-stat emp-animate-in" style="animation-delay:.05s">
        <div class="emp-stat-header">
            <div class="emp-stat-icon green"><i data-lucide="check-circle"></i></div>
        </div>
        <div class="emp-stat-label">Con stock</div>
        <div class="emp-stat-value"><?= $conStock ?></div>
    </div>
    <div class="emp-stat emp-animate-in" style="animation-delay:.1s">
        <div class="emp-stat-header">
            <div class="emp-stat-icon amber"><i data-lucide="alert-triangle"></i></div>
        </div>
        <div class="emp-stat-label">Stock bajo</div>
        <div class="emp-stat-value"><?= $bajoStock ?></div>
    </div>
    <div class="emp-stat emp-animate-in" style="animation-delay:.15s">
        <div class="emp-stat-header">
            <div class="emp-stat-icon red"><i data-lucide="x-circle"></i></div>
        </div>
        <div class="emp-stat-label">Sin stock</div>
        <div class="emp-stat-value"><?= $sinStock ?></div>
    </div>
</div>

<!-- Filtros -->
<div class="emp-card emp-animate-in" style="margin-bottom:20px;">
    <form method="GET" action="<?= BASE_URL ?>/index.php"
          style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="controller" value="empleado">
        <input type="hidden" name="action" value="stock">

        <div style="flex:1;min-width:220px;">
            <label class="emp-label">Buscar producto</label>
            <div class="emp-searchbar">
                <span class="emp-searchbar-icon"><i data-lucide="search"></i></span>
                <input type="text" name="busqueda" class="emp-input"
                       placeholder="Nombre del producto..."
                       value="<?= e($busquedaAct) ?>">
            </div>
        </div>

        <button type="submit" class="emp-btn emp-btn-primary" style="align-self:flex-end;">
            <i data-lucide="search"></i> Buscar
        </button>
        <?php if ($busquedaAct): ?>
            <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=stock"
               class="emp-btn emp-btn-ghost" style="align-self:flex-end;">
                <i data-lucide="x"></i> Limpiar
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Chips de estado rápido -->
<div class="emp-filter-chips" style="margin-bottom:16px;">
    <button type="button" onclick="filtrarStock('todos')" class="emp-chip active" id="chip-todos">Todos (<?= $totalProd ?>)</button>
    <button type="button" onclick="filtrarStock('ok')"    class="emp-chip" id="chip-ok">✓ Disponible (<?= $conStock ?>)</button>
    <button type="button" onclick="filtrarStock('bajo')"  class="emp-chip" id="chip-bajo">⚠ Bajo (<?= $bajoStock ?>)</button>
    <button type="button" onclick="filtrarStock('cero')"  class="emp-chip" id="chip-cero">⛔ Sin stock (<?= $sinStock ?>)</button>
</div>

<!-- Tabla de stock -->
<?php if (empty($productos)): ?>
    <div class="emp-card emp-empty">
        <div class="emp-empty-icon">📦</div>
        <div class="emp-empty-title">Sin resultados</div>
        <p class="emp-empty-text">No se encontraron productos con esa busqueda.</p>
    </div>
<?php else: ?>
    <div class="emp-table-wrap emp-animate-in">
        <table class="emp-table" id="stockTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>Categoria</th>
                    <th>Precio</th>
                    <th>Stock actual</th>
                    <th>Stock min.</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p):
                    $so = $p['stock'] <= 0;
                    $sl = !$so && $p['stock'] <= $p['stock_minimo'];
                    $estadoKey  = $so ? 'cero' : ($sl ? 'bajo' : 'ok');
                    $badgeClass = $so ? 'emp-badge-red' : ($sl ? 'emp-badge-amber' : 'emp-badge-green');
                    $estadoTxt  = $so ? 'Sin stock' : ($sl ? 'Stock bajo' : 'Disponible');
                    $stockPct   = ($p['stock_minimo'] > 0)
                        ? min(100, round($p['stock'] / max($p['stock_minimo'] * 3, 1) * 100))
                        : min(100, $p['stock'] > 0 ? 100 : 0);
                    $barClass   = $so ? 'low' : ($sl ? 'medium' : 'high');
                ?>
                <tr data-estado="<?= $estadoKey ?>">
                    <td style="color:var(--emp-text-sec);font-size:.78rem;"><?= $p['id'] ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <?php if (!empty($p['imagen'])): ?>
                                <img src="<?= IMG_URL ?>/productos/<?= e($p['imagen']) ?>"
                                     style="width:36px;height:36px;border-radius:8px;object-fit:cover;"
                                     onerror="this.style.display='none'">
                            <?php else: ?>
                                <div style="width:36px;height:36px;border-radius:8px;background:#F3F4F6;display:flex;align-items:center;justify-content:center;">
                                    <i data-lucide="package" style="width:16px;height:16px;color:#9CA3AF;"></i>
                                </div>
                            <?php endif; ?>
                            <strong><?= e($p['nombre']) ?></strong>
                        </div>
                    </td>
                    <td><span class="emp-badge emp-badge-indigo"><?= e($p['categoria_nombre'] ?? '—') ?></span></td>
                    <td><strong><?= formatPrice((float)$p['precio']) ?></strong></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <strong style="font-size:1rem;color:<?= $so ? '#EF4444' : ($sl ? '#D97706' : '#10B981') ?>">
                                <?= $p['stock'] ?>
                            </strong>
                            <div style="width:80px;">
                                <div class="emp-stock-bar">
                                    <div class="emp-stock-fill <?= $barClass ?>" style="width:<?= $stockPct ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="color:var(--emp-text-sec);"><?= $p['stock_minimo'] ?></td>
                    <td><span class="emp-badge <?= $badgeClass ?>"><?= $estadoTxt ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
function filtrarStock(estado) {
    document.querySelectorAll('.emp-chip').forEach(c => c.classList.remove('active'));
    document.getElementById('chip-' + estado)?.classList.add('active');
    document.querySelectorAll('#stockTable tbody tr').forEach(function (tr) {
        tr.style.display = estado === 'todos' || tr.dataset.estado === estado ? '' : 'none';
    });
}
</script>
