<?php
/**
 * Vista — Reportes de Ventas
 */
?>

<style>
/* Encabezado */
.rep-header {
    margin-bottom: 30px;
}
.rep-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 5px;
}
.rep-subtitle {
    color: var(--text-secondary);
}
.rep-highlight {
    color: #9b59b6;
}

/* KPIs (Total Ventas) */
.rep-kpis {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.kpi-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}
.kpi-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
}
.kpi-val {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--text-primary);
}
.kpi-lbl {
    font-size: 0.85rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Contenedores de tablas */
.rep-section {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
}
.rep-section h2 {
    font-size: 1.25rem;
    margin-bottom: 20px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 12px;
}
.rep-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}
@media (max-width: 992px) {
    .rep-grid-2 {
        grid-template-columns: 1fr;
    }
}

/* Tablas */
.rep-table {
    width: 100%;
    border-collapse: collapse;
}
.rep-table th {
    background: rgba(255,255,255,0.03);
    padding: 12px 16px;
    text-align: left;
    font-size: 0.85rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    border-bottom: 1px solid var(--border-color);
}
.rep-table td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
}
.rep-table tr:hover {
    background: rgba(255,255,255,0.02);
}
.prod-img {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    object-fit: cover;
    background: #333;
}
</style>

<!-- =========================================================================
     HU1: Visualizar reporte general de ventas
     ========================================================================= -->
<div class="rep-header animate-fade-in">
    <h1 class="rep-title">📊 <span class="rep-highlight">Reportes de Ventas</span></h1>
    <p class="rep-subtitle">Analiza el rendimiento general y la evolución de las ventas.</p>
</div>

<div class="rep-section animate-fade-in" style="margin-bottom:30px;">
    <form method="GET" action="<?= BASE_URL ?>/index.php" style="display:flex;align-items:end;gap:12px;flex-wrap:wrap;">
        <input type="hidden" name="controller" value="reportes">
        <input type="hidden" name="action" value="index">
        <div><label for="periodoReporte" style="display:block;margin-bottom:6px;color:var(--text-secondary);">Periodo de productos más vendidos</label>
            <select id="periodoReporte" name="periodo" class="form-control">
                <?php foreach (['diario' => 'Diario', 'semanal' => 'Semanal', 'mensual' => 'Mensual'] as $valor => $etiqueta): ?>
                    <option value="<?= $valor ?>" <?= ($periodo ?? 'mensual') === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary" type="submit"><i data-lucide="bar-chart-3"></i> Analizar</button>
    </form>
</div>

<!-- =========================================================================
     HU2: Mostrar total de ventas (Acumulado)
     ========================================================================= -->
<div class="rep-kpis stagger animate-fade-in">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:rgba(46,204,113,0.15); color:#2ecc71;">
            <i data-lucide="dollar-sign"></i>
        </div>
        <div>
            <div class="kpi-val">$<?= number_format($totalVentas, 2) ?></div>
            <div class="kpi-lbl">Total Acumulado</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:rgba(52,152,219,0.15); color:#3498db;">
            <i data-lucide="shopping-bag"></i>
        </div>
        <div>
            <div class="kpi-val"><?= number_format($kpis['pedidos_completados']) ?></div>
            <div class="kpi-lbl">Pedidos Completados</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:rgba(243,156,18,0.15); color:#f39c12;">
            <i data-lucide="clock"></i>
        </div>
        <div>
            <div class="kpi-val"><?= number_format($kpis['pedidos_pendientes']) ?></div>
            <div class="kpi-lbl">Pedidos Pendientes</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:rgba(155,89,182,0.15); color:#9b59b6;">
            <i data-lucide="trending-up"></i>
        </div>
        <div>
            <div class="kpi-val">$<?= number_format($kpis['ticket_promedio'], 2) ?></div>
            <div class="kpi-lbl">Ticket Promedio</div>
        </div>
    </div>
</div>

<div class="rep-grid-2 stagger animate-fade-in">
    <!-- =========================================================================
         HU3: Visualizar ventas diarias
         ========================================================================= -->
    <div class="rep-section">
        <h2><i data-lucide="calendar"></i> Ventas Diarias (Últimos días)</h2>
        <table class="rep-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Pedidos</th>
                    <th style="text-align:right">Total Ingresos</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($ventasDiarias)): ?>
                    <tr><td colspan="3" style="text-align:center;color:var(--text-secondary)">No hay datos registrados.</td></tr>
                <?php else: ?>
                    <?php foreach($ventasDiarias as $row): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($row['fecha'])) ?></td>
                            <td><?= $row['cantidad_pedidos'] ?></td>
                            <td style="text-align:right; font-weight:bold; color:#2ecc71;">$<?= number_format($row['total_ventas'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- =========================================================================
         HU4: Visualizar ventas mensuales
         ========================================================================= -->
    <div class="rep-section">
        <h2><i data-lucide="calendar-days"></i> Ventas Mensuales</h2>
        <table class="rep-table">
            <thead>
                <tr>
                    <th>Mes</th>
                    <th>Pedidos</th>
                    <th style="text-align:right">Total Ingresos</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($ventasMensuales)): ?>
                    <tr><td colspan="3" style="text-align:center;color:var(--text-secondary)">No hay datos registrados.</td></tr>
                <?php else: ?>
                    <?php foreach($ventasMensuales as $row): ?>
                        <tr>
                            <td><?= date('m/Y', strtotime($row['mes'].'-01')) ?></td>
                            <td><?= $row['cantidad_pedidos'] ?></td>
                            <td style="text-align:right; font-weight:bold; color:#3498db;">$<?= number_format($row['total_ventas'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="rep-grid-2 stagger animate-fade-in">
    <!-- =========================================================================
         HU5: Visualizar ventas anuales
         ========================================================================= -->
    <div class="rep-section">
        <h2><i data-lucide="bar-chart"></i> Ventas Anuales</h2>
        <table class="rep-table">
            <thead>
                <tr>
                    <th>Año</th>
                    <th>Pedidos</th>
                    <th style="text-align:right">Total Ingresos</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($ventasAnuales)): ?>
                    <tr><td colspan="3" style="text-align:center;color:var(--text-secondary)">No hay datos registrados.</td></tr>
                <?php else: ?>
                    <?php foreach($ventasAnuales as $row): ?>
                        <tr>
                            <td style="font-weight:bold"><?= $row['anio'] ?></td>
                            <td><?= $row['cantidad_pedidos'] ?></td>
                            <td style="text-align:right; font-weight:bold; color:#9b59b6;">$<?= number_format($row['total_ventas'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- =========================================================================
         HU6: Mostrar productos más vendidos
         ========================================================================= -->
    <div class="rep-section">
        <h2><i data-lucide="award"></i> Top 10 Productos Más Vendidos</h2>
        <div style="height:280px;margin-bottom:20px;"><canvas id="productosPeriodoChart"></canvas></div>
        <table class="rep-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cant. Vendida</th>
                    <th style="text-align:right">Ingresos</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($productosMasVendidos)): ?>
                    <tr><td colspan="3" style="text-align:center;color:var(--text-secondary)">No hay ventas registradas.</td></tr>
                <?php else: ?>
                    <?php foreach($productosMasVendidos as $prod): ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <?php if(!empty($prod['imagen'])): ?>
                                        <img src="<?= IMG_URL ?>/productos/<?= htmlspecialchars($prod['imagen']) ?>" class="prod-img" alt="<?= htmlspecialchars($prod['nombre']) ?>">
                                    <?php else: ?>
                                        <div class="prod-img" style="display:flex;align-items:center;justify-content:center;">📦</div>
                                    <?php endif; ?>
                                    <div>
                                        <div style="font-weight:600"><?= htmlspecialchars($prod['nombre']) ?></div>
                                        <div style="font-size:0.8rem; color:var(--text-secondary)"><?= htmlspecialchars($prod['categoria']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-weight:bold"><?= $prod['total_vendido'] ?> uds</td>
                            <td style="text-align:right; font-weight:bold; color:#f39c12;">$<?= number_format($prod['ingresos'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('productosPeriodoChart');
    if (!canvas) return;
    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($productosMasVendidos, 'nombre'), JSON_UNESCAPED_UNICODE) ?>,
            datasets: [{ label: 'Unidades vendidas', data: <?= json_encode(array_map('intval', array_column($productosMasVendidos, 'total_vendido'))) ?>, backgroundColor: '#2ecc71', borderRadius: 6 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
});
</script>
