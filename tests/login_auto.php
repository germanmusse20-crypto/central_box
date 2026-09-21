<?php
/**
 * login_auto.php — Acceso directo al dashboard (solo para pruebas locales)
 * URL: http://localhost/central_box/login_auto.php?rol=admin
 *      http://localhost/central_box/login_auto.php?rol=vendedor
 *      http://localhost/central_box/login_auto.php?rol=cliente
 */
session_start();

$rol = $_GET['rol'] ?? 'admin';

$usuarios = [
    'admin'    => ['id' => 1, 'nombre' => 'Administrador',  'email' => 'admin@central-box.com',   'rol' => 'admin'],
    'vendedor' => ['id' => 2, 'nombre' => 'Empleado',       'email' => 'vendedor@central-box.com', 'rol' => 'vendedor'],
    'cliente'  => ['id' => 3, 'nombre' => 'Cliente Demo',   'email' => 'cliente@central-box.com',  'rol' => 'cliente'],
];

$user = $usuarios[$rol] ?? $usuarios['admin'];

$_SESSION['usuario_id']     = $user['id'];
$_SESSION['usuario_nombre'] = $user['nombre'];
$_SESSION['usuario_email']  = $user['email'];
$_SESSION['usuario_rol']    = $user['rol'];
$_SESSION['usuario_avatar'] = null;

header('Location: http://localhost/central_box/public/index.php?controller=dashboard&action=index');
exit;
