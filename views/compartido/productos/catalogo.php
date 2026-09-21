<?php
/**
 * Vista — Catálogo de productos (cliente)
 */
?>

<div class="ped-header animate-fade-in">
    <div>
        <h1 class="ped-title">🛍️ Catalogo de Productos</h1>
        <p class="ped-subtitle">Encuentra lo que necesitas.</p>
    </div>
</div>

<!-- Filtros -->
<div class="card animate-fade-in mb-lg">
    <form method="GET" action="<?= BASE_URL ?>/index.php" class="ped-filters">
        <input type="hidden" name="controller" value="productos">
        <input type="hidden" name="action" value="catalogo">

        <div class="prov-search-wrap">
            <i data-lucide="search" class="prov-search-icon"></i>
            <input type="text" name="busqueda" class="form-control prov-search-input"
                   placeholder="Buscar producto..." value="<?= e($_GET['busqueda'] ?? '') ?>">
        </div>

        <select name="categoria" class="form-control" style="width:180px;">
            <option value="">Todas las categorias</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($_GET['categoria'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-primary">
            <i data-lucide="filter"></i> Filtrar
        </button>
    </form>
</div>

<!-- Grid de productos -->
<?php if (empty($productos)): ?>
    <div class="card">
        <div class="table-empty">
            <div class="table-empty-icon">📦</div>
            <p>No se encontraron productos.</p>
        </div>
    </div>
<?php else: ?>
    <div class="grid grid-4 stagger animate-fade-in">
        <?php foreach ($productos as $p): ?>
        <div class="card card-interactive" style="padding:0;overflow:hidden;">
            <div style="height:160px;background:var(--color-surface);display:flex;align-items:center;justify-content:center;">
                <?php if (!empty($p['imagen'])): ?>
                    <img src="<?= IMG_URL ?>/productos/<?= e($p['imagen']) ?>"
                         alt="<?= e($p['nombre']) ?>"
                         style="width:100%;height:160px;object-fit:cover;"
                         onerror="this.style.display='none'">
                <?php else: ?>
                    <i data-lucide="package" style="width:48px;height:48px;color:var(--color-text-muted);opacity:.3;"></i>
                <?php endif; ?>
            </div>
            <div style="padding:var(--space-md);">
                <div style="font-size:var(--font-size-xs);color:var(--color-text-muted);margin-bottom:4px;">
                    <?= e($p['categoria_nombre'] ?? '') ?>
                </div>
                <strong style="display:block;margin-bottom:8px;"><?= e($p['nombre']) ?></strong>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:var(--font-size-xl);font-weight:800;color:var(--color-primary);">
                        <?= formatPrice((float)$p['precio']) ?>
                    </span>
                    <?php if ($p['stock'] > 0): ?>
                        <a href="<?= BASE_URL ?>/index.php?controller=carrito&action=agregar&id=<?= $p['id'] ?>"
                           class="btn btn-primary btn-sm">
                            <i data-lucide="shopping-cart"></i>
                        </a>
                    <?php else: ?>
                        <span class="badge badge-danger">Sin stock</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
