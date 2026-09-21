<?php
/**
 * Modelo Usuario — Acceso a datos de la tabla `usuarios`
 */

require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los usuarios con filtros opcionales
     */
    public function getAll(?string $rol = null, ?string $busqueda = null, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT id, nombre, email, rol, telefono, direccion, avatar, activo, created_at, updated_at FROM usuarios WHERE 1=1";
        $params = [];

        if ($rol) {
            $sql .= " AND rol = :rol";
            $params[':rol'] = $rol;
        }

        if ($busqueda) {
            $sql .= " AND (nombre LIKE :busqueda OR email LIKE :busqueda2)";
            $params[':busqueda']  = "%{$busqueda}%";
            $params[':busqueda2'] = "%{$busqueda}%";
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

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
     * Buscar usuario por ID
     */
    public function findById(int $id): ?array
{
    $sql = "SELECT id, nombre, email, telefono, avatar, rol, activo
            FROM usuarios
            WHERE id = :id
            LIMIT 1";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
        ':id' => $id
    ]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    return $usuario ?: null;
}

public function findByEmail(string $email): ?array
{
    $stmt = $this->db->prepare(
        "SELECT * FROM usuarios WHERE email = :email LIMIT 1"
    );

    $stmt->execute([
        ':email' => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

    /**
     * Crear un nuevo usuario
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (nombre, email, password, rol, telefono, direccion, avatar)
            VALUES (:nombre, :email, :password, :rol, :telefono, :direccion, :avatar)
        ");

        $stmt->execute([
            ':nombre'    => $data['nombre'],
            ':email'     => $data['email'],
            ':password'  => password_hash($data['password'], PASSWORD_DEFAULT),
            ':rol'       => $data['rol'] ?? 'cliente',
            ':telefono'  => $data['telefono'] ?? null,
            ':direccion' => $data['direccion'] ?? null,
            ':avatar'    => $data['avatar'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar un usuario existente
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];

        $allowedFields = ['nombre', 'email', 'rol', 'telefono', 'direccion', 'avatar', 'activo'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }

        // Actualizar password solo si se proporciona
        if (!empty($data['password'])) {
            $fields[] = "password = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) return false;

        $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Eliminar un usuario (soft delete — desactivar)
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET activo = 0 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Eliminar permanentemente un usuario
     */
    public function hardDelete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Autenticar usuario por email y password
     */
    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if ($user && $user['activo'] && password_verify($password, $user['password'])) {
            return $user;
        }

        return null;
    }

    /**
     * Crear un token de recuperacion para una cuenta de cliente.
     */
    public function crearRecuperacion(int $usuarioId, int $minutos = 30): string
    {
        $token = bin2hex(random_bytes(32));
        $expiracion = (new DateTimeImmutable('now'))
            ->modify("+{$minutos} minutes")
            ->format('Y-m-d H:i:s');
        $stmt = $this->db->prepare(
            "INSERT INTO recuperacion_contrasena
                (id_usuario, token, fecha_expiracion)
             VALUES (:usuario, :token, :expiracion)"
        );
        $stmt->bindValue(':usuario', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->bindValue(':expiracion', $expiracion, PDO::PARAM_STR);
        $stmt->execute();
        return $token;
    }

    /**
     * Obtener un token vigente y no utilizado.
     */
    public function obtenerRecuperacion(string $token): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM recuperacion_contrasena
             WHERE token = :token AND utilizada = 0 AND fecha_expiracion >= NOW()
             LIMIT 1"
        );
        $stmt->execute([':token' => $token]);
        $recuperacion = $stmt->fetch(PDO::FETCH_ASSOC);
        return $recuperacion ?: null;
    }

    public function marcarRecuperacionUtilizada(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE recuperacion_contrasena SET utilizada = 1 WHERE id_recuperacion = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Contar usuarios por rol
     */
    public function countByRole(?string $rol = null): int
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE activo = 1";
        $params = [];

        if ($rol) {
            $sql .= " AND rol = :rol";
            $params[':rol'] = $rol;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Contar total de usuarios
     */
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM usuarios");
        return (int) $stmt->fetchColumn();
    }
}