# Módulo Usuarios — Gestión de Empleados (Admin)

## Descripción general

Permite al administrador gestionar los **usuarios con rol `vendedor`**: registrar nuevos empleados, activar/desactivar cuentas, recuperar credenciales y ver el perfil propio. La gestión de clientes se realiza de forma indirecta (se ven en el listado pero solo se pueden activar/desactivar).

- **Controlador:** `controllers/UsuariosController.php`
- **Modelo:** `models/Usuario.php`
- **Vistas:** `views/admin/usuarios/`, `views/compartido/perfil.php`
- **URL base:** `index.php?controller=usuarios`

---

## Acceso por rol

| Acción | admin | vendedor | cliente |
|--------|-------|----------|---------|
| Listar usuarios | ✅ | ❌ | ❌ |
| Crear empleado | ✅ | ❌ | ❌ |
| Cambiar estado | ✅ | ❌ | ❌ |
| Eliminar (desactivar) | ✅ | ❌ | ❌ |
| Recuperar credenciales | ✅ | ❌ | ❌ |
| Ver perfil propio | ✅ | ❌ | ❌ |

Todos los métodos usan `requireRole('admin')`.

---

## Métodos del controlador

### `lista(): void`
- **URL:** `?controller=usuarios&action=lista`
- Filtros: `?rol=admin|vendedor|cliente`, `?buscar=texto`.
- Retorna hasta 100 usuarios ordenados por `created_at DESC`.
- Vista: `views/admin/usuarios/lista.php`

---

### `nuevo(): void`
- **URL:** `?controller=usuarios&action=nuevo`
- Muestra el formulario de registro de empleado.
- Vista: `views/admin/usuarios/nuevo.php`

---

### `guardar(): void`
- **URL:** `?controller=usuarios&action=guardar` (POST)
- Valida CSRF.
- Validaciones:
  - Nombre mínimo 3 caracteres.
  - Email con formato válido (`FILTER_VALIDATE_EMAIL`).
  - Email no registrado (`findByEmail`).
  - Contraseña mínimo 8 caracteres.
  - Confirmación de contraseña igual.
- Crea el usuario con `rol = 'vendedor'` (forzado, no editable por el formulario).
- Redirige a `usuarios/lista` con mensaje de éxito.

---

### `cambiarEstado(): void`
- **URL:** `?controller=usuarios&action=cambiarEstado` (POST)
- Valida CSRF.
- Actualiza `activo = 1` o `activo = 0` según el valor del POST.
- Sirve tanto para activar como para desactivar usuarios de cualquier rol.

**POST esperado:**
```
csrf_token = TOKEN
id         = ID del usuario
activo     = 1 | 0
```

---

### `eliminar(): void`
- **URL:** `?controller=usuarios&action=eliminar` (POST)
- Valida CSRF.
- **Soft delete:** llama a `Usuario::delete(id)` que hace `UPDATE activo = 0`.
- No elimina el registro de la BD.

---

### `recuperarCredenciales(): void`
- **URL:** `?controller=usuarios&action=recuperarCredenciales` (POST)
- Valida CSRF.
- Solo aplica a usuarios con `rol = 'vendedor'`.
- Genera una contraseña temporal aleatoria de 8 caracteres (mezcla de letras y números).
- Actualiza la contraseña en la BD con `password_hash`.
- Muestra la nueva contraseña en un mensaje flash para que el admin la entregue al empleado.

> **Importante:** La contraseña temporal se muestra en texto plano en el flash. Debe entregarse al empleado de forma segura y cambiarse en el primer login.

---

### `perfil(): void`
- **URL:** `?controller=usuarios&action=perfil`
- Solo `admin`.
- Carga datos del admin autenticado desde la BD con `findById(session_usuario_id)`.
- Si no encuentra el usuario en BD, usa los datos de la sesión como fallback.
- Vista: `views/compartido/perfil.php`

---

## Modelo Usuario — métodos completos

| Método | Descripción |
|--------|-------------|
| `getAll(?string $rol, ?string $busqueda, int $limit, int $offset): array` | Lista con filtros; **no retorna la contraseña** |
| `findById(int $id): ?array` | Busca por ID; retorna `id, nombre, email, telefono, avatar, rol, activo` |
| `findByEmail(string $email): ?array` | Busca por email; retorna todos los campos incluida la contraseña (para autenticación) |
| `create(array $data): int` | Crea usuario; hashea la contraseña automáticamente |
| `update(int $id, array $data): bool` | Actualiza campos en `allowedFields`; si `$data['password']` existe, lo hashea |
| `delete(int $id): bool` | Soft delete: `activo = 0` |
| `hardDelete(int $id): bool` | Elimina el registro permanentemente (no expuesto en ningún controlador por defecto) |
| `authenticate(string $email, string $password): ?array` | Verifica credenciales y que `activo = 1` |
| `crearRecuperacion(int $usuarioId, int $minutos): string` | Genera token de recuperación con expiración |
| `obtenerRecuperacion(string $token): ?array` | Busca token vigente y no utilizado |
| `marcarRecuperacionUtilizada(int $id): bool` | Invalida el token |
| `countByRole(?string $rol): int` | Cuenta usuarios activos por rol |
| `count(): int` | Cuenta todos los usuarios |

---

## Tabla `usuarios` — campos clave

| Campo | Notas |
|-------|-------|
| `password` | Siempre almacenado con `password_hash(PASSWORD_DEFAULT)` |
| `activo` | El sistema usa soft delete; un usuario con `activo = 0` no puede loguearse |
| `rol` | `ENUM('admin','vendedor','cliente')` — controla acceso a portales |
| `avatar` | Solo el nombre del archivo; la URL se construye con `IMG_URL` o la ruta de uploads |

---

## Roles del sistema

| Rol | Portal | Puede gestionar |
|-----|--------|----------------|
| `admin` | `dashboard/index` | Todo: usuarios, productos, pedidos, reportes, configuración |
| `vendedor` | `empleado/dashboard` | POS, catálogo, stock, solicitudes de promociones |
| `cliente` | `cliente/dashboard` | Catálogo, carrito, pedidos propios, puntos de lealtad |

---

## Errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| `recuperarCredenciales` dice "No se puede gestionar" | El usuario seleccionado no es `vendedor` | La acción solo aplica a vendedores; para admins usar el flujo de recuperación del login |
| Contraseña temporal no funciona para el empleado | Se copió con espacios o caracteres invisibles | El admin debe copiar exactamente el texto del flash |
| Usuario desactivado sigue pudiendo loguearse | Sesión activa previa a la desactivación | La sesión persiste hasta que expire o el usuario haga logout; destruir la sesión manualmente si se requiere bloqueo inmediato |
| `getAll()` devuelve contraseñas | La query de `getAll()` excluye explícitamente la columna `password` | No se incluye `password` en el SELECT; es seguro |
| Email ya registrado al crear empleado | `findByEmail()` retorna un resultado | Mostrar el error al admin y sugerir otro email |
