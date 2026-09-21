# Módulo Dashboard — Panel de Control

## Descripción general

Punto de entrada tras el login. Muestra estadísticas y resumen **contextual según el rol** del usuario autenticado. Actúa como enrutador visual: si el usuario es `vendedor` o `cliente` lo redirige a su portal especializado.

- **Controlador:** `controllers/DashboardController.php`
- **Modelos usados:** `Usuario`, `Producto`, `Pedido`, `Inventario`
- **Vistas:** `views/compartido/dashboard/index.php`
- **URL base:** `index.php?controller=dashboard&action=index`

---

## Acceso por rol

| Rol | Comportamiento |
|-----|---------------|
| `admin` | Carga estadísticas globales y renderiza el dashboard |
| `vendedor` | Redirigido a `empleado/dashboard` |
| `cliente` | Redirigido a `cliente/dashboard` |
| Sin sesión | Redirigido al login por `requireLogin()` |

---

## Métodos del controlador

### `index(): void`
- **URL:** `?controller=dashboard&action=index`
- Único método del controlador.
- Requiere sesión activa (`requireLogin()`).
- Detecta el rol y actúa en consecuencia.

#### Datos cargados para `admin`

```php
$data = [
    'total_usuarios'     => $usuarioModel->count(),
    'total_productos'    => $productoModel->count(),
    'total_pedidos'      => $pedidoModel->count(),
    'total_ventas'       => $estadisticas['total_ventas'],    // suma de todos los pedidos no cancelados
    'ventas_mes'         => $estadisticas['ventas_mes'],      // ventas del mes actual
    'por_estado'         => $estadisticas['por_estado'],      // conteo agrupado por estado
    'pedidos_recientes'  => $estadisticas['recientes'],       // últimos N pedidos
    'bajo_stock'         => $inventarioModel->getProductosBajoStock(),
    'resumen_inventario' => $inventarioModel->getResumen(),   // totales del inventario
]
```

#### Datos cargados para `vendedor` (si no hubiera redirección)
```php
$data = [
    'mis_productos'   => $productoModel->getByVendedor($user['id']),
    'total_productos' => count($misProductos),
    'bajo_stock'      => $inventarioModel->getProductosBajoStock(),
]
```

#### Datos cargados para `cliente`
```php
$data = [
    'mis_pedidos'   => $pedidoModel->getByCliente($user['id'], 5),
    'total_pedidos' => count($misPedidos),
]
```

---

## Modelos y métodos invocados

| Modelo | Método | Retorna |
|--------|--------|---------|
| `Usuario` | `count()` | Total de usuarios en la BD |
| `Producto` | `count()` | Total de productos activos |
| `Pedido` | `count()` | Total de pedidos |
| `Pedido` | `getEstadisticas()` | Array con `total_ventas`, `ventas_mes`, `por_estado`, `recientes` |
| `Pedido` | `getByCliente(id, limit)` | Últimos pedidos de un cliente |
| `Inventario` | `getProductosBajoStock()` | Productos con `stock <= stock_minimo` |
| `Inventario` | `getResumen()` | Totales: productos, unidades, valor, bajo stock, sin stock |

---

## Assets cargados

```php
$extraCss = ['dashboard.css'];
$extraJs  = ['dashboard.js'];
```

---

## Flujo de renderizado

```
requireLogin()
     │
     ├── rol = vendedor ──► redirect empleado/dashboard
     ├── rol = cliente  ──► redirect cliente/dashboard
     └── rol = admin
           │
           ├── Instancia: Usuario, Producto, Pedido, Inventario
           ├── Obtiene estadísticas
           └── require header + views/compartido/dashboard/index.php + footer
```

---

## Errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| Redirige en bucle | El rol en `$_SESSION['usuario_rol']` está vacío o es incorrecto | Verificar `doLogin` en `AuthController` que guarda el rol |
| Dashboard vacío (sin datos) | Tablas vacías o sin pedidos | Normal en instalación nueva; importar datos de prueba del SQL |
| `getEstadisticas()` retorna valores en 0 | Ningún pedido en estado distinto a `cancelado` | Crear pedidos de prueba |
| Error de clase no encontrada (`Inventario`) | `require_once` faltante o nombre de archivo incorrecto | Verificar que `MODELS_PATH . '/Inventario.php'` existe |
