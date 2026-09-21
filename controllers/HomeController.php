<?php
/**
 * Controlador Home - Página de Inicio
 */

class HomeController
{
    public function index(): void
    {
        require_once MODELS_PATH . '/Producto.php';
        require_once MODELS_PATH . '/Categoria.php';

        $pageTitle = 'Inicio - central_box';
        $extraCss = ['home.css'];
        $productos = (new Producto())->getAvailable(8);
        $categorias = (new Categoria())->getAll(true);
        
        // No requiere login
        require_once VIEWS_PATH . '/home.php';
    }
}
?>
