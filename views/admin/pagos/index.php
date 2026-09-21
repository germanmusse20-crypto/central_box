<div class="pagos-header animate-fade-in">
    <div>
        <div class="pagos-eyebrow"><i data-lucide="settings-2"></i> CONFIGURACIÓN COMERCIAL</div>
        <h1 class="pagos-title"><span class="pagos-highlight">Métodos de pago</span></h1>
        <p class="pagos-subtitle">Define cómo pueden pagar tus clientes y mantén disponibles las opciones de cobro.</p>
    </div>
</div>

<?php $activos = count(array_filter($metodosPago, fn($p) => $p['estado'] === 'activo')); ?>
<div class="pagos-summary animate-fade-in">
    <div><i data-lucide="wallet-cards"></i><span><strong><?= count($metodosPago) ?></strong><small>Métodos configurados</small></span></div>
    <div><i data-lucide="circle-check"></i><span><strong><?= $activos ?></strong><small>Disponibles para cobrar</small></span></div>
    <div><i data-lucide="circle-off"></i><span><strong><?= count($metodosPago) - $activos ?></strong><small>Deshabilitados</small></span></div>
</div>

<section class="pagos-container animate-fade-in-up">
    <div class="pagos-section-heading">
        <div><h2><i data-lucide="credit-card"></i> Opciones de cobro</h2><p>Los métodos activos aparecerán como opción al registrar una compra.</p></div>
        <span class="pagos-count"><?= count($metodosPago) ?> configurados</span>
    </div>
    <div class="pagos-grid">
        <?php if (empty($metodosPago)): ?>
            <div class="pagos-empty"><i data-lucide="credit-card"></i><strong>No hay métodos configurados</strong><span>Agrega métodos para recibir pagos.</span></div>
        <?php else: ?>
            <?php foreach ($metodosPago as $pago): $activo = $pago['estado'] === 'activo'; ?>
                <article class="pagos-method <?= $activo ? 'is-active' : 'is-inactive' ?>">
                    <div class="pagos-method-top"><div class="icon-circle"><i data-lucide="<?= stripos($pago['nombre'], 'transfer') !== false ? 'landmark' : 'credit-card' ?>"></i></div><span class="badge-status <?= $activo ? 'badge-active' : 'badge-inactive' ?>"><?= $activo ? 'Activo' : 'Inactivo' ?></span></div>
                    <h3><?= e($pago['nombre']) ?></h3>
                    <p><?= e($pago['descripcion']) ?></p>
                    <div class="pagos-method-footer"><span><i data-lucide="<?= $activo ? 'check' : 'minus' ?>"></i><?= $activo ? 'Disponible en checkout' : 'No disponible' ?></span><a href="<?= BASE_URL ?>/index.php?controller=pagos&action=toggle&id=<?= (int) $pago['id'] ?>&estado=<?= $activo ? 'inactivo' : 'activo' ?>" class="btn-toggle <?= $activo ? 'btn-disable' : 'btn-enable' ?>"><?= $activo ? 'Deshabilitar' : 'Activar' ?></a></div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
</div>
