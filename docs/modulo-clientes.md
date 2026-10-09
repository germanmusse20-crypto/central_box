# Módulo Clientes — Portal del Cliente

## Descripción general

Portal completo para usuarios con rol `cliente`. Agrupa el catálogo, carrito, pedidos, promociones y programa de puntos de lealtad en un único controlador orientado a la experiencia de compra.

- **Controlador:** `controllers/ClienteController.php`
- **Modelos usados:** `Carrito`, `Producto`, `Pedido`, `Categoria`, `Promocion`
- **Vistas:** `views/cliente/`
- **URL base:** `index.php?controller=cliente`

---

## Acceso por rol

| Acción | cliente | admin/vendedor | visitante (sin sesión) |
|--------|---------|----------------|------------------------|
| `dashboard` | ✅ | ❌ | ❌ |
| `catalogo` | ✅ | ✅ | ✅ |
| `producto` | ✅ | ✅ | ✅ |
| `carrito` y variantes de lectura/modificación | ✅ | ❌ | ✅ |
| `agregarCarrito` | ✅ | ❌ | ✅ |
| `actualizarCarrito` / `eliminarCarrito` / `vaciarCarrito` | ✅ | ❌ | ✅ |
| `checkout` / `confirmarPedido` | ✅ | ❌ | ❌ → redirige al login |
| `misOrders` / `detallePedido` | ✅ | ❌ | ❌ |
| `promociones` | ✅ | ❌ | ❌ |
| `puntosLealtad` | ✅ | ❌ | ❌ |

Los métodos que permiten visitantes usan `if (isLoggedIn()) { requireRole('cliente'); }` en lugar de `requireLogin()`.

---

## Métodos del controlador

### `dashboard(): void`
- **URL:** `?controller=cliente&action=dashboard`
- Carga: últimos 5 pedidos, conteo de pedidos, puntos de lealtad, métodos de pago disponibles.
- Vista: `views/cliente/dashboard.php`

---

### `catalogo(): void`
- Accesible sin sesión.
- Filtros: `?categoria=ID`, `?busqueda=texto`, `?page=N`.
- Paginación con `ITEMS_PER_PAGE`.
- Carga categorías activas para el menú lateral.
- Vista: `views/cliente/catalogo.php`
- El botón "Agregar al carrito" en la vista incluye `&_back=URL` para que al agregar el usuario vuelva al catálogo (con los filtros activos conservados).

---

### `producto(): void`
- **URL:** `?controller=cliente&action=producto&id=N`
- Accesible sin sesión.
- Muestra el detalle de un producto.
- Redirige a `catalogo` si no existe.
- Vista: `views/cliente/producto_detalle.php`
- El formulario "Agregar al carrito" incluye un campo oculto `_back` con la URL del detalle, para que al agregar el usuario vuelva al mismo producto.

---

### `carrito(): void`
- **URL:** `?controller=cliente&action=carrito`
- Accesible sin sesión. Muestra el carrito temporal de `$_SESSION['carrito']`.
- Vista: `views/cliente/carrito.php`

---

### `agregarCarrito(): void`
- Accesible sin sesión.
- Valida stock disponible.
- Agrega el producto al carrito vía `Carrito::add()`.
- Lee `$_REQUEST['_back']` y redirige ahí al terminar. Si no hay `_back`, redirige al catálogo.
- El usuario **se queda en la misma página** de donde vino.

---

### `actualizarCarrito(): void` (POST)
- Accesible sin sesión.
- Actualiza cantidad de un ítem.
- Llama a `Carrito::updateQuantity()`.

---

### `eliminarCarrito(): void`
- Accesible sin sesión.
- Elimina un producto del carrito.

---

### `vaciarCarrito(): void`
- Accesible sin sesión.
- Vacía completamente el carrito.

---

### `checkout(): void`
- **Requiere sesión.** Si el visitante intenta acceder:
  1. Guarda `$_SESSION['redirect_after_login'] = 'index.php?controller=cliente&action=checkout'`.
  2. Flash informativo: *"Tus productos están guardados"*.
  3. Redirige al login.
- Si ya está logueado, verifica carrito no vacío y carga métodos de pago.
- Vista: `views/carrito/checkout.php`

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
- Requiere sesión y rol `cliente`.
- Valida CSRF, items, método de pago y dirección de envío.
- Lee el campo `direccion_envio` del POST (igual que la vista `checkout.php`).
- Aplica descuento de lealtad (`$_SESSION['descuento_lealtad']`) y producto gratis (`$_SESSION['producto_gratis_lealtad']`) si están activos.
- Llama a `Pedido::crear()`.
- Vacía el carrito con `Carrito::clear()` y limpia las variables de lealtad de sesión.
- Redirige a `misOrders`.

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

`ClienteController` contiene sus **propias implementaciones** de carrito (`agregarCarrito`, `actualizarCarrito`, etc.) que son las principales para el portal del cliente. `CarritoController` expone las mismas acciones como rutas independientes (`controller=carrito`) y las usa el catálogo compartido (`views/compartido/productos/`).

La vista del checkout (`views/carrito/checkout.php`) es compartida por ambos controladores. El formulario POST apunta a `cliente&confirmarPedido` porque ese método tiene la lógica de descuento de lealtad y producto gratis.

---

## Errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| Puntos de lealtad en 0 aunque hay pedidos | Pedidos en estado `confirmado` (no `entregado`) | Los puntos solo cuentan pedidos `entregado`; cambiar el estado desde el panel admin |
| `detallePedido` muestra pedido de otro cliente | Falta verificación de propiedad | Agregar `if ($pedido['cliente_id'] !== getUser()['id'])` antes de renderizar |
| Descuento de lealtad no persiste | Sesión expira entre pasos del checkout | Aumentar `SESSION_LIFETIME` en `config.php` o usar BD para el descuento |
| Catálogo sin productos | No hay productos activos con stock > 0 | Crear productos desde el panel admin; verificar `activo = 1` |
