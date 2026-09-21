<?php
/**
 * Layout Sidebar — Navegación lateral dinámica según rol
 */
$user = getUser();
$currentController = $_GET['controller'] ?? 'dashboard';
$currentAction     = $_GET['action'] ?? 'index';

function sidebarActive(string $controller, string $current): string {
    return $controller === $current ? 'active' : '';
}
?>

<aside class="sidebar" id="sidebar">

    <!-- Brand / Logo -->
    <div class="sidebar-brand">
        <img src="<?= IMG_URL ?>/logo.png" alt="central_box" class="sidebar-logo">
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">

        <!-- PRINCIPAL -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Principal</div>
            <a href="<?= BASE_URL ?>/index.php?controller=dashboard&action=index"
               class="sidebar-link <?= sidebarActive('dashboard', $currentController) ?>">
                <span class="sidebar-link-icon"><i data-lucide="layout-dashboard"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Dashboard</span>
                </span>
            </a>
        </div>

        <!-- GESTIÓN -->
        <?php if (hasRole('admin', 'vendedor')): ?>
        <div class="sidebar-section">
            <div class="sidebar-section-title">Gestión</div>

            <a href="<?= BASE_URL ?>/index.php?controller=ventas&action=online"
               class="sidebar-link <?= ($currentController === 'ventas' && $currentAction === 'online') ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i data-lucide="shopping-cart"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Ventas</span>
                </span>
            </a>

            <a href="<?= BASE_URL ?>/index.php?controller=ventas&action=puntoVenta"
               class="sidebar-link <?= ($currentController === 'ventas' && $currentAction === 'puntoVenta') ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i data-lucide="store"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Nueva venta</span>
                </span>
            </a>

            <a href="<?= BASE_URL ?>/index.php?controller=pedidos&action=index"
               class="sidebar-link <?= sidebarActive('pedidos', $currentController) ?>">
                <span class="sidebar-link-icon"><i data-lucide="clipboard-list"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Pedidos</span>
                </span>
            </a>

            <a href="<?= BASE_URL ?>/index.php?controller=inventario&action=index"
               class="sidebar-link <?= sidebarActive('inventario', $currentController) ?>">
                <span class="sidebar-link-icon"><i data-lucide="package"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Inventario</span>
                </span>
            </a>

            <?php if (hasRole('vendedor')): ?>
            <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=solicitarPromocion"
               class="sidebar-link <?= ($currentController === 'empleado' && $currentAction === 'solicitarPromocion') ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i data-lucide="tag"></i></span>
                <span class="sidebar-link-text"><span class="sidebar-link-name">Solicitar promoción</span></span>
            </a>
            <?php endif; ?>

            <?php if (hasRole('admin')): ?>
            <a href="<?= BASE_URL ?>/index.php?controller=reportes&action=index"
               class="sidebar-link <?= sidebarActive('reportes', $currentController) ?>">
                <span class="sidebar-link-icon"><i data-lucide="bar-chart-2"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Reportes</span>
                </span>
            </a>

            <a href="<?= BASE_URL ?>/index.php?controller=promociones&action=index"
               class="sidebar-link <?= sidebarActive('promociones', $currentController) ?>">
                <span class="sidebar-link-icon"><i data-lucide="tag"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Promociones</span>
                </span>
            </a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/index.php?controller=productos&action=lista"
               class="sidebar-link <?= ($currentController === 'cliente' && $currentAction === 'catalogo') ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i data-lucide="box"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Productos</span>
                </span>
            </a>

            <?php if (hasRole('admin')): ?>
            <a href="<?= BASE_URL ?>/index.php?controller=categorias&action=lista"
               class="sidebar-link <?= sidebarActive('categorias', $currentController) ?>">
                <span class="sidebar-link-icon"><i data-lucide="tags"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Categorías</span>
                </span>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- TIENDA (Cliente) -->
        <?php if (hasRole('cliente')): ?>
        <div class="sidebar-section">
            <div class="sidebar-section-title">Tienda</div>

                <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=catalogo"
               class="sidebar-link <?= sidebarActive('productos', $currentController) ?>">
                <span class="sidebar-link-icon"><i data-lucide="store"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Catálogo</span>
                </span>
            </a>

                <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=carrito"
               class="sidebar-link <?= ($currentController === 'cliente' && $currentAction === 'carrito') ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i data-lucide="shopping-cart"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Mi Carrito</span>
                </span>
            </a>

                <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=misOrders"
               class="sidebar-link <?= ($currentController === 'cliente' && $currentAction === 'misOrders') ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i data-lucide="receipt"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Mis Pedidos</span>
                </span>
            </a>

                    <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=metodosPago"
                        class="sidebar-link <?= ($currentController === 'cliente' && $currentAction === 'metodosPago') ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i data-lucide="credit-card"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Métodos de pago</span>
                </span>
            </a>

                <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=promociones"
                    class="sidebar-link <?= ($currentController === 'cliente' && $currentAction === 'promociones') ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i data-lucide="tag"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Promociones</span>
                </span>
            </a>

                <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=puntosLealtad"
                    class="sidebar-link <?= ($currentController === 'cliente' && $currentAction === 'puntosLealtad') ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i data-lucide="star"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Puntos de lealtad</span>
                </span>
            </a>

                <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=ayudaSoporte"
                    class="sidebar-link <?= ($currentController === 'cliente' && $currentAction === 'ayudaSoporte') ? 'active' : '' ?>">
                <span class="sidebar-link-icon"><i data-lucide="headphones"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Ayuda y soporte</span>
                </span>
            </a>
        </div>
        <?php endif; ?>

        <!-- ADMINISTRACIÓN -->
        <?php if (hasRole('admin')): ?>
        <div class="sidebar-section">
            <div class="sidebar-section-title">Administración</div>

            <a href="<?= BASE_URL ?>/index.php?controller=usuarios&action=lista"
               class="sidebar-link <?= sidebarActive('usuarios', $currentController) ?>">
                <span class="sidebar-link-icon"><i data-lucide="users"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Usuarios</span>
                    <span class="sidebar-link-sub">Administrar usuarios</span>
                </span>
            </a>

            <a href="<?= BASE_URL ?>/index.php?controller=pagos&action=index"
               class="sidebar-link <?= sidebarActive('pagos', $currentController) ?>">
                <span class="sidebar-link-icon"><i data-lucide="credit-card"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Pagos online</span>
                    <span class="sidebar-link-sub">Métodos y transacciones</span>
                </span>
            </a>
        </div>
        <?php endif; ?>

        <!-- CUENTA -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Cuenta</div>

            <a href="<?= BASE_URL ?>/index.php?controller=usuarios&action=perfil"
               class="sidebar-link">
                <span class="sidebar-link-icon"><i data-lucide="user-circle"></i></span>
                <span class="sidebar-link-text">
                    <span class="sidebar-link-name">Mi Perfil</span>
                </span>
            </a>
        </div>

    </nav>

    <!-- Footer logout -->
    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>/index.php?controller=auth&action=logout" class="sidebar-logout">
            <span class="sidebar-link-icon"><i data-lucide="log-out"></i></span>
            <span class="sidebar-link-text">
                <span class="sidebar-link-name">Cerrar Sesión</span>
            </span>
        </a>
    </div>

</aside>
