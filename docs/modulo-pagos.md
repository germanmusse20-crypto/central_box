# Módulo Pagos — Métodos de Pago

## Descripción general

Gestiona los métodos de pago disponibles en la plataforma. Permite al administrador activar o deshabilitar los métodos que los clientes pueden usar al realizar una compra.

- **Controlador:** `controllers/PagosController.php`
- **Modelo:** `models/Pago.php`
- **Vistas:** `views/admin/pagos/`
- **URL base:** `index.php?controller=pagos`

---

## Acceso por rol

| Acción | admin | vendedor | cliente |
|--------|-------|----------|---------|
| Ver métodos de pago | ✅ | ❌ | ❌ |
| Activar/desactivar | ✅ | ❌ | ❌ |

---

## Métodos del controlador

### `index(): void`
- **URL:** `?controller=pagos&action=index`
- Solo `admin`.
- Carga todos los métodos de pago con `Pago::getAll()`.
- Vista: `views/admin/pagos/index.php`
- CSS extra: `pagos.css`

---

### `toggle(): void`
- **URL:** `?controller=pagos&action=toggle&id=N&estado=activo|inactivo`
- Solo `admin`.
- Valida que `estado` sea exactamente `activo` o `inactivo`.
- Llama a `Pago::updateEstado(id, estado)`.
- Redirige a `pagos/index` con mensaje de resultado.

**Ejemplo de uso:**
```
// Deshabilitar método de pago ID 2 (Tarjeta)
?controller=pagos&action=toggle&id=2&estado=inactivo

// Activar de nuevo
?controller=pagos&action=toggle&id=2&estado=activo
```

---

## Modelo Pago — métodos

### `getAll(): array`
```sql
SELECT * FROM metodos_pago ORDER BY id ASC
```
Retorna todos los métodos, activos e inactivos.

---

### `updateEstado(int $id, string $estado): bool`
```sql
UPDATE metodos_pago SET estado = :estado WHERE id = :id
```
`$estado` debe ser `'activo'` o `'inactivo'`.

---

## Tabla `metodos_pago`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT UNSIGNED PK | |
| `nombre` | VARCHAR(100) | Nombre del método |
| `descripcion` | TEXT | Descripción del método |
| `estado` | ENUM('activo','inactivo') | Estado actual |
| `created_at` | DATETIME | |

**Datos iniciales (del SQL):**
| ID | Nombre | Estado |
|----|--------|--------|
| 1 | Efectivo | activo |
| 2 | Tarjeta | activo |
| 3 | Transferencia | activo |

---

## Relación con otros módulos

```
metodos_pago
    └── id ◄── pago.id_metodo_pago
                   └── id_pago ◄── venta.id_pago
```

Los métodos de pago también se usan como **referencia de texto** en la tabla `pedidos` (columna `metodo_pago VARCHAR(50)`), por lo que deshabilitar un método en la tabla `metodos_pago` no impide que aparezca en pedidos históricos.

---

## Cómo los clientes ven los métodos activos

`ClienteController::getMetodosPagoDisponibles()` consulta directamente:
```sql
SELECT * FROM metodos_pago WHERE estado = 'activo'
```
Solo los métodos activos aparecen en el checkout del cliente.

---

## Errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| `toggle` no hace nada | `estado` no es exactamente `activo` o `inactivo` | Verificar el valor del parámetro GET en el enlace de la vista |
| Método deshabilitado sigue apareciendo en checkout | Caché de sesión o vista desactualizada | `getMetodosPagoDisponibles()` consulta directamente la BD; limpiar caché del navegador |
| Todos los métodos deshabilitados | Admin deshabilitó todos | Siempre mantener al menos `Efectivo` activo; considerar agregar validación |
| No se puede agregar nuevo método | No hay acción de creación en el controlador | El módulo actual solo gestiona los métodos predefinidos; para agregar uno nuevo, hacer INSERT directo en BD o ampliar el controlador |
