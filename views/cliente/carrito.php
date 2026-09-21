<?php
/**
 * Mi carrito cliente.
 * UH-22: agregar productos | UH-23: eliminar/vaciar | UH-24: actualizar cantidades.
 */
$items = is_array($items ?? null) ? $items : [];
$total = (float)($total ?? 0);
$carritoCount = (int)($carritoCount ?? 0);
$carritoUrl = BASE_URL . '/index.php?controller=cliente&action=carrito';
?>

<div class="cli-page-header animate-fade-in">
    <div>
        <div class="cli-chip"><i data-lucide="shopping-cart"></i> TU COMPRA</div>
        <h1 class="cli-page-title">Mi <span>carrito</span></h1>
        <p class="cli-page-subtitle"><?= number_format($carritoCount) ?> producto<?= $carritoCount === 1 ? '' : 's' ?> seleccionado<?= $carritoCount === 1 ? '' : 's' ?>.</p>
    </div>
    <?php if ($items): ?>
        <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=vaciarCarrito" class="cli-btn cli-btn-ghost" onclick="return confirm('¿Vaciar todo el carrito?')">
            <i data-lucide="trash-2"></i> Vaciar carrito
        </a>
    <?php endif; ?>
</div>

<?php if (!$items): ?>
    <div class="cli-card cli-empty animate-fade-in-up">
        <div class="cli-empty-icon"><i data-lucide="shopping-cart"></i></div>
        <div class="cli-empty-title">Tu carrito está vacío</div>
        <p class="cli-empty-text">Agrega productos del catálogo para continuar con tu compra.</p>
        <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=catalogo" class="cli-btn cli-btn-primary">
            <i data-lucide="store"></i> Ver productos
        </a>
    </div>
<?php else: ?>
    <div class="cli-checkout-grid animate-fade-in-up">
        <div class="cli-card">
            <div class="cli-card-header">
                <h2 class="cli-card-title"><i data-lucide="package-check"></i> Productos seleccionados</h2>
                <span class="cli-badge cli-badge-green"><?= $carritoCount ?> unidad<?= $carritoCount === 1 ? '' : 'es' ?></span>
            </div>

            <?php foreach ($items as $productoId => $item):
                $stock = max(0, (int)($item['stock'] ?? 0));
                $cantidad = max(1, (int)($item['cantidad'] ?? 1));
                $precio = (float)($item['precio'] ?? 0);
            ?>
                <article class="cli-cart-item">
                    <div class="cli-cart-img">
                        <?php if (!empty($item['imagen'])): ?>
                            <img src="<?= IMG_URL ?>/productos/<?= e($item['imagen']) ?>" alt="<?= e($item['nombre'] ?? 'Producto') ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                            <i data-lucide="package" style="display:none"></i>
                        <?php else: ?><i data-lucide="package"></i><?php endif; ?>
                    </div>
                    <div class="cli-cart-info">
                        <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=producto&id=<?= (int)$productoId ?>" class="cli-cart-name" style="text-decoration:none;display:block;"><?= e($item['nombre'] ?? 'Producto') ?></a>
                        <div class="cli-cart-price"><?= formatPrice($precio) ?> por unidad</div>
                        <?php if ($stock < $cantidad): ?><div class="cli-field-error"><i data-lucide="triangle-alert"></i> Stock actualizado: quedan <?= $stock ?> unidades.</div><?php endif; ?>
                    </div>
                    <form method="POST" action="<?= BASE_URL ?>/index.php?controller=cliente&action=actualizarCarrito" class="cli-qty-ctrl">
                        <input type="hidden" name="producto_id" value="<?= (int)$productoId ?>">
                        <label class="sr-only" for="cantidad-<?= (int)$productoId ?>">Cantidad de <?= e($item['nombre'] ?? 'producto') ?></label>
                        <input id="cantidad-<?= (int)$productoId ?>" class="cli-qty-val" type="number" name="cantidad" min="0" max="<?= max(1, $stock) ?>" value="<?= $cantidad ?>" onchange="this.form.submit()" aria-label="Cantidad">
                    </form>
                    <div class="cli-cart-subtotal"><?= formatPrice($precio * $cantidad) ?></div>
                    <a class="cli-cart-del" href="<?= BASE_URL ?>/index.php?controller=cliente&action=eliminarCarrito&id=<?= (int)$productoId ?>" title="Eliminar producto" aria-label="Eliminar <?= e($item['nombre'] ?? 'producto') ?>" onclick="return confirm('¿Eliminar este producto del carrito?')"><i data-lucide="trash-2"></i></a>
                </article>
            <?php endforeach; ?>

            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:18px;flex-wrap:wrap;">
                <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=catalogo" class="cli-btn cli-btn-ghost cli-btn-sm"><i data-lucide="arrow-left"></i> Seguir comprando</a>
                <span class="cli-dashboard-note" style="font-size:.76rem;color:var(--cli-text-muted);">Las cantidades se actualizan al cambiar el valor.</span>
            </div>
        </div>

        <aside class="cli-summary-box">
            <h2 class="cli-summary-title">Resumen de compra</h2>
            <div class="cli-summary-item"><span>Productos</span><strong><?= number_format($carritoCount) ?></strong></div>
            <div class="cli-summary-divider"></div>
            <div class="cli-summary-total"><span>Total</span><span><?= formatPrice($total) ?></span></div>
            <p class="cli-dashboard-note" style="margin:12px 0 16px;">Elige tu método de pago y registra la dirección al finalizar el pedido.</p>
            <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=checkout" class="cli-btn cli-btn-primary cli-btn-block cli-btn-lg"><i data-lucide="credit-card"></i> Finalizar compra</a>
        </aside>
    </div>
<?php endif; ?>
