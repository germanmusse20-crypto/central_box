<?php
/**
 * ClienteController — Portal del cliente
 * UH-16 Registro | UH-17 Login (via AuthController)
 * UH-18 Catálogo | UH-19 Recuperar (via AuthController)
 * UH-20 Buscar   | UH-21 Filtrar categoría
 * UH-22 Agregar carrito | UH-23 Eliminar carrito | UH-24 Modificar cantidad
 * UH-25 Ver métodos de pago | UH-26 Seleccionar método de pago
 */

require_once MODELS_PATH . '/Producto.php';
require_once MODELS_PATH . '/Categoria.php';
require_once MODELS_PATH . '/Pedido.php';
require_once MODELS_PATH . '/Carrito.php';

class ClienteController
{
    private Producto  $productoModel;
    private Categoria $categoriaModel;
    private Pedido    $pedidoModel;
    private Carrito   $carritoModel;

    public function __construct()
    {
        $this->productoModel  = new Producto();
        $this->categoriaModel = new Categoria();
        $this->pedidoModel    = new Pedido();
        $this->carritoModel   = new Carrito();
    }

    /* ══════════════════════════════════════════
       DASHBOARD del cliente
       ══════════════════════════════════════════ */

    public function dashboard(): void
    {
        requireLogin();
        requireRole('cliente');

        $user      = getUser();
        $pageTitle = 'Mi Portal';
        $extraCss  = ['cliente.css'];

        // UH-22: cantidad en carrito
        $carritoCount = $this->carritoModel->getCount();
        $carritoTotal = $this->carritoModel->getTotal();

        // UH-18/20: últimas novedades
        $destacados = $this->productoModel->getAll(null, null, 4, 0, true);

        // Pedidos recientes del cliente
        $misPedidos = $this->pedidoModel->getByCliente($user['id'], 5);

        // Conteo total real de pedidos del cliente
        $totalPedidos = $this->pedidoModel->contarPorCliente($user['id']);

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/cliente/dashboard.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    /* ══════════════════════════════════════════
       UH-18 / UH-20 / UH-21 — Catálogo
       ══════════════════════════════════════════ */

    public function catalogo(): void
    {
        requireLogin();
        requireRole('cliente');

        $pageTitle = 'Catálogo';
        $extraCss  = ['cliente.css'];

        // UH-20: búsqueda | UH-21: filtro categoría
        $busqueda    = trim($_GET['busqueda']   ?? '');
        $categoriaId = (int)($_GET['categoria'] ?? 0) ?: null;
        $page        = max(1, (int)($_GET['page'] ?? 1));
        $offset      = ($page - 1) * ITEMS_PER_PAGE;

        // Validar campo vacío (UH-20 escenario 3)
        if (isset($_GET['busqueda']) && $busqueda === '' && isset($_GET['buscar'])) {
            setFlash('info', 'Ingresa un término de búsqueda para filtrar los productos.');
        }

        $productos   = $this->productoModel->getAll($categoriaId, $busqueda ?: null, ITEMS_PER_PAGE, $offset, true);
        $totalProds  = $this->productoModel->count($categoriaId, $busqueda ?: null, true);
        $categorias  = $this->categoriaModel->getAll(true);
        $totalPages  = (int)ceil($totalProds / ITEMS_PER_PAGE);
        $carritoCount= $this->carritoModel->getCount();

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/cliente/catalogo.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    /* ══════════════════════════════════════════
       Detalle de producto
       ══════════════════════════════════════════ */

    public function producto(): void
    {
        requireLogin();
        requireRole('cliente');

        $id      = (int)($_GET['id'] ?? 0);
        $producto= $this->productoModel->findById($id);

        if (!$producto || !$producto['activo']) {
            setFlash('error', 'Producto no encontrado.');
            redirect('index.php?controller=cliente&action=catalogo');
            return;
        }

        $pageTitle    = e($producto['nombre']);
        $extraCss     = ['cliente.css'];
        $carritoCount = $this->carritoModel->getCount();

        // Relacionados de la misma categoría
        $relacionados = $producto['categoria_id']
            ? $this->productoModel->getAll($producto['categoria_id'], null, 4, 0, true)
            : [];

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/cliente/producto_detalle.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    /* ══════════════════════════════════════════
       UH-22 / UH-23 / UH-24 — Carrito
       ══════════════════════════════════════════ */

    public function carrito(): void
    {
        requireLogin();
        requireRole('cliente');

        $pageTitle    = 'Mi Carrito';
        $extraCss     = ['cliente.css'];
        $items        = $this->carritoModel->getItems();
        $total        = $this->carritoModel->getTotal();
        $carritoCount = $this->carritoModel->getCount();

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/cliente/carrito.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function agregarCarrito(): void
    {
        requireLogin();
        requireRole('cliente');

        $productoId = (int)($_REQUEST['id'] ?? $_REQUEST['producto_id'] ?? 0);
        $cantidad   = max(1, (int)($_REQUEST['cantidad'] ?? 1));
        $producto   = $this->productoModel->findById($productoId);

        // UH-22 escenario 5: no autenticado → ya se maneja por requireLogin

        // UH-22 escenario 2: sin stock
        if (!$producto || !$producto['activo']) {
            setFlash('error', 'Producto no disponible.');
            redirect('index.php?controller=cliente&action=catalogo');
            return;
        }

        if ($producto['stock'] <= 0) {
            setFlash('error', '«' . $producto['nombre'] . '» no tiene stock disponible.');
            redirect('index.php?controller=cliente&action=catalogo');
            return;
        }

        // UH-22 escenario 3: cantidad mayor al stock
        $itemActual = $this->carritoModel->getItems()[$productoId] ?? null;
        $cantActual = $itemActual ? $itemActual['cantidad'] : 0;
        if ($cantActual + $cantidad > $producto['stock']) {
            setFlash('warning', 'No hay suficiente stock. Disponible: ' . $producto['stock'] . ' unidades.');
            redirect('index.php?controller=cliente&action=carrito');
            return;
        }

        // UH-22 escenario 4: producto duplicado → add() suma automáticamente
        $this->carritoModel->add($productoId, $cantidad, [
            'nombre' => $producto['nombre'],
            'precio' => $producto['precio'],
            'imagen' => $producto['imagen'] ?? null,
            'stock'  => $producto['stock'],
        ]);

        setFlash('success', '«' . $producto['nombre'] . '» agregado al carrito.');
        redirect('index.php?controller=cliente&action=carrito');
    }

    public function actualizarCarrito(): void
    {
        requireLogin();
        requireRole('cliente');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=cliente&action=carrito');
            return;
        }

        $productoId = (int)($_POST['producto_id'] ?? 0);
        $cantidad   = (int)($_POST['cantidad']    ?? 0);

        // UH-24 escenario 4: cantidad inválida
        if ($cantidad < 0) {
            setFlash('error', 'Cantidad inválida.');
            redirect('index.php?controller=cliente&action=carrito');
            return;
        }

        // UH-24 escenario 3: mayor al stock
        $producto = $this->productoModel->findById($productoId);
        if ($producto && $cantidad > $producto['stock']) {
            setFlash('warning', 'Solo hay ' . $producto['stock'] . ' unidades disponibles.');
            $cantidad = $producto['stock'];
        }

        $this->carritoModel->updateQuantity($productoId, $cantidad);
        redirect('index.php?controller=cliente&action=carrito');
    }

    public function eliminarCarrito(): void
    {
        requireLogin();
        requireRole('cliente');

        $productoId = (int)($_GET['id'] ?? 0);
        $this->carritoModel->remove($productoId);

        // UH-23 escenario 2: si carrito queda vacío se informa en la vista
        setFlash('success', 'Producto eliminado del carrito.');
        redirect('index.php?controller=cliente&action=carrito');
    }

    public function vaciarCarrito(): void
    {
        requireLogin();
        requireRole('cliente');

        $this->carritoModel->clear();
        setFlash('info', 'Carrito vaciado correctamente.');
        redirect('index.php?controller=cliente&action=carrito');
    }

    /* ══════════════════════════════════════════
       UH-25 / UH-26 — Checkout y métodos de pago
       ══════════════════════════════════════════ */

    public function checkout(): void
    {
        requireLogin();
        requireRole('cliente');

        if ($this->carritoModel->isEmpty()) {
            setFlash('error', 'Tu carrito está vacío. Agrega productos antes de continuar.');
            redirect('index.php?controller=cliente&action=catalogo');
            return;
        }

        $pageTitle    = 'Finalizar compra';
        $extraCss     = ['cliente.css'];
        $items        = $this->carritoModel->getItems();
        $total        = $this->carritoModel->getTotal();
        $carritoCount = $this->carritoModel->getCount();

        // UH-25: métodos de pago disponibles (hardcoded — los activa el admin)
        $metodosPago = $this->getMetodosPagoDisponibles();

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/cliente/checkout.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    /**
     * UH-25/UH-26 — Consulta de métodos de pago habilitados.
     * El cliente solo puede visualizarlos; la administración corresponde al admin.
     */
    public function metodosPago(): void
    {
        requireLogin();
        requireRole('cliente');

        $pageTitle = 'Métodos de pago';
        $extraCss = ['cliente.css'];
        $metodosPago = $this->getMetodosPagoDisponibles();
        $carritoCount = $this->carritoModel->getCount();

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/cliente/metodos_pago.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    /**
     * Consulta de promociones activas y vigentes para el cliente.
     */
    public function promociones(): void
    {
        requireLogin();
        requireRole('cliente');

        $pageTitle = 'Promociones';
        $extraCss = ['cliente.css'];
        $promociones = $this->getPromocionesActivasCliente();
        $carritoCount = $this->carritoModel->getCount();

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/cliente/promociones.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    /**
     * Consulta de puntos derivados de compras no canceladas y beneficios vigentes.
     */
    public function puntosLealtad(): void
    {
        requireLogin();
        requireRole('cliente');

        $pageTitle = 'Puntos de lealtad';
        $extraCss = ['cliente.css'];
        $usuarioId = (int)getUser()['id'];
        $totalCompras = 0.0;
        $comprasValidas = 0;

        try {
            $db = \Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT COUNT(*) AS compras, COALESCE(SUM(total), 0) AS total
                FROM pedidos WHERE cliente_id = :cliente_id AND estado != 'cancelado'");
            $stmt->execute([':cliente_id' => $usuarioId]);
            $resumen = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $comprasValidas = (int)($resumen['compras'] ?? 0);
            $totalCompras = (float)($resumen['total'] ?? 0);
        } catch (\Exception $e) {
            $comprasValidas = 0;
            $totalCompras = 0.0;
        }

        // La regla de lealtad es de 10 puntos por cada $100.000 en compras válidas.
        $puntos = (int)floor($totalCompras / 100000) * 10;
        $promociones = $this->getPromocionesActivasCliente();
        $carritoCount = $this->carritoModel->getCount();
        $descuentoLealtadActivo = (float) ($_SESSION['descuento_lealtad'] ?? 0) > 0;
        $premioGratisActivo = !empty($_SESSION['producto_gratis_lealtad']);

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/cliente/puntos_lealtad.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function activarDescuentoLealtad(): void
    {
        requireLogin();
        requireRole('cliente');
        if ($this->obtenerPuntosCliente() < 200) {
            setFlash('error', 'Necesitas 200 puntos para activar este beneficio.');
        } else {
            $_SESSION['descuento_lealtad'] = 50;
            setFlash('success', 'Descuento de lealtad activado para tu próxima compra.');
        }
        redirect('index.php?controller=cliente&action=puntosLealtad');
    }

    public function reclamarProductoGratis(): void
    {
        requireLogin();
        requireRole('cliente');
        if ($this->obtenerPuntosCliente() < 500) {
            setFlash('error', 'Necesitas 500 puntos para reclamar un producto gratis.');
            redirect('index.php?controller=cliente&action=puntosLealtad');
            return;
        }

        $disponibles = array_values(array_filter(
            $this->productoModel->getAll(null, null, 200, 0, true),
            static fn(array $producto): bool => (int) $producto['stock'] > 0
        ));
        if (!$disponibles) {
            setFlash('error', 'No hay productos disponibles para entregar como premio.');
            redirect('index.php?controller=cliente&action=puntosLealtad');
            return;
        }

        $premio = $disponibles[array_rand($disponibles)];
        $this->carritoModel->add((int) $premio['id'], 1, [
            'nombre' => $premio['nombre'] . ' (premio gratis)',
            'precio' => 0,
            'imagen' => $premio['imagen'] ?? null,
            'stock' => 1,
        ]);
        $_SESSION['producto_gratis_lealtad'] = (int) $premio['id'];
        setFlash('success', 'Se agregó al carrito tu producto gratis: ' . $premio['nombre'] . '.');
        redirect('index.php?controller=cliente&action=carrito');
    }

    private function obtenerPuntosCliente(): int
    {
        $stmt = \Database::getInstance()->getConnection()->prepare(
            "SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE cliente_id = :id AND estado != 'cancelado'"
        );
        $stmt->execute([':id' => (int) getUser()['id']]);
        return (int) floor((float) $stmt->fetchColumn() / 100000) * 10;
    }

    /**
     * Centro de ayuda y canales de soporte para el cliente.
     */
    public function ayudaSoporte(): void
    {
        requireLogin();
        requireRole('cliente');

        $pageTitle = 'Ayuda y soporte';
        $extraCss = ['cliente.css'];
        $carritoCount = $this->carritoModel->getCount();

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/cliente/ayuda_soporte.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function confirmarPedido(): void
    {
        requireLogin();
        requireRole('cliente');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=cliente&action=checkout');
            return;
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de seguridad inválido. Intenta de nuevo.');
            redirect('index.php?controller=cliente&action=checkout');
            return;
        }

        if ($this->carritoModel->isEmpty()) {
            setFlash('error', 'Tu carrito está vacío.');
            redirect('index.php?controller=cliente&action=carrito');
            return;
        }

        $metodoPago = trim($_POST['metodo_pago'] ?? '');
        $direccion  = trim($_POST['direccion']   ?? '');
        $notas      = trim($_POST['notas']       ?? '');

        // UH-26 escenario 2: método inválido
        $metodosValidos = ['efectivo', 'tarjeta', 'transferencia'];
        if (!in_array($metodoPago, $metodosValidos)) {
            setFlash('error', 'El método de pago seleccionado no está disponible.');
            redirect('index.php?controller=cliente&action=checkout');
            return;
        }

        if (empty($direccion)) {
            setFlash('error', 'La dirección de envío es obligatoria.');
            redirect('index.php?controller=cliente&action=checkout');
            return;
        }

        $items = $this->carritoModel->getItems();
        $pedidoItems = array_map(fn($item) => [
            'producto_id' => $item['producto_id'],
            'cantidad'    => $item['cantidad'],
            'precio'      => $item['precio'],
        ], array_values($items));

        try {
            $pedidoId = $this->pedidoModel->crear(getUser()['id'], $pedidoItems, [
                'metodo_pago' => $metodoPago,
                'notas'       => trim($direccion . ($notas ? ' | ' . $notas : '')),
                'descuento_pct' => (float) ($_SESSION['descuento_lealtad'] ?? 0),
                'producto_gratis_id' => (int) ($_SESSION['producto_gratis_lealtad'] ?? 0),
            ]);

            $this->carritoModel->clear();
            unset($_SESSION['descuento_lealtad'], $_SESSION['producto_gratis_lealtad']);
            setFlash('success', "¡Pedido #$pedidoId realizado con éxito! Pronto nos pondremos en contacto contigo.");
            redirect('index.php?controller=cliente&action=misOrders');
        } catch (\Exception $e) {
            setFlash('error', 'Error al procesar el pedido: ' . $e->getMessage());
            redirect('index.php?controller=cliente&action=checkout');
        }
    }

    /* ══════════════════════════════════════════
       Mis pedidos (UH-34 cliente)
       ══════════════════════════════════════════ */

    public function misOrders(): void
    {
        requireLogin();
        requireRole('cliente');

        $pageTitle    = 'Mis Pedidos';
        $extraCss     = ['cliente.css'];
        $user         = getUser();
        $carritoCount = $this->carritoModel->getCount();

        $estado  = trim($_GET['estado'] ?? '') ?: null;
        $pedidos = $this->pedidoModel->getByCliente($user['id'], 100);

        if ($estado) {
            $pedidos = array_values(array_filter($pedidos, fn($p) => $p['estado'] === $estado));
        }

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/cliente/mis_pedidos.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    public function detallePedido(): void
    {
        requireLogin();
        requireRole('cliente');

        $id     = (int)($_GET['id'] ?? 0);
        $pedido = $this->pedidoModel->getById($id);

        if (!$pedido || (int)$pedido['cliente_id'] !== (int)getUser()['id']) {
            setFlash('error', 'Pedido no encontrado.');
            redirect('index.php?controller=cliente&action=misOrders');
            return;
        }

        $detalles     = $this->pedidoModel->getDetalles($id);
        $pageTitle    = 'Pedido #' . $id;
        $extraCss     = ['cliente.css'];
        $carritoCount = $this->carritoModel->getCount();

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/cliente/pedido_detalle.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    /* ══════════════════════════════════════════
       Helper privado: métodos de pago (UH-25)
       ══════════════════════════════════════════ */

    private function getMetodosPagoDisponibles(): array
    {
        // Intenta cargar desde BD, con fallback a lista estática
        try {
            $db   = \Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT * FROM metodos_pago WHERE estado = 'activo' ORDER BY nombre");
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [
                ['id' => 1, 'nombre' => 'Efectivo',              'icono' => 'banknote',      'descripcion' => 'Pago en efectivo al recibir'],
                ['id' => 2, 'nombre' => 'Tarjeta de débito/crédito','icono' => 'credit-card', 'descripcion' => 'Visa, Mastercard, American Express'],
                ['id' => 3, 'nombre' => 'Transferencia bancaria', 'icono' => 'building-2',    'descripcion' => 'Transferencia a cuenta bancaria'],
            ];
        }
    }

    private function getPromocionesActivasCliente(): array
    {
        try {
            $db = \Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT p.*, prod.nombre AS producto_nombre
                FROM promociones p
                LEFT JOIN productos prod ON p.producto_id = prod.id
                WHERE p.estado = 'activa'
                  AND p.fecha_inicio <= CURDATE()
                  AND p.fecha_fin >= CURDATE()
                ORDER BY p.descuento DESC, p.fecha_fin ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
}
