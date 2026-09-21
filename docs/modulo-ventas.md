# Módulo Ventas — Punto de Venta e Historial

## Descripción general

Proporciona el **Punto de Venta (POS)** para registrar ventas presenciales y el **historial de ventas** para administradores y vendedores. Se diferencia del módulo `pedidos` en que está orientado a la operación en caja, mientras que `pedidos` es la vista de gestión.

- **Controlador:** `controllers/VentasController.php`
- **Modelos usados:** `Pedido`, `Producto`, `Usuario`, `Categoria`
- **Vistas:** `views/empleado/historial.php`, `views/empleado/nueva_venta.php`, `views/empleado/factura.php`
- **URL base:** `index.php?controller=ventas`

---

## Acceso por rol

| Acción | admin | vendedor | cliente |
|--------|-------|----------|---------|
| Historial de ventas | ✅ | ✅ | ❌ |
| Punto de Venta | ✅ | ✅ | ❌ |
| Guardar venta | ✅ | ✅ | ❌ |
| Ver factura | ✅ | ✅ | ❌ |

---

## Métodos del controlador

### `online(): void`
- **URL:** `?controller=ventas&action=online`
- Historial de todas las ventas con filtros:
  - `?estado=confirmado` — filtra por estado del pedido.
  - `?desde=YYYY-MM-DD&hasta=YYYY-MM-DD` — filtro por rango de fechas (en PHP, no en SQL).
  - `?busqueda=texto` — filtra por nombre de cliente o ID.
- Cards de resumen: total de ventas, ventas del día, clientes atendidos, total de stock.
- Vista: `views/empleado/historial.php` (compartida con el módulo empleado).
- Define variables de contexto para que los enlaces apunten al módulo `ventas`:
  ```php
  $historialController   = 'ventas';
  $nuevaVentaController  = 'ventas';
  $facturaController     = 'ventas';
  $esVentasOnline        = true;
  ```

---

### `puntoVenta(): void`
- **URL:** `?controller=ventas&action=puntoVenta`
- Carga todos los productos activos (hasta 200), clientes, categorías y promociones activas.
- Cards de estadísticas del día: ventas de hoy, productos vendidos, ticket promedio, última venta.
- Vista: `views/empleado/nueva_venta.php`
- Llama internamente a `getPromocionesActivas()` para aplicar descuentos.

---

### `guardarVenta(): void`
- **URL:** `?controller=ventas&action=guardarVenta` (POST)
- Requiere `admin` o `vendedor`.
- Valida CSRF.
- Valida método de pago: solo `efectivo`, `tarjeta`, `transferencia`.
- Valida que haya al menos un ítem en `$_POST['items']`.
- Si `cliente_id = 0` (venta sin cliente identificado), llama a `getOrCreateClientePresencial()`.
- Construye el array de ítems y llama a `Pedido::crear()`.
- En éxito: redirige a la factura generada.

**Estructura del POST esperado:**
```
POST /index.php?controller=ventas&action=guardarVenta
csrf_token     = TOKEN
cliente_id     = 5        (0 para cliente presencial anónimo)
metodo_pago    = efectivo
notas          = texto libre
promo_pct      = 10       (porcentaje de descuento, 0 si ninguno)
items[0][producto_id] = 3
items[0][cantidad]    = 2
items[0][precio]      = 15000
items[1][producto_id] = 7
items[1][cantidad]    = 1
items[1][precio]      = 8000
```

---

### `factura(): void`
- **URL:** `?controller=ventas&action=factura&id=N`
- Carga el pedido y su detalle para mostrar la factura imprimible.
- Si el ID no existe o es inválido: flash error + redirect.
- Vista: `views/empleado/factura.php`

---

## Métodos privados

### `getPromocionesActivas(): array`
Consulta directa a la BD:
```sql
SELECT * FROM promociones
WHERE estado = 'activa'
  AND fecha_inicio <= CURDATE()
  AND fecha_fin    >= CURDATE()
ORDER BY descuento DESC
```
Retorna array vacío ante cualquier excepción (seguridad anti-crash).

---

### `getOrCreateClientePresencial(): int`
- Busca o crea el usuario `presencial@central-box.com` con rol `cliente`.
- Contraseña generada aleatoriamente y no utilizable (solo para asociar la venta).
- Permite registrar ventas presenciales sin datos del comprador.

---

## Diferencia con el módulo `empleado`

| Aspecto | `ventas` | `empleado` |
|---------|---------|------------|
| Rol principal | admin + vendedor | vendedor |
| Historial | Vista unificada de todas las ventas | Solo del empleado/portal propio |
| POS | Mismo formulario | Mismo formulario |
| Login | No tiene login propio | Tiene login propio con token de recuperación |

Comparten las mismas vistas PHP (`historial.php`, `nueva_venta.php`, `factura.php`). La diferencia está en las variables `$historialController` / `$nuevaVentaController` que determinan a dónde apuntan los enlaces de la vista.

---

## Flujo de venta presencial

```
1. Vendedor abre: ?controller=ventas&action=puntoVenta
2. Selecciona productos, cantidades y cliente
3. Aplica promoción (descuento %)
4. Envía POST a: ?controller=ventas&action=guardarVenta
5. guardarVenta()
   ├── Verifica CSRF
   ├── Valida método de pago
   ├── Obtiene/crea cliente presencial si clienteId = 0
   ├── Pedido::crear(clienteId, items, [metodo_pago, notas, descuento_pct])
   │   ├── Inserta en pedidos
   │   ├── Inserta en detalle_pedidos
   │   └── Descuenta stock
   └── redirect → ventas/factura?id=N
6. Factura se muestra para impresión
```

---

## Errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| `items` llega vacío al POST | El JavaScript del POS no agregó ítems al formulario | Revisar `pos.js` / `punto_venta.js`; verificar que el array `items[]` se construya correctamente |
| Descuento no se aplica | `promo_pct` llega como string vacío | El modelo convierte con `(float)`, un string vacío queda en `0` — verificar el input en JS |
| `cliente_id` inválido y falla la FK | El ID enviado no existe en `usuarios` | Usar `0` para disparar `getOrCreateClientePresencial()` |
| Factura muestra totales incorrectos | El descuento se aplica pero el front no lo refleja antes de enviar | Verificar que el cálculo en el JS del POS coincide con el del backend |
| Stock no se descuenta | Excepción silenciada en la transacción | Habilitar logs de PHP para ver la excepción; verificar que `PDO::ERRMODE_EXCEPTION` está activo |
