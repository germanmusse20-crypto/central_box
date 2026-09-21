<?php
/**
 * Modelo Reporte — Acceso a datos estadísticos de ventas
 */
require_once __DIR__ . '/../config/database.php';

class Reporte
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * HU2: Mostrar total de ventas (acumulado general)
     */
    public function getTotalVentas(): float
    {
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(total), 0) 
            FROM pedidos 
            WHERE estado != 'cancelado'
        ");
        return (float) $stmt->fetchColumn();
    }

    /**
     * HU3: Visualizar ventas diarias (últimos 30 días)
     */
    public function getVentasDiarias(int $dias = 30): array
    {
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) as fecha, COALESCE(SUM(total), 0) as total_ventas, COUNT(id) as cantidad_pedidos
            FROM pedidos
            WHERE estado != 'cancelado' 
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
            GROUP BY DATE(created_at)
            ORDER BY fecha ASC
        ");
        $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HU4: Visualizar ventas mensuales (últimos 12 meses)
     */
    public function getVentasMensuales(): array
    {
        $stmt = $this->db->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as mes, COALESCE(SUM(total), 0) as total_ventas, COUNT(id) as cantidad_pedidos
            FROM pedidos
            WHERE estado != 'cancelado'
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY mes ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HU5: Visualizar ventas anuales
     */
    public function getVentasAnuales(): array
    {
        $stmt = $this->db->query("
            SELECT YEAR(created_at) as anio, COALESCE(SUM(total), 0) as total_ventas, COUNT(id) as cantidad_pedidos
            FROM pedidos
            WHERE estado != 'cancelado'
            GROUP BY YEAR(created_at)
            ORDER BY anio DESC
            LIMIT 5
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HU6: Mostrar productos más vendidos
     */
    public function getProductosMasVendidos(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT p.id, p.nombre, p.imagen, c.nombre AS categoria, 
                   COALESCE(SUM(dp.cantidad), 0) AS total_vendido, 
                   COALESCE(SUM(dp.subtotal), 0) AS ingresos
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            INNER JOIN detalle_pedidos dp ON p.id = dp.producto_id
            INNER JOIN pedidos ped ON dp.pedido_id = ped.id
            WHERE ped.estado != 'cancelado'
            GROUP BY p.id
            ORDER BY total_vendido DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductosMasVendidosPorPeriodo(string $periodo, int $limit = 10): array
    {
        $condicion = match ($periodo) {
            'diario' => 'DATE(ped.created_at) = CURDATE()',
            'semanal' => 'ped.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)',
            'mensual' => 'ped.created_at >= DATE_FORMAT(CURDATE(), \'%Y-%m-01\')',
            default => '1=1',
        };
        $stmt = $this->db->prepare("SELECT p.id, p.nombre, p.imagen, c.nombre AS categoria,
                   SUM(dp.cantidad) AS total_vendido, SUM(dp.subtotal) AS ingresos
            FROM productos p
            INNER JOIN detalle_pedidos dp ON p.id = dp.producto_id
            INNER JOIN pedidos ped ON dp.pedido_id = ped.id
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE ped.estado != 'cancelado' AND $condicion
            GROUP BY p.id
            ORDER BY total_vendido DESC
            LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * KPIs generales para el dashboard de reportes
     */
    public function getKPIs(): array
    {
        return [
            'total_ventas'       => $this->getTotalVentas(),
            'pedidos_completados'=> (int) $this->db->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'entregado'")->fetchColumn(),
            'pedidos_pendientes' => (int) $this->db->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'confirmado'")->fetchColumn(),
            'ticket_promedio'    => (float) $this->db->query("SELECT COALESCE(AVG(total), 0) FROM pedidos WHERE estado != 'cancelado'")->fetchColumn(),
        ];
    }
}
