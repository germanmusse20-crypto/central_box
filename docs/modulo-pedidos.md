# Módulo Pedidos — Gestión de Órdenes

## Descripción general

Administra el ciclo de vida de los pedidos generados tanto desde el carrito del cliente como desde el Punto de Venta. Permite ver, filtrar y cambiar el estado de los pedidos.

- **Controlador:** `controllers/PedidoController.php`
- **Modelo:** `models/Pedido.php`
- **Vistas:** `views/compartido/pedidos/`
- **URL base:** `index.php?controller=pedidos`

---

## Acceso por rol

| Acción | admin | vendedor | cliente |
|--------|-------|----------|---------|
| Listado general | ✅ | ✅ | ❌ |
| Detalle de pedido | ✅ | ✅ | ✅ (solo los suyos*) |
| Cambiar estado | ✅ | ✅ | ❌ |

> *El controlador no filtra por propietario en `detalle`; el control de acceso por cliente se debe implementar a nivel de vista o verificando `cliente_id == getUser()['id']`.

---

## Métodos del controlador

### `index(): void`
- **URL:** `?controller=pedidos&action=index`
- Requiere `admin` o `vendedor`.
- Filtros por query string: `?estado=confirmado`, `?busqueda=texto`.
- La búsqueda opera sobre ID del pedido y nombre del cliente (filtrado en PHP sobre el array ya obtenido).
- Carga estadísticas con `Pedido::getEstadisticas()`.
- Vista: `views/compartido/pedidos/index.php`

---

### `detalle(): void`
- **URL:** `?controller=pedidos&action=detalle&id=N`
- Requiere sesión activa (roles: admin, vendedor, cliente).
- Carga el pedido con `getById()` y sus líneas con `getDetalles()`.
- Si el pedido no existe: flash error + redirect a `index`.
- Vista: `views/compartido/pedidos/detalle.php`

---

### `updateEstado(): void`
- **URL:** `?controller=pedidos&action=updateEstado&id=N&estado=ESTADO`
- Requiere `admin` o `vendedor`.
- Estados válidos definidos en la BD: `confirmado`, `entregado`, `cancelado`.
- Redirige al listado con mensaje de resultado.

---

### Atajos de estado

```php
confirmar() → updateEstado('confirmado')
entregar()  → updateEstado('entregado')
cancelar()  → updateEstado('cancelado')
```

- **URLs:**
  - `?controller=pedidos&action=confirmar&id=N`
  - `?controller=pedidos&action=entregar&id=N`
  - `?controller=pedidos&action=cancelar&id=N`

---

## Modelo Pedido — métodos

### `crear(int $clienteId, array $items, array $datosExtra): int`
Método principal de creación de pedidos. Ejecuta una **transacción completa**:

1. Calcula subtotal por ítem (`cantidad × precio`).
2. Aplica descuento porcentual si `$datosExtra['descuento_pct']` existe.
3. Inserta el registro en `pedidos`.
4. Inserta cada línea en `detalle_pedidos`.
5. Descuenta stock de cada producto (`UPDATE productos SET stock = stock - cantidad`).
6. Llama a `registrarVentaNueva()` para registrar en la tabla `venta` (POS).
7. Hace `COMMIT` o `ROLLBACK` ante cualquier error.

```php
$items = [
    ['producto_id' => 1, 'cantidad' => 2, 'precio' => 15000],
    ['producto_id' => 3, 'cantidad' => 1, 'precio' => 50000],
];
$pedidoId = $pedidoModel->crear($clienteId, $items, [
    'metodo_pago'     => 'efectivo',
    'direccion_envio' => 'Calle 10 #5-20',
    'notas'           => 'Entregar en portería',
    'descuento_pct'   => 10, // 10% de descuento
]);
```

---

### `registrarVentaNueva()` (privado)
Registro en la tabla `venta` para trazabilidad del POS. Se invoca internamente desde `crear()`.

---

### `getAll(?string $estado, int $limit, int $offset): array`
Lista todos los pedidos con JOIN al nombre del cliente.

```php
$pedidos = $pedidoModel->getAll('confirmado', 50, 0);
```

---

### `getByCliente(int $clienteId, int $limit, int $offset): array`
Pedidos de un cliente específico, ordenados por fecha descendente.

---

### `getById(int $id): ?array`
Pedido con datos del cliente (JOIN a `usuarios`).

---

### `getDetalles(int $pedidoId): array`
Líneas del pedido con JOIN a `productos` (nombre, imagen).

---

### `updateEstado(int $id, string $estado): bool`
```sql
UPDATE pedidos SET estado = :estado WHERE id = :id
```

---

### `getEstadisticas(): array`
Retorna:
```php
[
    'total_ventas'  => float,   // SUM(total) donde estado != 'cancelado'
    'ventas_mes'    => float,   // SUM del mes actual
    'por_estado'    => array,   // COUNT agrupado por estado
    'recientes'     => array,   // Últimos 5 pedidos
]
```

---

### `getVentasDia(): float`
Total vendido en el día actual.

### `getProductosVendidosHoy(): int`
Suma de `cantidad` en `detalle_pedidos` de pedidos de hoy.

### `getUltimaVenta(): ?array`
Último pedido registrado.

### `contarPorCliente(int $clienteId): int`
Total de pedidos de un cliente.

### `count(?string $estado): int`
Total de pedidos (opcionalmente filtrados por estado).

---

## Estados del ciclo de vida

```
(creado) confirmado ──► entregado
                   └──► cancelado
```

| Estado | Descripción |
|--------|-------------|
| `confirmado` | Pedido recibido, en proceso |
| `entregado` | Pedido entregado al cliente |
| `cancelado` | Pedido anulado |

---

## Tabla de base de datos involucrada

```sql
-- Al crear un pedido:
INSERT INTO pedidos (cliente_id, total, estado, metodo_pago, ...)
INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario, subtotal)
UPDATE productos SET stock = stock - :cantidad WHERE id = :id
```

---

## Errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| Stock queda en negativo | No se valida stock antes de crear el pedido | El carrito valida stock en `CarritoController::agregar()`; verificar que no se bypasee |
| Pedido creado pero sin detalle | Transacción rollback parcial | Revisar el log de errores PHP; el `catch` en `crear()` relanza la excepción |
| `cliente_id` FK violation | El `$clienteId` no existe en `usuarios` | En ventas presenciales usar `getOrCreateClientePresencial()` |
| Estado no actualiza | ENUM no acepta el valor enviado | Solo `confirmado`, `entregado`, `cancelado` son válidos; verificar el valor por GET |
| Pedido visible para clientes ajenos | No hay verificación de propiedad en `detalle()` | Agregar `if ($pedido['cliente_id'] !== getUser()['id'] && !hasRole('admin','vendedor'))` |
