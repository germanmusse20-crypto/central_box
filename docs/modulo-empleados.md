# Módulo Empleados — Portal del Vendedor

## Descripción general

Portal exclusivo para usuarios con rol `vendedor`. Tiene su propio sistema de login (independiente del login de clientes), gestión de perfil, POS, solicitud de promociones y consulta de stock.

- **Controlador:** `controllers/EmpleadoController.php`
- **Modelos usados:** `Usuario`, `Producto`, `Pedido`, `Inventario`, `Promocion`, `Categoria`
- **Vistas:** `views/empleado/`
- **URL base:** `index.php?controller=empleado`

---

## Acceso por rol

| Acción | vendedor | admin | cliente |
|--------|---------|-------|---------|
| Login empleado | ✅ (sin sesión) | ❌ | ❌ |
| Dashboard | ✅ | ❌ (→ `dashboard/index`) | ❌ |
| Perfil | ✅ | ❌ | ❌ |
| Catálogo | ✅ | ❌ | ❌ |
| Nueva venta / POS | ✅ | ❌ | ❌ |
| Historial ventas | ✅ | ❌ | ❌ |
| Stock | ✅ | ❌ | ❌ |
| Promociones (ver / solicitar) | ✅ | ❌ | ❌ |

---

## Métodos del controlador

### `login(): void`
- **URL:** `?controller=empleado&action=login`
- Muestra el formulario de login específico del empleado.
- Vista: `views/empleado/login.php`
- Si ya hay sesión activa: redirige a `empleado/dashboard` (manejado en `index.php`).

---

### `doLogin(): void`
- **URL:** `?controller=empleado&action=doLogin` (POST)
- Autentica con `Usuario::authenticate()`.
- Verifica que el rol sea `vendedor`.
- Si es admin intentando entrar por este portal: redirige a `dashboard/index`.
- Carga la sesión igual que `AuthController::doLogin()`.
- Incluye validación de **token de recuperación** en el flujo.

---

### `recuperar(): void`
- **URL:** `?controller=empleado&action=recuperar`
- Formulario de solicitud de nueva contraseña para empleados.
- Vista: `views/empleado/recuperar.php`

---

### `doRecuperar(): void` (POST)
- Busca el usuario por email y verifica que sea vendedor.
- Genera token con `Usuario::crearRecuperacion()`.
- Retorna el token para que el administrador lo entregue manualmente.

---

### `nuevaPassword(): void`
- **URL:** `?controller=empleado&action=nuevaPassword&token=XXXX`
- Valida el token con `Usuario::obtenerRecuperacion()`.
- Muestra formulario para ingresar nueva contraseña.
- En POST: actualiza contraseña y marca token como utilizado.

---

### `dashboard(): void`
- **URL:** `?controller=empleado&action=dashboard`
- Requiere `vendedor`.
- Estadísticas del empleado:
  - Ventas del día.
  - Productos vendidos hoy.
  - Ticket promedio.
  - Última venta registrada.
  - Historial reciente de pedidos.
- Vista: `views/empleado/dashboard.php`

---

### `perfil(): void`
- **URL:** `?controller=empleado&action=perfil`
- Carga datos del usuario desde la BD.
- Vista: `views/empleado/editar_perfil.php` (modo solo lectura).

---

### `editarPerfil(): void`
- **URL:** `?controller=empleado&action=editarPerfil`
- Muestra formulario editable.
- Vista: `views/empleado/editar_perfil.php`

---

### `actualizarPerfil(): void` (POST)
- Valida CSRF.
- Permite actualizar: nombre, teléfono, contraseña y avatar.
- Si se sube avatar: llama a `guardarAvatarEmpleado()`.
- Actualiza `$_SESSION` con los nuevos datos.

---

### `guardarAvatarEmpleado(): ?string` (privado)
- Similar a `ProductoController::guardarImagen()`.
- Destino: `EMPLOYEE_UPLOADS_PATH`.
- Valida MIME real, tamaño y genera nombre único.

---

### `catalogo(): void`
- **URL:** `?controller=empleado&action=catalogo`
- Catálogo de productos con filtros por categoría y búsqueda.
- Vista: `views/empleado/catalogo.php`

---

### `nuevaVenta(): void`
- **URL:** `?controller=empleado&action=nuevaVenta`
- Carga productos, clientes, categorías y promociones para el POS.
- Define URLs para guardar y ver historial apuntando al módulo `empleado`.
- Vista: `views/empleado/nueva_venta.php`

---

### `promociones(): void`
- **URL:** `?controller=empleado&action=promociones`
- Lista las promociones solicitadas por el empleado actual + las activas.
- Usa `Promocion::getPromocionesByUsuario(getUser()['id'])`.
- Vista: `views/empleado/promociones.php`

---

### `solicitarPromocion(): void`
- **URL:** `?controller=empleado&action=solicitarPromocion`
- Formulario para proponer una nueva promoción al admin.
- Vista: `views/empleado/solicitar_promocion.php`

---

### `guardarSolicitudPromocion(): void` (POST)
- Valida CSRF y campos obligatorios.
- Llama a `Promocion::crearSolicitud()`.
- Crea registro en `promociones` con `estado = 'pendiente'`.
- También crea registro en `solicitud_promocion`.

---

### `confirmarVenta(): void` (POST)
- Guarda la venta desde el POS del empleado.
- Lógica idéntica a `VentasController::guardarVenta()`.
- Llama a `Pedido::crear()` y redirige a la factura.

---

### `stock(): void`
- **URL:** `?controller=empleado&action=stock`
- Muestra el stock actual de todos los productos.
- Resalta productos con bajo stock.
- Vista: `views/empleado/stock.php`

---

### `factura(): void`
- **URL:** `?controller=empleado&action=factura&id=N`
- Muestra la factura de una venta específica.
- Vista: `views/empleado/factura.php`

---

### `historial(): void`
- **URL:** `?controller=empleado&action=historial`
- Historial completo de ventas con filtros de estado, fecha y búsqueda.
- Vista: `views/empleado/historial.php`

---

## Métodos privados

### `getPromocionesActivas(): array`
Igual que en `VentasController`: consulta promociones vigentes por fecha y estado `activa`.

### `getOrCreateClientePresencial(): int`
Igual que en `VentasController`: crea o reutiliza el usuario `presencial@central-box.com`.

---

## Diferencia clave con VentasController

`EmpleadoController` es el **portal completo del vendedor** incluyendo login, perfil y operación. `VentasController` es una **capa de acceso admin** a las mismas operaciones de venta. Ambos usan las mismas vistas pero con variables de contexto distintas para los enlaces internos.

---

## Errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| Vendedor redirigido al login de cliente | Accede a `?controller=auth&action=login` en vez de `?controller=empleado&action=login` | Usar siempre la URL correcta del portal de empleados |
| Token de recuperación no funciona | El admin usa `UsuariosController::recuperarCredenciales()` que genera contraseña aleatoria, no token de enlace | Verificar si se usa el flujo de token (`doRecuperar`) o el de contraseña temporal desde el admin |
| Avatar no se actualiza en el header | `$_SESSION['usuario_avatar']` no se actualiza tras `actualizarPerfil()` | Verificar que `actualizarPerfil()` actualiza `$_SESSION` después del UPDATE |
| `stock()` muestra productos de otros vendedores | No filtra por `vendedor_id` | Si se requiere filtrar, pasar `getUser()['id']` a `Producto::getByVendedor()` |
