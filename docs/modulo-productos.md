# Módulo Productos — Catálogo y CRUD

## Descripción general

Gestiona el ciclo completo del catálogo de productos: creación, edición, visualización pública y eliminación. Incluye también la gestión de **categorías** como sub-módulo.

- **Controladores:** `controllers/ProductoController.php`, `controllers/CategoriaController.php`
- **Modelos:** `models/Producto.php`, `models/Categoria.php`
- **Vistas:** `views/compartido/productos/`, `views/admin/categorias/`
- **URL base:** `index.php?controller=productos` | `index.php?controller=categorias`

---

## Acceso por rol

| Acción | admin | vendedor | cliente | visitante |
|--------|-------|----------|---------|-----------|
| Ver catálogo (`catalogo`) | ✅ | ✅ | ✅ | ✅ |
| Ver detalle (`ver`) | ✅ | ✅ | ✅ | ✅ |
| Listar productos backoffice (`lista`) | ✅ | ✅ | ❌ | ❌ |
| Crear / editar producto | ✅ | ✅ | ❌ | ❌ |
| Eliminar producto | ✅ | ❌ | ❌ | ❌ |
| Gestionar categorías | ✅ | ❌ | ❌ | ❌ |

---

## ProductoController — métodos

### `catalogo(): void`
- **URL:** `?controller=productos&action=catalogo`
- Pública (no requiere sesión).
- Soporta filtros: `?categoria=ID`, `?busqueda=texto`, `?page=N`.
- Paginación con `ITEMS_PER_PAGE` (12 por defecto).
- Vista: `views/compartido/productos/catalogo.php`

---

### `lista(): void`
- **URL:** `?controller=productos&action=lista`
- Solo admin y vendedor.
- Mismos filtros que `catalogo` + los productos inactivos son excluidos (`soloActivos = false` pasa `false`).
- También carga el top 5 de productos más vendidos (`getMasVendidos(5)`).
- Vista: `views/compartido/productos/lista.php`

---

### `ver(): void`
- **URL:** `?controller=productos&action=ver&id=N`
- Pública.
- Carga producto con join a categoría y vendedor.
- Si no existe: flash error + redirect a catálogo.
- Vista: `views/compartido/productos/ver.php`

---

### `crear(): void`
- **URL:** `?controller=productos&action=crear`
- Requiere `admin` o `vendedor`.
- Muestra formulario vacío con listado de categorías activas.
- Vista: `views/compartido/productos/form.php`

---

### `guardar(): void`
- **URL:** `?controller=productos&action=guardar` (POST)
- Requiere `admin` o `vendedor`.
- Valida CSRF, nombre no vacío y precio > 0.
- Llama a `guardarImagen()` para el upload seguro.
- Asigna automáticamente `vendedor_id = getUser()['id']`.
- Redirige a `lista` con flash de éxito.

---

### `editar(): void`
- **URL:** `?controller=productos&action=editar&id=N`
- Requiere `admin` o `vendedor`.
- Carga el producto existente y lo pasa a la vista de formulario.

---

### `actualizar(): void`
- **URL:** `?controller=productos&action=actualizar` (POST)
- Requiere `admin` o `vendedor`.
- Si se sube nueva imagen, elimina la anterior del disco.
- Actualiza solo los campos presentes en `$allowedFields`.

---

### `eliminar(): void`
- **URL:** `?controller=productos&action=eliminar&id=N`
- Solo `admin`.
- **Soft delete**: actualiza `activo = 0`, no borra el registro.

---

### `guardarImagen(): ?string` (privado)
Proceso de subida de imagen:
1. Verifica que haya archivo (`UPLOAD_ERR_NO_FILE`).
2. Verifica tamaño ≤ `MAX_FILE_SIZE` (5 MB).
3. Detecta MIME real con `finfo` (no confía en la extensión).
4. Verifica que sea una imagen real con `getimagesize()`.
5. Genera nombre único: `bin2hex(random_bytes(16)) + extensión`.
6. Mueve a `UPLOADS_PATH`.

---

## Modelo Producto — métodos

| Método | Descripción |
|--------|-------------|
| `getAll(?int $categoriaId, ?string $busqueda, int $limit, int $offset, bool $soloActivos): array` | Lista con filtros y paginación |
| `getAvailable(int $limit): array` | Productos activos con stock > 0 (para home) |
| `count(?int $categoriaId, ?string $busqueda, bool $soloActivos): int` | Total para paginación |
| `findById(int $id): ?array` | Busca por ID con join a categoría y vendedor |
| `create(array $data): int` | Inserta y retorna el `lastInsertId` |
| `update(int $id, array $data): bool` | Actualiza campos permitidos dinámicamente |
| `delete(int $id): bool` | Soft delete (`activo = 0`) |
| `getByCategoria(int $categoriaId, int $limit): array` | Productos de una categoría |
| `getByVendedor(int $vendedorId, int $limit, int $offset): array` | Productos de un vendedor |
| `updateStock(int $id, int $cantidad): bool` | Ajuste incremental de stock (`stock + cantidad`) |
| `getLowStock(): array` | Productos con `stock <= stock_minimo` |
| `getMasVendidos(int $limit): array` | Top N productos por `SUM(detalle_pedidos.cantidad)` |

---

## CategoriaController — métodos

### `lista(): void`
- **URL:** `?controller=categorias&action=lista`
- Solo `admin`.
- Lista todas las categorías con conteo de productos activos.

### `crear(): void`
- **URL:** `?controller=categorias&action=crear`
- Muestra formulario de nueva categoría.

### `guardar(): void` (POST)
- Valida CSRF y nombre obligatorio.
- Llama a `Categoria::create()`.

### `editar(): void`
- **URL:** `?controller=categorias&action=editar&id=N`
- Carga categoría existente y la pasa al formulario.

### `actualizar(): void` (POST)
- Actualiza nombre y descripción.

### `eliminar(): void`
- **URL:** `?controller=categorias&action=eliminar&id=N`
- Soft delete: `activa = 0`.

---

## Modelo Categoria — métodos

| Método | Descripción |
|--------|-------------|
| `getAll(bool $soloActivas): array` | Lista con subquery de total de productos |
| `findById(int $id): ?array` | Busca por ID |
| `create(array $data): int` | Inserta nueva categoría |
| `update(int $id, array $data): bool` | Actualiza campos permitidos |
| `delete(int $id): bool` | Soft delete |
| `count(): int` | Total de categorías activas |

---

## Relaciones del modelo

```
productos
  ├── categoria_id ──► categorias.id
  ├── vendedor_id  ──► usuarios.id
  ├── id           ◄── detalle_pedidos.producto_id
  ├── id           ◄── detalle_carrito.id_producto
  ├── id           ◄── movimientos_inventario.producto_id
  └── id           ◄── promociones.producto_id
```

---

## Errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| Imagen no se muestra en el catálogo | Ruta `UPLOADS_PATH` distinta entre Laragon y built-in server | Verificar `config.php`; en Laragon la imagen va a `img/productos/` |
| `move_uploaded_file` falla | El directorio destino no tiene permisos de escritura | `chmod 755` en la carpeta de uploads o verificar permisos en Windows |
| Producto eliminado sigue apareciendo en catálogo | `soloActivos = true` pero la caché del navegador | Limpiar caché; en BD verificar que `activo = 0` |
| `getimagesize()` retorna `false` en imagen válida | Archivo corrupto o MIME mal detectado | Verificar que `php_fileinfo` esté habilitada en `php.ini` |
| Stock queda negativo tras venta | `updateStock` usa incremento; con cantidad negativa puede bajar de 0 | El modelo `Inventario` ya protege con `if ($stockNuevo < 0) $stockNuevo = 0` en movimientos; usar ese flujo |
