<?php
/**
 * InventarioController — Control de stock y movimientos
 */

require_once MODELS_PATH . '/Inventario.php';
require_once MODELS_PATH . '/Producto.php';

class InventarioController
{
    private Inventario $inventarioModel;
    private Producto   $productoModel;

    public function __construct()
    {
        $this->inventarioModel = new Inventario();
        $this->productoModel   = new Producto();
    }

    public function index(): void
    {
        requireLogin();
        requireRole('admin', 'vendedor');

        $pageTitle = 'Inventario';

        $resumen     = $this->inventarioModel->getResumen();
        $bajoStock   = $this->inventarioModel->getProductosBajoStock();
        $movimientos = $this->inventarioModel->getMovimientos(null, null, 20);
        $productos   = $this->productoModel->getAll(null, null, 500, 0, false);

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/compartido/inventario/index.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function movimientos(): void
    {
        requireLogin();
        requireRole('admin', 'vendedor');

        $pageTitle  = 'Movimientos de Inventario';
        $productoId = (int)($_GET['producto'] ?? 0) ?: null;
        $tipo       = trim($_GET['tipo'] ?? '') ?: null;
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $limit      = 50;
        $offset     = ($page - 1) * $limit;

        $movimientos = $this->inventarioModel->getMovimientos($productoId, $tipo, $limit, $offset);
        $productos   = $this->productoModel->getAll(null, null, 200, 0, false);

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/compartido/inventario/movimientos.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function ajuste(): void
    {
        requireLogin();
        requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=inventario&action=index');
            return;
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token inválido.');
            redirect('index.php?controller=inventario&action=index');
            return;
        }

        $productoId = (int)($_POST['producto_id'] ?? 0);
        $tipo       = trim($_POST['tipo']         ?? '');
        $cantidad   = (int)($_POST['cantidad']     ?? 0);
        $motivo     = trim($_POST['motivo']        ?? '');

        if (!$productoId || !in_array($tipo, ['entrada','salida','ajuste']) || $cantidad <= 0) {
            setFlash('error', 'Datos inválidos para el ajuste.');
            redirect('index.php?controller=inventario&action=index');
            return;
        }

        try {
            $this->inventarioModel->registrarMovimiento(
                $productoId,
                getUser()['id'],
                $tipo,
                $cantidad,
                $motivo
            );
            setFlash('success', 'Movimiento de inventario registrado.');
        } catch (\Exception $e) {
            setFlash('error', 'Error al registrar movimiento: ' . $e->getMessage());
        }

        redirect('index.php?controller=inventario&action=index');
    }
}
