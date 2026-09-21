<?php
/**
 * Modelo Promocion — Acceso a datos de promociones
 */
require_once __DIR__ . '/../config/database.php';

class Promocion
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener promociones (puede filtrar por estado)
     */
    public function getPromociones(?string $estado = null): array
    {
        $sql = "
            SELECT p.*, u.nombre as solicitante, prod.nombre as producto_nombre
            FROM promociones p
            LEFT JOIN usuarios u ON p.solicitado_por = u.id
            LEFT JOIN productos prod ON p.producto_id = prod.id
            WHERE p.estado != 'eliminada'
        ";
        
        $params = [];
        if ($estado) {
            $sql .= " AND p.estado = :estado";
            $params[':estado'] = $estado;
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener promociones solicitadas por un usuario específico
     */
    public function getPromocionesByUsuario(int $usuarioId): array
    {
        $sql = "
            SELECT p.*, prod.nombre as producto_nombre
            FROM promociones p
            LEFT JOIN productos prod ON p.producto_id = prod.id
            WHERE p.solicitado_por = :usuario_id AND p.estado != 'eliminada'
            ORDER BY p.created_at DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar estado de una promoción
     */
    public function updateEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->prepare("UPDATE promociones SET estado = :estado WHERE id = :id");
        return $stmt->execute([':estado' => $estado, ':id' => $id]);
    }

    public function crearSolicitud(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO promociones
            (producto_id, nombre, descripcion, descuento, fecha_inicio, fecha_fin, estado, solicitado_por)
            VALUES (:producto, :nombre, :descripcion, :descuento, :inicio, :fin, 'pendiente', :usuario)");
        $stmt->execute([
            ':producto' => $data['producto_id'] ?: null,
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':descuento' => $data['descuento'],
            ':inicio' => $data['fecha_inicio'],
            ':fin' => $data['fecha_fin'],
            ':usuario' => $data['solicitado_por'],
        ]);
        $promocionId = (int)$this->db->lastInsertId();

        try {
            $solicitud = $this->db->prepare(
                "INSERT INTO solicitud_promocion
                    (id_empleado, id_promocion, nombre_propuesta, descripcion, descuento_propuesto, estado)
                 VALUES (:empleado, :promocion, :nombre, :descripcion, :descuento, 'pendiente')"
            );
            $solicitud->execute([
                ':empleado' => $data['solicitado_por'],
                ':promocion' => $promocionId,
                ':nombre' => $data['nombre'],
                ':descripcion' => $data['descripcion'] ?? null,
                ':descuento' => $data['descuento'],
            ]);
        } catch (\PDOException $e) {
            // Compatibilidad con bases anteriores a la migracion operativa.
        }

        return $promocionId;
    }
}
