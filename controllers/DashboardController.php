<?php
/**
 * DashboardController — Dashboard contextual por rol
 */

require_once MODELS_PATH . '/Usuario.php';
require_once MODELS_PATH . '/Producto.php';
require_once MODELS_PATH . '/Pedido.php';
require_once MODELS_PATH . '/Inventario.php';

class DashboardController
{
    public function index(): void
    {
        requireLogin();

        // El vendedor tiene su propio portal — redirigir
        if (hasRole('vendedor')) {
            redirect('index.php?controller=empleado&action=dashboard');
            return;
        }

        // El cliente tiene su propio portal — redirigir
        if (hasRole('cliente')) {
            redirect('index.php?controller=cliente&action=dashboard');
            return;
        }

        $user = getUser();
        $pageTitle = 'Dashboard';
        $extraCss = ['dashboard.css'];
        $extraJs  = ['dashboard.js'];

        $usuarioModel   = new Usuario();
        $productoModel  = new Producto();
        $pedidoModel    = new Pedido();
        $inventarioModel = new Inventario();

        $data = [];

        if (hasRole('admin')) {
            // Admin: estadísticas globales
            $estadisticas = $pedidoModel->getEstadisticas();
            $data = [
                'total_usuarios'  => $usuarioModel->count(),
                'total_productos' => $productoModel->count(),
                'total_pedidos'   => $pedidoModel->count(),
                'total_ventas'    => $estadisticas['total_ventas'],
                'ventas_mes'      => $estadisticas['ventas_mes'],
                'por_estado'      => $estadisticas['por_estado'],
                'pedidos_recientes' => $estadisticas['recientes'],
                'bajo_stock'      => $inventarioModel->getProductosBajoStock(),
                'resumen_inventario' => $inventarioModel->getResumen(),
            ];
        } elseif (hasRole('vendedor')) {
            // Vendedor: sus productos y ventas
            $misProductos = $productoModel->getByVendedor($user['id']);
            $data = [
                'mis_productos'   => $misProductos,
                'total_productos' => count($misProductos),
                'bajo_stock'      => $inventarioModel->getProductosBajoStock(),
            ];
        } else {
            // Cliente: mis pedidos
            $misPedidos = $pedidoModel->getByCliente($user['id'], 5);
            $data = [
                'mis_pedidos'    => $misPedidos,
                'total_pedidos'  => count($misPedidos),
            ];
        }

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/compartido/dashboard/index.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }
}
