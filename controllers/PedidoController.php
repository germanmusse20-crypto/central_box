<?php
/**
 * PedidoController — Gestión de pedidos
 */

require_once MODELS_PATH . '/Pedido.php';
require_once MODELS_PATH . '/Usuario.php';

class PedidoController
{
    private Pedido  $pedidoModel;
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->pedidoModel  = new Pedido();
        $this->usuarioModel = new Usuario();
    }

    /**
     * Listado de pedidos con estadísticas
     */
    public function index(): void
    {
        requireLogin();
        requireRole('admin', 'vendedor');

        $pageTitle = 'Gestión de Pedidos';
        $extraCss  = ['pedidos.css'];

        $busqueda = trim($_GET['busqueda'] ?? '');
        $estado   = trim($_GET['estado']   ?? '') ?: null;

        // Todos los pedidos filtrados
        $pedidos = $this->pedidoModel->getAll($estado, 200, 0);

        // Filtro por búsqueda (nombre o ID)
        if ($busqueda !== '') {
            $pedidos = array_filter($pedidos, function ($p) use ($busqueda) {
                return stripos((string)$p['id'], $busqueda) !== false
                    || stripos($p['cliente_nombre'] ?? '', $busqueda) !== false;
            });
            $pedidos = array_values($pedidos);
        }

        // Estadísticas
        $stats = $this->pedidoModel->getEstadisticas();

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/compartido/pedidos/index.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    /**
     * Detalle de un pedido
     */
    public function detalle(): void
    {
        requireLogin();
        requireRole('admin', 'vendedor', 'cliente');

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            redirect('index.php?controller=pedidos&action=index');
            return;
        }

        $pedido   = $this->pedidoModel->getById($id);
        $detalles = $this->pedidoModel->getDetalles($id);

        if (!$pedido) {
            setFlash('error', 'Pedido no encontrado.');
            redirect('index.php?controller=pedidos&action=index');
            return;
        }

        $pageTitle = 'Pedido #' . $id;
        $extraCss  = ['pedidos.css'];

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/compartido/pedidos/detalle.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    /**
     * Actualizar estado de un pedido (AJAX o GET)
     */
    public function updateEstado(): void
    {
        requireLogin();
        requireRole('admin', 'vendedor');

        $id     = (int)($_GET['id']     ?? 0);
        $estado = trim($_GET['estado']  ?? '');

        if (!$id || !$estado) {
            setFlash('error', 'Datos inválidos.');
            redirect('index.php?controller=pedidos&action=index');
            return;
        }

        $ok = $this->pedidoModel->updateEstado($id, $estado);
        if ($ok) {
            setFlash('success', "Pedido #$id actualizado a «$estado».");
        } else {
            setFlash('error', 'No se pudo actualizar el estado.');
        }

        redirect('index.php?controller=pedidos&action=index');
    }

    /**
     * Atajos de estado
     */
    public function confirmar(): void  { $this->_cambiarEstado('confirmado'); }
    public function entregar(): void   { $this->_cambiarEstado('entregado'); }
    public function cancelar(): void   { $this->_cambiarEstado('cancelado'); }

    private function _cambiarEstado(string $nuevoEstado): void
    {
        requireLogin();
        requireRole('admin', 'vendedor');

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            redirect('index.php?controller=pedidos&action=index');
            return;
        }

        $ok = $this->pedidoModel->updateEstado($id, $nuevoEstado);
        if ($ok) {
            setFlash('success', "Pedido #$id → «$nuevoEstado».");
        } else {
            setFlash('error', 'No se pudo cambiar el estado.');
        }

        redirect('index.php?controller=pedidos&action=index');
    }
}
