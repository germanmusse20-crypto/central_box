<?php
/**
 * Vista — Gestión de Pedidos
 */
$badgeMap = [
    'confirmado' => 'info',
    'entregado'  => 'success',
    'cancelado'  => 'danger',
];

$estadoActual  = $_GET['estado']   ?? '';
$busquedaActual= $_GET['busqueda'] ?? '';
?>

<!-- ══════════════════════════════════
     ENCABEZADO
     ══════════════════════════════════ -->
<div class="ped-header animate-fade-in">
    <div>
        <h1 class="ped-title">📋 Gestión de Pedidos</h1>
        <p class="ped-subtitle">Administra y da seguimiento a todos los pedidos de la plataforma.</p>
    </div>
</div>

<!-- ══════════════════════════════════
     STATS RÁPIDAS
     ══════════════════════════════════ -->
<div class="ped-stats stagger animate-fade-in mb-lg">

    <div class="ped-stat-card">
        <div class="ped-stat-icon" style="background:rgba(46,204,113,.12);color:#27ae60;">
            <i data-lucide="shopping-bag"></i>
        </div>
        <div>
            <div class="ped-stat-num"><?= number_format($stats['total_pedidos']) ?></div>
            <div class="ped-stat-lbl">Total pedidos</div>
        </div>
    </div>

    <div class="ped-stat-card">
        <div class="ped-stat-icon" style="background:rgba(243,156,18,.12);color:#e67e22;">
            <i data-lucide="clock"></i>
        </div>
        <div>
            <div class="ped-stat-num"><?= $stats['por_estado']['confirmado'] ?? 0 ?></div>
            <div class="ped-stat-lbl">Confirmados</div>
        </div>
    </div>

    <div class="ped-stat-card">
        <div class="ped-stat-icon" style="background:rgba(46,204,113,.12);color:#27ae60;">
            <i data-lucide="check-circle"></i>
        </div>
        <div>
            <div class="ped-stat-num"><?= $stats['por_estado']['entregado'] ?? 0 ?></div>
            <div class="ped-stat-lbl">Entregados</div>
        </div>
    </div>

    <div class="ped-stat-card">
        <div class="ped-stat-icon" style="background:rgba(46,204,113,.12);color:#27ae60;">
            <i data-lucide="dollar-sign"></i>
        </div>
        <div>
            <div class="ped-stat-num"><?= formatPrice($stats['total_ventas']) ?></div>
            <div class="ped-stat-lbl">Total ventas</div>
        </div>
    </div>

    <div class="ped-stat-card">
        <div class="ped-stat-icon" style="background:rgba(46,204,113,.12);color:#27ae60;">
            <i data-lucide="trending-up"></i>
        </div>
        <div>
            <div class="ped-stat-num"><?= formatPrice($stats['ventas_mes']) ?></div>
            <div class="ped-stat-lbl">Ventas del mes</div>
        </div>
    </div>

</div>

<!-- ══════════════════════════════════
     FILTROS
     ══════════════════════════════════ -->
<div class="card animate-fade-in mb-lg">
    <form method="GET" action="<?= BASE_URL ?>/index.php" class="ped-filters">
        <input type="hidden" name="controller" value="pedidos">
        <input type="hidden" name="action"     value="index">

        <div class="ped-search-wrap">
            <i data-lucide="search" class="ped-search-icon"></i>
            <input type="text" name="busqueda"
                   class="form-control ped-search-input"
                   placeholder="Buscar por # pedido o nombre del cliente..."
                   value="<?= e($busquedaActual) ?>">
        </div>

        <select name="estado" class="form-control ped-select">
            <option value="">Todos los estados</option>
                <?php foreach (['confirmado','entregado','cancelado'] as $est): ?>
                <option value="<?= $est ?>" <?= $estadoActual === $est ? 'selected' : '' ?>>
                    <?= ucfirst($est) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-primary">
            <i data-lucide="filter"></i> Filtrar
        </button>

        <a href="<?= BASE_URL ?>/index.php?controller=pedidos&action=index"
           class="btn btn-secondary">
            <i data-lucide="x"></i> Limpiar
        </a>
    </form>
</div>

<!-- Tabs de estado rápido -->
<div class="ped-tabs animate-fade-in mb-md">
    <?php
    $tabs = [
        ''           => ['Todos',      $stats['total_pedidos'],               'secondary'],
        'confirmado' => ['Confirmado', $stats['por_estado']['confirmado'] ?? 0, 'info'],
        'entregado'  => ['Entregado',  $stats['por_estado']['entregado'] ?? 0, 'success'],
        'cancelado'  => ['Cancelado',  $stats['por_estado']['cancelado'] ?? 0, 'danger'],
    ];
    foreach ($tabs as $val => [$label, $count, $color]):
        $active = $estadoActual === $val ? 'active' : '';
        $url    = BASE_URL . '/index.php?controller=pedidos&action=index' . ($val ? "&estado=$val" : '');
    ?>
        <a href="<?= $url ?>" class="ped-tab <?= $active ?> ped-tab-<?= $color ?>">
            <?= $label ?>
            <span class="ped-tab-count"><?= $count ?></span>
        </a>
    <?php endforeach; ?>
</div>

<!-- ══════════════════════════════════
     TABLA DE PEDIDOS
     ══════════════════════════════════ -->
<div class="card animate-fade-in-up">
    <div class="card-header">
        <h4 class="card-title">
            📦 Pedidos
            <?php if ($busquedaActual || $estadoActual): ?>
                <span class="ped-filter-active">
                    <?= count($pedidos) ?> resultado<?= count($pedidos) !== 1 ? 's' : '' ?>
                </span>
            <?php endif; ?>
        </h4>
        <span class="venta-total-badge"><?= count($pedidos) ?> pedidos</span>
    </div>

    <?php if (empty($pedidos)): ?>
        <div class="table-empty">
            <div class="table-empty-icon">📭</div>
            <p>No se encontraron pedidos<?= $estadoActual ? " con estado <strong>" . ucfirst($estadoActual) . "</strong>" : '' ?><?= $busquedaActual ? " para <strong>" . e($busquedaActual) . "</strong>" : '' ?>.</p>
        </div>
    <?php else: ?>
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Método pago</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $p):
                        $badge = $badgeMap[$p['estado']] ?? 'secondary';
                    ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>/index.php?controller=pedidos&action=detalle&id=<?= $p['id'] ?>"
                               class="ped-id-link">
                                #<?= e($p['id']) ?>
                            </a>
                        </td>
                        <td>
                            <div class="ped-client-cell">
                                <div class="ped-avatar">
                                    <?= strtoupper(substr($p['cliente_nombre'] ?? 'C', 0, 1)) ?>
                                </div>
                                <div>
                                    <strong><?= e($p['cliente_nombre']) ?></strong>
                                    <span class="text-secondary"><?= e($p['cliente_email']) ?></span>
                                </div>
                            </div>
                        </td>
                        <td><strong><?= formatPrice((float)$p['total']) ?></strong></td>
                        <td>
                            <span class="badge badge-<?= $badge ?>">
                                <?= ucfirst(e($p['estado'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="ped-metodo">
                                <i data-lucide="<?= $p['metodo_pago'] === 'efectivo' ? 'banknote' : 'credit-card' ?>"
                                   style="width:13px;height:13px;display:inline;vertical-align:middle;"></i>
                                <?= ucfirst(e($p['metodo_pago'])) ?>
                            </span>
                        </td>
                        <td class="text-secondary">
                            <?= date('d/m/Y', strtotime($p['created_at'])) ?>
                            <br><small><?= date('H:i', strtotime($p['created_at'])) ?></small>
                        </td>
                        <td>
                            <div class="table-actions">
                                <!-- Ver detalle -->
                                <a href="<?= BASE_URL ?>/index.php?controller=pedidos&action=detalle&id=<?= $p['id'] ?>"
                                   class="btn btn-ghost btn-sm" title="Ver detalle">
                                    <i data-lucide="eye"></i>
                                </a>

                                <!-- Cambiar estado según estado actual -->
                                <?php if ($p['estado'] === 'confirmado'): ?>
                                    <a href="<?= BASE_URL ?>/index.php?controller=pedidos&action=enviar&id=<?= $p['id'] ?>"
                                       class="btn btn-sm btn-success" title="Marcar entregado"
                                       onclick="return confirm('¿Marcar como entregado el pedido #<?= $p['id'] ?>?')">
                                        <i data-lucide="package-check"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if ($p['estado'] === 'confirmado'): ?>
                                    <a href="<?= BASE_URL ?>/index.php?controller=pedidos&action=entregar&id=<?= $p['id'] ?>"
                                       class="btn btn-sm btn-success" title="Marcar entregado"
                                       onclick="return confirm('¿Marcar como entregado el pedido #<?= $p['id'] ?>?')">
                                        <i data-lucide="package-check"></i>
                                    </a>
                                <?php endif; ?>

                                <!-- Cancelar -->
                                <?php if ($p['estado'] === 'confirmado'): ?>
                                    <a href="<?= BASE_URL ?>/index.php?controller=pedidos&action=cancelar&id=<?= $p['id'] ?>"
                                       class="btn btn-sm btn-danger" title="Cancelar"
                                       onclick="return confirm('¿Cancelar pedido #<?= $p['id'] ?>?')">
                                        <i data-lucide="x-circle"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
