# Arquitectura — central_box

## Resumen

**central_box** es una aplicación web de comercio y punto de venta construida en PHP puro, sin frameworks externos. Sigue el patrón **MVC** (Model-View-Controller) con un único punto de entrada (*Front Controller*) y separación de roles por portal.

---

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Servidor web | Apache (Laragon) con `mod_rewrite` |
| Lenguaje backend | PHP 8.x |
| Base de datos | MySQL 8 / MariaDB |
| Acceso a datos | PDO (patrón Singleton) |
| Frontend | HTML5 + CSS vanilla + JavaScript |
| Gestión de sesión | PHP Sessions nativas |

---

## Estructura de directorios

```
central_box/
├── .htaccess               ← Redirige todo a public/
├── config/
│   ├── config.php          ← Constantes globales, helpers, URLs
│   └── database.php        ← Clase Database (Singleton PDO)
├── controllers/            ← Lógica de negocio por módulo
├── models/                 ← Acceso a datos (una clase = una tabla)
├── views/
│   ├── Layouts/            ← header.php, sidebar.php, footer.php
│   ├── admin/              ← Vistas del panel administrador
│   ├── empleado/           ← Portal del vendedor
│   ├── cliente/            ← Portal del cliente
│   ├── auth/               ← Login y registro
│   ├── compartido/         ← Vistas compartidas entre roles
│   └── home.php            ← Landing page pública
├── public/
│   ├── index.php           ← Front Controller (único punto de entrada)
│   ├── .htaccess           ← Redirige todo a index.php
│   └── assets/             ← Imágenes, scripts, estilos (público)
├── Styles/                 ← CSS de la aplicación
├── scripts/                ← JS de la aplicación
├── sql/
│   ├── central_box.sql     ← Schema completo de la BD
│   └── migrations/         ← Scripts de migración incremental
├── img/                    ← Imágenes de productos/empleados (Laragon)
├── docs/                   ← Esta documentación
└── tests/                  ← Pruebas
```

---

## Patrón MVC aplicado

```
Petición HTTP
     │
     ▼
.htaccess (raíz) ──► public/
     │
     ▼
public/.htaccess ──► public/index.php
     │
     ▼
Front Controller (index.php)
  1. session_start()
  2. require config.php + database.php
  3. Lee $_GET['controller'] y $_GET['action']
  4. Mapea a la clase controladora correcta
  5. Instancia el controlador
  6. Llama al método (acción)
     │
     ▼
Controller
  - Aplica requireLogin() / requireRole()
  - Instancia Model(s)
  - Ejecuta lógica de negocio
  - Pasa datos a la vista
     │
     ▼
View (require_once header + vista + footer)
  - Renderiza HTML con datos del controlador
  - Usa helpers: e(), formatPrice(), csrfField()
```

### URLs típicas

```
http://localhost/central_box/public/index.php?controller=dashboard&action=index
http://localhost/central_box/public/index.php?controller=productos&action=lista
http://localhost/central_box/public/index.php?controller=auth&action=login
```

---

## Mapa de controladores

| Clave URL | Clase | Descripción |
|-----------|-------|-------------|
| `home` | `HomeController` | Landing pública |
| `auth` | `AuthController` | Login / registro cliente |
| `empleado` | `EmpleadoController` | Portal del vendedor |
| `cliente` | `ClienteController` | Portal del cliente |
| `dashboard` | `DashboardController` | Dashboard contextual por rol |
| `usuarios` | `UsuariosController` | Gestión de usuarios (admin) |
| `productos` | `ProductoController` | CRUD de productos |
| `categorias` | `CategoriaController` | CRUD de categorías |
| `carrito` | `CarritoController` | Carrito de compras |
| `pedidos` | `PedidoController` | Gestión de pedidos |
| `inventario` | `InventarioController` | Control de stock |
| `ventas` | `VentasController` | Punto de venta / historial |
| `reportes` | `ReportesController` | Reportes y KPIs |
| `promociones` | `PromocionesController` | Gestión de promociones |
| `pagos` | `PagosController` | Métodos de pago |

---

## Portales por rol

```
admin    ──► DashboardController::index()  (estadísticas globales)
vendedor ──► EmpleadoController::dashboard() + VentasController
cliente  ──► ClienteController::dashboard() + CarritoController
```

Si un usuario accede a un portal que no le corresponde, `requireRole()` lo redirige automáticamente a su portal.

---

## Seguridad

### Autenticación
- Sesiones PHP con `session_start()` en `index.php`.
- Verificación con `isLoggedIn()` en cada acción protegida.
- Contraseñas hasheadas con `password_hash()` / `password_verify()`.

### Autorización
```php
requireLogin();          // redirige al login si no hay sesión
requireRole('admin');    // redirige al portal propio si el rol no coincide
```

### CSRF
Todas las acciones POST incluyen y verifican un token CSRF:
```php
// En la vista:
<?= csrfField() ?>

// En el controlador:
if (!verifyCsrf($_POST['csrf_token'] ?? '')) { ... }
```

### XSS
Todas las salidas en vistas deben pasar por `e($valor)`:
```php
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
```

### SQL Injection
Todas las consultas usan **sentencias preparadas PDO** con `bindValue()`. No hay concatenación directa de input del usuario en SQL.

### Subida de archivos
- Validación de MIME real con `finfo`.
- Tamaño máximo: **5 MB** (`MAX_FILE_SIZE`).
- Tipos permitidos: `image/jpeg`, `image/png`, `image/webp`, `image/gif`.
- Nombre del archivo generado con `bin2hex(random_bytes(16))`.

---

## Helpers globales (config.php)

| Función | Uso |
|---------|-----|
| `redirect(string $path)` | Redirige a una URL interna |
| `isLoggedIn(): bool` | Comprueba si hay sesión activa |
| `getUser(): ?array` | Devuelve datos del usuario de la sesión |
| `hasRole(string ...$roles): bool` | Verifica rol del usuario |
| `requireLogin(): void` | Protege rutas, redirige si no hay sesión |
| `requireRole(string ...$roles): void` | Protege rutas por rol |
| `setFlash(string $type, string $msg)` | Guarda mensaje flash en sesión |
| `getFlash(string $type): ?string` | Lee y limpia mensaje flash |
| `e(?string $value): string` | Escapa HTML (anti-XSS) |
| `formatPrice(float $price): string` | Formatea en pesos colombianos |
| `csrfToken(): string` | Genera/obtiene token CSRF |
| `verifyCsrf(string $token): bool` | Valida token CSRF |
| `csrfField(): string` | Genera campo `<input>` CSRF |

---

## Constantes globales (config.php)

| Constante | Valor / Descripción |
|-----------|---------------------|
| `APP_NAME` | `'central_box'` |
| `APP_VERSION` | `'1.0.0'` |
| `BASE_URL` | URL base dinámica (detecta Laragon vs built-in server) |
| `STYLES_URL` | URL a la carpeta de CSS |
| `SCRIPTS_URL` | URL a la carpeta de JS |
| `IMG_URL` | URL a la carpeta de imágenes |
| `ROOT_PATH` | Ruta absoluta a la raíz del proyecto |
| `VIEWS_PATH` | `ROOT_PATH . '/views'` |
| `CONTROLLERS_PATH` | `ROOT_PATH . '/controllers'` |
| `MODELS_PATH` | `ROOT_PATH . '/models'` |
| `UPLOADS_PATH` | Carpeta de imágenes de productos |
| `EMPLOYEE_UPLOADS_PATH` | Carpeta de avatares de empleados |
| `SESSION_LIFETIME` | `3600` (1 hora) |
| `MAX_FILE_SIZE` | `5242880` (5 MB) |
| `ITEMS_PER_PAGE` | `12` |

---

## Mensajes flash

El sistema de mensajes flash funciona a través de la sesión:
```php
setFlash('success', 'Operación exitosa');
setFlash('error', 'Ocurrió un error');
```
La vista llama a `getFlash('success')` / `getFlash('error')` para mostrar y limpiar el mensaje automáticamente.

---

## Flujo completo de una petición protegida (ejemplo: crear producto)

```
1. GET /index.php?controller=productos&action=crear
2. index.php mapea → ProductoController
3. ProductoController::crear()
   ├── requireLogin()   ← verifica sesión
   ├── requireRole('admin','vendedor')  ← verifica rol
   ├── $categorias = Categoria::getAll(true)
   └── require header + views/compartido/productos/form.php + footer

4. POST /index.php?controller=productos&action=guardar
5. ProductoController::guardar()
   ├── requireLogin() + requireRole()
   ├── verifyCsrf($_POST['csrf_token'])
   ├── Validación de campos
   ├── $this->guardarImagen()  ← upload seguro
   ├── Producto::create([...])
   └── redirect → productos/lista + flash 'success'
```

---

## Errores comunes y soluciones

| Error | Causa probable | Solución |
|-------|---------------|----------|
| Pantalla en blanco | `display_errors = 0` en PHP | Activa `error_reporting(E_ALL)` en `public/index.php` (ya incluido en dev) |
| `404 — Controlador no encontrado` | Clave `controller` no existe en el mapa | Verificar nombre en la URL y en el array `$controllers` de `index.php` |
| `500 — Archivo de controlador ausente` | El archivo `.php` no existe en `controllers/` | Crear el archivo o corregir nombre |
| `Error de conexión a la base de datos` | Credenciales incorrectas en `database.php` | Verificar host, puerto, dbname, user, password |
| Token CSRF inválido | Sesión expirada o formulario reenviado | Recargar el formulario; el token se regenera automáticamente |
| Imágenes no cargan | `UPLOADS_PATH` apunta a directorio incorrecto | Verificar si se usa Laragon o servidor built-in; ajustar `$isBuiltInServer` en `config.php` |
| Redirección infinita | Usuario con rol incorrecto accediendo a portal ajeno | Verificar que `requireRole()` y `redirect()` usen rutas correctas |
| `mod_rewrite` no funciona | `AllowOverride None` en Apache | Cambiar a `AllowOverride All` en `httpd.conf` de Laragon |
