# Módulo Inventario — Control de Stock

## Descripción general

Gestiona los movimientos de inventario (entradas, salidas y ajustes) con **trazabilidad completa** mediante una tabla de auditoría. Cada cambio de stock queda registrado con el usuario responsable, el motivo y los valores anterior/nuevo.

- **Controlador:** `controllers/InventarioController.php`
- **Modelo:** `models/Inventario.php`
- **Modelo auxiliar:** `Producto`
- **Vistas:** `views/compartido/inventario/`
- **URL base:** `index.php?controller=inventario`

---

## Acceso por rol

| Acción | admin | vendedor | cliente |
|--------|-------|----------|---------|
| Ver resumen | ✅ | ✅ | ❌ |
| Ver movimientos | ✅ | ✅ | ❌ |
| Registrar ajuste | ✅ | ❌ | ❌ |

---

## Métodos del controlador

### `index(): void`
- **URL:** `?controller=inventario&action=index`
- Requiere `admin` o `vendedor`.
- Carga:
  - Resumen general del inventario (`getResumen()`).
  - Lista de productos con bajo stock (`getProductosBajoStock()`).
  - Últimos 20 movimientos (`getMovimientos(null, null, 20)`).
  - Lista completa de productos (hasta 500) para el formulario de ajuste.
- Vista: `views/compartido/inventario/index.php`

---

### `movimientos(): void`
- **URL:** `?controller=inventario&action=movimientos`
- Requiere `admin` o `vendedor`.
- Filtros: `?producto=ID`, `?tipo=entrada|salida|ajuste`, `?page=N`.
- Paginación con 50 registros por página.
- Vista: `views/compartido/inventario/movimientos.php`

---

### `ajuste(): void`
- **URL:** `?controller=inventario&action=ajuste` (POST)
- **Solo `admin`**.
- Valida CSRF.
- Valida que `tipo` sea uno de: `entrada`, `salida`, `ajuste`.
- Valida `cantidad > 0`.
- Llama a `Inventario::registrarMovimiento()`.
- Redirige a `inventario/index`.

**Campos del POST:**
```
csrf_token   = TOKEN
producto_id  = ID del producto
tipo         = entrada | salida | ajuste
cantidad     = número entero positivo
motivo       = texto explicativo
```

---

## Modelo Inventario — métodos

### `registrarMovimiento(int $productoId, int $usuarioId, string $tipo, int $cantidad, string $motivo): bool`

Ejecuta una **transacción con bloqueo de fila** (`FOR UPDATE`):

```
BEGIN TRANSACTION
  1. SELECT stock FROM productos WHERE id = :id FOR UPDATE
  2. Calcula nuevo stock según tipo:
     - entrada: stock + cantidad
     - salida:  stock - cantidad (mínimo 0)
     - ajuste:  cantidad (stock nuevo directo)
  3. INSERT INTO movimientos_inventario (producto_id, usuario_id, tipo, cantidad, stock_anterior, stock_nuevo, motivo)
  4. UPDATE productos SET stock = :nuevo WHERE id = :id
COMMIT | ROLLBACK
```

> El tipo `ajuste` establece directamente el nuevo stock (útil para correcciones de inventario físico).

---

### `getMovimientos(?int $productoId, ?string $tipo, int $limit, int $offset): array`

```sql
SELECT mi.*, p.nombre AS producto_nombre, u.nombre AS usuario_nombre
FROM movimientos_inventario mi
INNER JOIN productos p ON mi.producto_id = p.id
INNER JOIN usuarios u ON mi.usuario_id = u.id
WHERE [filtros]
ORDER BY mi.created_at DESC
LIMIT :limit OFFSET :offset
```

---

### `getProductosBajoStock(): array`
```sql
SELECT p.*, c.nombre AS categoria_nombre
FROM productos p
LEFT JOIN categorias c ON p.categoria_id = c.id
WHERE p.activo = 1 AND p.stock <= p.stock_minimo
ORDER BY p.stock ASC
```

---

### `getResumen(): array`
Retorna en una sola consulta múltiple:
```php
[
    'total_productos' => int,    // productos activos
    'total_unidades'  => int,    // suma de stock de todos los productos activos
    'bajo_stock'      => int,    // productos donde stock <= stock_minimo
    'sin_stock'       => int,    // productos donde stock = 0
    'valor_total'     => float,  // SUM(precio * stock) de productos activos
]
```

---

## Tabla de base de datos

### `movimientos_inventario`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT PK | |
| `producto_id` | INT FK | → `productos.id` |
| `usuario_id` | INT FK | → `usuarios.id` |
| `tipo` | ENUM('entrada','salida','ajuste') | Tipo de movimiento |
| `cantidad` | INT | Unidades del movimiento |
| `stock_anterior` | INT | Stock antes del movimiento |
| `stock_nuevo` | INT | Stock después del movimiento |
| `motivo` | VARCHAR(255) | Descripción del ajuste |
| `created_at` | DATETIME | Timestamp automático |

---

## Tipos de movimiento

| Tipo | Efecto en stock | Caso de uso |
|------|----------------|-------------|
| `entrada` | `stock + cantidad` | Recepción de mercancía, devolución de cliente |
| `salida` | `stock - cantidad` (mín 0) | Pérdida, merma, producto dañado |
| `ajuste` | `= cantidad` | Corrección tras conteo físico |

> Las ventas **también decrementan el stock** directamente en `Pedido::crear()`. Los movimientos de inventario son para ajustes manuales administrativos.

---

## Flujo de ajuste manual

```
Admin abre: ?controller=inventario&action=index
  └── Ve tabla de productos + bajo stock

Admin llena formulario de ajuste:
  producto_id = 5
  tipo        = entrada
  cantidad    = 100
  motivo      = "Reposición de mercancía - Proveedor XYZ"

POST → ?controller=inventario&action=ajuste
  ├── verifyCsrf()
  ├── Valida tipo y cantidad
  └── Inventario::registrarMovimiento(5, adminId, 'entrada', 100, 'Reposición...')
      ├── FOR UPDATE en productos
      ├── stock_anterior = 15
      ├── stock_nuevo = 115
      ├── INSERT movimientos_inventario
      └── UPDATE productos SET stock = 115
```

---

## Errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| `Deadlock found` en transacción | Alta concurrencia o transacción abierta sin cerrar | El modelo usa `FOR UPDATE`; en desarrollo verificar que no haya transacciones huérfanas |
| Stock no baja con ventas | `Pedido::crear()` hace el decremento pero la transacción hizo ROLLBACK | Revisar logs PHP; el `catch` en `crear()` relanza la excepción |
| `getResumen()` muestra valor total en 0 | Todos los productos tienen `precio = 0` o `stock = 0` | Revisar datos; es un indicador válido si el stock es real |
| Movimiento insertado sin actualizar stock | Error entre los pasos 3 y 4 de la transacción | El ROLLBACK revierte ambas operaciones; no puede quedar inconsistente |
| Solo admin puede hacer ajustes pero vendedor necesita reportar faltantes | Restricción de rol | Considerar agregar un formulario de "reporte de merma" para vendedores que quede en estado pendiente |
