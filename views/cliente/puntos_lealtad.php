<?php
/**
 * Puntos de lealtad del cliente.
 * Los puntos son informativos y se calculan con compras no canceladas.
 */
$puntos = (int)($puntos ?? 0);
$totalCompras = (float)($totalCompras ?? 0);
$comprasValidas = (int)($comprasValidas ?? 0);
$promociones = is_array($promociones ?? null) ? $promociones : [];
$nivel = $puntos >= 500 ? 'Oro' : ($puntos >= 200 ? 'Plata' : 'Inicial');
$proximoNivel = $puntos < 200 ? 'Plata' : ($puntos < 500 ? 'Oro' : 'Beneficios especiales');
$puntosFaltantes = $puntos < 200 ? 200 - $puntos : ($puntos < 500 ? 500 - $puntos : 0);
$descuentoLealtadActivo = !empty($descuentoLealtadActivo);
$premioGratisActivo = !empty($premioGratisActivo);
?>

<div class="cli-page-header animate-fade-in">
    <div>
        <div class="cli-chip"><i data-lucide="star"></i> BENEFICIOS DEL CLIENTE</div>
        <h1 class="cli-page-title">Puntos de <span>lealtad</span></h1>
        <p class="cli-page-subtitle">Acumula puntos con tus compras y conoce las ofertas disponibles.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=catalogo" class="cli-btn cli-btn-primary"><i data-lucide="store"></i> Seguir comprando</a>
</div>

<div class="cli-stats animate-fade-in-up">
    <div class="cli-stat" style="cursor:default;"><div class="cli-stat-icon green"><i data-lucide="star"></i></div><div class="cli-stat-label">Puntos acumulados</div><div class="cli-stat-value"><?= number_format($puntos) ?></div><div class="cli-stat-sub">10 puntos por cada $100.000 en compras válidas</div></div>
    <div class="cli-stat" style="cursor:default;"><div class="cli-stat-icon blue"><i data-lucide="award"></i></div><div class="cli-stat-label">Nivel actual</div><div class="cli-stat-value" style="font-size:1.45rem;"><?= e($nivel) ?></div><div class="cli-stat-sub"><?= $puntosFaltantes ? number_format($puntosFaltantes) . ' puntos para ' . e($proximoNivel) : 'Nivel máximo alcanzado' ?></div></div>
    <div class="cli-stat" style="cursor:default;"><div class="cli-stat-icon indigo"><i data-lucide="shopping-bag"></i></div><div class="cli-stat-label">Compras válidas</div><div class="cli-stat-value"><?= number_format($comprasValidas) ?></div><div class="cli-stat-sub">Total acumulado: <?= formatPrice($totalCompras) ?></div></div>
</div>

<div class="cli-card animate-fade-in-up">
    <div class="cli-card-header"><h2 class="cli-card-title"><i data-lucide="gift"></i> Cómo obtener beneficios</h2></div>
    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;">
        <div style="padding:15px;border:1px solid var(--cli-border);border-radius:10px;"><i data-lucide="shopping-cart" style="color:var(--cli-green-dark);width:21px;height:21px;"></i><strong style="display:block;margin:9px 0 4px;font-size:.85rem;">Compra en la tienda</strong><p class="cli-page-subtitle" style="font-size:.76rem;">Cada compra no cancelada suma puntos automáticamente.</p></div>
        <div style="padding:15px;border:1px solid var(--cli-border);border-radius:10px;"><i data-lucide="trending-up" style="color:var(--cli-green-dark);width:21px;height:21px;"></i><strong style="display:block;margin:9px 0 4px;font-size:.85rem;">Sube de nivel</strong><p class="cli-page-subtitle" style="font-size:.76rem;">Alcanza 200 puntos para Plata y 500 para Oro.</p></div>
        <div style="padding:15px;border:1px solid var(--cli-border);border-radius:10px;"><i data-lucide="badge-percent" style="color:var(--cli-green-dark);width:21px;height:21px;"></i><strong style="display:block;margin:9px 0 4px;font-size:.85rem;">Aprovecha ofertas</strong><p class="cli-page-subtitle" style="font-size:.76rem;">Consulta las promociones activas para obtener descuentos.</p></div>
    </div>
</div>

<div class="cli-card animate-fade-in-up">
    <div class="cli-card-header"><h2 class="cli-card-title"><i data-lucide="gift"></i> Beneficios por puntos</h2></div>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;">
        <div style="padding:16px;border:1px solid var(--cli-border);border-radius:10px;">
            <strong style="display:block;margin-bottom:6px;">200 puntos: 50% de descuento</strong>
            <p class="cli-page-subtitle" style="font-size:.78rem;">Úsalo en cualquier producto disponible durante tu próxima compra.</p>
            <?php if ($descuentoLealtadActivo): ?>
                <span class="cli-badge cli-badge-green">Descuento activado</span>
            <?php elseif ($puntos >= 200): ?>
                <a class="cli-btn cli-btn-primary cli-btn-sm" href="<?= BASE_URL ?>/index.php?controller=cliente&action=activarDescuentoLealtad"><i data-lucide="badge-percent"></i> Activar 50%</a>
            <?php else: ?>
                <span class="cli-badge cli-badge-gray"><?= number_format(200 - $puntos) ?> puntos faltantes</span>
            <?php endif; ?>
        </div>
        <div style="padding:16px;border:1px solid var(--cli-border);border-radius:10px;">
            <strong style="display:block;margin-bottom:6px;">500 puntos: producto gratis</strong>
            <p class="cli-page-subtitle" style="font-size:.78rem;">Recibe un producto disponible elegido aleatoriamente.</p>
            <?php if ($premioGratisActivo): ?>
                <span class="cli-badge cli-badge-green">Premio agregado al carrito</span>
            <?php elseif ($puntos >= 500): ?>
                <a class="cli-btn cli-btn-primary cli-btn-sm" href="<?= BASE_URL ?>/index.php?controller=cliente&action=reclamarProductoGratis"><i data-lucide="gift"></i> Reclamar producto</a>
            <?php else: ?>
                <span class="cli-badge cli-badge-gray"><?= number_format(500 - $puntos) ?> puntos faltantes</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="cli-card animate-fade-in-up">
    <div class="cli-card-header"><h2 class="cli-card-title"><i data-lucide="tag"></i> Descuentos y ofertas activas</h2><a href="<?= BASE_URL ?>/index.php?controller=cliente&action=promociones" class="cli-btn cli-btn-ghost cli-btn-sm">Ver todas</a></div>
    <?php if (!$promociones): ?>
        <div class="cli-empty" style="padding:25px;"><div class="cli-empty-title">No hay ofertas activas en este momento</div><p class="cli-empty-text" style="margin-bottom:0;">Vuelve pronto para conocer nuevos beneficios.</p></div>
    <?php else: ?>
        <div class="cli-pay-grid">
            <?php foreach (array_slice($promociones, 0, 4) as $promocion): ?>
                <div class="cli-pay-option" style="cursor:default;"><div class="cli-pay-icon"><i data-lucide="tag"></i></div><div><div class="cli-pay-name"><?= e($promocion['nombre'] ?? 'Promoción') ?></div><div class="cli-pay-desc"><?= !empty($promocion['producto_nombre']) ? 'Aplica a: ' . e($promocion['producto_nombre']) : 'Aplica a toda la tienda' ?></div></div><span class="cli-badge cli-badge-red" style="margin-left:auto;">-<?= number_format((float)($promocion['descuento'] ?? 0), 0) ?>%</span></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
