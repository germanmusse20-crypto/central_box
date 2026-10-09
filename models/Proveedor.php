<?php
/**
 * Modelo Proveedor — Acceso a datos de la tabla `proveedores`
 */

require_once __DIR__ . '/../config/database.php';

class Proveedor
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    // ① getAll() — lista todos los proveedores con búsqueda opcional
    public function getAll(?string $busqueda = null, int $limit = 100, int $offset = 0): array
{
    $sql = "SELECT * FROM proveedores WHERE 1=1";
    $params = [];
    if ($busqueda) {
        $sql .= " AND (nombre LIKE :busqueda OR email LIKE :busqueda2 OR contacto LIKE :busqueda3)";
        $params[':busqueda']  = "%{$busqueda}%";
        $params[':busqueda2'] = "%{$busqueda}%";
        $params[':busqueda3'] = "%{$busqueda}%";
    }
    $sql .= " ORDER BY nombre ASC LIMIT :limit OFFSET :offset";
    $stmt = $this->db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
    // ② findById(int $id) — retorna un proveedor o null
public function findById(int $id): ?array
{
    $stmt = $this->db->prepare("SELECT * FROM proveedores WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}
    // ③ create(array $data) — inserta y retorna el id nuevo
public function create(array $data): int
{
    $stmt = $this->db->prepare("
        INSERT INTO proveedores (nombre, contacto, email, telefono, direccion)
        VALUES (:nombre, :contacto, :email, :telefono, :direccion)
    ");
    $stmt->execute([
        ':nombre'    => $data['nombre'],
        ':contacto'  => $data['contacto']  ?? null,
        ':email'     => $data['email']     ?? null,
        ':telefono'  => $data['telefono']  ?? null,
        ':direccion' => $data['direccion'] ?? null,
    ]);
    return (int) $this->db->lastInsertId();
}
    // ④ update(int $id, array $data) — actualiza campos permitidos
public function update(int $id, array $data): bool
{
    $campos = [];
    $params = [':id' => $id];
    $permitidos = ['nombre', 'contacto', 'email', 'telefono', 'direccion'];
    foreach ($permitidos as $campo) {
        if (array_key_exists($campo, $data)) {
            $campos[] = "{$campo} = :{$campo}";
            $params[":{$campo}"] = $data[$campo];
        }
    }
    if (empty($campos)) return false;
    $sql = "UPDATE proveedores SET " . implode(', ', $campos) . " WHERE id = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute($params);
}
    // ⑤ cambiarEstado(int $id, int $activo) — activa/desactiva
public function cambiarEstado(int $id, int $activo): bool
{
    $stmt = $this->db->prepare("UPDATE proveedores SET activo = :activo WHERE id = :id");
    return $stmt->execute([':activo' => $activo, ':id' => $id]);
}
    // ⑥ count() — total de proveedores activos (para el dashboard)
public function count(): int
{
    $stmt = $this->db->query("SELECT COUNT(*) FROM proveedores WHERE activo = 1");
    return (int) $stmt->fetchColumn();
}

}
