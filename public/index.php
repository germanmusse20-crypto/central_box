<?php
/**
 * central_box — Front Controller
 * Punto de entrada único de la aplicación
 */

// Mostrar errores temporalmente para diagnóstico
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión
session_start();

// Cargar configuración global
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Obtener controlador y acción de la URL
$controllerName = $_GET['controller'] ?? 'home';
$actionName     = $_GET['action']     ?? 'index';

// Si el usuario ya está logueado, redirigirlo a su portal según rol
if ($controllerName === 'auth' && $actionName === 'login' && isLoggedIn()) {
    $rol = $_SESSION['usuario_rol'] ?? 'cliente';
    if ($rol === 'vendedor') {
        redirect('index.php?controller=empleado&action=dashboard');
    } else {
        redirect('index.php?controller=dashboard&action=index');
    }
}

// Si el empleado/vendedor accede al login del empleado ya logueado, redirigir a su panel
if ($controllerName === 'empleado' && $actionName === 'login' && isLoggedIn()) {
    redirect('index.php?controller=empleado&action=dashboard');
}

// Mapeo de controladores disponibles
$controllers = [
    'home'       => 'HomeController',
    'auth'       => 'AuthController',
    'empleado'   => 'EmpleadoController',
    'cliente'    => 'ClienteController',
    'dashboard'  => 'DashboardController',
    'usuarios'   => 'UsuariosController',
    'productos'  => 'ProductoController',
    'categorias' => 'CategoriaController',
    'carrito'    => 'CarritoController',
    'pedidos'    => 'PedidoController',
    'inventario' => 'InventarioController',
    'ventas'     => 'VentasController',
    'reportes'   => 'ReportesController',
    'promociones'=> 'PromocionesController',
    'pagos'      => 'PagosController',
    'proveedores' => 'ProveedorController',
];

// Verificar que el controlador existe
if (!isset($controllers[$controllerName])) {
    http_response_code(404);
    echo '<h1>404 — Página no encontrada</h1>';
    echo '<p>El recurso solicitado no existe.</p>';
    echo '<a href="' . BASE_URL . '/index.php?controller=dashboard&action=index">Volver al inicio</a>';
    exit;
}

// Cargar el archivo del controlador
$controllerClass = $controllers[$controllerName];
$controllerFile  = CONTROLLERS_PATH . '/' . $controllerClass . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(500);
    echo '<h1>Error — Controlador no disponible</h1>';
    echo '<p>El módulo "' . e($controllerName) . '" aún no está implementado.</p>';
    echo '<a href="' . BASE_URL . '/index.php?controller=dashboard&action=index">Volver al inicio</a>';
    exit;
}

require_once $controllerFile;

// Instanciar el controlador
$controller = new $controllerClass();

// Verificar que la acción existe
if (!method_exists($controller, $actionName)) {
    http_response_code(404);
    echo '<h1>404 — Acción no encontrada</h1>';
    echo '<p>La acción "' . e($actionName) . '" no existe en el módulo "' . e($controllerName) . '".</p>';
    echo '<a href="' . BASE_URL . '/index.php?controller=dashboard&action=index">Volver al inicio</a>';
    exit;
}

// Ejecutar la acción
$controller->$actionName();
