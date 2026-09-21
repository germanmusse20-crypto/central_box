<?php
/**
 * Vista — Lista de Productos y Ranking de Más Vendidos
 */

// Extraer nombres y cantidades para el gráfico
$chartLabels = [];
$chartData   = [];
foreach ($topProductos as $tp) {
    $chartLabels[] = $tp['nombre'];
    $chartData[]   = (int)$tp['total_vendido'];
}
?>

<style>
/* Encabezado */
.prod-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}
.prod-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}
.prod-subtitle {
    color: var(--text-secondary);
    font-size: 0.95rem;
}
.prod-highlight {
    color: #2ecc71;
}

/* Gráfico / Ranking (Historia de Usuario) */
.chart-container {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
}
.chart-container h2 {
    font-size: 1.25rem;
    margin-bottom: 20px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 12px;
}
.chart-wrapper {
    position: relative;
    height: 300px;
    width: 100%;
}

/* Filtros y Búsqueda */
.prod-filters {
    background: var(--bg-card);
    padding: 16px 20px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    margin-bottom: 20px;
    display: flex;
    gap: 16px;
    align-items: center;
}
.prod-filters input, .prod-filters select {
    padding: 10px 14px;
    border-radius: 6px;
    border: 1px solid var(--border-color);
    background: rgba(255,255,255,0.05);
    color: var(--text-primary);
    flex: 1;
}
.prod-filters button {
    padding: 10px 20px;
    border-radius: 6px;
    background: var(--primary-color);
    color: #000;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.prod-filters button:hover {
    background: #00b894;
}

/* Tabla de Productos */
.prod-table-container {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    overflow-x: auto;
}
.prod-table {
    width: 100%;
    border-collapse: collapse;
}
.prod-table th {
    background: rgba(255,255,255,0.03);
    padding: 12px 16px;
    text-align: left;
    font-size: 0.85rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    border-bottom: 1px solid var(--border-color);
}
.prod-table td {
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}
.prod-table tr:hover {
    background: rgba(255,255,255,0.02);
}
.img-thumb {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    object-fit: cover;
    background: #333;
}
.badge-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}
.badge-active { background: rgba(46, 204, 113, 0.15); color: #2ecc71; }
.badge-inactive { background: rgba(231, 76, 60, 0.15); color: #e74c3c; }

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

<div class="prod-header animate-fade-in">
    <div>
        <h1 class="prod-title">📦 <span class="prod-highlight">Productos</span></h1>
        <p class="prod-subtitle">Gestiona tu catálogo e identifica los productos más demandados.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?controller=productos&action=crear" class="btn btn-primary" style="background:#2ecc71; color:#000;">
        <i data-lucide="plus-circle"></i> Nuevo Producto
    </a>
</div>

<!-- =========================================================================
     HU: Análisis gráfico y ranking de productos más vendidos
     ========================================================================= -->
<div class="chart-container stagger animate-fade-in">
    <h2><i data-lucide="bar-chart-2"></i> Ranking de Productos Más Vendidos</h2>
    <p style="color:var(--text-secondary); margin-bottom: 20px; font-size: 0.9rem;">
        Análisis gráfico general para identificar la demanda (Top 5).
    </p>
    
    <div class="chart-wrapper">
        <canvas id="rankingChart"></canvas>
    </div>
</div>

<!-- =========================================================================
     LISTADO DE PRODUCTOS (Gestión General)
     ========================================================================= -->
<div class="prod-filters stagger animate-fade-in">
    <form method="GET" action="index.php" style="display:flex; gap:16px; width:100%; align-items:center;">
        <input type="hidden" name="controller" value="productos">
        <input type="hidden" name="action" value="lista">
        
        <input type="text" name="busqueda" placeholder="Buscar por nombre o descripción..." value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
        
        <select name="categoria">
            <option value="">Todas las categorías</option>
            <?php foreach($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= (($_GET['categoria']??0) == $cat['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit"><i data-lucide="search" style="width:16px;"></i> Buscar</button>
    </form>
</div>

<div class="prod-table-container stagger animate-fade-in">
    <table class="prod-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($productos)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:30px; color:var(--text-secondary);">
                        No se encontraron productos registrados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($productos as $p): ?>
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <?php if($p['imagen']): ?>
                                    <img src="<?= IMG_URL ?>/productos/<?= htmlspecialchars($p['imagen']) ?>" class="img-thumb" alt="<?= htmlspecialchars($p['nombre']) ?>">
                                <?php else: ?>
                                    <div class="img-thumb" style="display:flex;align-items:center;justify-content:center;font-size:1.5rem;">📦</div>
                                <?php endif; ?>
                                <div>
                                    <strong style="display:block;"><?= htmlspecialchars($p['nombre']) ?></strong>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($p['categoria_nombre'] ?? 'Sin categoría') ?></td>
                        <td style="font-weight:bold; color:#2ecc71;">$<?= number_format($p['precio'], 2) ?></td>
                        <td>
                            <?php if($p['stock'] <= $p['stock_minimo']): ?>
                                <span style="color:#e74c3c; font-weight:bold;"><?= $p['stock'] ?> (Bajo)</span>
                            <?php else: ?>
                                <span><?= $p['stock'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($p['activo']): ?>
                                <span class="badge-status badge-active">Activo</span>
                            <?php else: ?>
                                <span class="badge-status badge-inactive">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="<?= BASE_URL ?>/index.php?controller=productos&action=editar&id=<?= $p['id'] ?>" class="btn-sm btn-edit">
                                    <i data-lucide="edit" style="width:14px;"></i> Editar
                                </a>
                                <a href="<?= BASE_URL ?>/index.php?controller=productos&action=eliminar&id=<?= $p['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este producto?');">
                                    <i data-lucide="trash-2" style="width:14px;"></i> Borrar
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- =========================================================================
     SCRIPT PARA EL GRÁFICO (Chart.js)
     ========================================================================= -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('rankingChart').getContext('2d');
    
    // Gradiente moderno para las barras
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(46, 204, 113, 0.8)');
    gradient.addColorStop(1, 'rgba(39, 174, 96, 0.2)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                label: 'Cantidad Vendida (Ranking)',
                data: <?= json_encode($chartData) ?>,
                backgroundColor: gradient,
                borderColor: '#2ecc71',
                borderWidth: 1,
                borderRadius: 6,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: 'rgba(255, 255, 255, 0.6)', stepSize: 1 }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: 'rgba(255, 255, 255, 0.8)' }
                }
            },
            plugins: {
                legend: {
                    labels: { color: '#fff' }
                }
            }
        }
    });
});
</script>
