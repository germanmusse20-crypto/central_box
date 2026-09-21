<?php
/**
 * PagosController — Gestión de métodos de pago
 */
require_once MODELS_PATH . '/Pago.php';

class PagosController
{
    private Pago $pagoModel;

    public function __construct()
    {
        $this->pagoModel = new Pago();
    }

    /**
     * HU1: Visualizar métodos de pago
     */
    public function index(): void
    {
        requireLogin();
        requireRole('admin');

        $pageTitle = 'Métodos de Pago';
        $extraCss  = ['pagos.css'];
        
        $metodosPago = $this->pagoModel->getAll();

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/admin/pagos/index.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    /**
     * HU2 y HU3: Activar / Deshabilitar método de pago online
     */
    public function toggle(): void
    {
        requireLogin();
        requireRole('admin');

        $id = (int)($_GET['id'] ?? 0);
        $estado = trim($_GET['estado'] ?? '');

        if ($id > 0 && in_array($estado, ['activo', 'inactivo'])) {
            $this->pagoModel->updateEstado($id, $estado);
            
            $msg = $estado === 'activo' ? 'Método de pago activado correctamente.' : 'Método de pago deshabilitado.';
            setFlash('success', $msg);
        } else {
            setFlash('error', 'Parámetros inválidos.');
        }

        redirect('index.php?controller=pagos&action=index');
    }
}
