<?php
/**
 * Layout Header — Navbar superior
 * Incluido en todas las vistas del dashboard
 */
$user = getUser();
$pageTitle = $pageTitle ?? 'Dashboard';
$stockAlerts = [];
if ($user && $user['rol'] === 'admin') {
    require_once MODELS_PATH . '/Inventario.php';
    try {
        $stockAlerts = (new Inventario())->getProductosBajoStock();
    } catch (Throwable $e) {
        $stockAlerts = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="central_box — Sistema de gestión para tienda virtual">
    <title><?= e($pageTitle) ?> — <?= APP_NAME ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons (local) -->
    <script src="<?= SCRIPTS_URL ?>/lucide.js"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="<?= STYLES_URL ?>/main.css">
    <link rel="stylesheet" href="<?= STYLES_URL ?>/components.css">
    <link rel="stylesheet" href="<?= STYLES_URL ?>/dark.css">
    <?php if (isset($extraCss)): ?>
        <?php foreach ((array)$extraCss as $css): ?>
            <link rel="stylesheet" href="<?= STYLES_URL ?>/<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <!-- Anti-flash: aplicar tema guardado antes del primer paint -->
    <script>
        (function() {
            if (localStorage.getItem('cb_dark_mode') === 'true') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
</head>
<body>
<div class="app-layout">

    <!-- Sidebar -->
    <?php require_once VIEWS_PATH . '/Layouts/sidebar.php'; ?>

    <!-- Mobile overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Main content area -->
    <div class="app-main" id="appMain">
        <!-- Header / Navbar -->
        <header class="app-header" id="appHeader">
            <div class="header-left">
                <button class="sidebar-toggle" id="sidebarToggle" title="Toggle sidebar">
                    <i data-lucide="menu"></i>
                </button>
                <h1 class="page-title"><?= e($pageTitle) ?></h1>
            </div>

            <div class="header-right">
                <!-- Botón modo oscuro -->
                <button id="darkModeToggle" class="btn btn-ghost btn-icon" title="Cambiar tema" aria-label="Activar modo oscuro">
                    <i data-lucide="moon" id="darkModeIconMoon"></i>
                    <i data-lucide="sun"  id="darkModeIconSun" style="display:none;"></i>
                </button>
                <?php if ($user && $user['rol'] === 'admin'): ?>
                    <div class="stock-notification" id="stockNotification">
                        <button type="button" class="btn btn-ghost btn-icon stock-notification-button"
                                id="stockNotificationButton" aria-label="Alertas de inventario"
                                aria-expanded="false" aria-controls="stockNotificationPanel">
                            <i data-lucide="bell"></i>
                            <?php if ($stockAlerts): ?>
                                <span class="stock-notification-count"><?= count($stockAlerts) ?></span>
                            <?php endif; ?>
                        </button>
                        <div class="stock-notification-panel" id="stockNotificationPanel" hidden>
                            <div class="stock-notification-header">
                                <div>
                                    <strong>Alertas de inventario</strong>
                                    <span><?= count($stockAlerts) ?> producto<?= count($stockAlerts) === 1 ? '' : 's' ?> requiere<?= count($stockAlerts) === 1 ? '' : 'n' ?> atención</span>
                                </div>
                                <i data-lucide="package-alert"></i>
                            </div>
                            <?php if ($stockAlerts): ?>
                                <div class="stock-notification-list">
                                    <?php foreach (array_slice($stockAlerts, 0, 6) as $alert): ?>
                                        <a href="<?= BASE_URL ?>/index.php?controller=inventario&action=index" class="stock-notification-item">
                                            <span class="stock-notification-icon <?= (int) $alert['stock'] === 0 ? 'is-out' : 'is-low' ?>">
                                                <i data-lucide="<?= (int) $alert['stock'] === 0 ? 'circle-x' : 'triangle-alert' ?>"></i>
                                            </span>
                                            <span class="stock-notification-detail">
                                                <strong><?= e($alert['nombre']) ?></strong>
                                                <small><?= (int) $alert['stock'] === 0 ? 'Producto agotado' : 'Stock bajo' ?> · mínimo <?= (int) $alert['stock_minimo'] ?></small>
                                            </span>
                                            <b><?= (int) $alert['stock'] ?></b>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                                <a href="<?= BASE_URL ?>/index.php?controller=inventario&action=index" class="stock-notification-footer">
                                    Revisar inventario <i data-lucide="arrow-right"></i>
                                </a>
                            <?php else: ?>
                                <div class="stock-notification-empty">
                                    <i data-lucide="circle-check"></i>
                                    <strong>Inventario al día</strong>
                                    <span>No hay productos con stock bajo.</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($user && $user['rol'] === 'cliente'): ?>
                    <?php
                    require_once MODELS_PATH . '/Carrito.php';
                    $carritoModel = new Carrito();
                    $carritoCount = $carritoModel->getCount();
                    ?>
                    <a href="<?= BASE_URL ?>/index.php?controller=carrito&action=index" class="btn btn-ghost btn-icon" title="Carrito" style="position:relative;">
                        <i data-lucide="shopping-cart"></i>
                        <?php if ($carritoCount > 0): ?>
                            <span style="position:absolute;top:-2px;right:-2px;background:var(--color-danger);color:white;width:18px;height:18px;border-radius:50%;font-size:0.65rem;display:flex;align-items:center;justify-content:center;font-weight:700;"><?= $carritoCount ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/index.php?controller=usuarios&action=perfil" class="header-user" title="Mi Perfil">
                    <div class="header-user-avatar">
                        <?= strtoupper(substr($user['nombre'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="header-user-info">
                        <span class="header-user-name"><?= e($user['nombre'] ?? 'Usuario') ?></span>
                        <span class="header-user-role"><?= e($user['rol'] ?? '') ?></span>
                    </div>
                </a>

                <a href="<?= BASE_URL ?>/index.php?controller=auth&action=logout" class="btn-logout-header" id="btnLogout" title="Cerrar Sesión">
                    <i data-lucide="log-out"></i>
                </a>
            </div>
        </header>

        <!-- Page content -->
        <main class="app-content">
            <?php
            // Flash messages
            $flashSuccess = getFlash('success');
            $flashError   = getFlash('error');
            $flashWarning = getFlash('warning');
            $flashInfo    = getFlash('info');
            ?>

            <?php if ($flashSuccess): ?>
                <div class="alert alert-success animate-fade-in">
                    <i data-lucide="check-circle"></i>
                    <span><?= e($flashSuccess) ?></span>
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="alert alert-error animate-fade-in">
                    <i data-lucide="alert-circle"></i>
                    <span><?= e($flashError) ?></span>
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            <?php endif; ?>

            <?php if ($flashWarning): ?>
                <div class="alert alert-warning animate-fade-in">
                    <i data-lucide="alert-triangle"></i>
                    <span><?= e($flashWarning) ?></span>
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            <?php endif; ?>

            <?php if ($flashInfo): ?>
                <div class="alert alert-info animate-fade-in">
                    <i data-lucide="info"></i>
                    <span><?= e($flashInfo) ?></span>
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            <?php endif; ?>
