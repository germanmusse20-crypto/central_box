<?php
/**
 * Vista — Detalle de Pedido
 */
$badgeMap = [
    'confirmado' => 'info',
    'entregado'  => 'success',
    'cancelado'  => 'danger',
];
$badge = $badgeMap[$pedido['estado']] ?? 'secondary';
?>

<!-- Encabezado -->
<div class="ped-header animate-fade-in">
    <div class="ped-header-left">
        <a href="<?= BASE_URL ?>/index.php?controller=pedidos&action=index" class="pv-back">
            <i data-lucide="arrow-left"></i>
        </a>
        <div>
            <h1 class="ped-title">📦 Pedido <span class="pv-highlight">#<?= e($pedido['id']) ?></span></h1>
            <p class="ped-subtitle">
                Registrado el <?= date('d/m/Y \a \l\a\s H:i', strtotime($pedido['created_at'])) ?>
            </p>
        </div>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
        <span class="badge badge-<?= $badge ?>" style="font-size:0.85rem;padding:6px 14px;">
            <?= ucfirst(e($pedido['estado'])) ?>
        </span>
        <?php if (!in_array($pedido['estado'], ['entregado','cancelado'])): ?>
            <a href="<?= BASE_URL ?>/index.php?controller=pedidos&action=cancelar&id=<?= $pedido['id'] ?>"
               class="btn btn-danger btn-sm"
               onclick="return confirm('¿Cancelar este pedido?')">
                <i data-lucide="x-circle"></i> Cancelar
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Layout 2 columnas -->
<div class="ped-detail-layout animate-fade-in">

    <!-- Columna izquierda: productos + notas -->
    <div class="ped-detail-main">

        <!-- Productos del pedido -->
        <div class="card mb-md">
            <div class="card-header">
                <h4 class="card-title">🛍️ Productos del pedido</h4>
            </div>

            <?php if (empty($detalles)): ?>
                <div class="table-empty">
                    <div class="table-empty-icon">📦</div>
                    <p>No hay productos en este pedido.</p>
                </div>
            <?php else: ?>
                <div class="table-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Precio unit.</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $d): ?>
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <?php if (!empty($d['producto_imagen'])): ?>
                                            <img src="<?= IMG_URL ?>/productos/<?= e($d['producto_imagen']) ?>"
                                                 alt="" style="width:36px;height:36px;object-fit:cover;border-radius:6px;border:1px solid var(--color-border);"
                                                 onerror="this.style.display='none'">
                                        <?php else: ?>
                                            <div style="width:36px;height:36px;border-radius:6px;background:var(--color-surface);display:flex;align-items:center;justify-content:center;border:1px solid var(--color-border);">
                                                <i data-lucide="package" style="width:16px;height:16px;color:var(--color-text-muted);"></i>
                                            </div>
                                        <?php endif; ?>
                                        <strong><?= e($d['producto_nombre']) ?></strong>
                                    </div>
                                </td>
                                <td><?= formatPrice((float)$d['precio_unitario']) ?></td>
                                <td>
                                    <span class="badge badge-secondary"><?= (int)$d['cantidad'] ?></span>
                                </td>
                                <td><strong><?= formatPrice((float)$d['subtotal']) ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Notas -->
        <?php if (!empty($pedido['notas'])): ?>
        <div class="card mb-md">
            <div class="card-header">
                <h4 class="card-title">📝 Notas</h4>
            </div>
            <p style="font-size:var(--font-size-sm);color:var(--color-text-secondary);line-height:1.7;">
                <?= nl2br(e($pedido['notas'])) ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Historial de estados -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">🔄 Cambiar estado</h4>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <?php
                $flujo = [
                    'confirmado' => ['Confirmado', 'info'],
                    'entregado'  => ['Entregado',  'success'],
                    'cancelado'  => ['Cancelado',  'danger'],
                ];
                foreach ($flujo as $est => [$label, $color]):
                    $isCurrent = $pedido['estado'] === $est;
                ?>
                    <a href="<?= BASE_URL ?>/index.php?controller=pedidos&action=updateEstado&id=<?= $pedido['id'] ?>&estado=<?= $est ?>"
                       class="btn btn-<?= $color ?> btn-sm <?= $isCurrent ? 'ped-estado-current' : '' ?>"
                       <?= $isCurrent ? 'style="opacity:0.6;pointer-events:none;"' : '' ?>
                       onclick="return confirm('¿Cambiar estado a «<?= $label ?>»?')">
                        <?= $label ?>
                        <?php if ($isCurrent): ?> ✓<?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- Columna derecha: info cliente + resumen -->
    <div class="ped-detail-side">

        <!-- Resumen económico -->
        <div class="card mb-md">
            <h4 class="card-title mb-md">💰 Resumen económico</h4>
            <div class="ped-sum-row">
                <span>Subtotal</span>
                <span><?= formatPrice((float)$pedido['total']) ?></span>
            </div>
            <div class="ped-sum-row">
                <span>Descuento</span>
                <span class="text-danger">— $0.00</span>
            </div>
            <div class="ped-sum-divider"></div>
            <div class="ped-sum-total">
                <span>Total</span>
                <span><?= formatPrice((float)$pedido['total']) ?></span>
            </div>
            <div class="ped-sum-row mt-sm">
                <span>Método de pago</span>
                <span>
                    <i data-lucide="<?= $pedido['metodo_pago'] === 'efectivo' ? 'banknote' : 'credit-card' ?>"
                       style="width:13px;height:13px;display:inline;vertical-align:middle;"></i>
                    <?= ucfirst(e($pedido['metodo_pago'])) ?>
                </span>
            </div>
        </div>

        <!-- Datos del cliente -->
        <div class="card mb-md">
            <h4 class="card-title mb-md">👤 Cliente</h4>
            <div class="ped-client-card">
                <div class="ped-avatar" style="width:44px;height:44px;font-size:1.1rem;">
                    <?= strtoupper(substr($pedido['cliente_nombre'] ?? 'C', 0, 1)) ?>
                </div>
                <div>
                    <strong><?= e($pedido['cliente_nombre']) ?></strong>
                    <span><?= e($pedido['cliente_email']) ?></span>
                    <?php if (!empty($pedido['cliente_telefono'])): ?>
                        <span><?= e($pedido['cliente_telefono']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($pedido['cliente_direccion'])): ?>
                        <span><?= e($pedido['cliente_direccion']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Info del pedido -->
        <div class="card">
            <h4 class="card-title mb-md">📋 Información</h4>
            <div class="ped-info-list">
                <div class="ped-info-row">
                    <span>N° Pedido</span>
                    <span class="ped-id-link">#<?= e($pedido['id']) ?></span>
                </div>
                <div class="ped-info-row">
                    <span>Estado</span>
                    <span class="badge badge-<?= $badge ?>"><?= ucfirst(e($pedido['estado'])) ?></span>
                </div>
                <div class="ped-info-row">
                    <span>Fecha creación</span>
                    <span><?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></span>
                </div>
                <?php if (!empty($pedido['updated_at']) && $pedido['updated_at'] !== $pedido['created_at']): ?>
                <div class="ped-info-row">
                    <span>Última actualización</span>
                    <span><?= date('d/m/Y H:i', strtotime($pedido['updated_at'])) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($pedido['direccion_envio'])): ?>
                <div class="ped-info-row">
                    <span>Dirección envío</span>
                    <span><?= e($pedido['direccion_envio']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
