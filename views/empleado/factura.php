<?php
/**
 * UH-33 — Factura / comprobante de venta
 */
$folio   = 'FV-' . str_pad($pedido['id'], 6, '0', STR_PAD_LEFT);
$subtotal= array_sum(array_column($detalles, 'subtotal'));
$user    = getUser();
$nuevaVentaUrl = $nuevaVentaUrl ?? BASE_URL . '/index.php?controller=empleado&action=nuevaVenta';
$historialUrl = $historialUrl ?? BASE_URL . '/index.php?controller=empleado&action=historial';
?>

<div class="emp-page-header emp-animate-in">
    <div>
        <h1 class="emp-page-title">Factura <span><?= $folio ?></span></h1>
        <p class="emp-page-subtitle">Comprobante de venta generado el <?= date('d/m/Y \a \l\a\s H:i') ?>.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <button onclick="window.print()" class="emp-btn emp-btn-secondary">
            <i data-lucide="printer"></i> Imprimir
        </button>
        <button onclick="empDescargarPDF()" class="emp-btn emp-btn-primary">
            <i data-lucide="download"></i> Descargar PDF
        </button>
        <a href="<?= e($nuevaVentaUrl) ?>"
           class="emp-btn emp-btn-ghost">
            <i data-lucide="plus"></i> Nueva venta
        </a>
    </div>
</div>

<?php if (!empty($flashVenta)): ?>
    <div class="emp-alert emp-alert-success emp-animate-in" style="margin-bottom:20px;">
        <i data-lucide="check-circle"></i>
        <strong>Venta registrada exitosamente.</strong>
        El stock ha sido actualizado automaticamente.
    </div>
<?php endif; ?>

<!-- Factura imprimible -->
<div class="emp-invoice emp-animate-in" id="facturaDoc">

    <!-- Encabezado -->
    <div class="emp-invoice-header">
        <div class="emp-invoice-brand">
            <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#10B981,#6366F1);display:flex;align-items:center;justify-content:center;">
                <i data-lucide="shopping-cart" style="width:22px;height:22px;color:white;"></i>
            </div>
            <div>
                <div class="emp-invoice-brand-name"><?= APP_NAME ?></div>
                <div style="font-size:.72rem;color:var(--emp-text-sec);">Tienda virtual de confianza</div>
            </div>
        </div>
        <div style="text-align:right;">
            <div class="emp-invoice-num">
                Folio: <strong><?= $folio ?></strong>
            </div>
            <div style="font-size:.78rem;color:var(--emp-text-sec);margin-top:4px;">
                <?= date('d/m/Y H:i', strtotime($pedido['created_at'])) ?>
            </div>
        </div>
    </div>

    <!-- Datos cliente y vendedor -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
        <div>
            <div style="font-size:.72rem;font-weight:700;color:var(--emp-text-sec);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Cliente</div>
            <div style="font-size:.9rem;font-weight:700;">
                <?= e($pedido['cliente_nombre'] ?? 'Cliente presencial') ?>
            </div>
            <?php if (!empty($pedido['cliente_email'])): ?>
                <div style="font-size:.78rem;color:var(--emp-text-sec);"><?= e($pedido['cliente_email']) ?></div>
            <?php endif; ?>
        </div>
        <div>
            <div style="font-size:.72rem;font-weight:700;color:var(--emp-text-sec);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Vendedor</div>
            <div style="font-size:.9rem;font-weight:700;"><?= e($user['nombre'] ?? '') ?></div>
            <div style="font-size:.78rem;color:var(--emp-text-sec);"><?= e($user['email'] ?? '') ?></div>
        </div>
    </div>

    <!-- Tabla de productos -->
    <?php if (empty($detalles)): ?>
        <p style="text-align:center;color:var(--emp-text-sec);padding:20px;">Sin productos registrados en esta venta.</p>
    <?php else: ?>
        <table class="emp-invoice-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th style="text-align:center;">Cant.</th>
                    <th style="text-align:right;">Precio unit.</th>
                    <th style="text-align:right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $d): ?>
                <tr>
                    <td><?= e($d['producto_nombre']) ?></td>
                    <td style="text-align:center;">
                        <span style="background:#EEF2FF;color:#6366F1;padding:2px 10px;border-radius:99px;font-weight:700;font-size:.8rem;">
                            <?= $d['cantidad'] ?>
                        </span>
                    </td>
                    <td style="text-align:right;"><?= formatPrice((float)$d['precio_unitario']) ?></td>
                    <td style="text-align:right;"><strong><?= formatPrice((float)$d['subtotal']) ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Totales -->
    <div style="display:flex;justify-content:flex-end;margin-top:8px;">
        <div class="emp-invoice-totals">
            <div class="emp-invoice-total-row">
                <span>Subtotal</span>
                <span><?= formatPrice($subtotal) ?></span>
            </div>
            <div class="emp-invoice-total-row">
                <span>Descuento</span>
                <span>$ 0</span>
            </div>
            <div class="emp-invoice-total-row">
                <span>Impuestos</span>
                <span>$ 0</span>
            </div>
            <div class="emp-invoice-total-final">
                <span>TOTAL</span>
                <span><?= formatPrice((float)$pedido['total']) ?></span>
            </div>
        </div>
    </div>

    <!-- Método de pago y estado -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:24px;padding-top:20px;border-top:1px solid var(--emp-border);">
        <div>
            <div style="font-size:.72rem;font-weight:700;color:var(--emp-text-sec);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Metodo de pago</div>
            <div style="font-size:.9rem;font-weight:700;"><?= ucfirst(e($pedido['metodo_pago'] ?? 'efectivo')) ?></div>
        </div>
        <div>
            <div style="font-size:.72rem;font-weight:700;color:var(--emp-text-sec);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Estado</div>
            <?php
            $eColor = ['pendiente'=>'amber','confirmado'=>'indigo','entregado'=>'green','cancelado'=>'red'][$pedido['estado']] ?? 'gray';
            ?>
            <span class="emp-badge emp-badge-<?= $eColor ?>"><?= ucfirst($pedido['estado']) ?></span>
        </div>
    </div>

    <!-- Nota -->
    <div style="margin-top:24px;padding:14px;background:#F0FDF4;border-radius:10px;border:1px solid #A7F3D0;font-size:.78rem;color:#065F46;display:flex;gap:8px;align-items:flex-start;">
        <i data-lucide="check-circle" style="width:15px;height:15px;flex-shrink:0;margin-top:1px;"></i>
        <span>Esta factura fue generada automaticamente. El stock se actualizo al registrar la venta. <?= APP_NAME ?> — Todos los derechos reservados.</span>
    </div>

</div>

<!-- Botones de navegación -->
<div style="display:flex;gap:12px;justify-content:center;margin-top:28px;">
    <a href="<?= e($historialUrl) ?>"
       class="emp-btn emp-btn-ghost">
        <i data-lucide="clock"></i> Ver historial
    </a>
    <a href="<?= e($nuevaVentaUrl) ?>"
       class="emp-btn emp-btn-primary">
        <i data-lucide="plus-circle"></i> Nueva venta
    </a>
</div>

<style>
@media print {
    .emp-sidebar, .emp-header, .emp-page-header .emp-btn,
    .emp-page-header div:last-child, .emp-alert,
    div[style*="justify-content:center"] { display: none !important; }
    body.emp-body { background: white !important; }
    .emp-main { margin-left: 0 !important; }
    .emp-content { padding: 0 !important; }
    .emp-invoice { box-shadow: none !important; border: 1px solid #ccc; }
}
</style>

<script>
function empDescargarPDF() {
    // En producción usar librería como dompdf o jsPDF
    // Por ahora usar print como fallback
    window.print();
}
</script>
