<?php
/**
 * Vista Dashboard — Contenido dinámico según rol
 */
$user = getUser();
?>

<div class="dashboard-welcome animate-fade-in">
    <div>
        <h2 style="margin-bottom: 4px;">¡Hola, <?= e(trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $user['nombre']))) ?>! 👋</h2>
        <p class="text-secondary" style="margin-top: 0;">Aquí tienes un resumen de tu actividad</p>
    </div>
    <span class="badge badge-<?= $user['rol'] === 'admin' ? 'primary' : ($user['rol'] === 'vendedor' ? 'secondary' : 'success') ?>">
        <?= ucfirst(e($user['rol'])) ?>
    </span>
</div>

<?php if (hasRole('admin')): ?>
<!-- ═══════════════════════════════════════════
     ADMIN DASHBOARD
     ═══════════════════════════════════════════ -->

<!-- Stats cards -->
<div class="grid grid-4 stagger mb-xl">
    <div class="stat-card animate-fade-in-up">
        <div class="stat-card-icon primary"><i data-lucide="dollar-sign"></i></div>
        <div class="stat-card-value text-gradient"><?= formatPrice($data['total_ventas']) ?></div>
        <div class="stat-card-label">Ventas Totales</div>
    </div>

    <div class="stat-card animate-fade-in-up">
        <div class="stat-card-icon secondary"><i data-lucide="shopping-bag"></i></div>
        <div class="stat-card-value"><?= number_format($data['total_pedidos']) ?></div>
        <div class="stat-card-label">Pedidos</div>
    </div>

    <div class="stat-card animate-fade-in-up">
        <div class="stat-card-icon success"><i data-lucide="package"></i></div>
        <div class="stat-card-value"><?= number_format($data['total_productos']) ?></div>
        <div class="stat-card-label">Productos</div>
    </div>

    <div class="stat-card animate-fade-in-up">
        <div class="stat-card-icon info"><i data-lucide="users"></i></div>
        <div class="stat-card-value"><?= number_format($data['total_usuarios']) ?></div>
        <div class="stat-card-label">Usuarios</div>
    </div>
</div>

<!-- Secondary stats -->
<div class="grid grid-3 mb-xl stagger">
    <div class="card animate-fade-in">
        <div class="card-header">
            <h4 class="card-title">💰 Ventas del Mes</h4>
        </div>
        <div class="stat-card-value" style="font-size:var(--font-size-2xl);"><?= formatPrice($data['ventas_mes']) ?></div>
    </div>

    <div class="card animate-fade-in">
        <div class="card-header">
            <h4 class="card-title">📊 Pedidos por Estado</h4>
        </div>
        <div class="dashboard-status-list">
            <?php
            $estados = ['confirmado' => 'info', 'entregado' => 'success', 'cancelado' => 'danger'];
            foreach ($estados as $estado => $color):
                $count = $data['por_estado'][$estado] ?? 0;
            ?>
                <div class="dashboard-status-item">
                    <span class="badge badge-<?= $color ?>"><?= ucfirst($estado) ?></span>
                    <span class="dashboard-status-count"><?= $count ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card animate-fade-in">
        <div class="card-header">
            <h4 class="card-title">📦 Inventario</h4>
        </div>
        <div class="dashboard-inventory-summary">
            <div class="dashboard-inv-row">
                <span>Total productos</span>
                <strong><?= $data['resumen_inventario']['total_productos'] ?></strong>
            </div>
            <div class="dashboard-inv-row">
                <span>Unidades en stock</span>
                <strong><?= number_format($data['resumen_inventario']['total_unidades']) ?></strong>
            </div>
            <div class="dashboard-inv-row <?= $data['resumen_inventario']['bajo_stock'] > 0 ? 'text-warning' : '' ?>">
                <span>⚠️ Bajo stock</span>
                <strong><?= $data['resumen_inventario']['bajo_stock'] ?></strong>
            </div>
            <div class="dashboard-inv-row">
                <span>Valor inventario</span>
                <strong style="font-size:var(--font-size-xs);"><?= formatPrice($data['resumen_inventario']['valor_total']) ?></strong>
            </div>
        </div>
    </div>
</div>

<!-- Recent orders table -->
<div class="card animate-fade-in-up">
    <div class="card-header">
        <h4 class="card-title">🕐 Pedidos Recientes</h4>
        <a href="<?= BASE_URL ?>/index.php?controller=pedidos&action=lista" class="btn btn-sm btn-secondary">Ver todos</a>
    </div>
    <?php if (!empty($data['pedidos_recientes'])): ?>
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['pedidos_recientes'] as $pedido): ?>
                <tr>
                    <td><strong>#<?= $pedido['id'] ?></strong></td>
                    <td><?= e($pedido['cliente_nombre']) ?></td>
                    <td><?= formatPrice($pedido['total']) ?></td>
                    <td>
                        <?php
                        $badgeMap = ['confirmado'=>'info','entregado'=>'success','cancelado'=>'danger'];
                        $badge = $badgeMap[$pedido['estado']] ?? 'secondary';
                        ?>
                        <span class="badge badge-<?= $badge ?>"><?= ucfirst($pedido['estado']) ?></span>
                    </td>
                    <td class="text-secondary"><?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="table-empty">
            <div class="table-empty-icon">📋</div>
            <p>No hay pedidos aún</p>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($data['bajo_stock'])): ?>
<!-- Low stock alerts -->
<div class="card animate-fade-in-up mt-lg">
    <div class="card-header">
        <h4 class="card-title">⚠️ Productos con Bajo Stock</h4>
        <a href="<?= BASE_URL ?>/index.php?controller=inventario&action=index" class="btn btn-sm btn-warning">Gestionar</a>
    </div>
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Stock Actual</th>
                    <th>Stock Mínimo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($data['bajo_stock'], 0, 5) as $prod): ?>
                <tr>
                    <td><strong><?= e($prod['nombre']) ?></strong></td>
                    <td class="<?= $prod['stock'] == 0 ? 'text-danger' : 'text-warning' ?>">
                        <strong><?= $prod['stock'] ?></strong>
                    </td>
                    <td class="text-secondary"><?= $prod['stock_minimo'] ?></td>
                    <td>
                        <?php if ($prod['stock'] == 0): ?>
                            <span class="badge badge-danger">Sin stock</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Bajo</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>


<?php elseif (hasRole('vendedor')): ?>
<!-- ═══════════════════════════════════════════
     VENDEDOR DASHBOARD
     ═══════════════════════════════════════════ -->

<div class="employee-dashboard employee-dashboard--compact">
    <div class="employee-panel-summary">
        <div class="employee-panel-summary-item">
            <div class="employee-summary-icon"><i data-lucide="package"></i></div>
            <div class="employee-summary-text">
                <strong><?= $data['total_productos'] ?></strong>
                <span>Productos</span>
            </div>
        </div>

        <div class="employee-panel-summary-item">
            <div class="employee-summary-icon warning"><i data-lucide="alert-triangle"></i></div>
            <div class="employee-summary-text">
                <strong><?= count($data['bajo_stock']) ?></strong>
                <span>Bajo stock</span>
            </div>
        </div>

        <div class="employee-panel-summary-item">
            <div class="employee-summary-icon primary"><i data-lucide="plus-circle"></i></div>
            <div class="employee-summary-text">
                <strong>Nuevo</strong>
                <span>Producto</span>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ═══════════════════════════════════════════
     CLIENTE DASHBOARD
     ═══════════════════════════════════════════ -->

<div class="employee-dashboard employee-dashboard--compact">
    <div class="stat-card animate-fade-in-up">
        <div class="stat-card-icon success"><i data-lucide="shopping-bag"></i></div>
        <div class="stat-card-value"><?= $data['total_pedidos'] ?></div>
        <div class="stat-card-label">Mis Pedidos</div>
    </div>

    <div class="stat-card animate-fade-in-up">
        <div class="stat-card-icon primary"><i data-lucide="store"></i></div>
        <a href="<?= BASE_URL ?>/index.php?controller=productos&action=catalogo" class="btn btn-primary btn-sm mt-sm">Explorar</a>
        <div class="stat-card-label mt-sm">Ir al Catálogo</div>
    </div>

    <div class="stat-card animate-fade-in-up">
        <div class="stat-card-icon secondary"><i data-lucide="shopping-cart"></i></div>
        <a href="<?= BASE_URL ?>/index.php?controller=carrito&action=index" class="btn btn-secondary btn-sm mt-sm">Ver Carrito</a>
        <div class="stat-card-label mt-sm">Mi Carrito</div>
    </div>
</div>

<div class="card animate-fade-in-up">
    <div class="card-header">
        <h4 class="card-title">🕐 Mis Pedidos Recientes</h4>
        <a href="<?= BASE_URL ?>/index.php?controller=pedidos&action=misPedidos" class="btn btn-sm btn-secondary">Ver todos</a>
    </div>
    <?php if (!empty($data['mis_pedidos'])): ?>
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th># Pedido</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['mis_pedidos'] as $pedido): ?>
                <tr>
                    <td><strong>#<?= $pedido['id'] ?></strong></td>
                    <td><?= formatPrice($pedido['total']) ?></td>
                    <td>
                        <?php
                        $badgeMap = ['pendiente'=>'warning','confirmado'=>'info','enviado'=>'primary','entregado'=>'success','cancelado'=>'danger'];
                        $badge = $badgeMap[$pedido['estado']] ?? 'secondary';
                        ?>
                        <span class="badge badge-<?= $badge ?>"><?= ucfirst($pedido['estado']) ?></span>
                    </td>
                    <td class="text-secondary"><?= date('d/m/Y', strtotime($pedido['created_at'])) ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>/index.php?controller=pedidos&action=detalle&id=<?= $pedido['id'] ?>" class="btn btn-sm btn-ghost">Ver</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="table-empty">
            <div class="table-empty-icon">🛍️</div>
            <p>Aún no tienes pedidos</p>
            <a href="<?= BASE_URL ?>/index.php?controller=productos&action=catalogo" class="btn btn-primary btn-sm mt-md">¡Empieza a comprar!</a>
        </div>
    <?php endif; ?>
</div>

<?php endif; ?>
