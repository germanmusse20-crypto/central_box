<?php
session_start();

// Configurar la ruta de base
define('BASE_URL', 'http://localhost/central_box/public');
define('BASE_PATH', __DIR__);
define('VIEWS_PATH', BASE_PATH . '/views');
define('MODELS_PATH', BASE_PATH . '/models');
define('CONTROLLERS_PATH', BASE_PATH . '/controllers');
define('CONFIG_PATH', BASE_PATH . '/config');

// Simular usuario logueado
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nombre'] = 'Administrador';
$_SESSION['usuario_email'] = 'admin@central-box.com';
$_SESSION['usuario_rol'] = 'admin';

// Cargar la BD
require_once CONFIG_PATH . '/database.php';

// Cargar funciones helper
require_once CONFIG_PATH . '/config.php';

// Cargar modelos
require_once MODELS_PATH . '/Usuario.php';
require_once MODELS_PATH . '/Producto.php';
require_once MODELS_PATH . '/Pedido.php';
require_once MODELS_PATH . '/Inventario.php';

echo "=== TEST DASHBOARD ===\n\n";

try {
    $usuarioModel = new Usuario();
    $productoModel = new Producto();
    $pedidoModel = new Pedido();
    $inventarioModel = new Inventario();
    
    echo "✓ Modelos cargados correctamente\n";
    
    // Intentar obtener estadísticas (esto es lo que fallaba)
    echo "\nObteniendo estadísticas de pedidos...\n";
    $estadisticas = $pedidoModel->getEstadisticas();
    
    echo "✓ Estadísticas obtenidas:\n";
    echo "  - Total ventas: $" . number_format($estadisticas['total_ventas'], 2) . "\n";
    echo "  - Total pedidos: " . $estadisticas['total_pedidos'] . "\n";
    echo "  - Ventas mes: $" . number_format($estadisticas['ventas_mes'], 2) . "\n";
    
    // Obtener datos del usuario
    $user = $usuarioModel->findById(1);
    echo "\n✓ Usuario encontrado: " . $user['nombre'] . "\n";
    
    // Obtener productos
    $productos = $productoModel->getAll();
    echo "✓ Productos cargados: " . count($productos) . "\n";
    
    echo "\n✓ ¡TODO FUNCIONA CORRECTAMENTE!\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
