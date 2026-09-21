<?php
/**
 * Modelo Producto — Acceso a datos de la tabla `productos`
 */

require_once __DIR__ . '/../config/database.php';

class Producto
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener productos con filtros, búsqueda y paginación
     */
    public function getAll(?int $categoriaId = null, ?string $busqueda = null, int $limit = 12, int $offset = 0, bool $soloActivos = true): array
    {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre, u.nombre AS vendedor_nombre
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN usuarios u ON p.vendedor_id = u.id
                WHERE 1=1";
        $params = [];

        if ($soloActivos) {
            $sql .= " AND p.activo = 1";
        }

        if ($categoriaId) {
            $sql .= " AND p.categoria_id = :categoria_id";
            $params[':categoria_id'] = $categoriaId;
        }

        if ($busqueda) {
            $sql .= " AND (p.nombre LIKE :busqueda OR p.descripcion LIKE :busqueda2)";
            $params[':busqueda']  = "%{$busqueda}%";
            $params[':busqueda2'] = "%{$busqueda}%";
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

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
     * Obtener productos activos con inventario disponible para la tienda.
     */
    public function getAvailable(int $limit = 8): array
    {
        $stmt = $this->db->prepare("SELECT p.*, c.nombre AS categoria_nombre
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.activo = 1 AND p.stock > 0
                ORDER BY p.created_at DESC, p.id DESC
                LIMIT :limit");
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Contar productos con filtros
     */
    public function count(?int $categoriaId = null, ?string $busqueda = null, bool $soloActivos = true): int
    {
        $sql = "SELECT COUNT(*) FROM productos WHERE 1=1";
        $params = [];

        if ($soloActivos) {
            $sql .= " AND activo = 1";
        }

        if ($categoriaId) {
            $sql .= " AND categoria_id = :categoria_id";
            $params[':categoria_id'] = $categoriaId;
        }

        if ($busqueda) {
            $sql .= " AND (nombre LIKE :busqueda OR descripcion LIKE :busqueda2)";
            $params[':busqueda']  = "%{$busqueda}%";
            $params[':busqueda2'] = "%{$busqueda}%";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Buscar producto por ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.nombre AS categoria_nombre, u.nombre AS vendedor_nombre
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN usuarios u ON p.vendedor_id = u.id
            WHERE p.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();
        return $product ?: null;
    }

    /**
     * Crear un nuevo producto
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO productos (nombre, descripcion, precio, stock, stock_minimo, imagen, categoria_id, vendedor_id)
            VALUES (:nombre, :descripcion, :precio, :stock, :stock_minimo, :imagen, :categoria_id, :vendedor_id)
        ");

        $stmt->execute([
            ':nombre'       => $data['nombre'],
            ':descripcion'  => $data['descripcion'] ?? null,
            ':precio'       => $data['precio'],
            ':stock'        => $data['stock'] ?? 0,
            ':stock_minimo' => $data['stock_minimo'] ?? 5,
            ':imagen'       => $data['imagen'] ?? null,
            ':categoria_id' => $data['categoria_id'] ?? null,
            ':vendedor_id'  => $data['vendedor_id'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar un producto existente
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        $allowedFields = ['nombre', 'descripcion', 'precio', 'stock', 'stock_minimo', 'imagen', 'categoria_id', 'vendedor_id', 'activo'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $sql = "UPDATE productos SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Eliminar producto (soft delete)
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE productos SET activo = 0 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Obtener productos por categoría
     */
    public function getByCategoria(int $categoriaId, int $limit = 12): array
    {
        return $this->getAll($categoriaId, null, $limit);
    }

    /**
     * Obtener productos por vendedor
     */
    public function getByVendedor(int $vendedorId, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.nombre AS categoria_nombre
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.vendedor_id = :vendedor_id
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':vendedor_id', $vendedorId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Actualizar stock de un producto
     */
    public function updateStock(int $id, int $cantidad): bool
    {
        $stmt = $this->db->prepare("UPDATE productos SET stock = stock + :cantidad WHERE id = :id");
        return $stmt->execute([':cantidad' => $cantidad, ':id' => $id]);
    }

    /**
     * Obtener productos con bajo stock
     */
    public function getLowStock(): array
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
     * Obtener productos más vendidos
     */
    public function getMasVendidos(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, c.nombre AS categoria_nombre, SUM(dp.cantidad) AS total_vendido
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            INNER JOIN detalle_pedidos dp ON p.id = dp.producto_id
            INNER JOIN pedidos ped ON dp.pedido_id = ped.id AND ped.estado != 'cancelado'
            WHERE p.activo = 1
            GROUP BY p.id
            ORDER BY total_vendido DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
