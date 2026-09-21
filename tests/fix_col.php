<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();
try {
    $db->exec('ALTER TABLE usuarios CHANGE id_usuario id INT UNSIGNED AUTO_INCREMENT');
    echo 'Column renamed from id_usuario to id successfully.';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
