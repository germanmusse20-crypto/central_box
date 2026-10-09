<?php
/**
 * Vista — Detalle de producto para el cliente
 */
$producto = is_array($producto ?? null) ? $producto : [];
$relacionados = is_array($relacionados ?? null) ? $relacionados : [];

$stock = (int)($producto['stock'] ?? 0);
$minimo = (int)($producto['stock_minimo'] ?? 0);
$agotado = $stock <= 0;
$stockBajo = !$agotado && $minimo > 0 && $stock <= $minimo;
$stockMax = max($minimo * 2, 1);
$stockPct = min(100, max(5, (int)round($stock / $stockMax * 100)));
$stockClass = $agotado ? 'low' : ($stockBajo ? 'medium' : '');
$badgeClass = $agotado ? 'cli-badge-red' : ($stockBajo ? 'cli-badge-amber' : 'cli-badge-green');
$badgeText = $agotado ? 'Agotado' : ($stockBajo ? 'Stock bajo' : 'Disponible');
?>

<div class="cli-page-header animate-fade-in">
    <div>
        <div class="cli-chip"><i data-lucide="package"></i> DETALLE DE PRODUCTO</div>
        <h1 class="cli-page-title"><?= e($producto['nombre'] ?? 'Producto no encontrado') ?></h1>
        <p class="cli-page-subtitle"><?= e($producto['categoria_nombre'] ?? 'Categoría general') ?></p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=catalogo" class="cli-btn cli-btn-ghost"><i data-lucide="arrow-left"></i> Volver al catálogo</a>
</div>

<div class="cli-checkout-grid animate-fade-in-up">
    <!-- Columna Izquierda: Imagen y Descripción -->
    <div style="display:flex; flex-direction:column; gap:20px;">
        <section class="cli-card" style="padding:0; overflow:hidden;">
            <?php if (!empty($producto['imagen'])): ?>
                <img src="<?= IMG_URL ?>/productos/<?= e($producto['imagen']) ?>" alt="<?= e($producto['nombre']) ?>" style="width:100%; max-height:400px; object-fit:cover;" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div class="cli-placeholder" style="display:none; height:300px; align-items:center; justify-content:center;"><i data-lucide="image" style="width:48px;height:48px;opacity:0.2;"></i></div>
            <?php else: ?>
                <div class="cli-placeholder" style="height:300px; display:flex; align-items:center; justify-content:center; background:#f9fafb;"><i data-lucide="package" style="width:48px;height:48px;opacity:0.2;"></i></div>
            <?php endif; ?>
        </section>

        <section class="cli-card">
            <h2 class="cli-card-title"><i data-lucide="align-left"></i> Descripción del producto</h2>
            <div style="color:var(--cli-text-sec); line-height:1.7; font-size:0.95rem; margin-top:16px;">
                <?= nl2br(e($producto['descripcion'] ?? 'Este producto no tiene una descripción detallada.')) ?>
            </div>
        </section>
    </div>

    <!-- Columna Derecha: Información de compra -->
    <div>
        <section class="cli-card" style="position:sticky; top:20px;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                <span class="cli-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
            </div>
            
            <div style="font-size:2rem; font-weight:800; color:var(--cli-primary); margin-bottom:20px;">
                <?= formatPrice((float)($producto['precio'] ?? 0)) ?>
            </div>

            <div style="margin-bottom:24px;">
                <div style="display:flex; justify-content:space-between; font-size:0.85rem; color:var(--cli-text-sec); margin-bottom:8px;">
                    <span>Disponibilidad</span>
                    <span style="font-weight:600; color:var(--cli-text);"><?= $agotado ? 'Sin stock' : $stock . ' unidades' ?></span>
                </div>
                <div class="cli-product-stock-bar" style="margin-bottom:0;"><div class="cli-product-stock-fill <?= $stockClass ?>" style="width:<?= $stockPct ?>%"></div></div>
            </div>

            <?php if ($agotado): ?>
                <button class="cli-btn cli-btn-ghost cli-btn-block" type="button" disabled style="height:48px; font-size:1rem;"><i data-lucide="ban"></i> Agotado temporalmente</button>
            <?php else: ?>
                <form action="<?= BASE_URL ?>/index.php?controller=cliente&action=agregarCarrito" method="POST">
                    <input type="hidden" name="producto_id" value="<?= (int)$producto['id'] ?>">
                    <input type="hidden" name="_back" value="index.php?controller=cliente&action=producto&id=<?= (int)$producto['id'] ?>">
                    
                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:8px; color:var(--cli-text-sec);">Cantidad</label>
                        <select name="cantidad" class="cli-input" style="width:100%;">
                            <?php 
                            $maxSelect = min(10, $stock);
                            for ($i = 1; $i <= $maxSelect; $i++): 
                            ?>
                                <option value="<?= $i ?>"><?= $i ?> unidad<?= $i > 1 ? 'es' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <button type="submit" class="cli-btn cli-btn-primary cli-btn-block" style="height:48px; font-size:1rem; margin-top:8px;">
                        <i data-lucide="shopping-cart"></i> Agregar al carrito
                    </button>
                </form>
            <?php endif; ?>
            
            <div style="margin-top:20px; padding-top:20px; border-top:1px solid var(--cli-border); font-size:0.85rem; color:var(--cli-text-sec); display:flex; flex-direction:column; gap:10px;">
                <div style="display:flex; align-items:center; gap:8px;"><i data-lucide="shield-check" style="width:16px;height:16px;color:var(--cli-primary);"></i> Compra 100% segura y garantizada</div>
                <div style="display:flex; align-items:center; gap:8px;"><i data-lucide="truck" style="width:16px;height:16px;color:var(--cli-primary);"></i> Envío disponible a todo el país</div>
            </div>
        </section>
    </div>
</div>

<?php if (!empty($relacionados)): ?>
<div style="margin-top:40px;" class="animate-fade-in-up">
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
        <h2 style="font-size:1.25rem; font-weight:700; color:var(--cli-text); margin:0;">Productos relacionados</h2>
        <div style="height:1px; flex:1; background:var(--cli-border);"></div>
    </div>
    
    <div class="cli-catalog-grid" style="grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));">
        <?php foreach ($relacionados as $rel):
            $rStock = (int)($rel['stock'] ?? 0);
            $rMinimo = (int)($rel['stock_minimo'] ?? 0);
            $rAgotado = $rStock <= 0;
            $rStockBajo = !$rAgotado && $rMinimo > 0 && $rStock <= $rMinimo;
            $rStockMax = max($rMinimo * 2, 1);
            $rStockPct = min(100, max(5, (int)round($rStock / $rStockMax * 100)));
            $rStockClass = $rAgotado ? 'low' : ($rStockBajo ? 'medium' : '');
        ?>
            <article class="cli-product-card <?= $rAgotado ? 'agotado' : '' ?>">
                <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=producto&id=<?= (int)$rel['id'] ?>" style="text-decoration:none;color:inherit;">
                    <div class="cli-product-img" style="height:180px;">
                        <?php if (!empty($rel['imagen'])): ?>
                            <img src="<?= IMG_URL ?>/productos/<?= e($rel['imagen']) ?>" alt="<?= e($rel['nombre']) ?>" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div class="cli-placeholder" style="display:none;align-items:center;justify-content:center;"><i data-lucide="package"></i></div>
                        <?php else: ?>
                            <div class="cli-placeholder"><i data-lucide="package"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="cli-product-body" style="padding:12px;">
                        <div class="cli-product-name" style="font-size:0.95rem;"><?= e($rel['nombre']) ?></div>
                        <div class="cli-product-price" style="font-size:1.1rem; margin-top:4px;"><?= formatPrice((float)$rel['precio']) ?></div>
                    </div>
                </a>
            </article>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
