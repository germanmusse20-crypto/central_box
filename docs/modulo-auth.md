# Módulo Auth — Autenticación de Clientes

## Descripción general

Gestiona el **registro, login y recuperación de contraseña** para usuarios con rol `cliente`. El acceso de vendedores tiene su propio flujo en el módulo `empleado`.

- **Controlador:** `controllers/AuthController.php`
- **Modelo:** `models/Usuario.php`
- **Vistas:** `views/auth/`
- **URL base:** `index.php?controller=auth`

---

## Acceso

| Rol | Permitido |
|-----|-----------|
| Visitante (sin sesión) | ✅ Login, registro, recuperación |
| `cliente` | ✅ (redirigido a su dashboard si ya está logueado) |
| `admin` / `vendedor` | ✅ (redirigidos a su dashboard si ya están logueados) |

---

## Métodos del controlador

### `login(): void`
- **URL:** `?controller=auth&action=login`
- Muestra el formulario de inicio de sesión del cliente.
- Vista: `views/auth/login.php`
- Si ya hay sesión activa redirige al portal correspondiente (lógica en `index.php`).

---

### `recuperarCliente(): void`
- **URL:** `?controller=auth&action=recuperarCliente`
- Muestra el formulario de solicitud de recuperación de contraseña.
- Vista: `views/auth/recuperar_cliente.php`

---

### `actualizarPasswordCliente(): void`
- **URL:** `?controller=auth&action=actualizarPasswordCliente`
- Procesa el formulario con el token de recuperación.
- Valida que el token sea vigente y no utilizado.
- Actualiza la contraseña con `password_hash()`.
- Marca el token como `utilizada = 1`.

---

### `doLogin(): void`
- **URL:** `?controller=auth&action=doLogin` (POST)
- Procesa las credenciales del formulario de login.
- Llama a `Usuario::authenticate(email, password)`.
- Si es válido, carga la sesión:
  ```php
  $_SESSION['usuario_id']     = $user['id'];
  $_SESSION['usuario_nombre'] = $user['nombre'];
  $_SESSION['usuario_email']  = $user['email'];
  $_SESSION['usuario_rol']    = $user['rol'];
  $_SESSION['usuario_avatar'] = $user['avatar'];
  ```
- **Fusión del carrito temporal:** si el usuario es `cliente` y había productos en `$_SESSION['carrito']` antes del login, los fusiona con el carrito guardado en BD. Si un producto se repite, suma las cantidades. Esto permite que un visitante que agrega productos antes de identificarse no los pierda al iniciar sesión.
- **Redirección inteligente:** si antes del login se guardó `$_SESSION['redirect_after_login']` (por ejemplo, al intentar ir al checkout sin sesión), redirige a esa URL y la borra de la sesión. Si no hay URL guardada, redirige según rol:
  - `vendedor` → `empleado/dashboard`
  - `admin` / `cliente` → `dashboard/index`
- Si falla: `setFlash('error', ...)` y vuelve al login.

---

### `registro(): void`
- **URL:** `?controller=auth&action=registro`
- Muestra el formulario de registro de cliente.
- Vista: `views/auth/registro.php`

---

### `registroAdmin(): void`
- **URL:** `?controller=auth&action=registroAdmin`
- Muestra formulario de registro con privilegios de admin.
- Vista: `views/auth/registro_admin.php`

---

### `doRegistro(): void`
- **URL:** `?controller=auth&action=doRegistro` (POST)
- Valida:
  - Nombre mínimo 3 caracteres.
  - Email con formato válido.
  - Email no registrado previamente.
  - Contraseña mínimo 8 caracteres.
  - Confirmación de contraseña coincidente.
- Crea el usuario con `rol = 'cliente'`.
- Inicia sesión automáticamente tras el registro.
- Redirige a `cliente/dashboard`.

---

### `doRegistroAdmin(): void`
- **URL:** `?controller=auth&action=doRegistroAdmin` (POST)
- Similar a `doRegistro` pero crea usuario con `rol = 'admin'`.
- Requiere validación CSRF.

---

### `logout(): void`
- **URL:** `?controller=auth&action=logout`
- Destruye la sesión completa.
- Redirige a `home`.

---

## Modelo Usuario — métodos usados en Auth

| Método | Descripción |
|--------|-------------|
| `findByEmail(string $email): ?array` | Busca un usuario por email |
| `authenticate(string $email, string $password): ?array` | Verifica credenciales y retorna el usuario o `null` |
| `create(array $data): int` | Inserta nuevo usuario; hashea la contraseña automáticamente |
| `crearRecuperacion(int $usuarioId, int $minutos): string` | Crea token de recuperación (TTL 30 min por defecto) |
| `obtenerRecuperacion(string $token): ?array` | Busca token vigente y no utilizado |
| `marcarRecuperacionUtilizada(int $id): bool` | Invalida el token tras su uso |
| `update(int $id, array $data): bool` | Actualiza la contraseña (vía campo `password`) |

---

## Flujo de recuperación de contraseña

```
1. Cliente visita: ?controller=auth&action=recuperarCliente
2. Ingresa su email
3. AuthController::actualizarPasswordCliente()
   ├── Busca usuario por email
   ├── Crea token: Usuario::crearRecuperacion()
   │   └── INSERT INTO recuperacion_contrasena (token, fecha_expiracion = NOW() + 30min)
   └── (En producción) enviaría email con enlace que contiene el token

4. Cliente sigue el enlace con ?token=XXXX
5. Controlador valida: Usuario::obtenerRecuperacion(token)
   ├── Verifica utilizada = 0
   └── Verifica fecha_expiracion >= NOW()
6. Si válido: actualiza password + marca token utilizada = 1
7. Redirige al login con mensaje de éxito
```

> **Nota:** El envío de email **no está implementado** en el código actual. El flujo genera el token pero el envío debe integrarse con un servicio de correo (phpmailer, sendmail, etc.).

---

## Errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| Contraseña incorrecta sin mensaje de error | `authenticate()` devuelve `null` cuando `activo = 0` | Verificar que el usuario esté activo en la BD |
| Token de recuperación expirado | Pasaron más de 30 minutos | Solicitar un nuevo token |
| `Duplicate entry` al registrarse | Email ya en uso | `findByEmail()` debe detectarlo antes del INSERT |
| Sesión no persiste | `session_start()` no se llama antes de escribir `$_SESSION` | `session_start()` está en `public/index.php`; no llamar de nuevo en el controlador |
| Login exitoso pero redirige al login otra vez | `$_SESSION['usuario_rol']` vacío | Verificar que `doLogin` guarda el campo `rol` en sesión |
