# Módulo Reportes — Estadísticas de Ventas

## Descripción general

Proporciona al administrador un panel de KPIs y reportes de ventas en distintos granularidades: diaria, mensual y anual. Incluye ranking de productos más vendidos con filtro por período.

- **Controlador:** `controllers/ReportesController.php`
- **Modelo:** `models/Reporte.php`
- **Vistas:** `views/admin/reportes/`
- **URL base:** `index.php?controller=reportes`

---

## Acceso por rol

| Acción | admin | vendedor | cliente |
|--------|-------|----------|---------|
| Ver reportes | ✅ | ❌ | ❌ |

---

## Métodos del controlador

### `index(): void`
- **URL:** `?controller=reportes&action=index`
- Solo `admin`.
- Carga todos los datos de reporte en una sola vista.
- Parámetro `?periodo=diario|semanal|mensual` para el ranking de productos (default: `mensual`).
- CSS extra: `reportes.css`
- Vista: `views/admin/reportes/index.php`

**Variables pasadas a la vista:**

| Variable | Tipo | Contenido |
|----------|------|-----------|
| `$totalVentas` | float | Suma acumulada de todos los pedidos no cancelados |
| `$ventasDiarias` | array | Ventas por día de los últimos 15 días |
| `$ventasMensuales` | array | Ventas por mes de los últimos 12 meses |
| `$ventasAnuales` | array | Ventas por año (últimos 5 años) |
| `$productosMasVendidos` | array | Top 10 productos del período seleccionado |
| `$kpis` | array | KPIs generales del negocio |

---

## Modelo Reporte — métodos

### `getTotalVentas(): float`
```sql
SELECT COALESCE(SUM(total), 0)
FROM pedidos
WHERE estado != 'cancelado'
```
Acumulado histórico total.

---

### `getVentasDiarias(int $dias = 30): array`
```sql
SELECT DATE(created_at) AS fecha,
       COALESCE(SUM(total), 0) AS total_ventas,
       COUNT(id) AS cantidad_pedidos
FROM pedidos
WHERE estado != 'cancelado'
  AND created_at >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
GROUP BY DATE(created_at)
ORDER BY fecha ASC
```
Retorna una fila por día. Si un día no tiene ventas, no aparece en el resultado (no hay relleno de días vacíos).

---

### `getVentasMensuales(): array`
```sql
SELECT DATE_FORMAT(created_at, '%Y-%m') AS mes,
       COALESCE(SUM(total), 0) AS total_ventas,
       COUNT(id) AS cantidad_pedidos
FROM pedidos
WHERE estado != 'cancelado'
  AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
GROUP BY DATE_FORMAT(created_at, '%Y-%m')
ORDER BY mes ASC
```
Útil para gráficas de tendencia mensual.

---

### `getVentasAnuales(): array`
```sql
SELECT YEAR(created_at) AS anio,
       COALESCE(SUM(total), 0) AS total_ventas,
       COUNT(id) AS cantidad_pedidos
FROM pedidos
WHERE estado != 'cancelado'
GROUP BY YEAR(created_at)
ORDER BY anio DESC
LIMIT 5
```

---

### `getProductosMasVendidos(int $limit = 10): array`
```sql
SELECT p.id, p.nombre, p.imagen, c.nombre AS categoria,
       COALESCE(SUM(dp.cantidad), 0) AS total_vendido,
       COALESCE(SUM(dp.subtotal), 0) AS ingresos
FROM productos p
LEFT JOIN categorias c ON p.categoria_id = c.id
INNER JOIN detalle_pedidos dp ON p.id = dp.producto_id
INNER JOIN pedidos ped ON dp.pedido_id = ped.id
WHERE ped.estado != 'cancelado'
GROUP BY p.id
ORDER BY total_vendido DESC
LIMIT :limit
```

---

### `getProductosMasVendidosPorPeriodo(string $periodo, int $limit = 10): array`
Igual que `getProductosMasVendidos` pero con filtro temporal:

| Período | Condición SQL |
|---------|--------------|
| `diario` | `DATE(ped.created_at) = CURDATE()` |
| `semanal` | `ped.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)` |
| `mensual` | `ped.created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')` |

---

### `getKPIs(): array`
```php
[
    'total_ventas'        => float,  // acumulado general
    'pedidos_completados' => int,    // COUNT WHERE estado = 'entregado'
    'pedidos_pendientes'  => int,    // COUNT WHERE estado = 'confirmado'
    'ticket_promedio'     => float,  // AVG(total) WHERE estado != 'cancelado'
]
```

---

## Estructura de datos de las respuestas

### `getVentasDiarias()` — formato de cada ítem
```php
[
    'fecha'            => '2026-09-01',
    'total_ventas'     => 450000.00,
    'cantidad_pedidos' => 12,
]
```

### `getVentasMensuales()` — formato de cada ítem
```php
[
    'mes'              => '2026-09',
    'total_ventas'     => 3200000.00,
    'cantidad_pedidos' => 87,
]
```

### `getProductosMasVendidosPorPeriodo()` — formato de cada ítem
```php
[
    'id'            => 3,
    'nombre'        => 'Audífonos Bluetooth',
    'imagen'        => 'abc123.webp',
    'categoria'     => 'Tecnología',
    'total_vendido' => 45,
    'ingresos'      => 2250000.00,
]
```

---

## Errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| Todos los reportes en 0 | Sin pedidos en BD o todos en estado `cancelado` | Crear pedidos de prueba o cambiar el estado de pedidos existentes |
| Gráfica de días vacíos | Los días sin ventas no aparecen en `getVentasDiarias()` | En el frontend, completar la serie de fechas con valores 0 entre huecos |
| `periodo` inválido muestra todos los datos | El `match` en `getProductosMasVendidosPorPeriodo` usa `default => '1=1'` | Validar el valor en el controlador (ya lo hace con `in_array`) |
| Ticket promedio muy bajo | Pedidos pequeños o muchos pedidos de prueba | Filtrar por rango de fechas o excluir pedidos de prueba del análisis |
| Productos sin ventas no aparecen en el ranking | El JOIN es `INNER JOIN detalle_pedidos` | Es el comportamiento esperado; solo aparecen productos que tienen al menos una venta |
