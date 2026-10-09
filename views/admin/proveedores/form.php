<div class="card" style="max-width:680px;">
    <div class="card-header">
        <h2 class="card-title">
            <?= $proveedor ? '✏️ Editar proveedor' : '🏭 Nuevo proveedor' ?>
        </h2>
    </div>

    <form method="POST" action="<?= e($accion) ?>">
        <?= csrfField() ?>

        <?php if ($proveedor): ?>
            <input type="hidden" name="id" value="<?= (int)$proveedor['id'] ?>">
        <?php endif; ?>

        <!-- Nombre -->
        <div class="form-group" style="margin-bottom:1.2rem;">
            <label class="form-label" for="nombre">Nombre del proveedor *</label>
            <input type="text" id="nombre" name="nombre" class="form-control"
                   value="<?= e($proveedor['nombre'] ?? '') ?>"
                   placeholder="Ej: Distribuidora XYZ" required minlength="3">
        </div>

        <!-- Contacto -->
        <div class="form-group" style="margin-bottom:1.2rem;">
            <label class="form-label" for="contacto">Nombre del contacto</label>
            <input type="text" id="contacto" name="contacto" class="form-control"
                   value="<?= e($proveedor['contacto'] ?? '') ?>"
                   placeholder="Ej: Juan Pérez">
        </div>

        <!-- Email -->
        <div class="form-group" style="margin-bottom:1.2rem;">
            <label class="form-label" for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" class="form-control"
                   value="<?= e($proveedor['email'] ?? '') ?>"
                   placeholder="contacto@proveedor.com">
        </div>

        <!-- Teléfono -->
        <div class="form-group" style="margin-bottom:1.2rem;">
            <label class="form-label" for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono" class="form-control"
                   value="<?= e($proveedor['telefono'] ?? '') ?>"
                   placeholder="Ej: 3001234567">
        </div>

        <!-- Dirección -->
        <div class="form-group" style="margin-bottom:1.5rem;">
            <label class="form-label" for="direccion">Dirección</label>
            <input type="text" id="direccion" name="direccion" class="form-control"
                   value="<?= e($proveedor['direccion'] ?? '') ?>"
                   placeholder="Calle 123 # 45-67, Ciudad">
        </div>

        <!-- Botones -->
        <div style="display:flex;gap:1rem;">
            <button type="submit" class="btn btn-primary">
                <?= $proveedor ? 'Guardar cambios' : 'Registrar proveedor' ?>
            </button>
            <a href="<?= BASE_URL ?>/index.php?controller=proveedores&action=lista"
               class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
