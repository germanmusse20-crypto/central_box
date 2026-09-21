<?php
/**
 * ReportesController — Gestión de reportes de ventas
 */
require_once MODELS_PATH . '/Reporte.php';

class ReportesController
{
    private Reporte $reporteModel;

    public function __construct()
    {
        $this->reporteModel = new Reporte();
    }

    /**
     * HU1: Visualizar reporte general de ventas
     */
    public function index(): void
    {
        requireLogin();
        requireRole('admin');

        $pageTitle = 'Reportes de Ventas';
        $extraCss  = ['reportes.css']; // Opcional si se requiere CSS externo

        // HU2: Total de ventas
        $totalVentas = $this->reporteModel->getTotalVentas();

        // HU3: Ventas diarias
        $ventasDiarias = $this->reporteModel->getVentasDiarias(15); // Últimos 15 días para la gráfica/tabla

        // HU4: Ventas mensuales
        $ventasMensuales = $this->reporteModel->getVentasMensuales();

        // HU5: Ventas anuales
        $ventasAnuales = $this->reporteModel->getVentasAnuales();

        // HU6: Productos más vendidos
        $periodo = $_GET['periodo'] ?? 'mensual';
        if (!in_array($periodo, ['diario', 'semanal', 'mensual'], true)) {
            $periodo = 'mensual';
        }
        $productosMasVendidos = $this->reporteModel->getProductosMasVendidosPorPeriodo($periodo, 10);

        // KPIs generales para enriquecer el dashboard
        $kpis = $this->reporteModel->getKPIs();

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/admin/reportes/index.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }
}
