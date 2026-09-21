<?php
/**
 * Promociones activas para clientes.
 * Solo muestra promociones aprobadas y vigentes.
 */
$promociones = is_array($promociones ?? null) ? $promociones : [];
?>

<div class="cli-page-header animate-fade-in">
    <div>
        <div class="cli-chip"><i data-lucide="tag"></i> BENEFICIOS CENTRAL BOX</div>
        <h1 class="cli-page-title">Promociones <span>activas</span></h1>
        <p class="cli-page-subtitle">Aprovecha las ofertas disponibles durante su periodo de vigencia.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=catalogo" class="cli-btn cli-btn-primary">
        <i data-lucide="store"></i> Ver productos
    </a>
</div>

<?php if (!$promociones): ?>
    <div class="cli-card cli-empty animate-fade-in-up">
        <div class="cli-empty-icon"><i data-lucide="tag"></i></div>
        <div class="cli-empty-title">No hay promociones activas</div>
        <p class="cli-empty-text">Vuelve pronto para conocer nuevas ofertas de la tienda.</p>
    </div>
<?php else: ?>
    <div class="cli-catalog-grid">
        <?php foreach ($promociones as $promocion):
            $inicio = !empty($promocion['fecha_inicio']) ? strtotime($promocion['fecha_inicio']) : false;
            $fin = !empty($promocion['fecha_fin']) ? strtotime($promocion['fecha_fin']) : false;
            $descuento = (float)($promocion['descuento'] ?? 0);
        ?>
            <article class="cli-product-card animate-fade-in-up">
                <div class="cli-product-img" style="height:145px;">
                    <div class="cli-placeholder" style="color:var(--cli-green-dark);"><i data-lucide="badge-percent" style="width:48px;height:48px;"></i></div>
                    <span class="cli-badge cli-badge-red cli-product-badge">-<?= number_format($descuento, 0) ?>%</span>
                </div>
                <div class="cli-product-body">
                    <div class="cli-product-cat"><?= !empty($promocion['producto_nombre']) ? 'Producto seleccionado' : 'Toda la tienda' ?></div>
                    <div class="cli-product-name"><?= e($promocion['nombre'] ?? 'Promoción activa') ?></div>
                    <?php if (!empty($promocion['descripcion'])): ?><p style="font-size:.78rem;color:var(--cli-text-sec);line-height:1.5;margin:0 0 10px;"><?= e($promocion['descripcion']) ?></p><?php endif; ?>
                    <?php if (!empty($promocion['producto_nombre'])): ?><div style="font-size:.78rem;color:var(--cli-text-sec);margin-bottom:8px;"><strong>Aplica a:</strong> <?= e($promocion['producto_nombre']) ?></div><?php endif; ?>
                    <div class="cli-product-price" style="font-size:.9rem;">Descuento del <?= number_format($descuento, 0) ?>%</div>
                    <div class="cli-product-stock-text" style="margin:0;">Vigencia: <?= $inicio ? date('d/m/Y', $inicio) : 'Sin fecha' ?> al <?= $fin ? date('d/m/Y', $fin) : 'Sin fecha' ?></div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <div class="cli-card" style="margin-top:18px;padding:14px 18px;"><p class="cli-page-subtitle" style="margin:0;"><i data-lucide="info" style="width:15px;height:15px;vertical-align:middle;"></i> Las promociones se aplican según sus condiciones al momento de finalizar la compra.</p></div>
<?php endif; ?>
