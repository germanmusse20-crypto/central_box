<?php
/**
 * Vista — Detalle de producto
 */
?>

<div class="ped-header animate-fade-in">
    <div class="ped-header-left">
        <a href="<?= BASE_URL ?>/index.php?controller=productos&action=catalogo" class="pv-back">
            <i data-lucide="arrow-left"></i>
        </a>
        <div>
            <h1 class="ped-title">📦 <?= e($producto['nombre']) ?></h1>
            <p class="ped-subtitle"><?= e($producto['categoria_nombre'] ?? '') ?></p>
        </div>
    </div>
    <?php if (hasRole('admin','vendedor')): ?>
        <a href="<?= BASE_URL ?>/index.php?controller=productos&action=editar&id=<?= $producto['id'] ?>"
           class="btn btn-secondary btn-sm">
            <i data-lucide="edit"></i> Editar
        </a>
    <?php endif; ?>
</div>

<div class="ped-detail-layout animate-fade-in">
    <div class="ped-detail-main">
        <div class="card">
            <?php if (!empty($producto['imagen'])): ?>
                <img src="<?= IMG_URL ?>/productos/<?= e($producto['imagen']) ?>"
                     alt="<?= e($producto['nombre']) ?>"
                     style="width:100%;max-height:300px;object-fit:cover;border-radius:var(--radius-md);margin-bottom:var(--space-lg);"
                     onerror="this.style.display='none'">
            <?php endif; ?>
            <h3><?= e($producto['nombre']) ?></h3>
            <p style="margin-top:var(--space-md);color:var(--color-text-secondary);line-height:1.7;">
                <?= nl2br(e($producto['descripcion'] ?? 'Sin descripcion.')) ?>
            </p>
        </div>
    </div>

    <div class="ped-detail-side">
        <div class="card">
            <div class="ped-sum-total" style="margin-bottom:var(--space-md);">
                <span>Precio</span>
                <span><?= formatPrice((float)$producto['precio']) ?></span>
            </div>
            <div class="ped-info-row">
                <span>Stock</span>
                <span class="badge badge-<?= $producto['stock'] > 0 ? 'success' : 'danger' ?>">
                    <?= $producto['stock'] > 0 ? $producto['stock'] . ' unidades' : 'Sin stock' ?>
                </span>
            </div>
            <div class="ped-info-row">
                <span>Categoria</span>
                <span><?= e($producto['categoria_nombre'] ?? '—') ?></span>
            </div>
            <?php if (hasRole('cliente') && $producto['stock'] > 0): ?>
                <a href="<?= BASE_URL ?>/index.php?controller=carrito&action=agregar&id=<?= $producto['id'] ?>"
                   class="btn btn-primary btn-block mt-md">
                    <i data-lucide="shopping-cart"></i> Agregar al carrito
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
