<?php
/**
 * Vista — Promociones
 * HU1: Visualizar promociones activas y solicitadas
 * HU2: Eliminar / rechazar promociones
 * HU3: Aprobar promociones
 */

$badgeMap = [
    'pendiente' => 'warning',
    'activa'    => 'success',
    'rechazada' => 'danger'
];
?>

<style>
.promo-header {
    margin-bottom: 24px;
}
.promo-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--color-text);
    margin-bottom: 4px;
}
.promo-subtitle {
    color: var(--color-text-secondary);
    font-size: 0.95rem;
}
.promo-highlight {
    color: #e74c3c;
}

.promo-container {
    background: transparent;
    padding: 24px 0;
    margin-bottom: 30px;
}
.promo-container h2 {
    font-size: 1.25rem;
    margin-bottom: 20px;
    color: var(--color-text);
    display: flex;
    align-items: center;
    gap: 8px;
}

.promo-table {
    width: 100%;
    border-collapse: collapse;
}
.promo-table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--color-text-secondary);
    text-transform: uppercase;
}
.promo-table td {
    padding: 14px 16px;
    color: var(--color-text);
    vertical-align: middle;
}
.promo-table tr:not(:last-child) {
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.badge {
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: capitalize;
}
.badge-warning {
    background: rgba(243, 156, 18, 0.2);
    color: #d68910;
}
.badge-success {
    background: rgba(46, 204, 113, 0.2);
    color: #27ae60;
}
.badge-danger {
    background: rgba(231, 76, 60, 0.2);
    color: #c0392b;
}

.action-buttons {
    display: flex;
    gap: 8px;
}
.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    color: white;
    text-decoration: none;
    transition: transform 0.2s;
}
.btn-icon:hover {
    transform: scale(1.05);
}
.btn-approve { background: #2ecc71; }
.btn-reject { background: #f39c12; }
.btn-delete { background: #e74c3c; }
</style>

<div class="promo-header animate-fade-in">
    <h1 class="promo-title">🏷️ <span class="promo-highlight">Promociones</span></h1>
    <p class="promo-subtitle">Gestiona las promociones activas y aprueba o rechaza las solicitadas por los empleados.</p>
</div>

<div class="promo-container animate-fade-in stagger">
    <h2><i data-lucide="tag" style="transform: rotate(90deg); margin-right: 4px;"></i> Listado de Promociones</h2>
    
    <div style="overflow-x: auto;">
        <table class="promo-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Descuento</th>
                    <th>Vigencia</th>
                    <th>Solicitado por</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($promociones)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--color-text-secondary); padding: 30px;">
                            No hay promociones registradas en el sistema.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($promociones as $promo): ?>
                        <tr>
                            <td>
                                <strong style="display:block;"><?= htmlspecialchars($promo['nombre']) ?></strong>
                                <?php if ($promo['producto_nombre']): ?>
                                    <span style="font-size:0.8rem; color:var(--color-text-secondary);">Producto: <?= htmlspecialchars($promo['producto_nombre']) ?></span>
                                <?php else: ?>
                                    <span style="font-size:0.8rem; color:var(--color-text-secondary);">Aplica a toda la tienda</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($promo['descripcion']) ?>">
                                <?= htmlspecialchars($promo['descripcion']) ?>
                            </td>
                            <td style="font-weight: bold; color: #e74c3c;">
                                -<?= number_format($promo['descuento'], 0) ?>%
                            </td>
                            <td style="font-size: 0.9rem;">
                                Del: <?= date('d/m/Y', strtotime($promo['fecha_inicio'])) ?><br>
                                Al: <?= date('d/m/Y', strtotime($promo['fecha_fin'])) ?>
                            </td>
                            <td><?= htmlspecialchars($promo['solicitante']) ?></td>
                            <td>
                                <span class="badge badge-<?= $badgeMap[$promo['estado']] ?? 'warning' ?>">
                                    <?= htmlspecialchars($promo['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($promo['estado'] === 'pendiente'): ?>
                                        <a href="<?= BASE_URL ?>/index.php?controller=promociones&action=aprobar&id=<?= $promo['id'] ?>" class="btn-icon btn-approve" title="Aprobar Promoción">
                                            <i data-lucide="check" style="width:16px;"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>/index.php?controller=promociones&action=rechazar&id=<?= $promo['id'] ?>" class="btn-icon btn-reject" title="Rechazar Promoción">
                                            <i data-lucide="x" style="width:16px;"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="<?= BASE_URL ?>/index.php?controller=promociones&action=eliminar&id=<?= $promo['id'] ?>" class="btn-icon btn-delete" title="Eliminar Promoción" onclick="return confirm('¿Estás seguro de eliminar esta promoción permanentemente?');">
                                        <i data-lucide="trash-2" style="width:16px;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
