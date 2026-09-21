<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();

try {
    // Delete PayPal and Criptomonedas
    $stmtDelete = $db->prepare("DELETE FROM metodos_pago WHERE nombre IN ('PayPal', 'Criptomonedas')");
    $stmtDelete->execute();
    
    // Add Tarjeta de Débito
    // First check if it exists so we don't insert duplicates if run twice
    $stmtCheck = $db->prepare("SELECT COUNT(*) FROM metodos_pago WHERE nombre = 'Tarjeta de Débito'");
    $stmtCheck->execute();
    if ($stmtCheck->fetchColumn() == 0) {
        $stmtInsert = $db->prepare("INSERT INTO metodos_pago (nombre, descripcion, estado) VALUES ('Tarjeta de Débito', 'Pagos con tarjetas de débito Visa, Mastercard o Maestro.', 'activo')");
        $stmtInsert->execute();
        echo "Tarjeta de Débito agregada.\n";
    }
    
    echo "Métodos de pago actualizados correctamente (PayPal y Criptomonedas eliminados).";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
