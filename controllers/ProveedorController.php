<?php
/**
 * ProveedorController
 * CRUD de proveedores — Solo accesible para admin
 */

require_once MODELS_PATH . '/Proveedor.php';

class ProveedorController
{
    private Proveedor $proveedorModel;

    public function __construct()
    {
        $this->proveedorModel = new Proveedor();
    }

    // Métodos que vas a implementar:
    // lista()         → muestra todos los proveedores
public function lista(): void
{
    requireRole('admin');
    $pageTitle = 'Proveedores';
    $busqueda  = trim($_GET['buscar'] ?? '');
    $proveedores = $this->proveedorModel->getAll(
        $busqueda !== '' ? $busqueda : null
    );
    require_once VIEWS_PATH . '/Layouts/header.php';
    require_once VIEWS_PATH . '/admin/proveedores/lista.php';
    require_once VIEWS_PATH . '/Layouts/footer.php';
}
    // nuevo()         → muestra el formulario vacío
public function nuevo(): void
{
    requireRole('admin');
    $pageTitle  = 'Nuevo Proveedor';
    $proveedor  = null; // null indica que es creación, no edición
    $accion     = BASE_URL . '/index.php?controller=proveedores&action=guardar';
    require_once VIEWS_PATH . '/Layouts/header.php';
    require_once VIEWS_PATH . '/admin/proveedores/form.php';
    require_once VIEWS_PATH . '/Layouts/footer.php';
}
    // guardar()       → procesa el POST de creación
public function guardar(): void
{
    requireRole('admin');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('index.php?controller=proveedores&action=nuevo');
        return;
    }
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token de seguridad inválido.');
        redirect('index.php?controller=proveedores&action=nuevo');
        return;
    }
    $nombre    = trim($_POST['nombre']    ?? '');
    $contacto  = trim($_POST['contacto']  ?? '');
    $email     = trim($_POST['email']     ?? '');
    $telefono  = trim($_POST['telefono']  ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    // Validación básica
    if (strlen($nombre) < 3) {
        setFlash('error', 'El nombre debe tener al menos 3 caracteres.');
        redirect('index.php?controller=proveedores&action=nuevo');
        return;
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'El correo electrónico no es válido.');
        redirect('index.php?controller=proveedores&action=nuevo');
        return;
    }
    $id = $this->proveedorModel->create([
        'nombre'    => $nombre,
        'contacto'  => $contacto  ?: null,
        'email'     => $email     ?: null,
        'telefono'  => $telefono  ?: null,
        'direccion' => $direccion ?: null,
    ]);
    if ($id) {
        setFlash('success', 'Proveedor registrado correctamente.');
        redirect('index.php?controller=proveedores&action=lista');
    } else {
        setFlash('error', 'No se pudo registrar el proveedor.');
        redirect('index.php?controller=proveedores&action=nuevo');
    }
}
    // editar()        → muestra el formulario con datos existentes
public function editar(): void
{
    requireRole('admin');
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        setFlash('error', 'Proveedor no válido.');
        redirect('index.php?controller=proveedores&action=lista');
        return;
    }
    $proveedor = $this->proveedorModel->findById($id);
    if (!$proveedor) {
        setFlash('error', 'Proveedor no encontrado.');
        redirect('index.php?controller=proveedores&action=lista');
        return;
    }
    $pageTitle = 'Editar Proveedor';
    $accion    = BASE_URL . '/index.php?controller=proveedores&action=actualizar';
    require_once VIEWS_PATH . '/Layouts/header.php';
    require_once VIEWS_PATH . '/admin/proveedores/form.php';
    require_once VIEWS_PATH . '/Layouts/footer.php';
}
    // actualizar()    → procesa el POST de edición
public function actualizar(): void
{
    requireRole('admin');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('index.php?controller=proveedores&action=lista');
        return;
    }
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token de seguridad inválido.');
        redirect('index.php?controller=proveedores&action=lista');
        return;
    }
    $id        = (int)($_POST['id'] ?? 0);
    $nombre    = trim($_POST['nombre']    ?? '');
    $contacto  = trim($_POST['contacto']  ?? '');
    $email     = trim($_POST['email']     ?? '');
    $telefono  = trim($_POST['telefono']  ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    if ($id <= 0) {
        setFlash('error', 'Proveedor no válido.');
        redirect('index.php?controller=proveedores&action=lista');
        return;
    }
    if (strlen($nombre) < 3) {
        setFlash('error', 'El nombre debe tener al menos 3 caracteres.');
        redirect('index.php?controller=proveedores&action=editar&id=' . $id);
        return;
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('error', 'El correo electrónico no es válido.');
        redirect('index.php?controller=proveedores&action=editar&id=' . $id);
        return;
    }
    $ok = $this->proveedorModel->update($id, [
        'nombre'    => $nombre,
        'contacto'  => $contacto  ?: null,
        'email'     => $email     ?: null,
        'telefono'  => $telefono  ?: null,
        'direccion' => $direccion ?: null,
    ]);
    if ($ok) {
        setFlash('success', 'Proveedor actualizado correctamente.');
    } else {
        setFlash('error', 'No se pudo actualizar el proveedor.');
    }
    redirect('index.php?controller=proveedores&action=lista');
}

    // cambiarEstado() → activa/desactiva vía POST
public function cambiarEstado(): void
{
    requireRole('admin');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect('index.php?controller=proveedores&action=lista');
        return;
    }
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Token de seguridad inválido.');
        redirect('index.php?controller=proveedores&action=lista');
        return;
    }
    $id     = (int)($_POST['id']     ?? 0);
    $activo = (int)($_POST['activo'] ?? 0);
    if ($id <= 0) {
        setFlash('error', 'Proveedor no válido.');
        redirect('index.php?controller=proveedores&action=lista');
        return;
    }
    $ok = $this->proveedorModel->cambiarEstado($id, $activo);
    setFlash($ok ? 'success' : 'error',
        $ok
            ? 'Estado actualizado correctamente.'
            : 'No se pudo cambiar el estado.'
    );
    redirect('index.php?controller=proveedores&action=lista');
}
}
