<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">🏭 Proveedores</h2>
            <p class="text-secondary">Empresas y personas que abastecen los productos de central_box.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/index.php?controller=proveedores&action=nuevo"
               class="btn btn-primary btn-sm">+ Nuevo proveedor</a>
        </div>
    </div>

    <!-- Formulario de búsqueda -->
    <form method="GET" action="<?= BASE_URL ?>/index.php" style="margin-bottom:2rem;">
        <input type="hidden" name="controller" value="proveedores">
        <input type="hidden" name="action" value="lista">
        <div style="display:flex; gap:1rem;">
            <input type="text" class="form-control" name="buscar"
                   value="<?= e($busqueda ?? '') ?>"
                   placeholder="Buscar por nombre, contacto o email" style="flex:1;">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
    </form>

    <?php if (empty($proveedores)): ?>
        <div class="table-empty">
            <div class="table-empty-icon">🏭</div>
            <p>No hay proveedores registrados aún.</p>
        </div>
    <?php else: ?>
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proveedores as $p): ?>
                    <tr>
                        <td><?= e((string)$p['id']) ?></td>
                        <td><?= e($p['nombre']) ?></td>
                        <td><?= e($p['contacto'] ?? '—') ?></td>
                        <td><?= e($p['email']    ?? '—') ?></td>
                        <td><?= e($p['telefono'] ?? '—') ?></td>
                        <td>
                            <?php if ($p['activo']): ?>
                                <span class="badge" style="background:rgba(46,204,113,0.15);color:#27ae60;">Activo</span>
                            <?php else: ?>
                                <span class="badge" style="background:rgba(231,76,60,0.15);color:#c0392b;">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                <!-- Botón Editar -->
                                <a href="<?= BASE_URL ?>/index.php?controller=proveedores&action=editar&id=<?= (int)$p['id'] ?>"
                                   class="btn btn-sm btn-info" style="padding:4px 10px;font-size:0.8rem;">
                                    Editar
                                </a>

                                <!-- Botón Activar / Desactivar -->
                                <form method="POST"
                                      action="<?= BASE_URL ?>/index.php?controller=proveedores&action=cambiarEstado"
                                      style="margin:0;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <input type="hidden" name="activo" value="<?= $p['activo'] ? 0 : 1 ?>">
                                    <button type="submit"
                                            class="btn btn-sm <?= $p['activo'] ? 'btn-warning' : 'btn-success' ?>"
                                            style="padding:4px 10px;font-size:0.8rem;">
                                        <?= $p['activo'] ? 'Desactivar' : 'Activar' ?>
                                    </button>
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