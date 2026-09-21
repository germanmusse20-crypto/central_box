<?php
/**
 * Vista — Lista de Categorías
 */
?>

<style>
/* Encabezado */
.cat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}
.cat-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}
.cat-subtitle {
    color: var(--text-secondary);
    font-size: 0.95rem;
}
.cat-highlight {
    color: #e67e22;
}

/* Contenedor de Tabla */
.cat-container {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
    overflow-x: auto;
}
.cat-container h2 {
    font-size: 1.25rem;
    margin-bottom: 20px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 12px;
}

/* Tabla */
.cat-table {
    width: 100%;
    border-collapse: collapse;
}
.cat-table th {
    background: rgba(255,255,255,0.03);
    padding: 12px 16px;
    text-align: left;
    font-size: 0.85rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    border-bottom: 1px solid var(--border-color);
}
.cat-table td {
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
    vertical-align: middle;
}
.cat-table tr:hover {
    background: rgba(255,255,255,0.02);
}

.action-btns {
    display: flex;
    gap: 8px;
}
.btn-sm {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.85rem;
    text-decoration: none;
    font-weight: 500;
    color: #fff;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.btn-edit { background: #3498db; }
.btn-edit:hover { background: #2980b9; }
.btn-delete { background: #e74c3c; }
.btn-delete:hover { background: #c0392b; }
</style>

<div class="cat-header animate-fade-in">
    <div>
        <h1 class="cat-title">📁 <span class="cat-highlight">Categorías</span></h1>
        <p class="cat-subtitle">Organiza tus productos en diferentes categorías</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?controller=categorias&action=crear" class="btn btn-primary" style="background:#e67e22; color:#fff;">
        <i data-lucide="plus-circle"></i> Nueva Categoría
    </a>
</div>

<div class="cat-container stagger animate-fade-in">
    <h2><i data-lucide="list"></i> Listado de Categorías Registradas</h2>
    
    <table class="cat-table">
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th style="width: 200px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categorias)): ?>
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                        No existen categorías registradas. (HU4)
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td style="color: var(--text-secondary); font-weight: bold;">#<?= $cat['id'] ?></td>
                        <td style="font-weight: bold; color: var(--text-primary);"><?= htmlspecialchars($cat['nombre']) ?></td>
                        <td><?= htmlspecialchars($cat['descripcion']) ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="<?= BASE_URL ?>/index.php?controller=categorias&action=editar&id=<?= $cat['id'] ?>" class="btn-sm btn-edit" title="Editar Categoría (HU2)">
                                    <i data-lucide="edit" style="width:14px;"></i> Editar
                                </a>
                                <a href="<?= BASE_URL ?>/index.php?controller=categorias&action=eliminar&id=<?= $cat['id'] ?>" class="btn-sm btn-delete" title="Eliminar Categoría Duplicada (HU3)" onclick="return confirm('¿Estás seguro de que deseas eliminar esta categoría? Esta acción es irreversible (HU3).');">
                                    <i data-lucide="trash-2" style="width:14px;"></i> Eliminar
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
