<?php
/**
 * Pedidos del cliente.
 * RF21: visualizar y gestionar los pedidos realizados.
 */
$pedidos = is_array($pedidos ?? null) ? $pedidos : [];
$estadoActual = trim((string)($_GET['estado'] ?? ''));
$estados = [
    '' => 'Todos',
    'confirmado' => 'Confirmados',
    'entregado' => 'Entregados',
    'cancelado' => 'Cancelados',
];
$badgeMap = ['confirmado'=>'cli-badge-blue','entregado'=>'cli-badge-green','cancelado'=>'cli-badge-red'];
$ordersUrl = BASE_URL . '/index.php?controller=cliente&action=misOrders';
?>

<div class="cli-page-header animate-fade-in">
    <div>
        <div class="cli-chip"><i data-lucide="receipt"></i> MI ACTIVIDAD</div>
        <h1 class="cli-page-title">Mis <span>pedidos</span></h1>
        <p class="cli-page-subtitle">Consulta el estado y el detalle de cada compra realizada.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=carrito" class="cli-btn cli-btn-primary"><i data-lucide="shopping-cart"></i> Ir al carrito</a>
</div>

<div class="cli-card animate-fade-in-up">
    <div class="cli-card-header" style="margin-bottom:12px;">
        <h2 class="cli-card-title"><i data-lucide="list-filter"></i> Filtrar pedidos</h2>
        <span class="cli-badge cli-badge-gray"><?= count($pedidos) ?> resultado<?= count($pedidos) === 1 ? '' : 's' ?></span>
    </div>
    <div class="cli-filter-bar" style="margin-bottom:0;">
        <?php foreach ($estados as $valor => $etiqueta): ?>
            <a class="cli-filter-chip <?= $estadoActual === $valor ? 'active' : '' ?>" href="<?= e($valor === '' ? $ordersUrl : $ordersUrl . '&estado=' . urlencode($valor)) ?>"><?= e($etiqueta) ?></a>
        <?php endforeach; ?>
    </div>
</div>

<div class="cli-card animate-fade-in-up">
    <div class="cli-card-header">
        <h2 class="cli-card-title"><i data-lucide="shopping-bag"></i> Historial de compras</h2>
        <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=catalogo" class="cli-btn cli-btn-ghost cli-btn-sm"><i data-lucide="store"></i> Comprar más</a>
    </div>
    <?php if (!$pedidos): ?>
        <div class="cli-empty" style="padding:35px 20px;">
            <div class="cli-empty-icon"><i data-lucide="receipt"></i></div>
            <div class="cli-empty-title"><?= $estadoActual ? 'No hay pedidos con este estado' : 'Aún no tienes pedidos' ?></div>
            <p class="cli-empty-text"><?= $estadoActual ? 'Prueba otro filtro para consultar tus compras.' : 'Cuando realices una compra aparecerá aquí.' ?></p>
            <?php if ($estadoActual): ?><a href="<?= e($ordersUrl) ?>" class="cli-btn cli-btn-secondary cli-btn-sm">Ver todos</a><?php else: ?><a href="<?= BASE_URL ?>/index.php?controller=cliente&action=catalogo" class="cli-btn cli-btn-primary cli-btn-sm">Ver productos</a><?php endif; ?>
        </div>
    <?php else: ?>
        <div class="cli-orders-table">
            <table>
                <thead><tr><th>Pedido</th><th>Total</th><th>Estado</th><th>Fecha</th><th>Acción</th></tr></thead>
                <tbody>
                <?php foreach ($pedidos as $pedido):
                    $estado = (string)($pedido['estado'] ?? 'confirmado');
                    $badge = $badgeMap[$estado] ?? 'cli-badge-gray';
                    $pedidoId = (int)($pedido['id'] ?? 0);
                ?>
                    <tr>
                        <td><strong>#<?= str_pad((string)$pedidoId, 4, '0', STR_PAD_LEFT) ?></strong></td>
                        <td><strong style="color:var(--cli-green-dark);"><?= formatPrice((float)($pedido['total'] ?? 0)) ?></strong></td>
                        <td><span class="cli-badge <?= $badge ?>"><?= e(ucfirst($estado)) ?></span></td>
                        <td class="text-secondary"><?= !empty($pedido['created_at']) ? date('d/m/Y H:i', strtotime($pedido['created_at'])) : 'Sin fecha' ?></td>
                        <td><a href="<?= BASE_URL ?>/index.php?controller=cliente&action=detallePedido&id=<?= $pedidoId ?>" class="cli-btn cli-btn-ghost cli-btn-sm"><i data-lucide="eye"></i> Ver detalle</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
