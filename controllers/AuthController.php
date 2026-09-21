<?php
/**
 * AuthController — Login, Registro, Logout
 */

require_once MODELS_PATH . '/Usuario.php';

class AuthController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Mostrar formulario de login
     */
    public function login(): void
    {
        require_once VIEWS_PATH . '/auth/login.php';
    }

    /**
     * Recuperación de contraseña exclusiva para cuentas cliente.
     */
    public function recuperarCliente(): void
    {
        $paso = !empty($_SESSION['cliente_recovery_user']) ? 2 : 1;
        require_once VIEWS_PATH . '/auth/recuperar_cliente.php';
    }

    /**
     * Solicitar recuperación o guardar la nueva contraseña del cliente.
     */
    public function actualizarPasswordCliente(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Solicitud inválida. Intenta de nuevo.');
            redirect('index.php?controller=auth&action=recuperarCliente');
            return;
        }

        $paso = (int)($_POST['paso'] ?? 1);

        if ($paso === 1) {
            $email = trim($_POST['email'] ?? '');
            $cliente = filter_var($email, FILTER_VALIDATE_EMAIL)
                ? $this->usuarioModel->findByEmail($email)
                : null;

            if (!$cliente || ($cliente['rol'] ?? '') !== 'cliente' || !(int)($cliente['activo'] ?? 0)) {
                setFlash('error', 'No encontramos una cuenta de cliente activa con ese correo.');
                redirect('index.php?controller=auth&action=recuperarCliente');
                return;
            }

            try {
                $token = $this->usuarioModel->crearRecuperacion((int)$cliente['id']);
            } catch (\PDOException $e) {
                setFlash('error', 'No fue posible iniciar la recuperación. Intenta de nuevo.');
                redirect('index.php?controller=auth&action=recuperarCliente');
                return;
            }

            $_SESSION['cliente_recovery_token'] = $token;
            setFlash('success', 'Correo verificado. Define una nueva contraseña.');
            redirect('index.php?controller=auth&action=recuperarCliente');
            return;
        }

        $token = (string)($_SESSION['cliente_recovery_token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmacion = $_POST['password_confirm'] ?? '';

        $recuperacion = $token !== '' ? $this->usuarioModel->obtenerRecuperacion($token) : null;
        if (!$recuperacion) {
            unset($_SESSION['cliente_recovery_token']);
            setFlash('error', 'La recuperación expiró. Verifica nuevamente tu correo.');
            redirect('index.php?controller=auth&action=recuperarCliente');
            return;
        }

        if (strlen($password) < 6 || $password !== $confirmacion) {
            setFlash('error', 'La contraseña debe tener al menos 6 caracteres y coincidir en ambos campos.');
            redirect('index.php?controller=auth&action=recuperarCliente');
            return;
        }

        $cliente = $this->usuarioModel->findById((int)$recuperacion['id_usuario']);
        if (!$cliente || ($cliente['rol'] ?? '') !== 'cliente' || !(int)($cliente['activo'] ?? 0)) {
            unset($_SESSION['cliente_recovery_token']);
            setFlash('error', 'La cuenta de cliente no está disponible.');
            redirect('index.php?controller=auth&action=login');
            return;
        }

        $this->usuarioModel->update((int)$recuperacion['id_usuario'], ['password' => $password]);
        $this->usuarioModel->marcarRecuperacionUtilizada((int)$recuperacion['id_recuperacion']);
        unset($_SESSION['cliente_recovery_token']);
        setFlash('success', 'Contraseña actualizada. Ya puedes iniciar sesión.');
        redirect('index.php?controller=auth&action=login');
    }

    /**
     * Procesar login
     */
    public function doLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=auth&action=login');
            return;
        }

        // Verificar CSRF
        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de seguridad invalido. Intenta de nuevo.');
            redirect('index.php?controller=auth&action=login');
            return;
        }

        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';

        if (empty($email) || empty($password)) {
            setFlash('error', 'Todos los campos son obligatorios.');
            redirect('index.php?controller=auth&action=login');
            return;
        }

        // Autenticar
        $user = $this->usuarioModel->authenticate($email, $password);

        if (!$user) {
            setFlash('error', 'Credenciales incorrectas o cuenta desactivada.');
            redirect('index.php?controller=auth&action=login');
            return;
        }

        // Escribir sesion
        $_SESSION['usuario_id']     = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_email']  = $user['email'];
        $_SESSION['usuario_rol']    = $user['rol'];
        $_SESSION['usuario_avatar'] = $user['avatar'] ?? null;

        setFlash('success', 'Bienvenido, ' . $user['nombre'] . '!');
        redirect('index.php?controller=dashboard&action=index');
    }

    /**
     * Mostrar formulario de registro
     */
    public function registro(): void
    {
        require_once VIEWS_PATH . '/auth/registro.php';
    }

    /**
     * Mostrar formulario de registro de administradores
     */
    public function registroAdmin(): void
    {
        require_once VIEWS_PATH . '/auth/registro_admin.php';
    }

    /**
     * Procesar registro de usuario (cliente)
     */
    public function doRegistro(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=auth&action=registro');
            return;
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de seguridad invalido.');
            redirect('index.php?controller=auth&action=registro');
            return;
        }

        $nombre   = trim($_POST['nombre']           ?? '');
        $email    = trim($_POST['email']            ?? '');
        $password = $_POST['password']              ?? '';
        $confirm  = $_POST['password_confirm']      ?? '';
        $telefono = trim($_POST['telefono']         ?? '');

        $errors = [];

        if (empty($nombre))           $errors[] = 'El nombre es obligatorio.';
        if (strlen($nombre) < 3)      $errors[] = 'El nombre debe tener al menos 3 caracteres.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Ingresa un email valido.';
        if (strlen($password) < 6)    $errors[] = 'La contrasena debe tener al menos 6 caracteres.';
        if ($password !== $confirm)   $errors[] = 'Las contrasenas no coinciden.';

        if ($this->usuarioModel->findByEmail($email)) {
            $errors[] = 'Este email ya esta registrado.';
        }

        if (!empty($errors)) {
            setFlash('error', implode(' ', $errors));
            redirect('index.php?controller=auth&action=registro');
            return;
        }

        $userId = $this->usuarioModel->create([
            'nombre'   => $nombre,
            'email'    => $email,
            'password' => $password,
            'rol'      => 'cliente',
            'telefono' => $telefono,
        ]);

        if ($userId) {
            setFlash('success', 'Cuenta creada exitosamente. Ahora puedes iniciar sesion.');
            redirect('index.php?controller=auth&action=login');
        } else {
            setFlash('error', 'Error al crear la cuenta. Intenta de nuevo.');
            redirect('index.php?controller=auth&action=registro');
        }
    }

    /**
     * Procesar registro de administrador
     */
    public function doRegistroAdmin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=auth&action=registroAdmin');
            return;
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de seguridad invalido.');
            redirect('index.php?controller=auth&action=registroAdmin');
            return;
        }

        $nombre   = trim($_POST['nombre']           ?? '');
        $email    = trim($_POST['email']            ?? '');
        $password = $_POST['password']              ?? '';
        $confirm  = $_POST['password_confirm']      ?? '';
        $telefono = trim($_POST['telefono']         ?? '');

        $errors = [];

        if (strlen($nombre) < 3)   $errors[] = 'El nombre debe tener al menos 3 caracteres.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Ingresa un email valido.';
        if (strlen($password) < 8) $errors[] = 'La contrasena debe tener al menos 8 caracteres.';
        if ($password !== $confirm) $errors[] = 'Las contrasenas no coinciden.';

        if ($this->usuarioModel->findByEmail($email)) {
            $errors[] = 'Este email ya esta registrado.';
        }

        if (!empty($errors)) {
            setFlash('error', implode(' ', $errors));
            redirect('index.php?controller=auth&action=registroAdmin');
            return;
        }

        $userId = $this->usuarioModel->create([
            'nombre'   => $nombre,
            'email'    => $email,
            'password' => $password,
            'rol'      => 'admin',
            'telefono' => $telefono,
        ]);

        if ($userId) {
            setFlash('success', 'Administrador creado exitosamente. Ahora puedes iniciar sesion.');
            redirect('index.php?controller=auth&action=login');
            return;
        }

        setFlash('error', 'Error al crear el administrador. Intenta de nuevo.');
        redirect('index.php?controller=auth&action=registroAdmin');
    }

    /**
     * Cerrar sesion
     */
    public function logout(): void
    {
        session_unset();
        session_destroy();
        session_start();
        setFlash('success', 'Has cerrado sesion correctamente.');
        redirect('index.php?controller=auth&action=login');
    }
}
