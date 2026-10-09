<?php
/**
 * Modelo Carrito — Manejo de carrito persistente con respaldo en sesión
 */

require_once __DIR__ . '/../config/database.php';

class Carrito
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Inicializar el carrito en la sesión si no existe
     */
    private function init(): void
    {
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        if ($usuarioId <= 0 || (int)($_SESSION['carrito_usuario_id'] ?? 0) === $usuarioId) {
            return;
        }

        // Fusionar carrito temporal (visitante) con el carrito guardado en BD
        $temporal = $_SESSION['carrito'];
        $desdeBD  = $this->loadFromDatabase($usuarioId);

        $fusionado = $desdeBD;
        foreach ($temporal as $id => $item) {
            if (isset($fusionado[$id])) {
                $fusionado[$id]['cantidad'] += $item['cantidad'];
            } else {
                $fusionado[$id] = $item;
            }
        }

        $_SESSION['carrito'] = $fusionado;
        $_SESSION['carrito_usuario_id'] = $usuarioId;
    }

    /**
     * Agregar un producto al carrito
     */
    public function add(int $productoId, int $cantidad = 1, array $productoInfo = []): void
    {
        $this->init();

        if (isset($_SESSION['carrito'][$productoId])) {
            $_SESSION['carrito'][$productoId]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$productoId] = [
                'producto_id' => $productoId,
                'nombre'      => $productoInfo['nombre'] ?? '',
                'precio'      => $productoInfo['precio'] ?? 0,
                'imagen'      => $productoInfo['imagen'] ?? null,
                'cantidad'    => $cantidad,
                'stock'       => $productoInfo['stock'] ?? 0,
            ];
        }

        $this->persist();
    }

    /**
     * Eliminar un producto del carrito
     */
    public function remove(int $productoId): void
    {
        $this->init();
        unset($_SESSION['carrito'][$productoId]);
        $this->persist();
    }

    /**
     * Actualizar cantidad de un producto
     */
    public function updateQuantity(int $productoId, int $cantidad): void
    {
        $this->init();

        if ($cantidad <= 0) {
            $this->remove($productoId);
            return;
        }

        if (isset($_SESSION['carrito'][$productoId])) {
            $_SESSION['carrito'][$productoId]['cantidad'] = $cantidad;
        }

        $this->persist();
    }

    /**
     * Obtener todos los items del carrito
     */
    public function getItems(): array
    {
        $this->init();
        return $_SESSION['carrito'];
    }

    /**
     * Obtener el total del carrito
     */
    public function getTotal(): float
    {
        $this->init();
        $total = 0;

        foreach ($_SESSION['carrito'] as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        return $total;
    }

    /**
     * Obtener número total de items en el carrito
     */
    public function getCount(): int
    {
        $this->init();
        $count = 0;

        foreach ($_SESSION['carrito'] as $item) {
            $count += $item['cantidad'];
        }

        return $count;
    }

    /**
     * Vaciar todo el carrito
     */
    public function clear(): void
    {
        $this->init();
        $_SESSION['carrito'] = [];
        unset($_SESSION['carrito_usuario_id']);
        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        if ($usuarioId > 0) {
            try {
                $stmt = $this->db->prepare(
                    "UPDATE carrito SET estado = 'vacio' WHERE id_cliente = :cliente AND estado = 'activo'"
                );
                $stmt->execute([':cliente' => $usuarioId]);
            } catch (\PDOException $e) {
                // La sesion sigue siendo un respaldo si la migracion aun no se ejecuto.
            }
        }
    }

    /**
     * Verificar si el carrito está vacío
     */
    public function isEmpty(): bool
    {
        $this->init();
        return empty($_SESSION['carrito']);
    }

    private function loadFromDatabase(int $usuarioId): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT dc.id_producto AS producto_id, dc.cantidad, dc.precio_unitario AS precio,
                        p.nombre, p.imagen, p.stock
                 FROM carrito c
                 INNER JOIN detalle_carrito dc ON dc.id_carrito = c.id_carrito
                 INNER JOIN productos p ON p.id = dc.id_producto
                 WHERE c.id_cliente = :cliente AND c.estado = 'activo'"
            );
            $stmt->execute([':cliente' => $usuarioId]);
            $items = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
                $items[(int)$item['producto_id']] = $item;
            }
            return $items;
        } catch (\PDOException $e) {
            return $_SESSION['carrito'] ?? [];
        }
    }

    private function persist(): void
    {
        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        if ($usuarioId <= 0) {
            return;
        }

        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare(
                "SELECT id_carrito FROM carrito WHERE id_cliente = :cliente AND estado = 'activo' LIMIT 1"
            );
            $stmt->execute([':cliente' => $usuarioId]);
            $cartId = (int)$stmt->fetchColumn();

            if (!$cartId) {
                $stmt = $this->db->prepare(
                    "INSERT INTO carrito (id_cliente, estado) VALUES (:cliente, 'activo')"
                );
                $stmt->execute([':cliente' => $usuarioId]);
                $cartId = (int)$this->db->lastInsertId();
            }

            $this->db->prepare("DELETE FROM detalle_carrito WHERE id_carrito = :carrito")
                ->execute([':carrito' => $cartId]);
            $stmt = $this->db->prepare(
                "INSERT INTO detalle_carrito (id_carrito, id_producto, cantidad, precio_unitario)
                 VALUES (:carrito, :producto, :cantidad, :precio)"
            );
            foreach ($_SESSION['carrito'] as $item) {
                $stmt->execute([
                    ':carrito' => $cartId,
                    ':producto' => (int)$item['producto_id'],
                    ':cantidad' => (int)$item['cantidad'],
                    ':precio' => (float)$item['precio'],
                ]);
            }
            $this->db->commit();
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
        }
    }
}
