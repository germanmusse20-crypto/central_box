<?php
/**
 * PromocionesController — Gestión de promociones
 */
require_once MODELS_PATH . '/Promocion.php';

class PromocionesController
{
    private Promocion $promocionModel;

    public function __construct()
    {
        $this->promocionModel = new Promocion();
    }

    /**
     * HU1: Visualizar promociones activas y pendientes
     */
    public function index(): void
    {
        requireLogin();
        requireRole('admin');

        $pageTitle = 'Gestión de Promociones';

        // HU1 y HU3: Obtener promociones activas y pendientes
        $promociones = $this->promocionModel->getPromociones();

        require_once VIEWS_PATH . '/Layouts/header.php';
        require_once VIEWS_PATH . '/admin/promociones/index.php';
        require_once VIEWS_PATH . '/Layouts/footer.php';
    }

    /**
     * HU3: Aprobar (Activar) promoción
     */
    public function aprobar(): void
    {
        requireLogin();
        requireRole('admin');

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->promocionModel->updateEstado($id, 'activa');
            setFlash('success', 'Promoción aprobada y activada exitosamente.');
        }
        
        redirect('index.php?controller=promociones&action=index');
    }

    /**
     * HU2: Rechazar (No autorizar) promoción
     */
    public function rechazar(): void
    {
        requireLogin();
        requireRole('admin');

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->promocionModel->updateEstado($id, 'rechazada');
            setFlash('success', 'Promoción rechazada correctamente.');
        }
        
        redirect('index.php?controller=promociones&action=index');
    }

    /**
     * HU2: Eliminar promoción
     */
    public function eliminar(): void
    {
        requireLogin();
        requireRole('admin');

        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->promocionModel->updateEstado($id, 'eliminada');
            setFlash('success', 'Promoción eliminada del sistema.');
        }
        
        redirect('index.php?controller=promociones&action=index');
    }
}
