<?php
/**
 * Vista — Formulario crear/editar producto
 */
$editando = isset($producto);
$titulo   = $editando ? 'Editar Producto' : 'Nuevo Producto';
$action   = $editando
    ? BASE_URL . '/index.php?controller=productos&action=actualizar'
    : BASE_URL . '/index.php?controller=productos&action=guardar';
?>

<div class="pv-header animate-fade-in">
    <div class="ped-header-left">
        <a href="<?= BASE_URL ?>/index.php?controller=productos&action=lista" class="pv-back">
            <i data-lucide="arrow-left"></i>
        </a>
        <div>
            <h1 class="pv-title">📦 <?= $titulo ?></h1>
        </div>
    </div>
</div>

<div class="pv-layout">
    <div class="pv-main">
        <form action="<?= $action ?>" method="POST" enctype="multipart/form-data" class="pv-section animate-fade-in-up">
            <?= csrfField() ?>
            <?php if ($editando): ?>
                <input type="hidden" name="id" value="<?= $producto['id'] ?>">
            <?php endif; ?>

            <div class="pv-form-grid">
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" class="form-control" required
                           value="<?= e($producto['nombre'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Precio *</label>
                    <input type="number" name="precio" class="form-control" step="0.01" min="0" required
                           value="<?= $producto['precio'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control" min="0"
                           value="<?= $producto['stock'] ?? 0 ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Stock minimo</label>
                    <input type="number" name="stock_minimo" class="form-control" min="0"
                           value="<?= $producto['stock_minimo'] ?? 5 ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Categoria</label>
                    <select name="categoria_id" class="form-control">
                        <option value="">Sin categoria</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= ($producto['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Descripcion</label>
                    <textarea name="descripcion" class="form-control" rows="4"><?= e($producto['descripcion'] ?? '') ?></textarea>
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label" for="imagen">Imagen del producto</label>
                    <?php if (!empty($producto['imagen'])): ?>
                        <div style="margin-bottom:10px;display:flex;align-items:center;gap:12px;">
                            <img src="<?= IMG_URL ?>/productos/<?= e($producto['imagen']) ?>" alt="Imagen actual" style="width:86px;height:86px;object-fit:cover;border-radius:10px;border:1px solid var(--border-color);">
                            <span style="font-size:.85rem;color:var(--text-secondary);">Imagen actual. Selecciona otra para reemplazarla.</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="imagen" id="imagen" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
                    <small style="display:block;margin-top:6px;color:var(--text-secondary);">JPG, PNG, WEBP o GIF. Máximo 5 MB.</small>
                </div>
            </div>

            <div class="pv-actions-footer">
                <a href="<?= BASE_URL ?>/index.php?controller=productos&action=lista"
                   class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save"></i> <?= $editando ? 'Actualizar' : 'Crear' ?> producto
                </button>
            </div>
        </form>
    </div>
</div>
