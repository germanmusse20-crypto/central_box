<?php
/**
 * CategoriaController — Gestión de categorías
 */

require_once MODELS_PATH . '/Categoria.php';

class CategoriaController
{
    private Categoria $categoriaModel;

    public function __construct()
    {
        $this->categoriaModel = new Categoria();
    }

    public function lista(): void
    {
        requireLogin();
        requireRole('admin');

        $pageTitle  = 'Categorías';
        $categorias = $this->categoriaModel->getAll();

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/admin/categorias/lista.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function crear(): void
    {
        requireLogin();
        requireRole('admin');

        $pageTitle = 'Nueva Categoría';
        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/admin/categorias/form.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function guardar(): void
    {
        requireLogin();
        requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=categorias&action=lista');
            return;
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token inválido.');
            redirect('index.php?controller=categorias&action=crear');
            return;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        if (empty($nombre)) {
            setFlash('error', 'El nombre es obligatorio.');
            redirect('index.php?controller=categorias&action=crear');
            return;
        }

        $this->categoriaModel->create([
            'nombre'      => $nombre,
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'imagen'      => null,
        ]);

        setFlash('success', "Categoría «$nombre» creada.");
        redirect('index.php?controller=categorias&action=lista');
    }

    public function editar(): void
    {
        requireLogin();
        requireRole('admin');

        $id         = (int)($_GET['id'] ?? 0);
        $categoria  = $this->categoriaModel->findById($id);

        if (!$categoria) {
            setFlash('error', 'Categoría no encontrada.');
            redirect('index.php?controller=categorias&action=lista');
            return;
        }

        $pageTitle = 'Editar Categoría';
        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/admin/categorias/form.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function actualizar(): void
    {
        requireLogin();
        requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=categorias&action=lista');
            return;
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token inválido.');
            redirect('index.php?controller=categorias&action=lista');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $this->categoriaModel->update($id, [
            'nombre'      => trim($_POST['nombre']      ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
        ]);

        setFlash('success', 'Categoría actualizada.');
        redirect('index.php?controller=categorias&action=lista');
    }

    public function eliminar(): void
    {
        requireLogin();
        requireRole('admin');

        $id = (int)($_GET['id'] ?? 0);
        $this->categoriaModel->delete($id);
        setFlash('success', 'Categoría eliminada.');
        redirect('index.php?controller=categorias&action=lista');
    }
}
