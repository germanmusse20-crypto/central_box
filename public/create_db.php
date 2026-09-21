<?php
try {
    $conn = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec('CREATE DATABASE IF NOT EXISTS central_box');
    echo 'OK: Database created';
} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
