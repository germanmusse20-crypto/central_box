<div class="card" style="max-width: 760px; margin: 0 auto;">
    <div class="card-header">
        <div>
            <h2 class="card-title">👤 Registrar empleado</h2>
            <p class="text-secondary">Crea una cuenta para un empleado del sistema con acceso del panel.</p>
        </div>
        <a href="<?= BASE_URL ?>/index.php?controller=usuarios&action=lista" class="btn btn-secondary btn-sm">Volver</a>
    </div>

    <form action="<?= BASE_URL ?>/index.php?controller=usuarios&action=guardar" method="POST" class="auth-form">
        <?= csrfField() ?>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="nombre">Nombre completo</label>
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre del empleado" required minlength="3" autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="telefono">Teléfono</label>
                <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="+57 300 000 0000">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Correo electrónico</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="empleado@centralbox.com" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="password">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Mínimo 8 caracteres" required minlength="8">
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirm">Confirmar contraseña</label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Repite la contraseña" required minlength="8">
            </div>
        </div>

        <div class="card-footer" style="justify-content: flex-end; padding-top: 0; border-top: none; margin-top: 1rem;">
            <button type="submit" class="btn btn-primary">Guardar empleado</button>
        </div>
    </form>
</div>
