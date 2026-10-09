# Módulo Carrito — Compras Online del Cliente

## Descripción general

Gestiona el carrito de compras con **doble persistencia**: sesión PHP (respuesta inmediata) y base de datos (recuperación entre sesiones). Al finalizar la compra, convierte el carrito en un pedido.

Los productos se pueden agregar **sin iniciar sesión**. El login solo se pide al momento de pagar. Al iniciar sesión, el carrito temporal se fusiona con el carrito guardado en la cuenta del usuario.

- **Controlador:** `controllers/CarritoController.php`
- **Modelo:** `models/Carrito.php`
- **Modelos auxiliares:** `Producto`, `Pedido`
- **Vistas:** `views/carrito/`
- **URL base:** `index.php?controller=carrito`

---

## Acceso por rol

| Acción | cliente | admin/vendedor | visitante (sin sesión) |
|--------|---------|----------------|------------------------|
| Ver carrito | ✅ | ❌ | ✅ |
| Agregar producto | ✅ | ❌ | ✅ |
| Actualizar cantidad | ✅ | ❌ | ✅ |
| Eliminar producto | ✅ | ❌ | ✅ |
| Vaciar carrito | ✅ | ❌ | ✅ |
| Checkout (pagar) | ✅ | ❌ | ❌ → redirige al login |
| Confirmar pedido | ✅ | ❌ | ❌ |

Los métodos que permiten visitantes aplican `requireRole('cliente')` solo si ya hay sesión activa (`if (isLoggedIn()) { requireRole('cliente'); }`).

---

## Métodos del controlador

### `index(): void`
- **URL:** `?controller=carrito&action=index`
- Accesible sin sesión. Muestra el carrito temporal guardado en `$_SESSION['carrito']`.
- Carga los ítems, el total y la instancia del modelo (necesaria en la vista).
- Vista: `views/carrito/index.php`

---

### `agregar(): void`
- **URL:** `?controller=carrito&action=agregar&id=N&cantidad=N&_back=URL`
- Accesible sin sesión.
- Verifica que el producto exista y tenga stock suficiente.
- Llama a `Carrito::add()` con la información del producto.
- Al terminar, redirige a la URL indicada en `$_GET['_back']`. Si no se recibe `_back`, redirige al índice del carrito.
- Esto permite que el usuario **se quede en la misma página** (catálogo, detalle, etc.) al agregar un producto.

**Validación:**
```php
if (!$producto || $producto['stock'] < $cantidad) {
    // flash error → redirect catalogo
}
```

---

### `actualizar(): void`
- **URL:** `?controller=carrito&action=actualizar` (POST)
- Accesible sin sesión.
- Actualiza la cantidad de un producto en el carrito.
- Si la cantidad es 0 o negativa, `Carrito::updateQuantity()` elimina el ítem.

---

### `eliminar(): void`
- **URL:** `?controller=carrito&action=eliminar&id=N`
- Accesible sin sesión.
- Elimina un producto específico del carrito.

---

### `vaciar(): void`
- **URL:** `?controller=carrito&action=vaciar`
- Accesible sin sesión.
- Vacía completamente el carrito (sesión + BD si hay usuario).

---

### `checkout(): void`
- **URL:** `?controller=carrito&action=checkout`
- **Requiere sesión.** Si el usuario no está logueado:
  1. Guarda `$_SESSION['redirect_after_login'] = 'index.php?controller=carrito&action=checkout'`.
  2. Muestra flash informativo: *"Tus productos están guardados"*.
  3. Redirige al login.
- Si ya está logueado, verifica que el carrito no esté vacío y muestra el formulario de pago.
- Vista: `views/carrito/checkout.php`

---

### `confirmarPedido(): void`
- **URL:** `?controller=carrito&action=confirmarPedido` (POST)
- Requiere sesión y rol `cliente`.
- Flujo completo de confirmación:

```
1. Verifica CSRF
2. Verifica carrito no vacío
3. Extrae items, método de pago, dirección, notas
4. Construye array de items para Pedido::crear()
5. Pedido::crear(clienteId, items, datosExtra)
   ├── Inserta pedido + detalles
   └── Descuenta stock
6. Carrito::clear()    ← vacía sesión, BD y limpia carrito_usuario_id
7. setFlash('success', "Pedido #N realizado")
8. redirect → pedidos/detalle?id=N
```

**Datos del POST:**
```
csrf_token      = TOKEN
metodo_pago     = efectivo | tarjeta | transferencia
direccion_envio = Dirección de entrega
notas           = Notas opcionales
```

---

## Modelo Carrito — mecanismo de persistencia

### Flujo de inicialización (`init()`)
1. Si no existe `$_SESSION['carrito']`, lo inicializa como array vacío.
2. Si el `usuario_id` en sesión es distinto al `carrito_usuario_id` guardado, **fusiona** el carrito temporal con el carrito de la BD:
   - Los items de la BD son la base.
   - Los items temporales se suman encima: si el producto ya existe en BD se suman las cantidades, si no existe se agrega.
3. Esto permite recuperar el carrito si el usuario cambia de dispositivo o la sesión expira y vuelve a iniciar sesión, **sin perder los productos que agregó como visitante**.

### `add(int $productoId, int $cantidad, array $productoInfo): void`
- Si el producto ya está en el carrito, suma la cantidad.
- Si no existe, agrega un nuevo ítem con toda la info del producto.
- Llama a `persist()` para sincronizar con la BD (solo si hay usuario en sesión).

### `remove(int $productoId): void`
- Elimina el ítem de `$_SESSION['carrito']` y sincroniza.

### `updateQuantity(int $productoId, int $cantidad): void`
- Si `$cantidad <= 0`, llama a `remove()`.
- Si no, actualiza la cantidad y sincroniza.

### `getItems(): array`
Retorna el array completo del carrito desde sesión.

### `getTotal(): float`
`SUM(precio × cantidad)` iterando los ítems de sesión.

### `getCount(): int`
`SUM(cantidad)` de todos los ítems. También funciona para visitantes sin sesión.

### `clear(): void`
- Vacía `$_SESSION['carrito']`.
- Borra `$_SESSION['carrito_usuario_id']` para que el próximo ciclo de compra empiece limpio.
- Marca el registro en BD como `estado = 'vacio'` (si hay usuario).

### `isEmpty(): bool`
Retorna `true` si el carrito en sesión está vacío.

---

### `loadFromDatabase(int $usuarioId): array` (privado)
```sql
SELECT dc.id_producto, dc.cantidad, dc.precio_unitario, p.nombre, p.imagen, p.stock
FROM carrito c
INNER JOIN detalle_carrito dc ON dc.id_carrito = c.id_carrito
INNER JOIN productos p ON p.id = dc.id_producto
WHERE c.id_cliente = :cliente AND c.estado = 'activo'
```
Retorna el array de sesión anterior ante cualquier `PDOException` (seguridad anti-crash si las tablas no existen).

---

### `persist(): void` (privado)
Solo se ejecuta si hay un `usuario_id` en sesión (visitantes no persisten en BD). Transacción que:
1. Busca el carrito activo del cliente en BD.
2. Si no existe, crea uno nuevo (`INSERT INTO carrito`).
3. Borra todas las líneas actuales (`DELETE FROM detalle_carrito WHERE id_carrito`).
4. Reinserta todas las líneas de la sesión.

Esto garantiza consistencia: la BD siempre refleja el estado actual de la sesión.

---

## Contador del carrito en el header

El archivo `views/Layouts/header.php` muestra un ícono de carrito con el número de productos:
- Para clientes logueados: usa `Carrito::getCount()` con el carrito del usuario.
- Para visitantes sin sesión: también muestra el contador usando el carrito temporal de `$_SESSION['carrito']`.

El número se actualiza automáticamente en cada carga de página al agregar, modificar o eliminar productos.

---

## Flujo completo — visitante que compra

```
1. Visitante navega el catálogo (sin sesión)
2. Clic en "Agregar al carrito"
   → producto se guarda en $_SESSION['carrito']
   → usuario se queda en la misma página
   → contador del header aumenta
3. Puede seguir agregando, cambiar cantidades, quitar productos
4. Clic en "Finalizar compra"
   → checkout() detecta que no hay sesión
   → guarda redirect_after_login = URL del checkout
   → redirige al login con mensaje informativo
5. Usuario inicia sesión (AuthController::doLogin)
   → Carrito::init() fusiona carrito temporal con carrito en BD
   → redirige a la URL guardada en redirect_after_login (checkout)
6. Usuario completa el formulario de pago y confirma
   → Pedido::crear() crea el pedido y descuenta stock
   → Carrito::clear() vacía sesión y BD, limpia carrito_usuario_id
7. Usuario puede volver al catálogo y hacer otra compra desde cero
```

---

## Tablas involucradas

```
carrito          ← un registro por cliente activo
detalle_carrito  ← una fila por producto en el carrito
```

---

## Errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| Carrito vacío al volver a entrar | Tablas `carrito`/`detalle_carrito` no creadas | Ejecutar migraciones en `sql/migrations/` |
| Stock no valida al agregar | `$producto['stock']` llega como `string` desde la BD | La comparación `<` en PHP convierte automáticamente; si falla, verificar que la columna es `INT` en la BD |
| Producto duplicado en el carrito | Clave única `(id_carrito, id_producto)` en `detalle_carrito` | La lógica de `add()` suma cantidades; si el INSERT falla, la excepción se silencia y la sesión mantiene el estado |
| `confirmarPedido` falla sin mensaje | Excepción en `Pedido::crear()` | Activar `display_errors` para ver el mensaje; revisar stock y FK |
| Carrito no se vacía tras el pedido | `Carrito::clear()` no llega a ejecutarse | Está después del `if ($pedidoId)`; verificar que `crear()` retorna un ID válido |
| Visitante agrega producto y lo manda al login | `_back` no está en la URL del botón | Verificar que el href del botón en la vista incluye `&_back=URL` |
| Carrito temporal se pierde al iniciar sesión | `$_SESSION['carrito_usuario_id']` no estaba limpio | `Carrito::init()` detecta usuario nuevo y fusiona; si no fusiona, verificar que `clear()` limpió `carrito_usuario_id` en la compra anterior |
