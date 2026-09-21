<?php
/**
 * UH-25/UH-26 — Métodos de pago disponibles para el cliente.
 * Esta vista es informativa: no permite seleccionar ni modificar métodos.
 */
$metodosPago = is_array($metodosPago ?? null) ? $metodosPago : [];
?>

<div class="cli-page-header animate-fade-in">
    <div>
        <div class="cli-chip"><i data-lucide="credit-card"></i> COMPRA SEGURA</div>
        <h1 class="cli-page-title">Métodos de <span>pago</span></h1>
        <p class="cli-page-subtitle">Consulta las formas de pago habilitadas para tus compras.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=carrito" class="cli-btn cli-btn-secondary">
        <i data-lucide="shopping-cart"></i> Mi carrito
    </a>
</div>

<div class="cli-card animate-fade-in-up">
    <div class="cli-card-header">
        <h2 class="cli-card-title"><i data-lucide="wallet-cards"></i> Métodos disponibles</h2>
        <span class="cli-badge cli-badge-green">Solo consulta</span>
    </div>

    <?php if (!$metodosPago): ?>
        <div class="cli-empty" style="padding:35px 20px;">
            <div class="cli-empty-icon"><i data-lucide="credit-card"></i></div>
            <div class="cli-empty-title">No hay métodos de pago disponibles</div>
            <p class="cli-empty-text">Por el momento no hay métodos habilitados para finalizar compras.</p>
        </div>
    <?php else: ?>
        <div class="cli-pay-grid">
            <?php foreach ($metodosPago as $metodo):
                $icono = preg_replace('/[^a-z0-9-]/i', '', (string)($metodo['icono'] ?? 'credit-card')) ?: 'credit-card';
            ?>
                <div class="cli-pay-option" style="cursor:default;">
                    <div class="cli-pay-icon"><i data-lucide="<?= e($icono) ?>"></i></div>
                    <div>
                        <div class="cli-pay-name"><?= e($metodo['nombre'] ?? 'Método de pago') ?></div>
                        <div class="cli-pay-desc"><?= e($metodo['descripcion'] ?? 'Disponible al finalizar tu compra.') ?></div>
                    </div>
                    <span class="cli-badge cli-badge-green" style="margin-left:auto;">Habilitado</span>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="cli-page-subtitle" style="margin-top:18px;">Los métodos se seleccionan únicamente durante el proceso de finalización de compra.</p>
    <?php endif; ?>
</div>
