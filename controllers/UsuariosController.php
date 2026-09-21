<?php

require_once MODELS_PATH . '/Usuario.php';

class UsuariosController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    public function lista(): void
    {
        requireRole('admin');

        $pageTitle = 'Usuarios';
        $rol = trim($_GET['rol'] ?? '');
        $busqueda = trim($_GET['buscar'] ?? '');

        $usuarios = $this->usuarioModel->getAll(
            $rol !== '' ? $rol : null,
            $busqueda !== '' ? $busqueda : null,
            100,
            0
        );

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/admin/usuarios/lista.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function nuevo(): void
    {
        requireRole('admin');

        $pageTitle = 'Registrar empleado';

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/admin/usuarios/nuevo.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function guardar(): void
    {
        requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=usuarios&action=nuevo');
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de seguridad inválido.');
            redirect('index.php?controller=usuarios&action=nuevo');
        }

        $nombre   = trim($_POST['nombre'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';
        $telefono = trim($_POST['telefono'] ?? '');

        $errors = [];

        if (empty($nombre) || strlen($nombre) < 3) {
            $errors[] = 'El nombre debe tener al menos 3 caracteres.';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ingresa un email válido.';
        }

        if ($this->usuarioModel->findByEmail($email)) {
            $errors[] = 'Este email ya está registrado.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if ($password !== $confirm) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        if (!empty($errors)) {
            setFlash('error', implode(' ', $errors));
            redirect('index.php?controller=usuarios&action=nuevo');
        }

        $userId = $this->usuarioModel->create([
            'nombre'   => $nombre,
            'email'    => $email,
            'password' => $password,
            'rol'      => 'vendedor',
            'telefono' => $telefono,
        ]);

        if ($userId) {
            setFlash('success', 'Empleado registrado correctamente.');
            redirect('index.php?controller=usuarios&action=lista');
        }

        setFlash('error', 'No se pudo registrar al empleado.');
        redirect('index.php?controller=usuarios&action=nuevo');
    }

    public function cambiarEstado(): void
    {
        requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=usuarios&action=lista');
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de seguridad inválido.');
            redirect('index.php?controller=usuarios&action=lista');
        }

        $id = (int)($_POST['id'] ?? 0);
        $activo = (int)($_POST['activo'] ?? 0);

        if ($id <= 0) {
            setFlash('error', 'No se seleccionó un usuario válido.');
            redirect('index.php?controller=usuarios&action=lista');
        }

        $updated = $this->usuarioModel->update($id, ['activo' => $activo]);

        if ($updated) {
            setFlash('success', 'Estado del usuario actualizado correctamente.');
        } else {
            setFlash('error', 'No se pudo actualizar el estado del usuario.');
        }

        redirect('index.php?controller=usuarios&action=lista');
    }

    public function eliminar(): void
    {
        requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=usuarios&action=lista');
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de seguridad inválido.');
            redirect('index.php?controller=usuarios&action=lista');
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            setFlash('error', 'No se seleccionó un usuario válido.');
            redirect('index.php?controller=usuarios&action=lista');
        }

        $deleted = $this->usuarioModel->delete($id);

        if ($deleted) {
            setFlash('success', 'Usuario desactivado correctamente.');
        } else {
            setFlash('error', 'No se pudo desactivar el usuario.');
        }

        redirect('index.php?controller=usuarios&action=lista');
    }

    public function recuperarCredenciales(): void
    {
        requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=usuarios&action=lista');
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de seguridad inválido.');
            redirect('index.php?controller=usuarios&action=lista');
        }

        $id = (int)($_POST['id'] ?? 0);
        $usuario = $this->usuarioModel->findById($id);

        if (!$usuario || $usuario['rol'] !== 'vendedor') {
            setFlash('error', 'No se puede gestionar la cuenta de este usuario.');
            redirect('index.php?controller=usuarios&action=lista');
            return;
        }

        // Generar una contraseña temporal aleatoria
        $newPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        $updated = $this->usuarioModel->update($id, ['password' => $newPassword]);

        if ($updated) {
            setFlash('success', "Credenciales recuperadas exitosamente. La nueva contraseña para {$usuario['email']} es: {$newPassword}");
        } else {
            setFlash('error', 'Ocurrió un error al intentar recuperar las credenciales.');
        }

        redirect('index.php?controller=usuarios&action=lista');
    }

 public function perfil(): void
{
    requireRole('admin');

    $pageTitle = 'Mi perfil';

    $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);

    $usuario = null;

    if ($usuarioId > 0) {
        $usuario = $this->usuarioModel->findById($usuarioId);
    }

    if (!$usuario) {
        $usuario = [
            'nombre' => $_SESSION['nombre'] ?? 'Usuario',
            'email' => $_SESSION['email'] ?? 'No disponible',
            'telefono' => $_SESSION['telefono'] ?? 'No registrado',
            'rol' => $_SESSION['rol'] ?? 'admin',
            'activo' => 1
        ];
    }

    require_once VIEWS_PATH . '/Layouts/header.php';
    require_once VIEWS_PATH . '/compartido/perfil.php';
    require_once VIEWS_PATH . '/Layouts/footer.php';
    }
}