<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();

try {
    $db->exec("
    CREATE TABLE IF NOT EXISTS metodos_pago (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        descripcion TEXT NULL,
        estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Insert some mock data if empty
    $count = $db->query("SELECT COUNT(*) FROM metodos_pago")->fetchColumn();
    if ($count == 0) {
        $db->exec("
            INSERT INTO metodos_pago (nombre, descripcion, estado)
            VALUES 
            ('Tarjeta de Crédito', 'Pagos con Visa, Mastercard y American Express.', 'activo'),
            ('PayPal', 'Plataforma de pagos seguros por internet.', 'activo'),
            ('Transferencia Bancaria', 'Depósito o transferencia directa a la cuenta de la empresa.', 'activo'),
            ('Criptomonedas', 'Pagos a través de Bitcoin y Ethereum.', 'inactivo');
        ");
    }

    echo "Tabla metodos_pago creada exitosamente.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
