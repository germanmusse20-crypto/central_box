<?php
/**
 * Modelo Pago — Acceso a datos de métodos de pago
 */
require_once __DIR__ . '/../config/database.php';

class Pago
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * HU1: Obtener todos los métodos de pago
     */
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM metodos_pago ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * HU2 y HU3: Actualizar el estado de un método de pago
     */
    public function updateEstado(int $id, string $estado): bool
    {
        $stmt = $this->db->prepare("UPDATE metodos_pago SET estado = :estado WHERE id = :id");
        return $stmt->execute([':estado' => $estado, ':id' => $id]);
    }
}
