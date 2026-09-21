<?php
/**
 * central_box — Configuración Global
 * Constantes, rutas y funciones helper
 */

// ============================================================
// Información de la aplicación
// ============================================================
define('APP_NAME',    'central_box');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR',  'central_box');

// ============================================================
// URLs y rutas del proyecto
// ============================================================
// Detectar protocolo y host automáticamente
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Detectar si se está usando el servidor built-in de PHP (ej. localhost:8000)
$isBuiltInServer = (strpos($host, ':') !== false); // tiene puerto explícito

if ($isBuiltInServer) {
    define('BASE_URL',    $protocol . '://' . $host);
    define('STYLES_URL',  $protocol . '://' . $host . '/assets/Styles');
    define('SCRIPTS_URL', $protocol . '://' . $host . '/assets/scripts');
    define('IMG_URL',     $protocol . '://' . $host . '/assets/img');
} else {
    // Laragon / Apache con mod_rewrite
    define('BASE_URL',    $protocol . '://' . $host . '/central_box/public');
    define('STYLES_URL',  $protocol . '://' . $host . '/central_box/Styles');
    define('SCRIPTS_URL', $protocol . '://' . $host . '/central_box/scripts');
    define('IMG_URL',     $protocol . '://' . $host . '/central_box/img');
}

define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('UPLOADS_PATH', $isBuiltInServer
    ? PUBLIC_PATH . '/assets/img/productos'
    : ROOT_PATH . '/img/productos');
define('EMPLOYEE_UPLOADS_PATH', $isBuiltInServer
    ? PUBLIC_PATH . '/assets/img/empleados'
    : ROOT_PATH . '/img/empleados');

// ============================================================
// Configuración de sesión
// ============================================================
define('SESSION_LIFETIME', 3600); // 1 hora

// ============================================================
// Configuración de subida de archivos
// ============================================================
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// ============================================================
// Paginación
// ============================================================
define('ITEMS_PER_PAGE', 12);

// ============================================================
// Funciones Helper
// ============================================================

/**
 * Redirigir a una URL interna
 */
function redirect(string $path): void
{
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

/**
 * Verificar si el usuario está logueado
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['usuario_id']);
}

/**
 * Obtener datos del usuario logueado desde la sesión
 */
function getUser(): ?array
{
    if (!isLoggedIn()) return null;
    return [
        'id'     => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['usuario_nombre'],
        'email'  => $_SESSION['usuario_email'],
        'rol'    => $_SESSION['usuario_rol'],
        'avatar' => $_SESSION['usuario_avatar'] ?? null,
    ];
}

/**
 * Verificar si el usuario tiene un rol específico
 */
function hasRole(string ...$roles): bool
{
    if (!isLoggedIn()) return false;
    return in_array($_SESSION['usuario_rol'], $roles);
}

/**
 * Requerir autenticación - redirige al login correcto si no está logueado
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = 'Debes iniciar sesión para acceder a esta página.';
        // Detectar si el acceso es al módulo empleado
        $ctrl = $_GET['controller'] ?? '';
        if ($ctrl === 'empleado') {
            redirect('index.php?controller=empleado&action=login');
        } else {
            redirect('index.php?controller=auth&action=login');
        }
    }
}

/**
 * Requerir un rol específico
 * Redirige al portal correcto según el rol del usuario si no tiene permiso
 */
function requireRole(string ...$roles): void
{
    requireLogin();
    if (!hasRole(...$roles)) {
        $_SESSION['flash_error'] = 'No tienes permisos para acceder a esta página.';
        // Redirigir al portal propio del rol, no siempre al admin
        $rol = $_SESSION['usuario_rol'] ?? 'cliente';
        if ($rol === 'vendedor') {
            redirect('index.php?controller=empleado&action=dashboard');
        } elseif ($rol === 'admin') {
            redirect('index.php?controller=dashboard&action=index');
        } else {
            redirect('index.php?controller=cliente&action=dashboard');
        }
    }
}

/**
 * Establecer un mensaje flash
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash_' . $type] = $message;
}

/**
 * Obtener y limpiar un mensaje flash
 */
function getFlash(string $type): ?string
{
    $key = 'flash_' . $type;
    if (isset($_SESSION[$key])) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }
    return null;
}

/**
 * Escapar HTML para prevenir XSS
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Formatear precio en pesos colombianos
 */
function formatPrice(float $price): string
{
    return '$ ' . number_format($price, 0, ',', '.');
}

/**
 * Generar un token CSRF
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCsrf(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generar campo hidden con token CSRF
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}
