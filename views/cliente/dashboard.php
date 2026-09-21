<?php
/**
 * Dashboard del cliente.
 * UH-18: catalogo | UH-22: carrito | pedidos recientes.
 */
$pedidos = is_array($misPedidos ?? null) ? $misPedidos : [];
$total = (int)($totalPedidos ?? count($pedidos));
$carritoCount = (int)($carritoCount ?? 0);
$carritoTotal = (float)($carritoTotal ?? 0);
$destacados = is_array($destacados ?? null) ? $destacados : [];
?>

<style>
</style>

<div class="dashboard-welcome animate-fade-in">
    <div>
        <h2>Hola, <?= e($user['nombre'] ?? 'cliente') ?>!</h2>
        <p class="text-secondary">Todo lo que necesitas para comprar en central box.</p>
    </div>
</div>

<div class="grid grid-3 stagger mb-xl">
    <div class="stat-card animate-fade-in-up">
        <div class="stat-card-icon success"><i data-lucide="shopping-bag"></i></div>
        <div class="stat-card-value"><?= number_format($total) ?></div>
        <div class="stat-card-label">Mis pedidos</div>
        <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=misOrders" class="btn btn-ghost btn-sm mt-sm">Ver pedidos</a>
    </div>
    <div class="stat-card animate-fade-in-up">
        <div class="stat-card-icon primary"><i data-lucide="shopping-cart"></i></div>
        <div class="stat-card-value"><?= number_format($carritoCount) ?></div>
        <div class="stat-card-label">Productos en carrito</div>
        <a href="<?= BASE_URL ?>/index.php?controller=carrito&action=index" class="btn btn-secondary btn-sm mt-sm">Ver carrito</a>
    </div>
    <div class="stat-card animate-fade-in-up">
        <div class="stat-card-icon secondary"><i data-lucide="wallet-cards"></i></div>
        <div class="stat-card-value"><?= formatPrice($carritoTotal) ?></div>
        <div class="stat-card-label">Total del carrito</div>
        <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=catalogo" class="btn btn-ghost btn-sm mt-sm">Seguir comprando</a>
    </div>
</div>

<div class="card animate-fade-in-up">
    <div class="card-header">
        <h4 class="card-title"><i data-lucide="clock-3"></i> Mis pedidos recientes</h4>
        <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=misOrders" class="btn btn-sm btn-secondary">Ver todos</a>
    </div>
    <?php if ($pedidos): ?>
        <div class="table-scroll">
            <table class="table">
                <thead><tr><th># Pedido</th><th>Total</th><th>Estado</th><th>Fecha</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($pedidos as $pedido):
                    $estado = (string)($pedido['estado'] ?? 'confirmado');
                    $badgeMap = ['confirmado'=>'info','entregado'=>'success','cancelado'=>'danger'];
                    $badge = $badgeMap[$estado] ?? 'secondary';
                ?>
                    <tr>
                        <td><strong>#<?= (int)$pedido['id'] ?></strong></td>
                        <td><?= formatPrice((float)$pedido['total']) ?></td>
                        <td><span class="badge badge-<?= $badge ?>"><?= e(ucfirst($estado)) ?></span></td>
                        <td class="text-secondary"><?= !empty($pedido['created_at']) ? date('d/m/Y', strtotime($pedido['created_at'])) : 'Sin fecha' ?></td>
                        <td><a href="<?= BASE_URL ?>/index.php?controller=cliente&action=detallePedido&id=<?= (int)$pedido['id'] ?>" class="btn btn-sm btn-ghost">Ver detalle</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="table-empty">
            <div class="table-empty-icon"><i data-lucide="shopping-bag"></i></div>
            <p>Aun no tienes pedidos</p>
            <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=catalogo" class="btn btn-primary btn-sm mt-md">Empezar a comprar</a>
        </div>
    <?php endif; ?>
</div>

<?php if ($destacados): ?>
<div class="card animate-fade-in-up mt-lg">
    <div class="card-header"><h4 class="card-title"><i data-lucide="sparkles"></i> Productos destacados</h4><a href="<?= BASE_URL ?>/index.php?controller=cliente&action=catalogo" class="btn btn-sm btn-secondary">Ver catalogo</a></div>
    <div class="grid grid-4">
        <?php foreach ($destacados as $producto): ?>
            <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=catalogo" class="card" style="text-decoration:none;color:inherit;padding:14px;">
                <strong><?= e($producto['nombre']) ?></strong>
                <span class="text-secondary"><?= formatPrice((float)$producto['precio']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
