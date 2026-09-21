<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();

try {
    $db->exec('ALTER TABLE productos CHANGE id_producto id INT UNSIGNED AUTO_INCREMENT');
    echo 'Renamed id_producto to id in productos.';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
