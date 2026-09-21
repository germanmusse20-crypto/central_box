<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query('DESCRIBE usuarios');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
