<?php
session_start();

// Simular login
require_once __DIR__ . '/config/database.php';

$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nombre'] = 'Administrador';
$_SESSION['usuario_email'] = 'admin@central-box.com';
$_SESSION['usuario_rol'] = 'admin';
$_SESSION['usuario_avatar'] = null;

// Ahora acceder al dashboard
require_once __DIR__ . '/public/index.php';
?>
