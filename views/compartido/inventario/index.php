<?php
/**
 * Vista — Inventario / Dashboard
 * Muestra indicadores y lista de productos
 */
?>

<!-- =========================================================================
     ESTILOS ESPECÍFICOS PARA INVENTARIO
     ========================================================================= -->
<style>
/* Encabezado */
.inv-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}
.inv-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}
.inv-subtitle {
    color: var(--text-secondary);
    font-size: 0.95rem;
}
.inv-highlight {
    color: var(--primary-color);
}

/* Tarjetas de Estadísticas */
.inv-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.inv-stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.inv-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}
.inv-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.inv-stat-num {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
}
.inv-stat-lbl {
    font-size: 0.85rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Tablas */
.inv-table-container {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    overflow-x: auto;
}
.inv-table-container h2 {
    font-size: 1.25rem;
    margin-bottom: 16px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
}
.inv-table {
    width: 100%;
    border-collapse: collapse;
}
.inv-table th {
    background: rgba(255,255,255,0.03);
    padding: 12px 16px;
    text-align: left;
    font-size: 0.85rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid var(--border-color);
}
.inv-table td {
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}
.inv-table tbody tr:hover {
    background: rgba(255,255,255,0.02);
}
.badge-stock-ok {
    background: rgba(46, 204, 113, 0.15);
    color: #2ecc71;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}
.badge-stock-low {
    background: rgba(243, 156, 18, 0.15);
    color: #f39c12;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}
.badge-stock-out {
    background: rgba(231, 76, 60, 0.15);
    color: #e74c3c;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}
.prod-img {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    object-fit: cover;
    background: #333;
}
.prod-info {
    display: flex;
    align-items: center;
    gap: 12px;
}
</style>

<!-- =========================================================================
     CONTENIDO DE LA VISTA
     ========================================================================= -->

<!-- Encabezado -->
<div class="inv-header animate-fade-in">
    <div>
        <h1 class="inv-title">📦 <span class="inv-highlight">Inventario</span></h1>
        <p class="inv-subtitle">Controla el stock de productos y recibe alertas oportunas.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?controller=inventario&action=movimientos" class="btn btn-secondary">
        <i data-lucide="history"></i> Ver Movimientos
    </a>
</div>

<!-- Estadísticas (Indicadores de stock) -->
<div class="inv-stats stagger animate-fade-in">
    
    <div class="inv-stat-card">
        <div class="inv-stat-icon" style="background:rgba(52,152,219,0.15); color:#3498db;">
            <i data-lucide="package"></i>
        </div>
        <div>
            <div class="inv-stat-num"><?= number_format($resumen['total_productos'] ?? 0) ?></div>
            <div class="inv-stat-lbl">Productos</div>
        </div>
    </div>

    <div class="inv-stat-card">
        <div class="inv-stat-icon" style="background:rgba(46,204,113,0.15); color:#2ecc71;">
            <i data-lucide="layers"></i>
        </div>
        <div>
            <div class="inv-stat-num"><?= number_format($resumen['total_unidades'] ?? 0) ?></div>
            <div class="inv-stat-lbl">Unidades Totales</div>
        </div>
    </div>

    <div class="inv-stat-card">
        <div class="inv-stat-icon" style="background:rgba(243,156,18,0.15); color:#f39c12;">
            <i data-lucide="alert-triangle"></i>
        </div>
        <div>
            <div class="inv-stat-num"><?= number_format($resumen['bajo_stock'] ?? 0) ?></div>
            <div class="inv-stat-lbl">Bajo Stock</div>
        </div>
    </div>

    <div class="inv-stat-card">
        <div class="inv-stat-icon" style="background:rgba(231,76,60,0.15); color:#e74c3c;">
            <i data-lucide="x-octagon"></i>
        </div>
        <div>
            <div class="inv-stat-num"><?= number_format($resumen['sin_stock'] ?? 0) ?></div>
            <div class="inv-stat-lbl">Sin Stock</div>
        </div>
    </div>

    <div class="inv-stat-card">
        <div class="inv-stat-icon" style="background:rgba(155,89,182,0.15); color:#9b59b6;">
            <i data-lucide="dollar-sign"></i>
        </div>
        <div>
            <div class="inv-stat-num">$<?= number_format($resumen['valor_total'] ?? 0, 2) ?></div>
            <div class="inv-stat-lbl">Valor Inventario</div>
        </div>
    </div>
</div>

<!-- Listado de Productos Disponibles -->
<div class="inv-table-container animate-fade-in stagger">
    <h2><i data-lucide="list"></i> Consultar Productos</h2>
    
    <table class="inv-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock Actual</th>
                <th>Mínimo</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                        No hay productos registrados en el inventario.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($productos as $prod): 
                    $estado = '';
                    $badgeClass = '';
                    if ($prod['stock'] == 0) {
                        $estado = 'Agotado';
                        $badgeClass = 'badge-stock-out';
                    } elseif ($prod['stock'] <= $prod['stock_minimo']) {
                        $estado = 'Bajo Stock';
                        $badgeClass = 'badge-stock-low';
                    } else {
                        $estado = 'Óptimo';
                        $badgeClass = 'badge-stock-ok';
                    }
                ?>
                <tr>
                    <td>
                        <div class="prod-info">
                            <?php if (!empty($prod['imagen'])): ?>
                                <img src="<?= IMG_URL ?>/productos/<?= htmlspecialchars($prod['imagen']) ?>" alt="<?= htmlspecialchars($prod['nombre']) ?>" class="prod-img">
                            <?php else: ?>
                                <div class="prod-img" style="display:flex;align-items:center;justify-content:center;font-size:1.2rem;">📦</div>
                            <?php endif; ?>
                            <span style="font-weight: 600;"><?= htmlspecialchars($prod['nombre']) ?></span>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($prod['categoria_nombre'] ?? 'Sin categoría') ?></td>
                    <td>$<?= number_format($prod['precio'], 2) ?></td>
                    <td style="font-weight: bold; font-size: 1.1rem;"><?= $prod['stock'] ?></td>
                    <td style="color: var(--text-secondary);"><?= $prod['stock_minimo'] ?></td>
                    <td><span class="<?= $badgeClass ?>"><?= $estado ?></span></td>
                    <td>
                        <a href="<?= BASE_URL ?>/index.php?controller=productos&action=editar&id=<?= $prod['id'] ?>" class="btn btn-sm btn-secondary" style="padding: 6px 10px; font-size: 0.8rem;">
                            <i data-lucide="edit-2" style="width: 14px;"></i> Editar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal para ajustar inventario podría ir aquí, pero ahora la historia de usuario dice solo "Visualizar y consultar" -->
