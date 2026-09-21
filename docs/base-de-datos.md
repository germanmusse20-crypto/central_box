# Base de Datos — central_box

## Datos de conexión (Laragon / desarrollo)

| Parámetro | Valor |
|-----------|-------|
| Host | `127.0.0.1` |
| Puerto | `3306` |
| Base de datos | `central_box` |
| Usuario | `root` |
| Contraseña | *(vacía en Laragon)* |
| Charset | `utf8mb4` / `utf8mb4_unicode_ci` |

La conexión se gestiona en `config/database.php` mediante el patrón **Singleton PDO**:

```php
$db = Database::getInstance()->getConnection(); // devuelve un PDO
```

Opciones PDO activas:
- `ERRMODE_EXCEPTION` — lanza excepciones en errores SQL.
- `FETCH_ASSOC` — devuelve arrays asociativos por defecto.
- `EMULATE_PREPARES = false` — usa prepared statements reales.

---

## Diagrama de tablas y relaciones

```
usuarios ──┬──────────────────────── pedidos (cliente_id)
           ├──────────────────────── productos (vendedor_id)
           ├──────────────────────── movimientos_inventario (usuario_id)
           ├──────────────────────── carrito (id_cliente)
           ├──────────────────────── venta (id_cliente, id_usuario)
           ├──────────────────────── pedidos_proveedor (usuario_id)
           ├──────────────────────── solicitud_promocion (id_empleado)
           ├──────────────────────── promociones (solicitado_por)
           └──────────────────────── recuperacion_contrasena (id_usuario)

productos ──┬──────────────────────── detalle_pedidos (producto_id)
            ├──────────────────────── movimientos_inventario (producto_id)
            ├──────────────────────── detalle_carrito (id_producto)
            ├──────────────────────── detalle_venta (id_producto)
            ├──────────────────────── detalle_pedido_proveedor (producto_id)
            └──────────────────────── promociones (producto_id)

categorias ─────────────────────────  productos (categoria_id)

pedidos ──────────────────────────── detalle_pedidos (pedido_id)

carrito ──────────────────────────── detalle_carrito (id_carrito)

venta ──┬─────────────────────────── detalle_venta (id_venta)
        └─────────────────────────── pago (id_pago)

pago ─────────────────────────────── metodos_pago (id_metodo_pago)

proveedores ──────────────────────── pedidos_proveedor (proveedor_id)

pedidos_proveedor ────────────────── detalle_pedido_proveedor (pedido_id)

promociones ──────────────────────── solicitud_promocion (id_promocion)
```

---

## Descripción de cada tabla

### `usuarios`
Tabla central del sistema. Contiene todos los actores: admin, vendedor y cliente.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT UNSIGNED PK | Identificador único |
| `nombre` | VARCHAR(100) | Nombre completo |
| `email` | VARCHAR(150) UNIQUE | Email (usado como login) |
| `password` | VARCHAR(255) | Hash bcrypt (`password_hash`) |
| `rol` | ENUM('admin','vendedor','cliente') | Rol del usuario |
| `telefono` | VARCHAR(20) | Teléfono opcional |
| `direccion` | VARCHAR(200) | Dirección opcional |
| `avatar` | VARCHAR(255) | Nombre del archivo de avatar |
| `activo` | TINYINT(1) | `1` activo / `0` desactivado (soft delete) |
| `created_at` | DATETIME | Fecha de registro |
| `updated_at` | DATETIME | Última modificación |

**Usuario admin por defecto:**
- Email: `admin@central-box.com`
- Contraseña: `Admin2025*`

---

### `categorias`
Agrupación de productos.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT UNSIGNED PK | |
| `nombre` | VARCHAR(80) UNIQUE | Nombre de la categoría |
| `descripcion` | VARCHAR(255) | Descripción opcional |
| `imagen` | VARCHAR(255) | Imagen representativa |
| `activa` | TINYINT(1) | Soft delete |
| `created_at` | DATETIME | |

---

### `productos`
Catálogo de productos de la tienda.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT UNSIGNED PK | |
| `nombre` | VARCHAR(100) | Nombre del producto |
| `descripcion` | TEXT | Descripción larga |
| `precio` | DECIMAL(12,2) | Precio de venta |
| `stock` | INT | Unidades disponibles |
| `stock_minimo` | INT | Umbral de alerta de bajo stock (default 5) |
| `imagen` | VARCHAR(255) | Nombre del archivo de imagen |
| `categoria_id` | INT UNSIGNED FK | → `categorias.id` (SET NULL al eliminar) |
| `vendedor_id` | INT UNSIGNED FK | → `usuarios.id` (SET NULL al eliminar) |
| `activo` | TINYINT(1) | Soft delete |
| `created_at` | DATETIME | |
| `updated_at` | DATETIME | |

---

### `pedidos`
Órdenes de compra generadas por clientes o por el POS.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT UNSIGNED PK | |
| `cliente_id` | INT UNSIGNED FK | → `usuarios.id` (RESTRICT) |
| `total` | DECIMAL(12,2) | Total del pedido |
| `estado` | ENUM('confirmado','entregado','cancelado') | Estado del pedido |
| `direccion_envio` | VARCHAR(200) | Dirección de entrega |
| `metodo_pago` | VARCHAR(50) | `efectivo`, `tarjeta`, `transferencia` |
| `notas` | TEXT | Notas adicionales |
| `created_at` | DATETIME | |
| `updated_at` | DATETIME | |

---

### `detalle_pedidos`
Líneas de productos de cada pedido.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT UNSIGNED PK | |
| `pedido_id` | INT UNSIGNED FK | → `pedidos.id` (CASCADE) |
| `producto_id` | INT UNSIGNED FK | → `productos.id` (RESTRICT) |
| `cantidad` | INT | Unidades pedidas |
| `precio_unitario` | DECIMAL(12,2) | Precio en el momento del pedido |
| `subtotal` | DECIMAL(12,2) | `cantidad × precio_unitario` |

---

### `movimientos_inventario`
Auditoría de cambios de stock.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT UNSIGNED PK | |
| `producto_id` | INT UNSIGNED FK | → `productos.id` |
| `usuario_id` | INT UNSIGNED FK | → `usuarios.id` |
| `tipo` | ENUM('entrada','salida','ajuste') | Tipo de movimiento |
| `cantidad` | INT | Cantidad del movimiento |
| `stock_anterior` | INT | Stock antes del movimiento |
| `stock_nuevo` | INT | Stock después del movimiento |
| `motivo` | VARCHAR(255) | Descripción del motivo |
| `created_at` | DATETIME | |

---

### `carrito` y `detalle_carrito`
Carrito persistente en base de datos (respaldo de sesión).

**`carrito`**

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_carrito` | INT UNSIGNED PK | |
| `id_cliente` | INT UNSIGNED FK | → `usuarios.id` (CASCADE) |
| `fecha_creacion` | DATETIME | |
| `estado` | VARCHAR(30) | `activo` / `vacio` |

**`detalle_carrito`**

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_detalle_carrito` | INT UNSIGNED PK | |
| `id_carrito` | INT UNSIGNED FK | → `carrito.id_carrito` (CASCADE) |
| `id_producto` | INT UNSIGNED FK | → `productos.id` (RESTRICT) |
| `cantidad` | INT | |
| `precio_unitario` | DECIMAL(10,2) | Precio capturado al agregar |
| UNIQUE | `(id_carrito, id_producto)` | Un producto por carrito |

---

### `metodos_pago`
Catálogo de métodos de pago habilitables.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT UNSIGNED PK | |
| `nombre` | VARCHAR(100) | Efectivo, Tarjeta, Transferencia |
| `descripcion` | TEXT | |
| `estado` | ENUM('activo','inactivo') | Habilitado/deshabilitado |
| `created_at` | DATETIME | |

---

### `promociones` y `solicitud_promocion`
Sistema de solicitud y aprobación de descuentos.

**`promociones`**

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT UNSIGNED PK | |
| `producto_id` | INT UNSIGNED FK | → `productos.id` (SET NULL) |
| `nombre` | VARCHAR(100) | Nombre de la promoción |
| `descripcion` | VARCHAR(255) | |
| `descuento` | DECIMAL(5,2) | Porcentaje de descuento |
| `fecha_inicio` | DATE | |
| `fecha_fin` | DATE | |
| `estado` | ENUM('pendiente','activa','rechazada','eliminada') | |
| `solicitado_por` | INT UNSIGNED FK | → `usuarios.id` |
| `created_at` | DATETIME | |

**`solicitud_promocion`**

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_solicitud` | INT UNSIGNED PK | |
| `id_empleado` | INT UNSIGNED FK | Vendedor que la solicitó |
| `id_promocion` | INT UNSIGNED FK | → `promociones.id` |
| `nombre_propuesta` | VARCHAR(100) | |
| `descuento_propuesto` | DECIMAL(5,2) | |
| `estado` | VARCHAR(30) | `pendiente` / `aprobada` / `rechazada` |

---

### `venta`, `pago` y `detalle_venta`
Ventas presenciales del Punto de Venta.

**`pago`**

| Columna | Descripción |
|---------|-------------|
| `id_pago` | PK |
| `id_metodo_pago` | FK → `metodos_pago` |
| `monto` | Total del pago |
| `fecha_pago` | |
| `estado` | `pendiente` / `completado` |
| `referencia` | Código de referencia |

**`venta`**

| Columna | Descripción |
|---------|-------------|
| `id_venta` | PK |
| `id_cliente` | FK → `usuarios` (nullable) |
| `id_usuario` | FK → `usuarios` (vendedor) |
| `id_pago` | FK → `pago` |
| `subtotal` / `descuento` / `total` | Importes |
| `tipo_venta` | `PRESENCIAL` / `ONLINE` |

---

### `recuperacion_contrasena`
Tokens para restablecer contraseña.

| Columna | Descripción |
|---------|-------------|
| `id_recuperacion` | PK |
| `id_usuario` | FK → `usuarios` (CASCADE) |
| `token` | Token hexadecimal único (64 chars) |
| `fecha_expiracion` | Expiración (30 min por defecto) |
| `utilizada` | `0` = válido / `1` = ya utilizado |

---

### `proveedores`, `pedidos_proveedor`, `detalle_pedido_proveedor`
Gestión de compras a proveedores (módulo extendido).

**`proveedores`** — datos de contacto del proveedor.  
**`pedidos_proveedor`** — órdenes de compra con estados: `pendiente → confirmado → pagado → recibido → cancelado`.  
**`detalle_pedido_proveedor`** — líneas de cada orden de compra.

---

## Comando de instalación

```sql
-- Importar el schema completo desde la raíz del proyecto:
mysql -u root central_box < sql/central_box.sql
```

O desde phpMyAdmin: importar `sql/central_box.sql`.

---

## Errores comunes de BD y soluciones

| Error | Causa | Solución |
|-------|-------|----------|
| `SQLSTATE[HY000] [1045] Access denied` | Credenciales incorrectas | Verificar `$username` / `$password` en `database.php` |
| `SQLSTATE[HY000] [1049] Unknown database 'central_box'` | La BD no existe | Ejecutar `CREATE DATABASE central_box` o importar el SQL |
| `SQLSTATE[23000] Integrity constraint violation (FK)` | Se intenta eliminar un registro con dependientes | Usar soft delete o eliminar dependientes primero |
| `SQLSTATE[42S02] Table not found` | Migración no aplicada | Revisar carpeta `sql/migrations/` y ejecutar los scripts pendientes |
| `SQLSTATE[22003] Out of range value` | Precio o stock con valor negativo/demasiado grande | Validar datos antes de INSERT/UPDATE |
| Carrito no persiste entre sesiones | Tabla `carrito` no creada | Ejecutar migraciones en `sql/migrations/` |
| Tokens de recuperación no expiran | `fecha_expiracion` nula | Verificar que el campo `utilizada = 0` se marque al usar el token |
| `Deadlock found when trying to get lock` | Transacción en `movimientos_inventario` con alta concurrencia | El modelo ya incluye `FOR UPDATE` y manejo de transacción; revisar timeout de MySQL |

---

## Migraciones disponibles

| Archivo | Descripción |
|---------|-------------|
| `20260904_estados_pedidos.sql` | Ajuste de estados en tabla `pedidos` |
| `20260904_pedidos_campos_venta.sql` | Agrega campos de venta al pedido |
| `20260904_productos_catalogo.sql` | Campos adicionales en catálogo |
| `20260905_tablas_operacion.sql` | Tablas de operación extendidas |

Para aplicar una migración:
```bash
mysql -u root central_box < sql/migrations/nombre_migracion.sql
```
