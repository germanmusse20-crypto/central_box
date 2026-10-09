<?php
/**
 * CarritoController — Carrito de compras (solo clientes)
 */

require_once MODELS_PATH . '/Carrito.php';
require_once MODELS_PATH . '/Producto.php';
require_once MODELS_PATH . '/Pedido.php';

class CarritoController
{
    private Carrito  $carritoModel;
    private Producto $productoModel;
    private Pedido   $pedidoModel;

    public function __construct()
    {
        $this->carritoModel  = new Carrito();
        $this->productoModel = new Producto();
        $this->pedidoModel   = new Pedido();
    }

    public function index(): void
    {
        if (isLoggedIn()) {
            requireRole('cliente');
        }

        $pageTitle    = 'Mi Carrito';
        $items        = $this->carritoModel->getItems();
        $total        = $this->carritoModel->getTotal();
        $carritoModel = $this->carritoModel;

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/carrito/index.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function agregar(): void
    {
        // No se requiere login: visitantes pueden agregar al carrito temporal en sesión
        if (isLoggedIn()) {
            requireRole('cliente');
        }

        $productoId = (int)($_GET['id'] ?? 0);
        $cantidad   = (int)($_GET['cantidad'] ?? 1);
        $producto   = $this->productoModel->findById($productoId);

        if (!$producto || $producto['stock'] < $cantidad) {
            setFlash('error', 'Producto no disponible o sin stock suficiente.');
            redirect('index.php?controller=productos&action=catalogo');
            return;
        }

        $this->carritoModel->add($productoId, $cantidad, [
            'nombre' => $producto['nombre'],
            'precio' => $producto['precio'],
            'imagen' => $producto['imagen'] ?? null,
            'stock'  => $producto['stock'],
        ]);

        setFlash('success', '«' . $producto['nombre'] . '» agregado al carrito.');
        $back = trim($_GET['_back'] ?? '');
        redirect($back ?: 'index.php?controller=carrito&action=index');
    }

    public function actualizar(): void
    {
        if (isLoggedIn()) {
            requireRole('cliente');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=carrito&action=index');
            return;
        }

        $productoId = (int)($_POST['producto_id'] ?? 0);
        $cantidad   = (int)($_POST['cantidad']    ?? 1);

        $this->carritoModel->updateQuantity($productoId, $cantidad);
        redirect('index.php?controller=carrito&action=index');
    }

    public function eliminar(): void
    {
        if (isLoggedIn()) {
            requireRole('cliente');
        }

        $productoId = (int)($_GET['id'] ?? 0);
        $this->carritoModel->remove($productoId);
        setFlash('success', 'Producto eliminado del carrito.');
        redirect('index.php?controller=carrito&action=index');
    }

    public function vaciar(): void
    {
        if (isLoggedIn()) {
            requireRole('cliente');
        }

        $this->carritoModel->clear();
        redirect('index.php?controller=carrito&action=index');
    }

    public function checkout(): void
    {
        if (!isLoggedIn()) {
            $_SESSION['redirect_after_login'] = 'index.php?controller=carrito&action=checkout';
            setFlash('info', 'Inicia sesión para finalizar tu compra. Tus productos están guardados.');
            redirect('index.php?controller=auth&action=login');
            return;
        }
        requireRole('cliente');

        if ($this->carritoModel->isEmpty()) {
            setFlash('error', 'El carrito está vacío.');
            redirect('index.php?controller=carrito&action=index');
            return;
        }

        $pageTitle = 'Finalizar compra';
        $extraCss  = ['cliente.css'];
        $items     = $this->carritoModel->getItems();
        $total     = $this->carritoModel->getTotal();

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/carrito/checkout.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function confirmarPedido(): void
    {
        requireLogin();
        requireRole('cliente');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=carrito&action=checkout');
            return;
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de seguridad inválido.');
            redirect('index.php?controller=carrito&action=checkout');
            return;
        }

        if ($this->carritoModel->isEmpty()) {
            setFlash('error', 'El carrito está vacío.');
            redirect('index.php?controller=carrito&action=index');
            return;
        }

        $items     = $this->carritoModel->getItems();
        $metodoPago= trim($_POST['metodo_pago']    ?? 'efectivo');
        $direccion = trim($_POST['direccion_envio'] ?? '');
        $notas     = trim($_POST['notas']           ?? '');

        $pedidoItems = array_map(fn($item) => [
            'producto_id' => $item['producto_id'],
            'cantidad'    => $item['cantidad'],
            'precio'      => $item['precio'],
        ], array_values($items));

        try {
            $pedidoId = $this->pedidoModel->crear(getUser()['id'], $pedidoItems, [
                'metodo_pago'    => $metodoPago,
                'direccion_envio'=> $direccion,
                'notas'          => $notas,
            ]);

            $this->carritoModel->clear();
            setFlash('success', "¡Pedido #$pedidoId realizado! Te contactaremos pronto.");
            redirect('index.php?controller=pedidos&action=detalle&id=' . $pedidoId);
        } catch (\Exception $e) {
            setFlash('error', 'Error al procesar el pedido: ' . $e->getMessage());
            redirect('index.php?controller=carrito&action=checkout');
        }
    }
}
