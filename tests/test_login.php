<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simular el entorno
session_start();

// Cargar config
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h2>Test de diagnóstico del login</h2>";

// 1. Test conexión BD
try {
    $db = Database::getInstance()->getConnection();
    echo "<p style='color:green'>✓ BD conectada OK</p>";
} catch (Exception $e) {
    die("<p style='color:red'>✗ BD ERROR: " . $e->getMessage() . "</p>");
}

// 2. Test tabla usuarios
try {
    $stmt = $db->query("SELECT COUNT(*) FROM usuarios");
    $count = $stmt->fetchColumn();
    echo "<p style='color:green'>✓ Tabla usuarios OK — $count usuarios</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Tabla usuarios ERROR: " . $e->getMessage() . "</p>";
}

// 3. Test autenticación
require_once __DIR__ . '/models/Usuario.php';
$usuarioModel = new Usuario();
$user = $usuarioModel->authenticate('admin@central-box.com', 'Admin2025*');
if ($user) {
    echo "<p style='color:green'>✓ Autenticación OK — Usuario: {$user['nombre']} / Rol: {$user['rol']}</p>";
} else {
    echo "<p style='color:red'>✗ Autenticación FALLÓ para admin@central-box.com / Admin2025*</p>";
    // Verificar si existe el usuario
    $u = $usuarioModel->findByEmail('admin@central-box.com');
    if ($u) {
        echo "<p style='color:orange'>⚠ El email existe pero la contraseña no coincide. Hash en BD: " . substr($u['password'],0,20) . "...</p>";
        echo "<p>activo = " . $u['activo'] . "</p>";
    } else {
        echo "<p style='color:red'>✗ El email admin@central-box.com NO existe en la BD</p>";
        // Listar todos los usuarios
        $todos = $usuarioModel->getAll();
        echo "<p>Usuarios en BD:</p><ul>";
        foreach ($todos as $t) {
            echo "<li>{$t['email']} — {$t['rol']}</li>";
        }
        echo "</ul>";
    }
}

// 4. Test sesión
$_SESSION['usuario_id']     = 999;
$_SESSION['usuario_nombre'] = 'Test';
$_SESSION['usuario_rol']    = 'admin';
$_SESSION['usuario_email']  = 'test@test.com';

session_regenerate_id(false);

echo "<p style='color:" . (isset($_SESSION['usuario_id']) ? 'green' : 'red') . "'>
    " . (isset($_SESSION['usuario_id']) ? '✓' : '✗') . " Sesión después de regenerate_id(false): usuario_id=" . ($_SESSION['usuario_id'] ?? 'PERDIDO') . "
</p>";

// 5. Test DashboardController
try {
    require_once __DIR__ . '/models/Pedido.php';
    require_once __DIR__ . '/models/Producto.php';
    require_once __DIR__ . '/models/Inventario.php';
    $pedidoModel = new Pedido();
    $stats = $pedidoModel->getEstadisticas();
    echo "<p style='color:green'>✓ getEstadisticas() OK</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ getEstadisticas() ERROR: " . $e->getMessage() . "</p>";
}

echo "<hr><p><strong>Session actual:</strong> <pre>" . print_r($_SESSION, true) . "</pre></p>";
echo "<p><a href='public/index.php?controller=auth&action=login'>Ir al login</a></p>";
