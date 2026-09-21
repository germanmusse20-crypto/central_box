<?php
/**
 * Modelo Pedido — Acceso a datos de las tablas `pedidos` y `detalle_pedidos`
 */

require_once __DIR__ . '/../config/database.php';

class Pedido
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crear un pedido completo (cabecera + detalles) dentro de una transacción
     */
    public function crear(int $clienteId, array $items, array $datosExtra = []): int
    {
        $this->db->beginTransaction();

        try {
            $stmtProducto = $this->db->prepare("
                SELECT precio, stock, activo FROM productos WHERE id = :id FOR UPDATE
            ");
            $lineas = [];
            $subtotalGeneral = 0.0;
            $descuentoGeneral = 0.0;

            foreach ($items as $item) {
                $cantidad = (int) ($item['cantidad'] ?? 0);
                $productoId = (int) ($item['producto_id'] ?? 0);
                if ($productoId <= 0 || $cantidad <= 0) {
                    throw new \InvalidArgumentException('La venta contiene un producto o cantidad inválida.');
                }

                $stmtProducto->execute([':id' => $productoId]);
                $producto = $stmtProducto->fetch();
                if (!$producto || !(int) $producto['activo']) {
                    throw new \RuntimeException('El producto seleccionado no está disponible.');
                }

                if ((int) $producto['stock'] < $cantidad) {
                    throw new \RuntimeException('No hay stock suficiente para completar la venta.');
                }

                $precio = (int) ($datosExtra['producto_gratis_id'] ?? 0) === $productoId
                    ? 0.0
                    : (float) $producto['precio'];
                $subtotalBase = (float) $producto['precio'] * $cantidad;
                $descuentoPct = max(0, min(100, (float) ($datosExtra['descuento_pct'] ?? 0)));
                $precio *= 1 - ($descuentoPct / 100);
                $subtotal = $precio * $cantidad;
                $subtotalGeneral += $subtotal;
                $descuentoGeneral += max(0, $subtotalBase - $subtotal);
                $lineas[] = compact('productoId', 'cantidad', 'precio', 'subtotal');
            }

            if (!$lineas) {
                throw new \InvalidArgumentException('La venta debe contener al menos un producto.');
            }

            $total = round($subtotalGeneral, 2);

            $stmt = $this->db->prepare("
                INSERT INTO pedidos (cliente_id, total, estado, metodo_pago, notas)
                VALUES (:cliente_id, :total, 'confirmado', :metodo_pago, :notas)
            ");
            $stmt->execute([
                ':cliente_id'  => $clienteId,
                ':total'       => $total,
                ':metodo_pago' => $datosExtra['metodo_pago'] ?? 'efectivo',
                ':notas'       => $datosExtra['notas'] ?? null,
            ]);

            $pedidoId = (int) $this->db->lastInsertId();
            $stmtDetalle = $this->db->prepare("
                INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario, subtotal)
                VALUES (:pedido_id, :producto_id, :cantidad, :precio_unitario, :subtotal)
            ");
            $stmtStock = $this->db->prepare(
                'UPDATE productos SET stock = stock - :cantidad WHERE id = :id AND stock >= :cantidad2'
            );

            foreach ($lineas as $linea) {
                $stmtStock->execute([
                    ':cantidad' => $linea['cantidad'],
                    ':id' => $linea['productoId'],
                    ':cantidad2' => $linea['cantidad'],
                ]);
                if ($stmtStock->rowCount() !== 1) {
                    throw new \RuntimeException('No hay stock suficiente para completar la venta.');
                }

                $stmtDetalle->execute([
                    ':pedido_id'       => $pedidoId,
                    ':producto_id'     => $linea['productoId'],
                    ':cantidad'        => $linea['cantidad'],
                    ':precio_unitario' => $linea['precio'],
                    ':subtotal'        => $linea['subtotal'],
                ]);
            }

            $this->registrarVentaNueva($clienteId, $lineas, $subtotalGeneral, $descuentoGeneral, $datosExtra);

            $this->db->commit();
            return $pedidoId;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Mantener el modelo de ventas nuevo sincronizado con el flujo historico de pedidos.
     */
    private function registrarVentaNueva(
        int $clienteId,
        array $lineas,
        float $subtotal,
        float $descuento,
        array $datosExtra
    ): void {
        try {
            $metodo = (string)($datosExtra['metodo_pago'] ?? 'efectivo');
            $stmt = $this->db->prepare(
                "SELECT id FROM metodos_pago WHERE LOWER(nombre) LIKE LOWER(:metodo) LIMIT 1"
            );
            $stmt->execute([':metodo' => '%' . $metodo . '%']);
            $metodoId = (int)$stmt->fetchColumn();

            if (!$metodoId) {
                $stmt = $this->db->prepare(
                    "INSERT INTO metodos_pago (nombre, estado) VALUES (:nombre, 'activo')"
                );
                $stmt->execute([':nombre' => ucfirst($metodo)]);
                $metodoId = (int)$this->db->lastInsertId();
            }

            $stmt = $this->db->prepare(
                "INSERT INTO pago (id_metodo_pago, monto, estado, referencia)
                 VALUES (:metodo, :monto, 'confirmado', :referencia)"
            );
            $stmt->execute([
                ':metodo' => $metodoId,
                ':monto' => $subtotal,
                ':referencia' => $datosExtra['referencia_pago'] ?? null,
            ]);
            $pagoId = (int)$this->db->lastInsertId();
            $usuarioId = (int)($_SESSION['usuario_id'] ?? $clienteId);
            $tipoVenta = (($_SESSION['usuario_rol'] ?? 'cliente') === 'cliente') ? 'ONLINE' : 'PRESENCIAL';

            $stmt = $this->db->prepare(
                "INSERT INTO venta
                    (id_cliente, id_usuario, id_pago, subtotal, descuento, total, estado, tipo_venta)
                 VALUES (:cliente, :usuario, :pago, :subtotal, :descuento, :total, 'completada', :tipo)"
            );
            $stmt->execute([
                ':cliente' => $clienteId,
                ':usuario' => $usuarioId,
                ':pago' => $pagoId,
                ':subtotal' => $subtotal + $descuento,
                ':descuento' => $descuento,
                ':total' => $subtotal,
                ':tipo' => $tipoVenta,
            ]);
            $ventaId = (int)$this->db->lastInsertId();

            $stmt = $this->db->prepare(
                "INSERT INTO detalle_venta
                    (id_venta, id_producto, cantidad, precio_unitario, descuento, subtotal)
                 VALUES (:venta, :producto, :cantidad, :precio, :descuento, :subtotal)"
            );
            foreach ($lineas as $linea) {
                $stmt->execute([
                    ':venta' => $ventaId,
                    ':producto' => $linea['productoId'],
                    ':cantidad' => $linea['cantidad'],
                    ':precio' => $linea['cantidad'] > 0 ? $linea['subtotal'] / $linea['cantidad'] : 0,
                    ':descuento' => 0,
                    ':subtotal' => $linea['subtotal'],
                ]);
            }
        } catch (\PDOException $e) {
            // El pedido historico sigue funcionando si una BD antigua aun no migro estas tablas.
        }
    }

    /**
     * Obtener todos los pedidos con filtros
     */
    public function getAll(?string $estado = null, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT p.*,
                   (SELECT nombre FROM usuarios WHERE id = p.cliente_id LIMIT 1) AS cliente_nombre,
                   (SELECT email  FROM usuarios WHERE id = p.cliente_id LIMIT 1) AS cliente_email
                FROM pedidos p
                WHERE 1=1";
        $params = [];

        if ($estado) {
            $sql .= " AND p.estado = :estado";
            $params[':estado'] = $estado;
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
     * Obtener pedidos de un cliente específico
     */
    public function getByCliente(int $clienteId, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM pedidos
            WHERE cliente_id = :cliente_id
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener un pedido por ID con datos del cliente
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*,
                   (SELECT nombre   FROM usuarios WHERE id = p.cliente_id LIMIT 1) AS cliente_nombre,
                   (SELECT email    FROM usuarios WHERE id = p.cliente_id LIMIT 1) AS cliente_email,
                   (SELECT telefono FROM usuarios WHERE id = p.cliente_id LIMIT 1) AS cliente_telefono,
                   (SELECT direccion FROM usuarios WHERE id = p.cliente_id LIMIT 1) AS cliente_direccion
            FROM pedidos p
            WHERE p.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $pedido = $stmt->fetch();
        return $pedido ?: null;
    }

    /**
     * Obtener los detalles (productos) de un pedido
     */
    public function getDetalles(int $pedidoId): array
    {
        $stmt = $this->db->prepare("
            SELECT dp.*, p.nombre AS producto_nombre, p.imagen AS producto_imagen
            FROM detalle_pedidos dp
            INNER JOIN productos p ON dp.producto_id = p.id
            WHERE dp.pedido_id = :pedido_id
        ");
        $stmt->execute([':pedido_id' => $pedidoId]);
        return $stmt->fetchAll();
    }

    /**
     * Actualizar el estado de un pedido
     */
    public function updateEstado(int $id, string $estado): bool
    {
        $estadosValidos = ['confirmado', 'entregado', 'cancelado'];
        if (!in_array($estado, $estadosValidos)) return false;

        $actual = $this->getById($id);
        if (!$actual) return false;

        $transiciones = [
            'confirmado' => ['entregado', 'cancelado'],
            'entregado'  => [],
            'cancelado'  => [],
        ];
        if ($actual['estado'] !== $estado && !in_array($estado, $transiciones[$actual['estado']] ?? [], true)) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE pedidos SET estado = :estado WHERE id = :id");
        return $stmt->execute([':estado' => $estado, ':id' => $id]);
    }

    /**
     * Obtener estadísticas generales de pedidos
     */
    public function getEstadisticas(): array
    {
        try {
            $stmt = $this->db->query("SELECT COALESCE(SUM(total), 0) AS total_ventas FROM pedidos WHERE estado != 'cancelado'");
            $totalVentas = $stmt->fetch()['total_ventas'];
        } catch (\Exception $e) { $totalVentas = 0; }

        try {
            $stmt = $this->db->query("SELECT estado, COUNT(*) AS total FROM pedidos GROUP BY estado");
            $porEstado = [];
            while ($row = $stmt->fetch()) {
                $porEstado[$row['estado']] = (int) $row['total'];
            }
        } catch (\Exception $e) { $porEstado = []; }

        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM pedidos");
            $totalPedidos = (int) $stmt->fetchColumn();
        } catch (\Exception $e) { $totalPedidos = 0; }

        try {
            $stmt = $this->db->query("
                SELECT COALESCE(SUM(total), 0) AS ventas_mes
                FROM pedidos
                WHERE estado != 'cancelado'
                AND MONTH(created_at) = MONTH(CURRENT_DATE())
                AND YEAR(created_at) = YEAR(CURRENT_DATE())
            ");
            $ventasMes = $stmt->fetch()['ventas_mes'];
        } catch (\Exception $e) { $ventasMes = 0; }

        try {
            $stmt = $this->db->query("
                SELECT p.*,
                       (SELECT nombre FROM usuarios WHERE id = p.cliente_id LIMIT 1) AS cliente_nombre
                FROM pedidos p
                ORDER BY p.created_at DESC
                LIMIT 5
            ");
            $recientes = $stmt->fetchAll();
        } catch (\Exception $e) { $recientes = []; }

        return [
            'total_ventas'  => (float) $totalVentas,
            'total_pedidos' => $totalPedidos,
            'ventas_mes'    => (float) $ventasMes,
            'por_estado'    => $porEstado,
            'recientes'     => $recientes,
        ];
    }

    /**
     * Ventas del día actual
     */
    public function getVentasDia(): float
    {
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(total), 0) AS ventas_dia
            FROM pedidos
            WHERE estado != 'cancelado'
            AND DATE(created_at) = CURDATE()
        ");
        return (float) $stmt->fetch()['ventas_dia'];
    }

    /**
     * Productos vendidos hoy (suma de cantidades en detalle_pedidos)
     */
    public function getProductosVendidosHoy(): int
    {
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(dp.cantidad), 0) AS total
            FROM detalle_pedidos dp
            INNER JOIN pedidos p ON dp.pedido_id = p.id
            WHERE p.estado != 'cancelado'
            AND DATE(p.created_at) = CURDATE()
        ");
        return (int) $stmt->fetch()['total'];
    }

    /**
     * Última venta registrada
     */
    public function getUltimaVenta(): ?array
    {
        $stmt = $this->db->query("
            SELECT p.*, u.nombre AS cliente_nombre
            FROM pedidos p
            LEFT JOIN usuarios u ON p.cliente_id = u.id
            ORDER BY p.created_at DESC
            LIMIT 1
        ");
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Contar pedidos de un cliente específico
     */
    public function contarPorCliente(int $clienteId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM pedidos WHERE cliente_id = :cid");
        $stmt->execute([':cid' => $clienteId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Contar pedidos
     */
    public function count(?string $estado = null): int
    {
        $sql = "SELECT COUNT(*) FROM pedidos";
        $params = [];

        if ($estado) {
            $sql .= " WHERE estado = :estado";
            $params[':estado'] = $estado;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
