<?php
/**
 * Layout Header — Módulo Empleado
 */
$empUser = getUser();
$empCtrl = $_GET['controller'] ?? 'empleado';
$empAct  = $_GET['action']     ?? 'dashboard';

$empNavMap = [
    'dashboard'  => 'Panel',
    'catalogo'   => 'Catalogo',
    'nuevaVenta' => 'Nueva Venta',
    'stock'      => 'Inventario',
    'promociones'=> 'Promociones',
    'historial'  => 'Historial',
    'factura'    => 'Factura',
];

$empPageLabel = $empNavMap[$empAct] ?? ($pageTitle ?? 'Panel');

function empNavActive(string $action, string $current): string {
    return $action === $current ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Empleado') ?> — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= SCRIPTS_URL ?>/lucide.js"></script>

    <link rel="stylesheet" href="<?= STYLES_URL ?>/empleado.css">
    <?php if (!empty($extraCss) && is_array($extraCss)): ?>
        <?php foreach (array_filter($extraCss, fn($c) => $c !== 'empleado.css') as $css): ?>
            <link rel="stylesheet" href="<?= STYLES_URL ?>/<?= e($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="emp-body">

<!-- Mobile overlay -->
<div class="emp-overlay" id="empOverlay"></div>

<div class="emp-layout">

    <!-- ════════════════ SIDEBAR ════════════════ -->
    <aside class="emp-sidebar" id="empSidebar">

        <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=dashboard"
           class="emp-sidebar-brand">
            <div class="emp-brand-icon">
                <img src="<?= IMG_URL ?>/logo.png" alt="central_box"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <i data-lucide="shopping-cart" style="display:none;color:white;"></i>
            </div>
            <div>
                <div class="emp-brand-name"><?= APP_NAME ?></div>
                <div class="emp-brand-badge">Panel Empleado</div>
            </div>
        </a>

        <nav class="emp-sidebar-nav">

            <!-- Principal -->
            <div class="emp-nav-section">
                <div class="emp-nav-label">Principal</div>

                <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=dashboard"
                   class="emp-nav-link <?= empNavActive('dashboard', $empAct) ?>">
                    <i data-lucide="layout-dashboard"></i>
                    Panel de inicio
                </a>
            </div>

            <!-- Ventas -->
            <div class="emp-nav-section">
                <div class="emp-nav-label">Ventas</div>

                <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=nuevaVenta"
                   class="emp-nav-link <?= empNavActive('nuevaVenta', $empAct) ?>">
                    <i data-lucide="plus-circle"></i>
                    Nueva venta
                </a>

                <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=historial"
                   class="emp-nav-link <?= empNavActive('historial', $empAct) ?>">
                    <i data-lucide="clock"></i>
                    Historial de ventas
                </a>
            </div>

            <!-- Productos -->
            <div class="emp-nav-section">
                <div class="emp-nav-label">Productos</div>

                <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=catalogo"
                   class="emp-nav-link <?= empNavActive('catalogo', $empAct) ?>">
                    <i data-lucide="grid-2x2"></i>
                    Catalogo
                </a>

                <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=stock"
                   class="emp-nav-link <?= empNavActive('stock', $empAct) ?>">
                    <i data-lucide="package"></i>
                    Stock e inventario
                </a>

                <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=promociones"
                   class="emp-nav-link <?= empNavActive('promociones', $empAct) ?>">
                    <i data-lucide="tag"></i>
                    Promociones
                </a>
            </div>

        </nav>

        <div class="emp-sidebar-footer">
            <div class="emp-sidebar-user">
                <div class="emp-user-avatar">
                    <?php if (!empty($empUser['avatar'])): ?>
                        <img src="<?= IMG_URL ?>/empleados/<?= e($empUser['avatar']) ?>" alt="Foto de perfil">
                    <?php else: ?>
                        <?= strtoupper(substr($empUser['nombre'] ?? 'E', 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div class="emp-user-info">
                    <span class="emp-user-name"><?= e($empUser['nombre'] ?? 'Empleado') ?></span>
                    <span class="emp-user-role"><?= ucfirst(e($empUser['rol'] ?? 'vendedor')) ?></span>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/index.php?controller=auth&action=logout" class="emp-logout-btn">
                <i data-lucide="log-out"></i>
                Cerrar sesion
            </a>
        </div>

    </aside>

    <!-- ════════════════ TOPBAR ════════════════ -->
    <div class="emp-main" id="empMain">

        <header class="emp-header" id="empHeader">
            <div class="emp-header-left">
                <button class="emp-header-toggle" id="empToggle">
                    <i data-lucide="menu"></i>
                </button>
                <div class="emp-breadcrumb">
                    <span><?= APP_NAME ?></span>
                    <span class="emp-breadcrumb-sep">/</span>
                    <strong><?= e($empPageLabel) ?></strong>
                </div>
            </div>

            <div class="emp-header-right">
                <!-- Nueva venta rápida -->
                <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=nuevaVenta"
                   class="emp-header-btn" title="Nueva venta">
                    <i data-lucide="plus"></i>
                </a>
                <!-- Perfil -->
                <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=perfil"
                   class="emp-header-btn" title="Mi perfil">
                    <i data-lucide="user-circle"></i>
                </a>
                <!-- Logout -->
                <a href="<?= BASE_URL ?>/index.php?controller=auth&action=logout"
                   class="emp-header-btn" title="Cerrar sesion"
                   style="color:#EF4444;border-color:#FECACA;">
                    <i data-lucide="log-out"></i>
                </a>
            </div>
        </header>

        <!-- Contenido de la página -->
        <main class="emp-content">

            <?php
            $fs = getFlash('success');
            $fe = getFlash('error');
            $fw = getFlash('warning');
            $fi = getFlash('info');
            ?>

            <?php if ($fs): ?>
                <div class="emp-alert emp-alert-success emp-animate-in">
                    <i data-lucide="check-circle"></i>
                    <span><?= e($fs) ?></span>
                    <button class="emp-alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            <?php endif; ?>

            <?php if ($fe): ?>
                <div class="emp-alert emp-alert-error emp-animate-in">
                    <i data-lucide="alert-circle"></i>
                    <span><?= e($fe) ?></span>
                    <button class="emp-alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            <?php endif; ?>

            <?php if ($fw): ?>
                <div class="emp-alert emp-alert-warning emp-animate-in">
                    <i data-lucide="alert-triangle"></i>
                    <span><?= e($fw) ?></span>
                    <button class="emp-alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            <?php endif; ?>

            <?php if ($fi): ?>
                <div class="emp-alert emp-alert-info emp-animate-in">
                    <i data-lucide="info"></i>
                    <span><?= e($fi) ?></span>
                    <button class="emp-alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            <?php endif; ?>
