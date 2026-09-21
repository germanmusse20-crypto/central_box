<?php
/**
 * EmpleadoController
 * UH-27 Login | UH-28 Recuperar | UH-29 Catalogo | UH-30 Ventas |
 * UH-31 Stock  | UH-32 AutoStock | UH-33 Factura  | UH-34 Historial | UH-35 Promociones
 */

require_once MODELS_PATH . '/Usuario.php';
require_once MODELS_PATH . '/Producto.php';
require_once MODELS_PATH . '/Pedido.php';
require_once MODELS_PATH . '/Categoria.php';
require_once MODELS_PATH . '/Promocion.php';

class EmpleadoController
{
    private const MAX_INTENTOS   = 5;
    private const BLOQUEO_SEG    = 300; // 5 minutos

    private Usuario   $usuarioModel;
    private Producto  $productoModel;
    private Pedido    $pedidoModel;
    private Categoria $categoriaModel;
    private Promocion $promocionModel;

    public function __construct()
    {
        $this->usuarioModel   = new Usuario();
        $this->productoModel  = new Producto();
        $this->pedidoModel    = new Pedido();
        $this->categoriaModel = new Categoria();
        $this->promocionModel = new Promocion();
    }

    /* ═══════════════════════════════════════
       UH-27 — Login del empleado
       ═══════════════════════════════════════ */

    public function login(): void
    {
        if (isLoggedIn() && hasRole('vendedor', 'admin')) {
            redirect('index.php?controller=empleado&action=dashboard');
            return;
        }

        $pageTitle = 'Acceso Empleado';
        $extraCss  = ['empleado.css'];
        require_once VIEWS_PATH . '/empleado/login.php';
    }

    public function doLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=empleado&action=login');
            return;
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token de seguridad invalido.');
            redirect('index.php?controller=empleado&action=login');
            return;
        }

        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';

        // Validar campos vacíos
        if (empty($email) || empty($password)) {
            setFlash('error', 'campos_vacios');
            redirect('index.php?controller=empleado&action=login');
            return;
        }

        // Comprobar bloqueo temporal por IP
        $ipKey = 'emp_intentos_' . md5($_SERVER['REMOTE_ADDR'] ?? 'local');
        $intentos    = $_SESSION[$ipKey . '_count'] ?? 0;
        $bloqueadoAt = $_SESSION[$ipKey . '_time']  ?? 0;

        if ($intentos >= self::MAX_INTENTOS) {
            $elapsed = time() - $bloqueadoAt;
            if ($elapsed < self::BLOQUEO_SEG) {
                $restante = self::BLOQUEO_SEG - $elapsed;
                $_SESSION['emp_bloqueo_restante'] = $restante;
                setFlash('error', 'bloqueado');
                redirect('index.php?controller=empleado&action=login');
                return;
            }
            // Reiniciar tras tiempo de bloqueo
            $_SESSION[$ipKey . '_count'] = 0;
            unset($_SESSION['emp_bloqueo_restante']);
        }

        // Autenticar
        $user = $this->usuarioModel->authenticate($email, $password);

        if (!$user || !in_array($user['rol'], ['vendedor', 'admin'])) {
            $_SESSION[$ipKey . '_count'] = $intentos + 1;
            $_SESSION[$ipKey . '_time']  = time();
            $restantes = self::MAX_INTENTOS - ($intentos + 1);
            setFlash('error', $restantes > 0 ? "credenciales|$restantes" : 'bloqueado');
            redirect('index.php?controller=empleado&action=login');
            return;
        }

        // Resetear intentos y crear sesión
        $_SESSION[$ipKey . '_count'] = 0;
        session_regenerate_id(false);

        $_SESSION['usuario_id']     = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_email']  = $user['email'];
        $_SESSION['usuario_rol']    = $user['rol'];
        $_SESSION['usuario_avatar'] = $user['avatar'] ?? null;

        setFlash('success', 'Bienvenido, ' . $user['nombre'] . '!');
        redirect('index.php?controller=empleado&action=dashboard');
    }

    /* ═══════════════════════════════════════
       UH-28 — Recuperar contraseña
       ═══════════════════════════════════════ */

    public function recuperar(): void
    {
        $pageTitle = 'Recuperar contraseña';
        $extraCss  = ['empleado.css'];
        $paso = (int)($_GET['paso'] ?? 1);
        require_once VIEWS_PATH . '/empleado/recuperar.php';
    }

    public function doRecuperar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=empleado&action=recuperar');
            return;
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token invalido.');
            redirect('index.php?controller=empleado&action=recuperar');
            return;
        }

        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('error', 'email_invalido');
            redirect('index.php?controller=empleado&action=recuperar');
            return;
        }

        $user = $this->usuarioModel->findByEmail($email);

        if (!$user) {
            setFlash('error', 'email_no_existe');
            redirect('index.php?controller=empleado&action=recuperar');
            return;
        }

        // Simular envío de token (en producción enviar por email)
        $token = bin2hex(random_bytes(20));
        $_SESSION['emp_recovery_token'] = password_hash($token, PASSWORD_DEFAULT);
        $_SESSION['emp_recovery_email'] = $email;
        $_SESSION['emp_recovery_exp']   = time() + 1800; // 30 min

        setFlash('success', 'correo_enviado');
        redirect('index.php?controller=empleado&action=recuperar&paso=2');
    }

    public function nuevaPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=empleado&action=recuperar&paso=3');
            return;
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token invalido.');
            redirect('index.php?controller=empleado&action=recuperar&paso=3');
            return;
        }

        $pwd  = $_POST['password']         ?? '';
        $conf = $_POST['password_confirm'] ?? '';
        $errors = [];

        if (strlen($pwd) < 8)         $errors[] = 'min_8';
        if (!preg_match('/[A-Z]/', $pwd)) $errors[] = 'mayuscula';
        if (!preg_match('/[0-9]/', $pwd)) $errors[] = 'numero';
        if ($pwd !== $conf)           $errors[] = 'no_coincide';

        if ($errors) {
            setFlash('error', implode('|', $errors));
            redirect('index.php?controller=empleado&action=recuperar&paso=3');
            return;
        }

        $email = $_SESSION['emp_recovery_email'] ?? null;
        if (!$email) {
            redirect('index.php?controller=empleado&action=recuperar');
            return;
        }

        $user = $this->usuarioModel->findByEmail($email);
        if ($user) {
            $this->usuarioModel->update($user['id'], ['password' => $pwd]);
        }

        unset($_SESSION['emp_recovery_token'], $_SESSION['emp_recovery_email'], $_SESSION['emp_recovery_exp']);
        setFlash('success', 'password_actualizada');
        redirect('index.php?controller=empleado&action=login');
    }

    /* ═══════════════════════════════════════
       Dashboard del empleado
       ═══════════════════════════════════════ */

    public function dashboard(): void
    {
        requireLogin();
        requireRole('vendedor', 'admin');

        $pageTitle = 'Panel Empleado';
        $extraCss  = ['empleado.css'];

        try {
            $stats = $this->pedidoModel->getEstadisticas();
        } catch (\Exception $e) {
            $stats = ['total_ventas' => 0, 'total_pedidos' => 0, 'ventas_mes' => 0, 'por_estado' => [], 'recientes' => []];
        }

        $ventasDia  = $this->pedidoModel->getVentasDia();
        $bajoStock  = [];
        try {
            require_once MODELS_PATH . '/Inventario.php';
            $inv       = new Inventario();
            $bajoStock = $inv->getProductosBajoStock();
        } catch (\Exception $e) {}

        require_once VIEWS_PATH . '/empleado/layouts/header.php';
        require_once VIEWS_PATH . '/empleado/dashboard.php';
        require_once VIEWS_PATH . '/empleado/layouts/footer.php';
    }

    public function perfil(): void
    {
        requireLogin();
        requireRole('vendedor', 'admin');

        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
        $usuario = $this->usuarioModel->findById($usuarioId);

        if (!$usuario) {
            setFlash('error', 'No se pudo cargar la información del perfil.');
            redirect('index.php?controller=empleado&action=dashboard');
            return;
        }

        $pageTitle = 'Mi Perfil';
        $extraCss = ['empleado.css'];
        $perfilReturnUrl = BASE_URL . '/index.php?controller=empleado&action=dashboard';
        $perfilEditUrl = BASE_URL . '/index.php?controller=empleado&action=editarPerfil';

        require_once VIEWS_PATH . '/empleado/layouts/header.php';
        require_once VIEWS_PATH . '/compartido/perfil.php';
        require_once VIEWS_PATH . '/empleado/layouts/footer.php';
    }

    public function editarPerfil(): void
    {
        requireLogin();
        requireRole('vendedor', 'admin');

        $usuario = $this->usuarioModel->findById((int) $_SESSION['usuario_id']);
        if (!$usuario) {
            setFlash('error', 'No se pudo cargar el perfil.');
            redirect('index.php?controller=empleado&action=dashboard');
            return;
        }

        $pageTitle = 'Editar Perfil';
        $extraCss = ['empleado.css'];
        $perfilReturnUrl = BASE_URL . '/index.php?controller=empleado&action=perfil';

        require_once VIEWS_PATH . '/empleado/layouts/header.php';
        require_once VIEWS_PATH . '/empleado/editar_perfil.php';
        require_once VIEWS_PATH . '/empleado/layouts/footer.php';
    }

    public function actualizarPerfil(): void
    {
        requireLogin();
        requireRole('vendedor', 'admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Solicitud inválida.');
            redirect('index.php?controller=empleado&action=editarPerfil');
            return;
        }

        $id = (int) $_SESSION['usuario_id'];
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if (strlen($nombre) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('error', 'Ingresa un nombre válido y un correo electrónico válido.');
            redirect('index.php?controller=empleado&action=editarPerfil');
            return;
        }

        $existente = $this->usuarioModel->findByEmail($email);
        if ($existente && (int) $existente['id'] !== $id) {
            setFlash('error', 'Ese correo ya está registrado por otro usuario.');
            redirect('index.php?controller=empleado&action=editarPerfil');
            return;
        }

        $actual = $this->usuarioModel->findById($id);
        $avatar = $this->guardarAvatarEmpleado();
        $datos = ['nombre' => $nombre, 'email' => $email, 'telefono' => $telefono];
        if ($avatar) $datos['avatar'] = $avatar;

        $this->usuarioModel->update($id, $datos);
        $_SESSION['usuario_nombre'] = $nombre;
        $_SESSION['usuario_email'] = $email;
        if ($avatar) $_SESSION['usuario_avatar'] = $avatar;

        if ($avatar && !empty($actual['avatar'])) {
            $anterior = EMPLOYEE_UPLOADS_PATH . DIRECTORY_SEPARATOR . basename($actual['avatar']);
            if (is_file($anterior)) @unlink($anterior);
        }

        setFlash('success', 'Perfil actualizado correctamente.');
        redirect('index.php?controller=empleado&action=perfil');
    }

    private function guardarAvatarEmpleado(): ?string
    {
        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) return null;
        $archivo = $_FILES['avatar'];
        $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        if ($archivo['error'] !== UPLOAD_ERR_OK || $archivo['size'] > MAX_FILE_SIZE || !isset($permitidos[$mime]) || @getimagesize($archivo['tmp_name']) === false) {
            setFlash('error', 'La foto debe ser JPG, PNG o WEBP y no superar 5 MB.');
            redirect('index.php?controller=empleado&action=editarPerfil');
        }

        $directorio = EMPLOYEE_UPLOADS_PATH;
        if (!is_dir($directorio) && !mkdir($directorio, 0755, true)) {
            setFlash('error', 'No se pudo preparar la carpeta de fotos.');
            redirect('index.php?controller=empleado&action=editarPerfil');
        }

        $nombre = bin2hex(random_bytes(16)) . '.' . $permitidos[$mime];
        if (!move_uploaded_file($archivo['tmp_name'], $directorio . DIRECTORY_SEPARATOR . $nombre)) {
            setFlash('error', 'No se pudo guardar la foto.');
            redirect('index.php?controller=empleado&action=editarPerfil');
        }
        return $nombre;
    }

    /* ═══════════════════════════════════════
       UH-29 — Catálogo de productos
       ═══════════════════════════════════════ */

    public function catalogo(): void
    {
        requireLogin();
        requireRole('vendedor', 'admin');

        $pageTitle = 'Catálogo';
        $extraCss  = ['empleado.css'];

        $busqueda   = trim($_GET['busqueda']   ?? '');
        $categoriaId= (int)($_GET['categoria'] ?? 0) ?: null;

        $productos  = $this->productoModel->getAll($categoriaId, $busqueda ?: null, 100, 0, true);
        $categorias = $this->categoriaModel->getAll(true);

        require_once VIEWS_PATH . '/empleado/layouts/header.php';
        require_once VIEWS_PATH . '/empleado/catalogo.php';
        require_once VIEWS_PATH . '/empleado/layouts/footer.php';
    }

    /* ═══════════════════════════════════════
       UH-30+32+35 — Nueva venta / POS
       ═══════════════════════════════════════ */

    public function nuevaVenta(): void
    {
        requireLogin();
        requireRole('vendedor', 'admin');

        $pageTitle = 'Nueva Venta';
        $extraCss  = ['empleado.css', 'ventas.css'];

        $productos  = $this->productoModel->getAll(null, null, 200, 0, true);
        $categorias = $this->categoriaModel->getAll(true);

        // Promociones activas autorizadas (admin las activa)
        $promociones = $this->getPromocionesActivas();
        $guardarVentaUrl = BASE_URL . '/index.php?controller=empleado&action=confirmarVenta';
        $historialUrl = BASE_URL . '/index.php?controller=empleado&action=historial';

        require_once VIEWS_PATH . '/empleado/layouts/header.php';
        require_once VIEWS_PATH . '/empleado/nueva_venta.php';
        require_once VIEWS_PATH . '/empleado/layouts/footer.php';
    }

    public function promociones(): void
    {
        requireLogin();
        requireRole('vendedor');

        $pageTitle = 'Mis Promociones';
        $extraCss = ['empleado.css'];
        
        $usuarioId = (int)$_SESSION['usuario_id'];
        $promociones = $this->promocionModel->getPromocionesByUsuario($usuarioId);

        require_once VIEWS_PATH . '/empleado/layouts/header.php';
        require_once VIEWS_PATH . '/empleado/promociones.php';
        require_once VIEWS_PATH . '/empleado/layouts/footer.php';
    }

    public function solicitarPromocion(): void
    {
        requireLogin();
        requireRole('vendedor');
        $pageTitle = 'Solicitar promoción';
        $extraCss = ['empleado.css'];
        $productos = $this->productoModel->getAll(null, null, 200, 0, true);
        require_once VIEWS_PATH . '/empleado/layouts/header.php';
        require_once VIEWS_PATH . '/empleado/solicitar_promocion.php';
        require_once VIEWS_PATH . '/empleado/layouts/footer.php';
    }

    public function guardarSolicitudPromocion(): void
    {
        requireLogin();
        requireRole('vendedor');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Solicitud inválida.');
            redirect('index.php?controller=empleado&action=solicitarPromocion');
            return;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $descuento = (float)($_POST['descuento'] ?? 0);
        $inicio = trim($_POST['fecha_inicio'] ?? '');
        $fin = trim($_POST['fecha_fin'] ?? '');
        if ($nombre === '' || $descuento <= 0 || $descuento > 100 || !$inicio || !$fin || $fin < $inicio) {
            setFlash('error', 'Completa los datos de la promoción con valores válidos.');
            redirect('index.php?controller=empleado&action=solicitarPromocion');
            return;
        }

        $this->promocionModel->crearSolicitud([
            'producto_id' => (int)($_POST['producto_id'] ?? 0),
            'nombre' => $nombre,
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'descuento' => $descuento,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'solicitado_por' => (int)getUser()['id'],
        ]);
        setFlash('success', 'Solicitud enviada al administrador.');
        redirect('index.php?controller=empleado&action=dashboard');
    }

    public function confirmarVenta(): void
    {
        requireLogin();
        requireRole('vendedor', 'admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php?controller=empleado&action=nuevaVenta');
            return;
        }

        if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Token invalido.');
            redirect('index.php?controller=empleado&action=nuevaVenta');
            return;
        }

        $itemsRaw    = $_POST['items']       ?? [];
        $metodoPago  = trim($_POST['metodo_pago']    ?? 'efectivo');
        $notas       = trim($_POST['notas']           ?? '');
        $clienteId   = (int)($_POST['cliente_id']    ?? 0) ?: null;
        $promoDescPct= (float)($_POST['promo_pct']   ?? 0);

        if (empty($itemsRaw)) {
            setFlash('error', 'Agrega al menos un producto.');
            redirect('index.php?controller=empleado&action=nuevaVenta');
            return;
        }

        $items = [];
        foreach ($itemsRaw as $item) {
            if (!empty($item['producto_id']) && !empty($item['cantidad'])) {
                $precio = (float)$item['precio'];
                // Aplicar descuento de promoción
                if ($promoDescPct > 0) {
                    $precio = $precio * (1 - $promoDescPct / 100);
                }
                $items[] = [
                    'producto_id' => (int)$item['producto_id'],
                    'cantidad'    => (int)$item['cantidad'],
                    'precio'      => round($precio, 2),
                ];
            }
        }

        // Usar cliente dummy si no hay cliente registrado (venta presencial)
        if (!$clienteId) {
            $clienteId = $this->getOrCreateClientePresencial();
        }

        try {
            $pedidoId = $this->pedidoModel->crear($clienteId, $items, [
                'metodo_pago' => $metodoPago,
                'notas'       => $notas,
            ]);
            setFlash('success', "venta_ok|$pedidoId");
            $destino = ($_SESSION['usuario_rol'] ?? '') === 'admin' ? 'ventas' : 'empleado';
            redirect('index.php?controller=' . $destino . '&action=factura&id=' . $pedidoId);
        } catch (\Exception $e) {
            setFlash('error', 'Error al registrar la venta: ' . $e->getMessage());
            redirect('index.php?controller=empleado&action=nuevaVenta');
        }
    }

    /* ═══════════════════════════════════════
       UH-31 — Consulta de stock
       ═══════════════════════════════════════ */

    public function stock(): void
    {
        requireLogin();
        requireRole('vendedor', 'admin');

        $pageTitle = 'Inventario / Stock';
        $extraCss  = ['empleado.css'];

        $busqueda   = trim($_GET['busqueda'] ?? '');
        $productos  = $this->productoModel->getAll(null, $busqueda ?: null, 200, 0, false);
        $categorias = $this->categoriaModel->getAll(true);

        require_once VIEWS_PATH . '/empleado/layouts/header.php';
        require_once VIEWS_PATH . '/empleado/stock.php';
        require_once VIEWS_PATH . '/empleado/layouts/footer.php';
    }

    /* ═══════════════════════════════════════
       UH-33 — Factura / comprobante
       ═══════════════════════════════════════ */

    public function factura(): void
    {
        requireLogin();
        requireRole('vendedor', 'admin');

        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            setFlash('error', 'Debe registrar una venta primero.');
            redirect('index.php?controller=empleado&action=nuevaVenta');
            return;
        }

        $pedido   = $this->pedidoModel->getById($id);
        $detalles = $this->pedidoModel->getDetalles($id);

        if (!$pedido) {
            setFlash('error', 'Venta no encontrada.');
            redirect('index.php?controller=empleado&action=historial');
            return;
        }

        $pageTitle = 'Factura #' . $id;
        $extraCss  = ['empleado.css'];

        require_once VIEWS_PATH . '/empleado/layouts/header.php';
        require_once VIEWS_PATH . '/empleado/factura.php';
        require_once VIEWS_PATH . '/empleado/layouts/footer.php';
    }

    /* ═══════════════════════════════════════
       UH-34 — Historial de ventas
       ═══════════════════════════════════════ */

    public function historial(): void
    {
        requireLogin();
        requireRole('vendedor', 'admin');

        $pageTitle = 'Historial de Ventas';
        $extraCss  = ['empleado.css'];

        $desde    = trim($_GET['desde']    ?? '');
        $hasta    = trim($_GET['hasta']    ?? '');
        $busqueda = trim($_GET['busqueda'] ?? '');
        $estado   = trim($_GET['estado']   ?? '') ?: null;

        $ventas = $this->pedidoModel->getAll($estado, 200, 0);

        // Filtro por rango de fechas
        if ($desde || $hasta) {
            $ventas = array_filter($ventas, function ($v) use ($desde, $hasta) {
                $f = strtotime($v['created_at']);
                if ($desde && $f < strtotime($desde)) return false;
                if ($hasta && $f > strtotime($hasta . ' 23:59:59')) return false;
                return true;
            });
            $ventas = array_values($ventas);
        }

        // Filtro por cliente (nombre)
        if ($busqueda) {
            $ventas = array_filter($ventas, fn($v) =>
                stripos($v['cliente_nombre'] ?? '', $busqueda) !== false ||
                stripos((string)$v['id'], $busqueda) !== false
            );
            $ventas = array_values($ventas);
        }

        // Variables de navegación para la vista compartida historial.php
        $facturaController    = 'empleado';
        $facturaAction        = 'factura';
        $historialController  = 'empleado';
        $historialAction      = 'historial';
        $nuevaVentaController = 'empleado';
        $nuevaVentaAction     = 'nuevaVenta';

        // Totales para el estado vacío
        $db = \Database::getInstance()->getConnection();
        $totalProductos = (int) $db->query('SELECT COUNT(*) FROM productos WHERE activo = 1')->fetchColumn();
        $totalStock     = (int) $db->query('SELECT COALESCE(SUM(stock),0) FROM productos WHERE activo = 1')->fetchColumn();

        require_once VIEWS_PATH . '/empleado/layouts/header.php';
        require_once VIEWS_PATH . '/empleado/historial.php';
        require_once VIEWS_PATH . '/empleado/layouts/footer.php';
    }

    /* ═══════════════════════════════════════
       Helpers internos
       ═══════════════════════════════════════ */

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
        // Cliente genérico para ventas presenciales sin cliente registrado
        $email = 'presencial@central-box.com';
        $user  = $this->usuarioModel->findByEmail($email);

        if ($user) return (int)$user['id'];

        return $this->usuarioModel->create([
            'nombre'   => 'Cliente Presencial',
            'email'    => $email,
            'password' => bin2hex(random_bytes(16)),
            'rol'      => 'cliente',
        ]);
    }
}
