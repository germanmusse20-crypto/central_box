<?php
/**
 * Vista — Formulario de Categoría (Crear/Editar)
 * HU1, HU2, HU5
 */
$isEdit = isset($categoria) && $categoria !== null;
$actionUrl = $isEdit ? BASE_URL . '/index.php?controller=categorias&action=actualizar' : BASE_URL . '/index.php?controller=categorias&action=guardar';
?>

<style>
.cat-form-container {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 30px;
    max-width: 600px;
    margin: 0 auto;
}
.cat-form-container h2 {
    font-size: 1.5rem;
    margin-bottom: 24px;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 12px;
}
.form-group {
    margin-bottom: 20px;
}
.form-label {
    display: block;
    margin-bottom: 8px;
    color: var(--text-secondary);
    font-weight: 500;
}
.form-control {
    width: 100%;
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    background: rgba(255,255,255,0.03);
    color: var(--text-primary);
    font-size: 1rem;
    transition: border-color 0.2s;
}
.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
}
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 30px;
}
.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-primary {
    background: #e67e22;
    color: #fff;
}
.btn-primary:hover {
    background: #d35400;
}
.btn-secondary {
    background: rgba(255,255,255,0.1);
    color: var(--text-primary);
}
.btn-secondary:hover {
    background: rgba(255,255,255,0.2);
}
</style>

<div class="cat-form-container animate-fade-in">
    <h2><?= $isEdit ? '✏️ Editar Categoría' : '➕ Nueva Categoría' ?></h2>

    <!-- HU5: La validación de campos obligatorios se maneja con 'required' de HTML5 y flash messages en el backend -->
    <form action="<?= $actionUrl ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label class="form-label" for="nombre">Nombre de la Categoría <span style="color:#e74c3c;">*</span></label>
            <input type="text" id="nombre" name="nombre" class="form-control" 
                   value="<?= htmlspecialchars($categoria['nombre'] ?? '') ?>" 
                   placeholder="Ej. Electrónica, Ropa, Hogar..." required>
        </div>

        <div class="form-group">
            <label class="form-label" for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" class="form-control" rows="4" 
                      placeholder="Breve descripción de la categoría..."><?= htmlspecialchars($categoria['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <a href="<?= BASE_URL ?>/index.php?controller=categorias&action=lista" class="btn btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save"></i> <?= $isEdit ? 'Guardar Categoría' : 'Crear Categoría' ?>
            </button>
        </div>
    </form>
</div>
