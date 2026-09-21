<?php
/**
 * Detalle de pedido del cliente.
 * RF21: consultar y gestionar el pedido desde su información real.
 */
$pedido = is_array($pedido ?? null) ? $pedido : [];
$detalles = is_array($detalles ?? null) ? $detalles : [];
$estado = (string)($pedido['estado'] ?? 'confirmado');
$badgeMap = ['confirmado'=>'cli-badge-blue','entregado'=>'cli-badge-green','cancelado'=>'cli-badge-red'];
$badge = $badgeMap[$estado] ?? 'cli-badge-gray';
?>

<div class="cli-page-header animate-fade-in">
    <div>
        <div class="cli-chip"><i data-lucide="receipt-text"></i> DETALLE DE COMPRA</div>
        <h1 class="cli-page-title">Pedido <span>#<?= str_pad((string)(int)($pedido['id'] ?? 0), 4, '0', STR_PAD_LEFT) ?></span></h1>
        <p class="cli-page-subtitle">Revisa los productos y el estado de tu pedido.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=misOrders" class="cli-btn cli-btn-ghost"><i data-lucide="arrow-left"></i> Mis pedidos</a>
</div>

<div class="cli-checkout-grid animate-fade-in-up">
    <section class="cli-card">
        <div class="cli-card-header">
            <h2 class="cli-card-title"><i data-lucide="package-check"></i> Productos del pedido</h2>
            <span class="cli-badge <?= $badge ?>"><?= e(ucfirst($estado)) ?></span>
        </div>
        <?php if (!$detalles): ?>
            <div class="cli-empty" style="padding:30px 10px;"><div class="cli-empty-title">No hay productos para mostrar</div></div>
        <?php else: ?>
            <?php foreach ($detalles as $detalle):
                $cantidad = (int)($detalle['cantidad'] ?? 0);
                $precio = (float)($detalle['precio_unitario'] ?? $detalle['precio'] ?? 0);
                $subtotal = (float)($detalle['subtotal'] ?? ($precio * $cantidad));
            ?>
                <div class="cli-cart-item">
                    <div class="cli-cart-img">
                        <?php if (!empty($detalle['producto_imagen'])): ?><img src="<?= IMG_URL ?>/productos/<?= e($detalle['producto_imagen']) ?>" alt="<?= e($detalle['producto_nombre'] ?? 'Producto') ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='block'"><i data-lucide="package" style="display:none"></i><?php else: ?><i data-lucide="package"></i><?php endif; ?>
                    </div>
                    <div class="cli-cart-info"><div class="cli-cart-name"><?= e($detalle['producto_nombre'] ?? 'Producto') ?></div><div class="cli-cart-price"><?= $cantidad ?> x <?= formatPrice($precio) ?></div></div>
                    <div class="cli-cart-subtotal"><?= formatPrice($subtotal) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <aside class="cli-summary-box">
        <h2 class="cli-summary-title">Resumen del pedido</h2>
        <div class="cli-summary-item"><span>Estado</span><span class="cli-badge <?= $badge ?>"><?= e(ucfirst($estado)) ?></span></div>
        <div class="cli-summary-item"><span>Fecha</span><strong><?= !empty($pedido['created_at']) ? date('d/m/Y H:i', strtotime($pedido['created_at'])) : 'Sin fecha' ?></strong></div>
        <?php if (!empty($pedido['metodo_pago'])): ?><div class="cli-summary-item"><span>Método de pago</span><strong><?= e(ucfirst((string)$pedido['metodo_pago'])) ?></strong></div><?php endif; ?>
        <div class="cli-summary-divider"></div>
        <div class="cli-summary-total"><span>Total</span><span><?= formatPrice((float)($pedido['total'] ?? 0)) ?></span></div>
        <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=catalogo" class="cli-btn cli-btn-secondary cli-btn-block" style="margin-top:16px;"><i data-lucide="store"></i> Seguir comprando</a>
    </aside>
</div>
