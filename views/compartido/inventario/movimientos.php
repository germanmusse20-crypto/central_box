<?php
/**
 * Vista — Movimientos de Inventario
 */
$tipoMap = ['entrada' => 'success', 'salida' => 'danger', 'ajuste' => 'warning'];
?>

<div class="ped-header animate-fade-in">
    <div>
        <h1 class="ped-title">📦 Movimientos de Inventario</h1>
        <p class="ped-subtitle">Historial de entradas, salidas y ajustes de stock.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?controller=inventario&action=index"
       class="btn btn-secondary btn-sm">
        <i data-lucide="arrow-left"></i> Volver a Inventario
    </a>
</div>

<!-- Filtros -->
<div class="card animate-fade-in mb-lg">
    <form method="GET" action="<?= BASE_URL ?>/index.php" class="ped-filters">
        <input type="hidden" name="controller" value="inventario">
        <input type="hidden" name="action" value="movimientos">

        <select name="producto" class="form-control" style="width:220px;">
            <option value="">Todos los productos</option>
            <?php foreach ($productos as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($_GET['producto'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                    <?= e($p['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="tipo" class="form-control" style="width:160px;">
            <option value="">Todos los tipos</option>
            <option value="entrada"    <?= ($_GET['tipo'] ?? '') === 'entrada'    ? 'selected' : '' ?>>Entrada</option>
            <option value="salida"     <?= ($_GET['tipo'] ?? '') === 'salida'     ? 'selected' : '' ?>>Salida</option>
            <option value="ajuste"     <?= ($_GET['tipo'] ?? '') === 'ajuste'     ? 'selected' : '' ?>>Ajuste</option>
        </select>

        <button type="submit" class="btn btn-primary">
            <i data-lucide="filter"></i> Filtrar
        </button>
        <a href="<?= BASE_URL ?>/index.php?controller=inventario&action=movimientos"
           class="btn btn-secondary">
            <i data-lucide="x"></i> Limpiar
        </a>
    </form>
</div>

<div class="card animate-fade-in-up">
    <div class="card-header">
        <h4 class="card-title">📋 Historial de movimientos</h4>
        <span class="venta-total-badge"><?= count($movimientos) ?> registros</span>
    </div>

    <?php if (empty($movimientos)): ?>
        <div class="table-empty">
            <div class="table-empty-icon">📦</div>
            <p>No hay movimientos registrados.</p>
        </div>
    <?php else: ?>
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Stock anterior</th>
                        <th>Stock nuevo</th>
                        <th>Motivo</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimientos as $m):
                        $badge = $tipoMap[$m['tipo']] ?? 'secondary';
                    ?>
                    <tr>
                        <td class="text-secondary"><?= $m['id'] ?></td>
                        <td><strong><?= e($m['producto_nombre']) ?></strong></td>
                        <td><span class="badge badge-<?= $badge ?>"><?= ucfirst($m['tipo']) ?></span></td>
                        <td><strong><?= $m['cantidad'] ?></strong></td>
                        <td class="text-secondary"><?= $m['stock_anterior'] ?></td>
                        <td><strong><?= $m['stock_nuevo'] ?></strong></td>
                        <td class="text-secondary"><?= e($m['motivo'] ?? '—') ?></td>
                        <td class="text-secondary">
                            <?= date('d/m/Y H:i', strtotime($m['created_at'])) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
