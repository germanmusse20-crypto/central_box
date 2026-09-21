<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();

try {
    $db->exec("
    CREATE TABLE IF NOT EXISTS promociones (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        producto_id INT UNSIGNED NULL,
        nombre VARCHAR(150) NOT NULL,
        descripcion TEXT NULL,
        descuento DECIMAL(5,2) NOT NULL,
        fecha_inicio DATE NOT NULL,
        fecha_fin DATE NOT NULL,
        estado ENUM('pendiente', 'activa', 'rechazada', 'eliminada') NOT NULL DEFAULT 'pendiente',
        solicitado_por INT UNSIGNED NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        
        CONSTRAINT fk_promo_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL,
        CONSTRAINT fk_promo_usuario FOREIGN KEY (solicitado_por) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Insert some mock data if empty
    $count = $db->query("SELECT COUNT(*) FROM promociones")->fetchColumn();
    if ($count == 0) {
        // Assume user 1 is admin, user 2 is vendedor (we will just put 1 for both if 2 doesn't exist)
        $db->exec("
            INSERT INTO promociones (producto_id, nombre, descripcion, descuento, fecha_inicio, fecha_fin, estado, solicitado_por)
            VALUES 
            (NULL, 'Black Friday', 'Descuento general 20%', 20.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'activa', 1),
            (NULL, 'Liquidación de verano', 'Hasta 30% en productos seleccionados', 30.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'pendiente', 1);
        ");
    }

    echo "Tabla promociones creada exitosamente.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
