<?php
/**
 * ProductoController — Gestión de productos
 */

require_once MODELS_PATH . '/Producto.php';
require_once MODELS_PATH . '/Categoria.php';

class ProductoController
{
    private Producto  $productoModel;
    private Categoria $categoriaModel;

    public function __construct()
    {
        $this->productoModel  = new Producto();
        $this->categoriaModel = new Categoria();
    }
    public function index(): void
    {
        // Si es admin o vendedor, lo manda a la lista administrativa
        if (isLoggedIn() && hasRole('admin', 'vendedor')) {
            $this->lista();
        } else {
            // Si es cliente o visitante, lo manda al catálogo
            $this->catalogo();
        }
    }

    public function lista(): void
    {
        requireLogin();
        requireRole('admin', 'vendedor');

        $pageTitle  = 'Productos';
        $busqueda   = trim($_GET['busqueda']    ?? '');
        $categoriaId= (int)($_GET['categoria']  ?? 0) ?: null;
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $offset     = ($page - 1) * ITEMS_PER_PAGE;

        $productos   = $this->productoModel->getAll($categoriaId, $busqueda ?: null, ITEMS_PER_PAGE, $offset, false);
        $total       = $this->productoModel->count($categoriaId, $busqueda ?: null, false);
        $categorias  = $this->categoriaModel->getAll(true);
        $totalPages  = (int)ceil($total / ITEMS_PER_PAGE);
        $topProductos= $this->productoModel->getMasVendidos(5);

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/compartido/productos/lista.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function catalogo(): void
    {
        $pageTitle  = 'Catálogo';
        $categoriaId= (int)($_GET['categoria'] ?? 0) ?: null;
        $busqueda   = trim($_GET['busqueda']   ?? '');
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $offset     = ($page - 1) * ITEMS_PER_PAGE;

        $productos  = $this->productoModel->getAll($categoriaId, $busqueda ?: null, ITEMS_PER_PAGE, $offset);
        $total      = $this->productoModel->count($categoriaId, $busqueda ?: null);
        $categorias = $this->categoriaModel->getAll(true);
        $totalPages = (int)ceil($total / ITEMS_PER_PAGE);

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/compartido/productos/catalogo.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function ver(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $producto = $this->productoModel->findById($id);

        if (!$producto) {
            setFlash('error', 'Producto no encontrado.');
            redirect('index.php?controller=productos&action=catalogo');
            return;
        }

        $pageTitle = e($producto['nombre']);
        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/compartido/productos/ver.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function crear(): void
    {
        requireLogin();
        requireRole('admin', 'vendedor');

        $pageTitle  = 'Nuevo Producto';
        $categorias = $this->categoriaModel->getAll(true);

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/compartido/productos/form.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function guardar(): void
    {
        requireLogin();
        requireRole('admin', 'vendedor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=productos&action=lista');
            return;
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token inválido.');
            redirect('index.php?controller=productos&action=crear');
            return;
        }

        $nombre     = trim($_POST['nombre']      ?? '');
        $precio     = (float)($_POST['precio']   ?? 0);
        $stock      = (int)($_POST['stock']      ?? 0);
        $catId      = (int)($_POST['categoria_id']?? 0) ?: null;
        $desc       = trim($_POST['descripcion'] ?? '');
        $stockMin   = (int)($_POST['stock_minimo']?? 5);

        if (empty($nombre) || $precio <= 0) {
            setFlash('error', 'Nombre y precio son obligatorios.');
            redirect('index.php?controller=productos&action=crear');
            return;
        }

        $imagen = $this->guardarImagen();

        $this->productoModel->create([
            'nombre'       => $nombre,
            'descripcion'  => $desc,
            'precio'       => $precio,
            'stock'        => $stock,
            'stock_minimo' => $stockMin,
            'imagen'       => $imagen,
            'categoria_id' => $catId,
            'vendedor_id'  => getUser()['id'],
        ]);

        setFlash('success', "Producto «$nombre» creado.");
        redirect('index.php?controller=productos&action=lista');
    }

    public function editar(): void
    {
        requireLogin();
        requireRole('admin', 'vendedor');

        $id      = (int)($_GET['id'] ?? 0);
        $producto= $this->productoModel->findById($id);

        if (!$producto) {
            setFlash('error', 'Producto no encontrado.');
            redirect('index.php?controller=productos&action=lista');
            return;
        }

        $pageTitle  = 'Editar Producto';
        $categorias = $this->categoriaModel->getAll(true);

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/compartido/productos/form.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function actualizar(): void
    {
        requireLogin();
        requireRole('admin', 'vendedor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=productos&action=lista');
            return;
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token inválido.');
            redirect('index.php?controller=productos&action=lista');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $producto = $this->productoModel->findById($id);
        if (!$producto) {
            setFlash('error', 'Producto no encontrado.');
            redirect('index.php?controller=productos&action=lista');
            return;
        }

        $imagen = $this->guardarImagen();
        $this->productoModel->update($id, [
            'nombre'       => trim($_POST['nombre']      ?? ''),
            'descripcion'  => trim($_POST['descripcion'] ?? ''),
            'precio'       => (float)($_POST['precio']   ?? 0),
            'stock'        => (int)($_POST['stock']      ?? 0),
            'stock_minimo' => (int)($_POST['stock_minimo']?? 5),
            'categoria_id' => (int)($_POST['categoria_id']?? 0) ?: null,
            ...($imagen ? ['imagen' => $imagen] : []),
        ]);

        if ($imagen && !empty($producto['imagen'])) {
            $anterior = UPLOADS_PATH . DIRECTORY_SEPARATOR . basename($producto['imagen']);
            if (is_file($anterior)) {
                @unlink($anterior);
            }
        }

        setFlash('success', 'Producto actualizado.');
        redirect('index.php?controller=productos&action=lista');
    }

    public function eliminar(): void
    {
        requireLogin();
        requireRole('admin');

        $id = (int)($_GET['id'] ?? 0);
        $this->productoModel->delete($id);
        setFlash('success', 'Producto eliminado.');
        redirect('index.php?controller=productos&action=lista');
    }

    private function guardarImagen(): ?string
    {
        if (empty($_FILES['imagen']) || $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $archivo = $_FILES['imagen'];
        if ($archivo['error'] !== UPLOAD_ERR_OK || $archivo['size'] > MAX_FILE_SIZE) {
            setFlash('error', 'La imagen es inválida o supera el tamaño máximo permitido.');
            redirect('index.php?controller=productos&action=lista');
        }

        $mimePermitido = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        if (!isset($mimePermitido[$mime]) || @getimagesize($archivo['tmp_name']) === false) {
            setFlash('error', 'Solo se permiten imágenes JPG, PNG, WEBP o GIF.');
            redirect('index.php?controller=productos&action=lista');
        }

        if (!is_dir(UPLOADS_PATH) && !mkdir(UPLOADS_PATH, 0755, true)) {
            setFlash('error', 'No se pudo preparar la carpeta de imágenes.');
            redirect('index.php?controller=productos&action=lista');
        }

        $nombre = bin2hex(random_bytes(16)) . '.' . $mimePermitido[$mime];
        if (!move_uploaded_file($archivo['tmp_name'], UPLOADS_PATH . DIRECTORY_SEPARATOR . $nombre)) {
            setFlash('error', 'No se pudo guardar la imagen.');
            redirect('index.php?controller=productos&action=lista');
        }

        return $nombre;
    }
}
