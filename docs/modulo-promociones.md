# Módulo Promociones — Gestión de Descuentos

## Descripción general

Implementa un flujo de **solicitud y aprobación de promociones**: los vendedores proponen descuentos y el administrador los aprueba, rechaza o elimina. Las promociones activas se aplican en el POS y son visibles para los clientes.

- **Controlador:** `controllers/PromocionesController.php`
- **Modelo:** `models/Promocion.php`
- **Vistas admin:** `views/admin/promociones/`
- **Vistas empleado:** `views/empleado/` (solicitar, ver)
- **Vistas cliente:** `views/cliente/promociones.php`
- **URL base:** `index.php?controller=promociones`

---

## Acceso por rol

| Acción | admin | vendedor | cliente |
|--------|-------|----------|---------|
| Ver y gestionar todas (`index`) | ✅ | ❌ | ❌ |
| Aprobar / rechazar / eliminar | ✅ | ❌ | ❌ |
| Solicitar promoción | ❌ | ✅ (vía `EmpleadoController`) | ❌ |
| Ver promociones activas | ❌ | ✅ (vía `EmpleadoController`) | ✅ (vía `ClienteController`) |

---

## Métodos del controlador

### `index(): void`
- **URL:** `?controller=promociones&action=index`
- Solo `admin`.
- Carga todas las promociones con `estado != 'eliminada'`.
- Incluye JOIN con el nombre del solicitante (vendedor) y del producto.
- Vista: `views/admin/promociones/index.php`

---

### `aprobar(): void`
- **URL:** `?controller=promociones&action=aprobar&id=N`
- Solo `admin`.
- Actualiza `estado = 'activa'` en la tabla `promociones`.
- Mensaje flash de éxito y redirige al listado.

---

### `rechazar(): void`
- **URL:** `?controller=promociones&action=rechazar&id=N`
- Solo `admin`.
- Actualiza `estado = 'rechazada'`.

---

### `eliminar(): void`
- **URL:** `?controller=promociones&action=eliminar&id=N`
- Solo `admin`.
- **Soft delete:** actualiza `estado = 'eliminada'`.
- La promoción no aparece en ningún listado posterior.

---

## Modelo Promocion — métodos

### `getPromociones(?string $estado): array`
```sql
SELECT p.*, u.nombre AS solicitante, prod.nombre AS producto_nombre
FROM promociones p
LEFT JOIN usuarios u ON p.solicitado_por = u.id
LEFT JOIN productos prod ON p.producto_id = prod.id
WHERE p.estado != 'eliminada'
[AND p.estado = :estado]
ORDER BY p.created_at DESC
```
Filtra opcionalmente por estado. Nunca muestra las `eliminada`.

---

### `getPromocionesByUsuario(int $usuarioId): array`
Lista las promociones solicitadas por un vendedor específico (excluyendo `eliminada`).

---

### `updateEstado(int $id, string $estado): bool`
```sql
UPDATE promociones SET estado = :estado WHERE id = :id
```
Usado para aprobar, rechazar y eliminar.

---

### `crearSolicitud(array $data): int`
Crea dos registros en una operación:

1. **INSERT en `promociones`** con `estado = 'pendiente'`.
2. **INSERT en `solicitud_promocion`** (tabla de trazabilidad del flujo de solicitud).

El segundo INSERT tiene compatibilidad hacia atrás: si la tabla `solicitud_promocion` no existe (base antigua), la excepción se silencia y no rompe el flujo principal.

```php
$data = [
    'producto_id'    => 3,           // null si es descuento global
    'nombre'         => 'Black Friday',
    'descripcion'    => 'Descuento especial',
    'descuento'      => 20.00,       // porcentaje
    'fecha_inicio'   => '2026-11-28',
    'fecha_fin'      => '2026-11-30',
    'solicitado_por' => 5,           // usuario_id del vendedor
];
$id = $promocionModel->crearSolicitud($data);
```

---

## Ciclo de vida de una promoción

```
vendedor solicita
     │
     ▼
estado = 'pendiente'
     │
     ├── admin aprueba ──► estado = 'activa'  ──► visible en POS y cliente
     ├── admin rechaza ──► estado = 'rechazada' (visible en listado admin)
     └── admin elimina ──► estado = 'eliminada' (oculta en todos los listados)
```

---

## Uso en el POS

`VentasController::getPromocionesActivas()` y `EmpleadoController::getPromocionesActivas()` consultan:
```sql
SELECT * FROM promociones
WHERE estado = 'activa'
  AND fecha_inicio <= CURDATE()
  AND fecha_fin >= CURDATE()
ORDER BY descuento DESC
```
Las promociones activas y vigentes se cargan en el Punto de Venta para que el vendedor aplique el descuento porcentual.

---

## Tablas involucradas

### `promociones`
| Columna | Descripción |
|---------|-------------|
| `id` | PK |
| `producto_id` | FK opcional → `productos` |
| `nombre` | Nombre de la promoción |
| `descuento` | Porcentaje (ej: `15.00` = 15%) |
| `fecha_inicio` | Inicio de vigencia |
| `fecha_fin` | Fin de vigencia |
| `estado` | `pendiente` / `activa` / `rechazada` / `eliminada` |
| `solicitado_por` | FK → `usuarios` |

### `solicitud_promocion`
| Columna | Descripción |
|---------|-------------|
| `id_solicitud` | PK |
| `id_empleado` | FK → `usuarios` (vendedor) |
| `id_promocion` | FK → `promociones` |
| `nombre_propuesta` | Nombre sugerido por el empleado |
| `descuento_propuesto` | Porcentaje sugerido |
| `estado` | `pendiente` / `aprobada` / `rechazada` |

---

## Errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| Promoción aprobada no aparece en el POS | Fechas fuera de rango | Verificar `fecha_inicio <= HOY <= fecha_fin` |
| `crearSolicitud` falla silenciosamente | La tabla `solicitud_promocion` no existe | Ejecutar la migración `20260905_tablas_operacion.sql` |
| Descuento del POS no reduce el total | El JS del POS no aplica el porcentaje | Verificar `pos.js` / `punto_venta.js`; el backend usa `$_POST['promo_pct']` |
| Vendedor ve promociones de otros empleados | `getPromocionesByUsuario()` filtra por `solicitado_por` | Verificar que se pasa `getUser()['id']` correctamente |
| Estado `eliminada` aparece en el listado admin | La query no tiene filtro `!= 'eliminada'` | `getPromociones()` ya excluye `eliminada`; verificar que la vista no hace otra consulta directa |
