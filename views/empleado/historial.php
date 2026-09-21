<?php
/**
 * UH-34 — Historial de ventas
 */
$desdeAct  = $_GET['desde']    ?? '';
$hastaAct  = $_GET['hasta']    ?? '';
$busAct    = $_GET['busqueda'] ?? '';
$estAct    = $_GET['estado']   ?? '';
$total     = array_sum(array_column($ventas, 'total'));
$historialController = $historialController ?? 'empleado';
$historialAction = $historialAction ?? 'historial';
$nuevaVentaController = $nuevaVentaController ?? 'empleado';
$nuevaVentaAction = $nuevaVentaAction ?? 'nuevaVenta';
$facturaController = $facturaController ?? 'ventas';
$facturaAction = $facturaAction ?? 'factura';
$esVentasOnline = $esVentasOnline ?? false;
$historialUrl = BASE_URL . '/index.php?controller=' . rawurlencode($historialController)
    . '&action=' . rawurlencode($historialAction);

$badgeMap = ['confirmado'=>'indigo','entregado'=>'green','cancelado'=>'red'];

$totalProductos = $totalProductos ?? 0;
$totalStock = $totalStock ?? 0;
?>

<div class="emp-page-header emp-animate-in">
    <div>
        <?php if ($esVentasOnline): ?>
            <h1 class="emp-page-title">Ventas <span>realizadas</span></h1>
            <p class="emp-page-subtitle">Consulta y gestiona las ventas realizadas en el establecimiento.</p>
        <?php else: ?>
            <h1 class="emp-page-title">Historial de <span>Ventas</span></h1>
            <p class="emp-page-subtitle">Consulta y verifica todas las transacciones.</p>
        <?php endif; ?>
    </div>
    <?php if ($totalStock > 0): ?>
    <a href="<?= BASE_URL ?>/index.php?controller=<?= e($nuevaVentaController) ?>&action=<?= e($nuevaVentaAction) ?>"
       class="emp-btn emp-btn-primary">
        <i data-lucide="plus-circle"></i> Nueva venta
    </a>
    <?php endif; ?>
</div>

<!-- Filtros -->
<div class="emp-card emp-animate-in" style="margin-bottom:20px;">
    <form method="GET" action="<?= BASE_URL ?>/index.php"
          style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="controller" value="<?= e($historialController) ?>">
        <input type="hidden" name="action"     value="<?= e($historialAction) ?>">

        <!-- Busqueda por cliente -->
        <div style="flex:1;min-width:200px;">
            <label class="emp-label">Buscar cliente / # venta</label>
            <div class="emp-searchbar">
                <span class="emp-searchbar-icon"><i data-lucide="search"></i></span>
                <input type="text" name="busqueda" class="emp-input"
                       placeholder="Nombre del cliente o ID..."
                       value="<?= e($busAct) ?>">
            </div>
        </div>

        <!-- Rango de fechas -->
        <div class="emp-date-range">
            <div>
                <label class="emp-label">Desde</label>
                <input type="date" name="desde" class="emp-input" value="<?= e($desdeAct) ?>">
            </div>
            <span class="emp-date-sep" style="align-self:flex-end;padding-bottom:11px;">—</span>
            <div>
                <label class="emp-label">Hasta</label>
                <input type="date" name="hasta" class="emp-input" value="<?= e($hastaAct) ?>">
            </div>
        </div>

        <!-- Estado -->
        <div style="width:160px;">
            <label class="emp-label">Estado</label>
            <select name="estado" class="emp-select">
                <option value="">Todos</option>
                <?php foreach (['confirmado','entregado','cancelado'] as $e): ?>
                    <option value="<?= $e ?>" <?= $estAct === $e ? 'selected' : '' ?>><?= ucfirst($e) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="emp-btn emp-btn-primary" style="align-self:flex-end;">
            <i data-lucide="filter"></i> Filtrar
        </button>
        <a href="<?= e($historialUrl) ?>"
           class="emp-btn emp-btn-ghost" style="align-self:flex-end;">
            <i data-lucide="x"></i> Limpiar
        </a>
    </form>
</div>

<!-- Resultado -->
<?php if (empty($ventas)): ?>
    <div class="emp-card emp-empty emp-animate-in">
        <div class="emp-empty-icon">📋</div>
        <?php if ($totalProductos === 0 && !($busAct || $desdeAct || $hastaAct || $estAct)): ?>
            <div class="emp-empty-title">No hay productos registrados</div>
            <p class="emp-empty-text">Registra al menos un producto con su precio y stock inicial para comenzar a vender.</p>
            <a href="<?= BASE_URL ?>/index.php?controller=productos&action=crear"
               class="emp-btn emp-btn-primary emp-btn-sm" style="margin-top:12px;">
                <i data-lucide="plus"></i> Registrar producto
            </a>
        <?php elseif ($totalStock === 0 && !($busAct || $desdeAct || $hastaAct || $estAct)): ?>
            <div class="emp-empty-title">Sin stock disponible</div>
            <p class="emp-empty-text">Los productos registrados están agotados. Agrega stock a tus productos para vender.</p>
        <?php else: ?>
            <div class="emp-empty-title">Sin ventas registradas</div>
            <p class="emp-empty-text">
                <?= ($busAct || $desdeAct || $hastaAct || $estAct)
                    ? 'No hay resultados para los filtros aplicados.'
                    : 'Aun no hay ventas registradas en el sistema.' ?>
            </p>
            <a href="<?= BASE_URL ?>/index.php?controller=<?= e($nuevaVentaController) ?>&action=<?= e($nuevaVentaAction) ?>"
               class="emp-btn emp-btn-primary emp-btn-sm" style="margin-top:12px;">
                <i data-lucide="plus"></i> Registrar primera venta
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <!-- Resumen rápido -->
    <div style="display:flex;gap:16px;margin-bottom:16px;flex-wrap:wrap;">
        <div class="emp-badge emp-badge-green" style="font-size:.82rem;padding:6px 14px;">
            <?= count($ventas) ?> ventas
        </div>
        <div class="emp-badge emp-badge-indigo" style="font-size:.82rem;padding:6px 14px;">
            Total: <?= formatPrice($total) ?>
        </div>
        <?php if ($desdeAct || $hastaAct): ?>
            <div class="emp-badge emp-badge-gray" style="font-size:.78rem;padding:6px 12px;">
                <?= $desdeAct ? date('d/m/Y', strtotime($desdeAct)) : '…' ?>
                →
                <?= $hastaAct ? date('d/m/Y', strtotime($hastaAct)) : hoy ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="emp-table-wrap emp-animate-in">
        <table class="emp-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Metodo pago</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas as $v):
                    $badge = $badgeMap[$v['estado']] ?? 'gray';
                ?>
                <tr>
                    <td>
                        <a href="<?= BASE_URL ?>/index.php?controller=<?= e($facturaController) ?>&action=<?= e($facturaAction) ?>&id=<?= (int) $v['id'] ?>"
                           style="font-weight:700;color:#6366F1;text-decoration:none;">
                            #<?= $v['id'] ?>
                        </a>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#10B981,#6366F1);color:white;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0;">
                                <?= strtoupper(substr($v['cliente_nombre'] ?? 'C', 0, 1)) ?>
                            </div>
                            <?= e($v['cliente_nombre'] ?? '—') ?>
                        </div>
                    </td>
                    <td><strong><?= formatPrice((float)$v['total']) ?></strong></td>
                    <td><span class="emp-badge emp-badge-<?= $badge ?>"><?= ucfirst($v['estado']) ?></span></td>
                    <td style="color:var(--emp-text-sec);"><?= ucfirst(e($v['metodo_pago'] ?? '')) ?></td>
                    <td style="color:var(--emp-text-sec);font-size:.78rem;">
                        <?= date('d/m/Y', strtotime($v['created_at'])) ?><br>
                        <span style="color:#9CA3AF;"><?= date('H:i', strtotime($v['created_at'])) ?></span>
                    </td>
                    <td>
                        <a href="<?= BASE_URL ?>/index.php?controller=<?= e($facturaController) ?>&action=<?= e($facturaAction) ?>&id=<?= (int) $v['id'] ?>"
                           class="emp-btn emp-btn-ghost emp-btn-sm" title="Ver factura">
                            <i data-lucide="file-text"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
