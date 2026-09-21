<?php
require 'config/database.php';
$db = Database::getInstance()->getConnection();
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    echo "--- $table ---\n";
    $cols = $db->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        if ($col['Key'] == 'PRI') {
            echo "PK: " . $col['Field'] . "\n";
        }
    }
}
