<?php
/**
 * Catálogo cliente.
 * UH-18: visualizar productos | UH-20: buscar | UH-21: filtrar categoría.
 * UH-22: agregar productos disponibles al carrito.
 */
$busquedaAct = trim((string)($_GET['busqueda'] ?? ''));
$categoriaAct = (int)($_GET['categoria'] ?? 0);
$productos = is_array($productos ?? null) ? $productos : [];
$categorias = is_array($categorias ?? null) ? $categorias : [];
$carritoCount = (int)($carritoCount ?? 0);
$totalProds = (int)($totalProds ?? count($productos));
$totalPages = max(1, (int)($totalPages ?? 1));
$page = max(1, (int)($_GET['page'] ?? 1));

$catalogUrl = BASE_URL . '/index.php?controller=cliente&action=catalogo';
?>

<div class="cli-page-header animate-fade-in">
    <div>
        <div class="cli-chip"><i data-lucide="store"></i> TIENDA CENTRAL BOX</div>
        <h1 class="cli-page-title">Catálogo de <span>productos</span></h1>
        <p class="cli-page-subtitle"><?= number_format($totalProds) ?> producto<?= $totalProds === 1 ? '' : 's' ?> disponibles para comprar.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=carrito" class="cli-btn cli-btn-secondary">
        <i data-lucide="shopping-cart"></i> Mi carrito<?php if ($carritoCount > 0): ?> (<?= $carritoCount ?>)<?php endif; ?>
    </a>
</div>

<div class="cli-card animate-fade-in-up">
    <form method="GET" action="<?= BASE_URL ?>/index.php" class="cli-form-group" style="display:grid;grid-template-columns:minmax(0,1fr) 220px auto auto;gap:10px;align-items:end;margin:0;">
        <input type="hidden" name="controller" value="cliente">
        <input type="hidden" name="action" value="catalogo">
        <div>
            <label class="cli-label" for="busqueda">Buscar producto</label>
            <div class="cli-search-wrap">
                <span class="cli-search-icon"><i data-lucide="search"></i></span>
                <input id="busqueda" name="busqueda" class="cli-input" type="search" value="<?= e($busquedaAct) ?>" placeholder="Nombre del producto...">
            </div>
        </div>
        <div>
            <label class="cli-label" for="categoria">Categoría</label>
            <select id="categoria" name="categoria" class="cli-select">
                <option value="0">Todas las categorías</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= (int)$categoria['id'] ?>" <?= $categoriaAct === (int)$categoria['id'] ? 'selected' : '' ?>><?= e($categoria['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="cli-btn cli-btn-primary" type="submit"><i data-lucide="filter"></i> Buscar</button>
        <?php if ($busquedaAct || $categoriaAct): ?><a class="cli-btn cli-btn-ghost" href="<?= e($catalogUrl) ?>"><i data-lucide="x"></i> Limpiar</a><?php endif; ?>
    </form>

    <div class="cli-filter-bar" style="margin-bottom:0;">
        <a class="cli-filter-chip <?= !$categoriaAct ? 'active' : '' ?>" href="<?= e($catalogUrl) ?>">Todos</a>
        <?php foreach ($categorias as $categoria): ?>
            <a class="cli-filter-chip <?= $categoriaAct === (int)$categoria['id'] ? 'active' : '' ?>" href="<?= e($catalogUrl . '&categoria=' . (int)$categoria['id']) ?>"><?= e($categoria['nombre']) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (!$productos): ?>
    <div class="cli-card cli-empty animate-fade-in-up">
        <div class="cli-empty-icon"><i data-lucide="package-search"></i></div>
        <div class="cli-empty-title">No encontramos productos</div>
        <p class="cli-empty-text"><?= $busquedaAct || $categoriaAct ? 'Prueba con otra búsqueda o categoría.' : 'Aún no hay productos disponibles en la tienda.' ?></p>
        <?php if ($busquedaAct || $categoriaAct): ?><a href="<?= e($catalogUrl) ?>" class="cli-btn cli-btn-secondary">Ver todo el catálogo</a><?php endif; ?>
    </div>
<?php else: ?>
    <div class="cli-catalog-grid">
        <?php foreach ($productos as $producto):
            $stock = (int)($producto['stock'] ?? 0);
            $minimo = (int)($producto['stock_minimo'] ?? 0);
            $agotado = $stock <= 0;
            $stockBajo = !$agotado && $minimo > 0 && $stock <= $minimo;
            $stockMax = max($minimo * 2, 1);
            $stockPct = min(100, max(5, (int)round($stock / $stockMax * 100)));
            $stockClass = $agotado ? 'low' : ($stockBajo ? 'medium' : '');
        ?>
            <article class="cli-product-card <?= $agotado ? 'agotado' : '' ?>">
                <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=producto&id=<?= (int)$producto['id'] ?>" style="text-decoration:none;color:inherit;">
                    <div class="cli-product-img">
                        <?php if (!empty($producto['imagen'])): ?>
                            <img src="<?= IMG_URL ?>/productos/<?= e($producto['imagen']) ?>" alt="<?= e($producto['nombre']) ?>" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div class="cli-placeholder" style="display:none;align-items:center;justify-content:center;"><i data-lucide="package"></i></div>
                        <?php else: ?>
                            <div class="cli-placeholder"><i data-lucide="package"></i></div>
                        <?php endif; ?>
                        <span class="cli-badge <?= $agotado ? 'cli-badge-red' : ($stockBajo ? 'cli-badge-amber' : 'cli-badge-green') ?> cli-product-badge">
                            <?= $agotado ? 'Agotado' : ($stockBajo ? 'Stock bajo' : 'Disponible') ?>
                        </span>
                    </div>
                    <div class="cli-product-body">
                        <div class="cli-product-cat"><?= e($producto['categoria_nombre'] ?? 'Producto') ?></div>
                        <div class="cli-product-name"><?= e($producto['nombre']) ?></div>
                        <div class="cli-product-price"><?= formatPrice((float)$producto['precio']) ?></div>
                        <div class="cli-product-stock-bar"><div class="cli-product-stock-fill <?= $stockClass ?>" style="width:<?= $stockPct ?>%"></div></div>
                        <div class="cli-product-stock-text"><?= $agotado ? 'No disponible para agregar al carrito' : $stock . ' unidad' . ($stock === 1 ? '' : 'es') . ' disponibles' ?></div>
                    </div>
                </a>
                <?php if ($agotado): ?>
                    <div style="padding:0 14px 14px;"><button class="cli-btn cli-btn-ghost cli-btn-block" type="button" disabled><i data-lucide="ban"></i> No disponible</button></div>
                <?php else: ?>
                    <div style="padding:0 14px 14px;"><a class="cli-btn cli-btn-primary cli-btn-block" href="<?= BASE_URL ?>/index.php?controller=cliente&action=agregarCarrito&id=<?= (int)$producto['id'] ?>&_back=<?= urlencode('index.php?controller=cliente&action=catalogo' . ($busquedaAct || $categoriaAct ? '&busqueda=' . urlencode($busquedaAct) . '&categoria=' . $categoriaAct . '&page=' . $page : '')) ?>"><i data-lucide="shopping-cart"></i> Agregar al carrito</a></div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav aria-label="Paginación del catálogo" style="display:flex;justify-content:center;gap:8px;margin:24px 0;">
            <?php if ($page > 1): ?><a class="cli-btn cli-btn-ghost cli-btn-sm" href="<?= e($catalogUrl . '&busqueda=' . urlencode($busquedaAct) . '&categoria=' . $categoriaAct . '&page=' . ($page - 1)) ?>"><i data-lucide="chevron-left"></i> Anterior</a><?php endif; ?>
            <span class="cli-btn cli-btn-secondary cli-btn-sm">Página <?= $page ?> de <?= $totalPages ?></span>
            <?php if ($page < $totalPages): ?><a class="cli-btn cli-btn-ghost cli-btn-sm" href="<?= e($catalogUrl . '&busqueda=' . urlencode($busquedaAct) . '&categoria=' . $categoriaAct . '&page=' . ($page + 1)) ?>">Siguiente <i data-lucide="chevron-right"></i></a><?php endif; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
