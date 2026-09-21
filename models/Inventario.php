<?php
/**
 * Modelo Inventario — Acceso a datos de `movimientos_inventario`
 */

require_once __DIR__ . '/../config/database.php';

class Inventario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Registrar un movimiento de inventario
     */
    public function registrarMovimiento(int $productoId, int $usuarioId, string $tipo, int $cantidad, string $motivo = ''): bool
    {
        $this->db->beginTransaction();

        try {
            // Obtener stock actual
            $stmt = $this->db->prepare("SELECT stock FROM productos WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $productoId]);
            $producto = $stmt->fetch();

            if (!$producto) {
                $this->db->rollBack();
                return false;
            }

            $stockAnterior = (int) $producto['stock'];

            // Calcular nuevo stock
            switch ($tipo) {
                case 'entrada':
                    $stockNuevo = $stockAnterior + $cantidad;
                    break;
                case 'salida':
                    $stockNuevo = $stockAnterior - $cantidad;
                    if ($stockNuevo < 0) $stockNuevo = 0;
                    break;
                case 'ajuste':
                    $stockNuevo = $cantidad; // En ajuste, la cantidad es el stock nuevo directo
                    break;
                default:
                    $this->db->rollBack();
                    return false;
            }

            // Registrar movimiento
            $stmt = $this->db->prepare("
                INSERT INTO movimientos_inventario (producto_id, usuario_id, tipo, cantidad, stock_anterior, stock_nuevo, motivo)
                VALUES (:producto_id, :usuario_id, :tipo, :cantidad, :stock_anterior, :stock_nuevo, :motivo)
            ");
            $stmt->execute([
                ':producto_id'    => $productoId,
                ':usuario_id'     => $usuarioId,
                ':tipo'           => $tipo,
                ':cantidad'       => $cantidad,
                ':stock_anterior' => $stockAnterior,
                ':stock_nuevo'    => $stockNuevo,
                ':motivo'         => $motivo,
            ]);

            // Actualizar stock del producto
            $stmt = $this->db->prepare("UPDATE productos SET stock = :stock WHERE id = :id");
            $stmt->execute([':stock' => $stockNuevo, ':id' => $productoId]);

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Obtener movimientos de inventario con filtros
     */
    public function getMovimientos(?int $productoId = null, ?string $tipo = null, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT mi.*, p.nombre AS producto_nombre, u.nombre AS usuario_nombre
                FROM movimientos_inventario mi
                INNER JOIN productos p ON mi.producto_id = p.id
                INNER JOIN usuarios u ON mi.usuario_id = u.id
                WHERE 1=1";
        $params = [];

        if ($productoId) {
            $sql .= " AND mi.producto_id = :producto_id";
            $params[':producto_id'] = $productoId;
        }

        if ($tipo) {
            $sql .= " AND mi.tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        $sql .= " ORDER BY mi.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtener productos con bajo stock (stock <= stock_minimo)
     */
    public function getProductosBajoStock(): array
    {
        $stmt = $this->db->query("
            SELECT p.*, c.nombre AS categoria_nombre
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.activo = 1 AND p.stock <= p.stock_minimo
            ORDER BY p.stock ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtener resumen del inventario
     */
    public function getResumen(): array
    {
        // Total de productos activos
        $stmt = $this->db->query("SELECT COUNT(*) FROM productos WHERE activo = 1");
        $totalProductos = (int) $stmt->fetchColumn();

        // Total de unidades en stock
        $stmt = $this->db->query("SELECT COALESCE(SUM(stock), 0) FROM productos WHERE activo = 1");
        $totalUnidades = (int) $stmt->fetchColumn();

        // Productos con bajo stock
        $stmt = $this->db->query("SELECT COUNT(*) FROM productos WHERE activo = 1 AND stock <= stock_minimo");
        $bajoStock = (int) $stmt->fetchColumn();

        // Productos sin stock
        $stmt = $this->db->query("SELECT COUNT(*) FROM productos WHERE activo = 1 AND stock = 0");
        $sinStock = (int) $stmt->fetchColumn();

        // Valor total del inventario
        $stmt = $this->db->query("SELECT COALESCE(SUM(precio * stock), 0) FROM productos WHERE activo = 1");
        $valorTotal = (float) $stmt->fetchColumn();

        return [
            'total_productos' => $totalProductos,
            'total_unidades'  => $totalUnidades,
            'bajo_stock'      => $bajoStock,
            'sin_stock'       => $sinStock,
            'valor_total'     => $valorTotal,
        ];
    }
}
