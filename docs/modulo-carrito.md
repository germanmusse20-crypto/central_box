# Módulo Carrito — Compras Online del Cliente

## Descripción general

Gestiona el carrito de compras de los clientes con **doble persistencia**: sesión PHP (respuesta inmediata) y base de datos (recuperación entre sesiones). Al finalizar la compra, convierte el carrito en un pedido.

- **Controlador:** `controllers/CarritoController.php`
- **Modelo:** `models/Carrito.php`
- **Modelos auxiliares:** `Producto`, `Pedido`
- **Vistas:** `views/carrito/`
- **URL base:** `index.php?controller=carrito`

---

## Acceso por rol

| Acción | cliente | admin/vendedor | visitante |
|--------|---------|----------------|-----------|
| Ver carrito | ✅ | ❌ | ❌ |
| Agregar producto | ✅ | ❌ | ❌ |
| Actualizar cantidad | ✅ | ❌ | ❌ |
| Eliminar producto | ✅ | ❌ | ❌ |
| Checkout | ✅ | ❌ | ❌ |
| Confirmar pedido | ✅ | ❌ | ❌ |

Todos los métodos aplican `requireRole('cliente')`.

---

## Métodos del controlador

### `index(): void`
- **URL:** `?controller=carrito&action=index`
- Carga los ítems del carrito y el total.
- Vista: `views/carrito/index.php`

---

### `agregar(): void`
- **URL:** `?controller=carrito&action=agregar&id=N&cantidad=N`
- Verifica que el producto exista y tenga stock suficiente.
- Llama a `Carrito::add()` con la información del producto.
- Redirige a `carrito/index` con mensaje de éxito.

**Validación:**
```php
if (!$producto || $producto['stock'] < $cantidad) {
    // flash error → redirect catalogo
}
```

---

### `actualizar(): void`
- **URL:** `?controller=carrito&action=actualizar` (POST)
- Actualiza la cantidad de un producto en el carrito.
- Si la cantidad es 0 o negativa, `Carrito::updateQuantity()` elimina el ítem.

---

### `eliminar(): void`
- **URL:** `?controller=carrito&action=eliminar&id=N`
- Elimina un producto específico del carrito.

---

### `vaciar(): void`
- **URL:** `?controller=carrito&action=vaciar`
- Vacía completamente el carrito (sesión + BD).

---

### `checkout(): void`
- **URL:** `?controller=carrito&action=checkout`
- Verifica que el carrito no esté vacío.
- Muestra el resumen de compra y el formulario de datos de entrega.
- Vista: `views/carrito/checkout.php`

---

### `confirmarPedido(): void`
- **URL:** `?controller=carrito&action=confirmarPedido` (POST)
- Flujo completo de confirmación:

```
1. Verifica CSRF
2. Verifica carrito no vacío
3. Extrae items, método de pago, dirección, notas
4. Construye array de items para Pedido::crear()
5. Pedido::crear(clienteId, items, datosExtra)
   ├── Inserta pedido + detalles
   └── Descuenta stock
6. Carrito::clear()    ← vacía sesión y BD
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
2. Si el `usuario_id` en sesión es distinto al `carrito_usuario_id` guardado, recarga el carrito desde la BD (`loadFromDatabase()`).
3. Esto permite recuperar el carrito si el usuario cambia de dispositivo o la sesión expira y vuelve a iniciar sesión.

### `add(int $productoId, int $cantidad, array $productoInfo): void`
- Si el producto ya está en el carrito, suma la cantidad.
- Si no existe, agrega un nuevo ítem con toda la info del producto.
- Llama a `persist()` para sincronizar con la BD.

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
`SUM(cantidad)` de todos los ítems.

### `clear(): void`
- Vacía `$_SESSION['carrito']`.
- Marca el registro en BD como `estado = 'vacio'`.

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
Transacción que:
1. Busca el carrito activo del cliente en BD.
2. Si no existe, crea uno nuevo (`INSERT INTO carrito`).
3. Borra todas las líneas actuales (`DELETE FROM detalle_carrito WHERE id_carrito`).
4. Reinserta todas las líneas de la sesión.

Esto garantiza consistencia: la BD siempre refleja el estado actual de la sesión.

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
