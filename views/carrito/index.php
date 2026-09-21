<?php
/**
 * Vista — Mi Carrito (cliente)
 */
?>

<div class="ped-header animate-fade-in">
    <div>
        <h1 class="ped-title">🛒 Mi Carrito</h1>
        <p class="ped-subtitle">
            <?= $carritoModel->getCount() ?> producto(s) — Total:
            <strong style="color:var(--color-primary)"><?= formatPrice($total) ?></strong>
        </p>
    </div>
    <?php if (!$carritoModel->isEmpty()): ?>
        <div style="display:flex;gap:8px;">
            <a href="<?= BASE_URL ?>/index.php?controller=carrito&action=vaciar"
               class="btn btn-secondary btn-sm"
               onclick="return confirm('Vaciar carrito?')">
                <i data-lucide="trash-2"></i> Vaciar
            </a>
            <a href="<?= BASE_URL ?>/index.php?controller=carrito&action=checkout"
               class="btn btn-primary">
                <i data-lucide="credit-card"></i> Finalizar compra
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if ($carritoModel->isEmpty()): ?>
    <div class="card animate-fade-in">
        <div class="table-empty">
            <div class="table-empty-icon">🛒</div>
            <p>Tu carrito esta vacio.</p>
            <a href="<?= BASE_URL ?>/index.php?controller=productos&action=catalogo"
               class="btn btn-primary btn-sm mt-md">
                <i data-lucide="store"></i> Ver catalogo
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card animate-fade-in-up">
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $productoId => $item): ?>
                    <tr>
                        <td><strong><?= e($item['nombre']) ?></strong></td>
                        <td><?= formatPrice((float)$item['precio']) ?></td>
                        <td>
                            <form method="POST"
                                  action="<?= BASE_URL ?>/index.php?controller=carrito&action=actualizar"
                                  style="display:inline-flex;align-items:center;gap:6px;">
                                <input type="hidden" name="producto_id" value="<?= $productoId ?>">
                                <input type="number" name="cantidad" value="<?= $item['cantidad'] ?>"
                                       min="1" max="<?= $item['stock'] ?>"
                                       class="form-control" style="width:70px;padding:5px 8px;"
                                       onchange="this.form.submit()">
                            </form>
                        </td>
                        <td><strong><?= formatPrice((float)$item['precio'] * $item['cantidad']) ?></strong></td>
                        <td>
                            <a href="<?= BASE_URL ?>/index.php?controller=carrito&action=eliminar&id=<?= $productoId ?>"
                               class="btn btn-ghost btn-sm" title="Eliminar">
                                <i data-lucide="trash-2"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="padding:var(--space-lg);border-top:1px solid var(--color-border);display:flex;justify-content:space-between;align-items:center;">
            <a href="<?= BASE_URL ?>/index.php?controller=productos&action=catalogo"
               class="btn btn-secondary btn-sm">
                <i data-lucide="arrow-left"></i> Seguir comprando
            </a>
            <div style="text-align:right;">
                <div style="font-size:var(--font-size-sm);color:var(--color-text-secondary);">Total</div>
                <div style="font-size:var(--font-size-2xl);font-weight:800;color:var(--color-primary);">
                    <?= formatPrice($total) ?>
                </div>
                <a href="<?= BASE_URL ?>/index.php?controller=carrito&action=checkout"
                   class="btn btn-primary mt-sm">
                    <i data-lucide="credit-card"></i> Finalizar compra
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>
