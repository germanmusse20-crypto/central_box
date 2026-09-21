# Módulo Clientes — Portal del Cliente

## Descripción general

Portal completo para usuarios con rol `cliente`. Agrupa el catálogo, carrito, pedidos, promociones y programa de puntos de lealtad en un único controlador orientado a la experiencia de compra.

- **Controlador:** `controllers/ClienteController.php`
- **Modelos usados:** `Carrito`, `Producto`, `Pedido`, `Categoria`, `Promocion`
- **Vistas:** `views/cliente/`
- **URL base:** `index.php?controller=cliente`

---

## Acceso por rol

Todos los métodos del controlador requieren rol `cliente`, salvo `catalogo` y `producto` que son públicos.

| Acción | cliente | admin/vendedor | visitante |
|--------|---------|----------------|-----------|
| `dashboard` | ✅ | ❌ | ❌ |
| `catalogo` | ✅ | ✅ | ✅ |
| `producto` | ✅ | ✅ | ✅ |
| `carrito` y variantes | ✅ | ❌ | ❌ |
| `checkout` / `confirmarPedido` | ✅ | ❌ | ❌ |
| `misOrders` / `detallePedido` | ✅ | ❌ | ❌ |
| `promociones` | ✅ | ❌ | ❌ |
| `puntosLealtad` | ✅ | ❌ | ❌ |

---

## Métodos del controlador

### `dashboard(): void`
- **URL:** `?controller=cliente&action=dashboard`
- Carga: últimos 5 pedidos, conteo de pedidos, puntos de lealtad, métodos de pago disponibles.
- Vista: `views/cliente/dashboard.php`

---

### `catalogo(): void`
- Filtros: `?categoria=ID`, `?busqueda=texto`, `?page=N`.
- Paginación con `ITEMS_PER_PAGE`.
- Carga categorías activas para el menú lateral.
- Vista: `views/cliente/catalogo.php`

---

### `producto(): void`
- **URL:** `?controller=cliente&action=producto&id=N`
- Muestra el detalle de un producto.
- Redirige a `catalogo` si no existe.
- Vista: `views/cliente/producto_detalle.php`

---

### `carrito(): void`
- **URL:** `?controller=cliente&action=carrito`
- Muestra el carrito actual con ítems y total.
- Vista: `views/cliente/carrito.php`

---

### `agregarCarrito(): void`
- Valida stock disponible.
- Agrega el producto al carrito vía `Carrito::add()`.
- Redirige a `carrito`.

---

### `actualizarCarrito(): void` (POST)
- Actualiza cantidad de un ítem.
- Llama a `Carrito::updateQuantity()`.

---

### `eliminarCarrito(): void`
- Elimina un producto del carrito.

---

### `vaciarCarrito(): void`
- Vacía completamente el carrito.

---

### `checkout(): void`
- Verifica carrito no vacío.
- Carga métodos de pago disponibles y activos.
- Vista: `views/cliente/carrito.php` (sección checkout) o formulario dedicado.

---

### `metodosPago(): void`
- **URL:** `?controller=cliente&action=metodosPago`
- Muestra los métodos de pago disponibles para el cliente.
- Llama a `getMetodosPagoDisponibles()`.
- Vista: `views/cliente/metodos_pago.php`

---

### `promociones(): void`
- **URL:** `?controller=cliente&action=promociones`
- Carga las promociones activas y vigentes (fecha).
- Llama a `getPromocionesActivasCliente()`.
- Vista: `views/cliente/promociones.php`

---

### `puntosLealtad(): void`
- **URL:** `?controller=cliente&action=puntosLealtad`
- Calcula puntos: `1 punto por cada $1.000 COP` en pedidos entregados.
- Carga historial de pedidos completados.
- Vista: `views/cliente/puntos_lealtad.php`

---

### `activarDescuentoLealtad(): void`
- Requiere un mínimo de puntos (umbral definido en el controlador).
- Guarda en sesión el descuento activado para la próxima compra.
- Redirige con mensaje de éxito.

---

### `reclamarProductoGratis(): void`
- Permite canjear puntos por un producto gratuito.
- Verifica saldo de puntos suficiente.
- Agrega el producto al carrito con precio `0`.

---

### `confirmarPedido(): void` (POST)
- Flujo idéntico al `CarritoController::confirmarPedido()`.
- Valida CSRF, items, método de pago.
- Llama a `Pedido::crear()`.
- Vacía el carrito y redirige al detalle del pedido.

---

### `misOrders(): void`
- **URL:** `?controller=cliente&action=misOrders`
- Lista todos los pedidos del cliente autenticado.
- Usa `Pedido::getByCliente(getUser()['id'])`.
- Vista: `views/cliente/mis_pedidos.php`

---

### `detallePedido(): void`
- **URL:** `?controller=cliente&action=detallePedido&id=N`
- Verifica que el pedido pertenece al cliente actual.
- Carga detalle con `Pedido::getDetalles()`.
- Vista: `views/cliente/pedido_detalle.php`

---

## Métodos privados

### `obtenerPuntosCliente(): int`
```
Puntos = SUM(total de pedidos entregados) / 1000
```
Consulta directa a `pedidos` filtrando por `cliente_id` y `estado = 'entregado'`.

### `getMetodosPagoDisponibles(): array`
Consulta `metodos_pago` donde `estado = 'activo'`.

### `getPromocionesActivasCliente(): array`
```sql
SELECT * FROM promociones
WHERE estado = 'activa'
  AND fecha_inicio <= CURDATE()
  AND fecha_fin >= CURDATE()
```

---

## Diferencia con CarritoController

`ClienteController` contiene sus **propias implementaciones** de carrito (`agregarCarrito`, `actualizarCarrito`, etc.) que son equivalentes a las de `CarritoController`. Ambos controladores coexisten; `ClienteController` es el portal unificado del cliente mientras que `CarritoController` expone las acciones del carrito como rutas independientes (`controller=carrito`).

---

## Errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| Puntos de lealtad en 0 aunque hay pedidos | Pedidos en estado `confirmado` (no `entregado`) | Los puntos solo cuentan pedidos `entregado`; cambiar el estado desde el panel admin |
| `detallePedido` muestra pedido de otro cliente | Falta verificación de propiedad | Agregar `if ($pedido['cliente_id'] !== getUser()['id'])` antes de renderizar |
| Descuento de lealtad no persiste | Sesión expira entre pasos del checkout | Aumentar `SESSION_LIFETIME` en `config.php` o usar BD para el descuento |
| Catálogo sin productos | No hay productos activos con stock > 0 | Crear productos desde el panel admin; verificar `activo = 1` |
