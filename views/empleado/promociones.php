<?php
/**
 * Vista: Mis Promociones (Vendedor)
 */
$badgeMap = [
    'pendiente' => 'yellow',
    'activa' => 'green',
    'rechazada' => 'red',
    'vencida' => 'gray',
    'eliminada' => 'gray'
];
?>
<div class="emp-page-header emp-animate-in">
    <div>
        <h1 class="emp-page-title">Mis <span>Promociones</span></h1>
        <p class="emp-page-subtitle">Gestiona las solicitudes de promociones enviadas al administrador.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=solicitarPromocion" class="emp-btn emp-btn-primary">
        <i data-lucide="plus-circle"></i> Solicitar Promoción
    </a>
</div>

<?php if (isset($_SESSION['flash_success']) || isset($_SESSION['flash_error'])): ?>
    <!-- Handled by layout / flash messages, but shown here explicitly if needed -->
<?php endif; ?>

<div class="emp-card emp-animate-in">
    <?php if (empty($promociones)): ?>
        <div class="emp-empty" style="text-align:center; padding: 40px 20px;">
            <div style="font-size: 3rem; margin-bottom: 15px;">🏷️</div>
            <h3 style="font-size: 1.2rem; color: var(--emp-text-main); margin-bottom: 10px;">Sin promociones solicitadas</h3>
            <p style="color: var(--emp-text-sec); margin-bottom: 20px;">No has enviado ninguna solicitud de promoción al administrador.</p>
            <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=solicitarPromocion" class="emp-btn emp-btn-primary" style="display:inline-flex;">
                <i data-lucide="plus"></i> Crear mi primera solicitud
            </a>
        </div>
    <?php else: ?>
        <div class="emp-table-wrap">
            <table class="emp-table">
                <thead>
                    <tr>
                        <th>Nombre / Descripción</th>
                        <th>Producto</th>
                        <th>Descuento</th>
                        <th>Vigencia</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($promociones as $p): 
                        $estado = strtolower($p['estado'] ?? 'pendiente');
                        
                        // Lógica de presentación según historias de usuario: 
                        // Promoción vencida si se pasó de la fecha de inicio/fin y seguía pendiente o no autorizada
                        if ($estado === 'pendiente' && strtotime($p['fecha_fin']) < time()) {
                            $estado = 'vencida';
                        }
                        
                        $badgeColor = $badgeMap[$estado] ?? 'gray';
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600; color: var(--emp-text-main);"><?= e($p['nombre']) ?></div>
                            <div style="font-size: 0.8rem; color: var(--emp-text-sec); margin-top: 4px; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($p['descripcion']) ?>">
                                <?= e($p['descripcion']) ?: 'Sin descripción' ?>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($p['producto_id'])): ?>
                                <span class="emp-badge emp-badge-indigo" style="display:inline-flex; align-items:center;">
                                    <i data-lucide="package" style="width:14px;height:14px;margin-right:4px;"></i> 
                                    <?= e($p['producto_nombre'] ?? 'Desconocido') ?>
                                </span>
                            <?php else: ?>
                                <span class="emp-badge emp-badge-gray">Toda la tienda</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight: 700; color: #10B981; font-size:1.1rem;">
                            <?= e($p['descuento']) ?>%
                        </td>
                        <td style="font-size: 0.85rem; color: var(--emp-text-sec);">
                            <?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?> <br>
                            <?= date('d/m/Y', strtotime($p['fecha_fin'])) ?>
                        </td>
                        <td>
                            <span class="emp-badge emp-badge-<?= $badgeColor ?>">
                                <?= ucfirst($estado) ?>
                            </span>
                            <?php if ($estado === 'rechazada'): ?>
                                <div style="font-size: 0.75rem; color: #EF4444; margin-top: 4px; line-height:1.2;">No aprobada<br>por el admin.</div>
                            <?php elseif ($estado === 'vencida'): ?>
                                <div style="font-size: 0.75rem; color: #6B7280; margin-top: 4px; line-height:1.2;">Expiró por falta<br>de aprobación.</div>
                            <?php elseif ($estado === 'pendiente'): ?>
                                <div style="font-size: 0.75rem; color: #D97706; margin-top: 4px; line-height:1.2;">Esperando revisión<br>del administrador.</div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
