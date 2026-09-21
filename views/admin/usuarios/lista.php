<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">👥 Usuarios registrados</h2>
            <p class="text-secondary">Clientes, empleados y administradores del sistema.</p>
        </div>
        <div class="table-actions" style="display:flex; gap: 12px; align-items: center;">
            <a href="<?= BASE_URL ?>/index.php?controller=usuarios&action=nuevo" class="btn btn-primary btn-sm">Registrar empleado</a>
            <a href="<?= BASE_URL ?>/index.php?controller=usuarios&action=lista" style="color: var(--color-text); text-decoration: none; font-weight: 600; font-size: 0.9rem;">Todos</a>
            <a href="<?= BASE_URL ?>/index.php?controller=usuarios&action=lista&rol=cliente" style="color: var(--color-text); text-decoration: none; font-weight: 600; font-size: 0.9rem;">Clientes</a>
            <a href="<?= BASE_URL ?>/index.php?controller=usuarios&action=lista&rol=vendedor" style="color: var(--color-text); text-decoration: none; font-weight: 600; font-size: 0.9rem;">Empleados</a>
        </div>
    </div>

    <form method="GET" action="<?= BASE_URL ?>/index.php" class="user-search-form" style="margin-bottom: 2rem;">
        <input type="hidden" name="controller" value="usuarios">
        <input type="hidden" name="action" value="lista">
        <?php if (!empty($_GET['rol'])): ?>
            <input type="hidden" name="rol" value="<?= e($_GET['rol']) ?>">
        <?php endif; ?>
        <label class="form-label" for="buscar" style="font-size:0.85rem; color:var(--color-text-secondary); margin-bottom: 4px; display:block;">Buscar usuario</label>
        <div style="display:flex; gap: 1rem;">
            <input type="text" class="form-control" style="flex: 1;" id="buscar" name="buscar" value="<?= e($busqueda ?? '') ?>" placeholder="Nombre o correo">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
    </form>

    <?php if (empty($usuarios)): ?>
        <div class="table-empty">
            <div class="table-empty-icon">👤</div>
            <p>No hay usuarios registrados con esos filtros.</p>
        </div>
    <?php else: ?>
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr style="background: rgba(255,255,255,0.03); text-transform: uppercase; font-size: 0.75rem;">
                        <th style="padding:12px; color:var(--color-text-secondary);">ID</th>
                        <th style="padding:12px; color:var(--color-text-secondary);">Nombre</th>
                        <th style="padding:12px; color:var(--color-text-secondary);">Email</th>
                        <th style="padding:12px; color:var(--color-text-secondary);">Rol</th>
                        <th style="padding:12px; color:var(--color-text-secondary);">Teléfono</th>
                        <th style="padding:12px; color:var(--color-text-secondary);">Estado</th>
                        <th style="padding:12px; color:var(--color-text-secondary);">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td style="padding:14px; border-bottom: 1px solid rgba(0,0,0,0.05);"><?= e((string)$usuario['id']) ?></td>
                            <td style="padding:14px; border-bottom: 1px solid rgba(0,0,0,0.05);"><?= e($usuario['nombre']) ?></td>
                            <td style="padding:14px; border-bottom: 1px solid rgba(0,0,0,0.05);"><?= e($usuario['email']) ?></td>
                            <td style="padding:14px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                <span class="badge" style="background:rgba(46,204,113,0.15); color:#27ae60;">
                                    <?= e(ucfirst($usuario['rol'])) ?>
                                </span>
                            </td>
                            <td style="padding:14px; border-bottom: 1px solid rgba(0,0,0,0.05);"><?= e($usuario['telefono'] ?? 'Sin teléfono') ?></td>
                            <td style="padding:14px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                <?php if (!empty($usuario['activo'])): ?>
                                    <span class="badge" style="background:rgba(46,204,113,0.15); color:#27ae60;">Activo</span>
                                <?php else: ?>
                                    <span class="badge" style="background:rgba(231,76,60,0.15); color:#c0392b;">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:14px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                                <div class="table-actions" style="display:flex; gap: 8px; flex-wrap: wrap;">
                                    <?php if ($usuario['rol'] === 'vendedor'): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/index.php?controller=usuarios&action=recuperarCredenciales" style="margin:0;">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= e((string)$usuario['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-info" style="padding: 4px 10px; font-size: 0.8rem;" onclick="return confirm('¿Deseas generar y recuperar la contraseña para este empleado?');">Gestionar cuenta</button>
                                    </form>
                                    <?php endif; ?>
                                    <form method="POST" action="<?= BASE_URL ?>/index.php?controller=usuarios&action=cambiarEstado" style="margin:0;">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= e((string)$usuario['id']) ?>">
                                        <input type="hidden" name="activo" value="<?= (int)empty($usuario['activo']) ?>">
                                        <button type="submit" class="btn btn-sm <?= !empty($usuario['activo']) ? 'btn-warning' : 'btn-success' ?>" style="padding: 4px 10px; font-size: 0.8rem;">
                                            <?= !empty($usuario['activo']) ? 'Desactivar' : 'Activar' ?>
                                        </button>
                                    </form>
                                    <form method="POST" action="<?= BASE_URL ?>/index.php?controller=usuarios&action=eliminar" onsubmit="return confirm('¿Deseas eliminar este usuario?');" style="margin:0;">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="id" value="<?= e((string)$usuario['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" style="padding: 4px 10px; font-size: 0.8rem;">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>