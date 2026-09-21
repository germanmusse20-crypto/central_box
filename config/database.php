<?php
/**
 * central_box — Conexión a Base de Datos
 * Patrón Singleton con PDO para MySQL
 */

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    // Configuración de conexión (Laragon defaults)
    private static string $host = '127.0.0.1';
    private static string $port = '3306';
    private static string $dbname = 'central_box';
    private static string $username = 'root';
    private static string $password = '';
    private static string $charset = 'utf8mb4';

    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$dbname . ";charset=" . self::$charset;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . self::$charset,
            ];

            $this->connection = new PDO($dsn, self::$username, self::$password, $options);
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    // Prevenir clonación
    private function __clone() {}

    // Prevenir deserialización
    public function __wakeup()
    {
        throw new \Exception("No se puede deserializar un Singleton.");
    }

    /**
     * Obtener instancia única de la base de datos
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener la conexión PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
