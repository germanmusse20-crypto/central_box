<?php
/**
 * Modelo Categoria — Acceso a datos de la tabla `categorias`
 */

require_once __DIR__ . '/../config/database.php';

class Categoria
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todas las categorías
     */
    public function getAll(bool $soloActivas = false): array
    {
        $sql = "SELECT c.*, (SELECT COUNT(*) FROM productos p WHERE p.categoria_id = c.id AND p.activo = 1) AS total_productos FROM categorias c";
        if ($soloActivas) {
            $sql .= " WHERE c.activa = 1";
        }
        $sql .= " ORDER BY c.nombre ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Buscar categoría por ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM categorias WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $cat = $stmt->fetch();
        return $cat ?: null;
    }

    /**
     * Crear una nueva categoría
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO categorias (nombre, descripcion, imagen) VALUES (:nombre, :descripcion, :imagen)
        ");
        $stmt->execute([
            ':nombre'      => $data['nombre'],
            ':descripcion' => $data['descripcion'] ?? null,
            ':imagen'      => $data['imagen'] ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar categoría
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        foreach (['nombre', 'descripcion', 'imagen', 'activa'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $sql = "UPDATE categorias SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Eliminar categoría (soft delete)
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE categorias SET activa = 0 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Contar categorías activas
     */
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM categorias WHERE activa = 1");
        return (int) $stmt->fetchColumn();
    }
}
