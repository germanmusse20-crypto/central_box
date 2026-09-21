<?php
/**
 * VentasController — Ventas online / Punto de venta
 */

require_once MODELS_PATH . '/Pedido.php';
require_once MODELS_PATH . '/Producto.php';
require_once MODELS_PATH . '/Usuario.php';

class VentasController
{
    private Pedido   $pedidoModel;
    private Producto $productoModel;
    private Usuario  $usuarioModel;

    public function __construct()
    {
        $this->pedidoModel   = new Pedido();
        $this->productoModel = new Producto();
        $this->usuarioModel  = new Usuario();
    }

    /**
     * Ventas Online — vista principal con estadísticas + tabla
     */
    public function online(): void
    {
        requireLogin();
        requireRole('admin', 'vendedor');

        $pageTitle = 'Ventas';
        $extraCss  = ['empleado.css', 'ventas.css'];

        // Filtro por estado
        $estado = trim($_GET['estado'] ?? '') ?: null;
        $desde = trim($_GET['desde'] ?? '');
        $hasta = trim($_GET['hasta'] ?? '');
        $busqueda = trim($_GET['busqueda'] ?? '');

        // Datos para la tabla
        $ventas = $this->pedidoModel->getAll($estado, 100, 0);

        if ($desde || $hasta) {
            $ventas = array_filter($ventas, function (array $venta) use ($desde, $hasta): bool {
                $fecha = strtotime($venta['created_at']);
                return (!$desde || $fecha >= strtotime($desde))
                    && (!$hasta || $fecha <= strtotime($hasta . ' 23:59:59'));
            });
            $ventas = array_values($ventas);
        }

        if ($busqueda) {
            $ventas = array_values(array_filter($ventas, static fn (array $venta): bool =>
                stripos($venta['cliente_nombre'] ?? '', $busqueda) !== false
                || stripos((string) $venta['id'], $busqueda) !== false
            ));
        }

        // Estadísticas para las cards superiores
        $stats = $this->pedidoModel->getEstadisticas();

        // Ventas del día
        $ventasDia = $this->pedidoModel->getVentasDia();

        // Clientes únicos atendidos
        $clientesAtendidos = $this->usuarioModel->countByRole('cliente');

        $db = \Database::getInstance()->getConnection();
        $totalProductos = (int) $db->query(
            'SELECT COUNT(*) FROM productos WHERE activo = 1'
        )->fetchColumn();
        $totalStock = (int) $db->query(
            'SELECT COALESCE(SUM(stock), 0) FROM productos WHERE activo = 1'
        )->fetchColumn();

        // La vista de historial es compartida, pero los enlaces deben conservar
        // el módulo actual para no devolver al usuario al portal de empleado.
        $historialController = 'ventas';
        $historialAction = 'online';
        $nuevaVentaController = 'ventas';
        $nuevaVentaAction = 'puntoVenta';
        $facturaController = 'ventas';
        $facturaAction = 'factura';
        $esVentasOnline = true;

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/empleado/historial.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    /**
     * Punto de Venta — formulario de nueva venta
     */
    public function puntoVenta(): void
    {
        requireLogin();
        requireRole('admin', 'vendedor');

        $pageTitle = 'Punto de Venta';
        $extraCss  = ['empleado.css', 'ventas.css'];

        $productos = $this->productoModel->getAll(null, null, 200, 0, true);
        $clientes  = $this->usuarioModel->getAll('cliente');

        // Stats del día para las cards superiores
        $stats       = $this->pedidoModel->getEstadisticas();
        $ventasHoy   = $this->pedidoModel->getVentasDia();
        $prodVendidos= $this->pedidoModel->getProductosVendidosHoy();
        $ticketProm  = $stats['total_pedidos'] > 0
                       ? round($stats['total_ventas'] / $stats['total_pedidos'], 2)
                       : 0;
        $ultimaVenta = $this->pedidoModel->getUltimaVenta();

        require_once MODELS_PATH . '/Categoria.php';
        $categoriaModel = new Categoria();
        $categorias = $categoriaModel->getAll(true);
        $promociones = $this->getPromocionesActivas();
        $guardarVentaUrl = BASE_URL . '/index.php?controller=ventas&action=guardarVenta';
        $historialUrl = BASE_URL . '/index.php?controller=ventas&action=online';

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/empleado/nueva_venta.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    /**
     * Guardar venta desde punto de venta (POST)
     */
    public function guardarVenta(): void
    {
        requireLogin();
        requireRole('admin', 'vendedor');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=ventas&action=online');
            return;
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de seguridad inválido.');
            redirect('index.php?controller=ventas&action=puntoVenta');
            return;
        }

        $clienteId  = (int) ($_POST['cliente_id'] ?? 0);
        $metodoPago = trim($_POST['metodo_pago'] ?? 'efectivo');
        $notas      = trim($_POST['notas'] ?? '');
        $descuento  = (float) ($_POST['promo_pct'] ?? 0);
        $itemsRaw   = $_POST['items'] ?? [];

        if (!in_array($metodoPago, ['efectivo', 'tarjeta', 'transferencia'], true)) {
            setFlash('error', 'Método de pago inválido.');
            redirect('index.php?controller=ventas&action=puntoVenta');
            return;
        }

        if (empty($itemsRaw)) {
            setFlash('error', 'Datos incompletos. Agrega al menos un producto.');
            redirect('index.php?controller=ventas&action=puntoVenta');
            return;
        }

        // Las ventas presenciales pueden registrarse sin cliente asociado.
        if (!$clienteId) {
            $clienteId = $this->getOrCreateClientePresencial();
        }

        $items = [];
        foreach ($itemsRaw as $item) {
            $items[] = [
                'producto_id' => (int)   $item['producto_id'],
                'cantidad'    => (int)   $item['cantidad'],
                'precio'      => (float) $item['precio'],
            ];
        }

        try {
            $pedidoId = $this->pedidoModel->crear($clienteId, $items, [
                'metodo_pago' => $metodoPago,
                'notas'       => $notas,
                'descuento_pct' => $descuento,
            ]);
            setFlash('success', "Venta #$pedidoId registrada correctamente.");
            redirect("index.php?controller=ventas&action=factura&id=$pedidoId");
        } catch (\Exception $e) {
            setFlash('error', 'Error al guardar la venta: ' . $e->getMessage());
            redirect('index.php?controller=ventas&action=puntoVenta');
        }
    }

    public function factura(): void
    {
        requireLogin();
        requireRole('admin', 'vendedor');

        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) {
            setFlash('error', 'Debe registrar una venta primero.');
            redirect('index.php?controller=ventas&action=online');
            return;
        }

        $pedido = $this->pedidoModel->getById($id);
        if (!$pedido) {
            setFlash('error', 'Venta no encontrada.');
            redirect('index.php?controller=ventas&action=online');
            return;
        }

        $detalles = $this->pedidoModel->getDetalles($id);
        $pageTitle = 'Factura #' . $id;
        $extraCss = ['empleado.css', 'ventas.css'];
        $nuevaVentaUrl = BASE_URL . '/index.php?controller=ventas&action=puntoVenta';
        $historialUrl = BASE_URL . '/index.php?controller=ventas&action=online';

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/empleado/factura.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    private function getPromocionesActivas(): array
    {
        try {
            $db   = \Database::getInstance()->getConnection();
            $stmt = $db->query("
                SELECT * FROM promociones
                                WHERE estado = 'activa'
                  AND fecha_inicio <= CURDATE()
                  AND fecha_fin    >= CURDATE()
                ORDER BY descuento DESC
            ");
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getOrCreateClientePresencial(): int
    {
        $email = 'presencial@central-box.com';
        $cliente = $this->usuarioModel->findByEmail($email);

        if ($cliente) {
            return (int) $cliente['id'];
        }

        return $this->usuarioModel->create([
            'nombre'   => 'Cliente Presencial',
            'email'    => $email,
            'password' => bin2hex(random_bytes(16)),
            'rol'      => 'cliente',
        ]);
    }
}
