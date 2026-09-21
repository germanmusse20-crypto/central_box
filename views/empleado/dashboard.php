<?php
/**
 * Dashboard del Empleado — Portal central
 * HU: acceso rápido a Nueva venta (UH-30), Catálogo (UH-29),
 *     Stock (UH-31), Historial (UH-34), Factura (UH-33)
 */
$user     = getUser();
$hora     = (int)date('H');
$saludo   = $hora < 12 ? 'Hola' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');
$pendiente= $stats['por_estado']['confirmado'] ?? 0;
$entregado= $stats['por_estado']['entregado']  ?? 0;
$totalPed = $stats['total_pedidos']            ?? 0;
$sinStock = count(array_filter($bajoStock, fn($p) => $p['stock'] <= 0));
$recientes= $stats['recientes']               ?? [];
$ventasMes = (float)($stats['ventas_mes'] ?? 0);
$sinStock = count(array_filter($bajoStock, fn($p) => ($p['stock'] ?? 0) <= 0));
?>

<style>
    .emp-dashboard-hero { background:linear-gradient(115deg,#064E3B,#0F766E 58%,#155E75); border-radius:18px; color:#fff; padding:28px 32px; margin-bottom:22px; position:relative; overflow:hidden; }
    .emp-dashboard-hero::after { content:''; position:absolute; width:230px; height:230px; right:-70px; top:-100px; border:38px solid rgba(255,255,255,.12); border-radius:50%; }
    .emp-dashboard-hero-row { display:flex; align-items:center; justify-content:space-between; gap:20px; position:relative; z-index:1; }
    .emp-dashboard-kicker { color:#A7F3D0; font-size:.72rem; font-weight:800; letter-spacing:.12em; text-transform:uppercase; margin-bottom:10px; }
    .emp-dashboard-hero h1 { color:#fff; font-size:clamp(1.45rem,3vw,2rem); margin:0 0 7px; }
    .emp-dashboard-hero p { color:#D1FAE5; font-size:.9rem; margin:0; }
    .emp-dashboard-actions { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; margin-bottom:22px; }
    .emp-dashboard-action { min-height:96px; padding:15px; border:1px solid var(--emp-border); border-radius:10px; color:var(--emp-text); display:flex; flex-direction:column; justify-content:space-between; gap:10px; text-decoration:none; background:#fff; transition:transform .18s,box-shadow .18s,border-color .18s; }
    .emp-dashboard-action:hover { border-color:var(--emp-primary); box-shadow:0 6px 18px rgba(16,185,129,.14); transform:translateY(-2px); }
    .emp-dashboard-action svg { width:21px; height:21px; color:var(--emp-primary); }
    .emp-dashboard-action strong { font-size:.82rem; }
    .emp-dashboard-action small { color:var(--emp-text-sec); font-size:.7rem; line-height:1.35; }
    .emp-dashboard-context { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; margin-bottom:28px; }
    .emp-dashboard-context-item { padding:15px 18px; border:1px solid var(--emp-border); border-radius:12px; background:#fff; box-shadow:var(--emp-shadow); }
    .emp-dashboard-context-item span { display:block; color:var(--emp-text-sec); font-size:.74rem; margin-bottom:5px; }
    .emp-dashboard-context-item strong { color:var(--emp-text); font-size:1.2rem; }
    @media (max-width:800px) { .emp-dashboard-actions { grid-template-columns:repeat(2,minmax(0,1fr)); } .emp-dashboard-context { grid-template-columns:1fr; } }
    @media (max-width:560px) { .emp-dashboard-hero { padding:22px 20px; } .emp-dashboard-hero-row { align-items:flex-start; flex-direction:column; } }
</style>

<section class="emp-dashboard-hero emp-animate-in" aria-labelledby="employee-dashboard-title">
    <div class="emp-dashboard-hero-row">
        <div>
            <div class="emp-dashboard-kicker">Centro de operaciones</div>
            <h1 id="employee-dashboard-title"><?= e($saludo) ?>, <?= e($user['nombre'] ?? 'empleado') ?></h1>
            <p>Gestiona ventas, cobros, existencias y comprobantes desde un solo lugar.</p>
        </div>
        <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=nuevaVenta" class="emp-btn emp-btn-primary emp-btn-lg">
            <i data-lucide="plus-circle"></i> Nueva venta
        </a>
    </div>
</section>

<div class="emp-dashboard-context" aria-label="Resumen operativo">
    <div class="emp-dashboard-context-item"><span>Ventas del mes</span><strong><?= formatPrice($ventasMes) ?></strong></div>
    <div class="emp-dashboard-context-item"><span>Pedidos confirmados</span><strong><?= number_format($pendiente) ?></strong></div>
    <div class="emp-dashboard-context-item"><span>Productos sin stock</span><strong><?= number_format($sinStock) ?></strong></div>
</div>

<div class="emp-dashboard-actions" aria-label="Acciones del empleado">
    <a class="emp-dashboard-action" href="<?= BASE_URL ?>/index.php?controller=empleado&action=nuevaVenta"><i data-lucide="shopping-cart"></i><strong>Crear venta</strong><small>Selecciona productos y cliente.</small></a>
    <a class="emp-dashboard-action" href="<?= BASE_URL ?>/index.php?controller=empleado&action=nuevaVenta"><i data-lucide="credit-card"></i><strong>Cobrar pedido</strong><small>Efectivo, tarjeta o transferencia.</small></a>
    <a class="emp-dashboard-action" href="<?= BASE_URL ?>/index.php?controller=empleado&action=catalogo"><i data-lucide="grid-2x2"></i><strong>Consultar catalogo</strong><small>Encuentra productos disponibles.</small></a>
    <a class="emp-dashboard-action" href="<?= BASE_URL ?>/index.php?controller=empleado&action=stock"><i data-lucide="boxes"></i><strong>Controlar stock</strong><small>Detecta faltantes y reposicion.</small></a>
    <a class="emp-dashboard-action" href="<?= BASE_URL ?>/index.php?controller=empleado&action=historial"><i data-lucide="history"></i><strong>Ver historial</strong><small>Busca ventas y estados.</small></a>
    <a class="emp-dashboard-action" href="<?= BASE_URL ?>/index.php?controller=empleado&action=historial"><i data-lucide="file-check-2"></i><strong>Emitir comprobante</strong><small>Abre una factura registrada.</small></a>
</div>

<?php if (false): ?>
<!-- ══════════════════════════════════════
     CHIP DE BIENVENIDA + SALUDO
     ══════════════════════════════════════ -->
<div class="emp-page-header emp-animate-in">
    <div>
        <!-- Chip "PANEL EMPLEADO" -->
        <div style="display:inline-flex;align-items:center;gap:7px;
                    background:linear-gradient(135deg,#D1FAE5,#EEF2FF);
                    padding:5px 14px;border-radius:99px;
                    font-size:.72rem;font-weight:700;color:#065F46;
                    margin-bottom:14px;letter-spacing:.04em;">
            <i data-lucide="star" style="width:12px;height:12px;color:#10B981;"></i>
            PANEL EMPLEADO
        </div>

        <h1 class="emp-page-title">
            <?= $saludo ?>, <span><?= e($user['nombre']) ?></span>! 👋
        </h1>
        <p class="emp-page-subtitle">
            Aquí tienes un resumen de tu actividad.
        </p>
    </div>

    <!-- Botón de acción principal (UH-30) -->
    <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=nuevaVenta"
       class="emp-btn emp-btn-primary emp-btn-lg">
        <i data-lucide="plus-circle"></i> Nueva venta
    </a>
</div>

<!-- ══════════════════════════════════════
     STATS (4 tarjetas) — UH-31, UH-34
     ══════════════════════════════════════ -->
<div class="emp-stats" style="grid-template-columns:repeat(4,1fr);">

    <!-- Ventas de hoy (UH-34) -->
    <div class="emp-stat emp-animate-in"
         style="border-top:3px solid #10B981;cursor:pointer;"
         onclick="location.href='<?= BASE_URL ?>/index.php?controller=empleado&action=historial'">
        <div class="emp-stat-header">
            <div class="emp-stat-icon green">
                <i data-lucide="trending-up"></i>
            </div>
            <span class="emp-badge emp-badge-green" style="font-size:.65rem;">Hoy</span>
        </div>
        <div class="emp-stat-label">Ventas de hoy</div>
        <div class="emp-stat-value"><?= formatPrice($ventasDia) ?></div>
        <div class="emp-stat-trend up" style="font-size:.72rem;margin-top:4px;">
            <i data-lucide="arrow-right" style="width:12px;height:12px;vertical-align:middle;"></i>
            Ver historial
        </div>
    </div>

    <!-- Pedidos totales (UH-34) -->
    <div class="emp-stat emp-animate-in"
         style="border-top:3px solid #6366F1;cursor:pointer;animation-delay:.05s;"
         onclick="location.href='<?= BASE_URL ?>/index.php?controller=empleado&action=historial'">
        <div class="emp-stat-header">
            <div class="emp-stat-icon indigo">
                <i data-lucide="shopping-bag"></i>
            </div>
        </div>
        <div class="emp-stat-label">Pedidos totales</div>
        <div class="emp-stat-value"><?= number_format($totalPed) ?></div>
        <div style="font-size:.72rem;color:var(--emp-text-sec);margin-top:4px;">
            <?= $pendiente ?> pendiente<?= $pendiente !== 1 ? 's' : '' ?>
        </div>
    </div>

    <!-- Pedidos confirmados -->
    <div class="emp-stat emp-animate-in"
         style="border-top:3px solid #F59E0B;cursor:pointer;animation-delay:.1s;"
         onclick="location.href='<?= BASE_URL ?>/index.php?controller=empleado&action=historial&estado=confirmado'">
        <div class="emp-stat-header">
            <div class="emp-stat-icon amber">
                <i data-lucide="clock"></i>
            </div>
            <?php if ($pendiente > 0): ?>
                <span class="emp-badge emp-badge-amber" style="font-size:.65rem;">
                    <?= $pendiente ?> urgente<?= $pendiente !== 1 ? 's' : '' ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="emp-stat-label">Confirmados</div>
        <div class="emp-stat-value"><?= $pendiente ?></div>
        <div style="font-size:.72rem;color:var(--emp-text-sec);margin-top:4px;">
            <?= $entregado ?> entregado<?= $entregado !== 1 ? 's' : '' ?>
        </div>
    </div>

    <!-- Bajo stock (UH-31) -->
    <div class="emp-stat emp-animate-in"
         style="border-top:3px solid <?= count($bajoStock) > 0 ? '#EF4444' : '#10B981' ?>;cursor:pointer;animation-delay:.15s;"
         onclick="location.href='<?= BASE_URL ?>/index.php?controller=empleado&action=stock'">
        <div class="emp-stat-header">
            <div class="emp-stat-icon <?= count($bajoStock) > 0 ? 'red' : 'green' ?>">
                <i data-lucide="alert-triangle"></i>
            </div>
            <?php if ($sinStock > 0): ?>
                <span class="emp-badge emp-badge-red" style="font-size:.65rem;">
                    <?= $sinStock ?> sin stock
                </span>
            <?php endif; ?>
        </div>
        <div class="emp-stat-label">Bajo stock</div>
        <div class="emp-stat-value"><?= count($bajoStock) ?></div>
        <div style="font-size:.72rem;color:var(--emp-text-sec);margin-top:4px;">
            producto<?= count($bajoStock) !== 1 ? 's' : '' ?> con alerta
        </div>
    </div>

<!-- ══════════════════════════════════════
     GRID PRINCIPAL — 2 columnas
     ══════════════════════════════════════ -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">

    <!-- ── Acciones rápidas (UH-29, 30, 31, 34) ── -->
    <div class="emp-card emp-animate-in" style="animation-delay:.2s;">
        <div class="emp-card-header">
            <h3 class="emp-card-title">
                <i data-lucide="zap"></i>
                Acciones rápidas
            </h3>
        </div>

        <div class="emp-quick-grid">
            <!-- UH-30: Nueva venta -->
            <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=nuevaVenta"
               class="emp-quick-card" style="border-color:#D1FAE5;background:linear-gradient(135deg,#F0FDF4,#fff);">
                <i data-lucide="plus-circle" style="color:#10B981;"></i>
                <span>Nueva venta</span>
            </a>

            <!-- UH-29: Catálogo -->
            <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=catalogo"
               class="emp-quick-card" style="border-color:#C7D2FE;background:linear-gradient(135deg,#EEF2FF,#fff);">
                <i data-lucide="grid-2x2" style="color:#6366F1;"></i>
                <span>Catálogo</span>
            </a>

            <!-- UH-31: Stock -->
            <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=stock"
               class="emp-quick-card" style="border-color:#FDE68A;background:linear-gradient(135deg,#FFFBEB,#fff);">
                <i data-lucide="package" style="color:#D97706;"></i>
                <span>Ver stock</span>
            </a>

            <!-- UH-34: Historial -->
            <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=historial"
               class="emp-quick-card" style="border-color:#FECACA;background:linear-gradient(135deg,#FFF5F5,#fff);">
                <i data-lucide="clock" style="color:#EF4444;"></i>
                <span>Historial</span>
            </a>
        </div>
    </div>

    <!-- ── Alertas de stock bajo (UH-31) ── -->
    <div class="emp-card emp-animate-in" style="animation-delay:.25s;">
        <div class="emp-card-header">
            <h3 class="emp-card-title">
                <i data-lucide="alert-triangle"></i>
                Alertas de stock
            </h3>
            <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=stock"
               class="emp-btn emp-btn-ghost emp-btn-sm">
                <i data-lucide="arrow-right"></i> Ver todo
            </a>
        </div>

        <?php if (empty($bajoStock)): ?>
            <div style="text-align:center;padding:28px 16px;">
                <div style="width:48px;height:48px;border-radius:50%;background:#D1FAE5;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                    <i data-lucide="check-circle" style="width:22px;height:22px;color:#10B981;"></i>
                </div>
                <p style="font-size:.85rem;font-weight:700;color:#065F46;">
                    Todo el stock está en orden
                </p>
                <p style="font-size:.75rem;color:var(--emp-text-sec);margin-top:4px;">
                    No hay productos con stock bajo
                </p>
            </div>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:0;max-height:200px;overflow-y:auto;">
                <?php foreach (array_slice($bajoStock, 0, 6) as $p): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;
                                padding:10px 4px;border-bottom:1px solid var(--emp-border);">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:8px;height:8px;border-radius:50%;
                                        background:<?= $p['stock'] <= 0 ? '#EF4444' : '#F59E0B' ?>;
                                        flex-shrink:0;"></div>
                            <span style="font-size:.82rem;font-weight:600;color:var(--emp-text);">
                                <?= e($p['nombre']) ?>
                            </span>
                        </div>
                        <span class="emp-badge <?= $p['stock'] <= 0 ? 'emp-badge-red' : 'emp-badge-amber' ?>">
                            <?= $p['stock'] <= 0 ? 'Sin stock' : $p['stock'] . ' uds.' ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ══════════════════════════════════════
     VENTAS RECIENTES (UH-34 + UH-33)
     ══════════════════════════════════════ -->
<div class="emp-card emp-animate-in" style="animation-delay:.3s;">
    <div class="emp-card-header">
        <h3 class="emp-card-title">
            <i data-lucide="receipt"></i>
            Ventas recientes
        </h3>
        <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=historial"
           class="emp-btn emp-btn-ghost emp-btn-sm">
            <i data-lucide="arrow-right"></i> Ver historial completo
        </a>
    </div>

    <?php if (empty($recientes)): ?>
        <div class="emp-empty" style="padding:40px;">
            <div class="emp-empty-icon">🛍️</div>
            <div class="emp-empty-title">Sin ventas registradas aún</div>
            <p class="emp-empty-text">
                Registra tu primera venta del día usando el módulo de Nueva Venta.
            </p>
            <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=nuevaVenta"
               class="emp-btn emp-btn-primary emp-btn-sm">
                <i data-lucide="plus-circle"></i> Registrar venta
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="emp-table">
                <thead>
                    <tr>
                        <th># Venta</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Comprobante</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $colores = [
                        'confirmado' => 'emp-badge-indigo',
                        'entregado'  => 'emp-badge-green',
                        'cancelado'  => 'emp-badge-red',
                    ];
                    foreach ($recientes as $v):
                        $badge = $colores[$v['estado']] ?? 'emp-badge-gray';
                    ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=factura&id=<?= $v['id'] ?>"
                               style="font-weight:700;color:#6366F1;text-decoration:none;">
                                #<?= str_pad($v['id'], 4, '0', STR_PAD_LEFT) ?>
                            </a>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="width:28px;height:28px;border-radius:50%;
                                            background:linear-gradient(135deg,#10B981,#6366F1);
                                            color:white;display:flex;align-items:center;
                                            justify-content:center;font-size:.72rem;
                                            font-weight:700;flex-shrink:0;">
                                    <?= strtoupper(substr($v['cliente_nombre'] ?? 'C', 0, 1)) ?>
                                </div>
                                <?= e($v['cliente_nombre'] ?? 'Cliente presencial') ?>
                            </div>
                        </td>
                        <td>
                            <strong style="color:#10B981;">
                                <?= formatPrice((float)$v['total']) ?>
                            </strong>
                        </td>
                        <td>
                            <span class="emp-badge <?= $badge ?>">
                                <?= ucfirst(e($v['estado'])) ?>
                            </span>
                        </td>
                        <td style="font-size:.78rem;color:var(--emp-text-sec);">
                            <?= date('d/m/Y', strtotime($v['created_at'])) ?>
                            <span style="color:#9CA3AF;">
                                <?= date('H:i', strtotime($v['created_at'])) ?>
                            </span>
                        </td>
                        <td>
                            <!-- UH-33: Generar factura -->
                            <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=factura&id=<?= $v['id'] ?>"
                               class="emp-btn emp-btn-ghost emp-btn-sm"
                               title="Ver comprobante">
                                <i data-lucide="file-text"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>
