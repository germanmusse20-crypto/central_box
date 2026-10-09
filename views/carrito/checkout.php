<?php
/**
 * Vista — Checkout (finalizar compra)
 */
?>

<div class="pv-header animate-fade-in">
    <div class="ped-header-left">
        <a href="<?= BASE_URL ?>/index.php?controller=carrito&action=index" class="pv-back">
            <i data-lucide="arrow-left"></i>
        </a>
        <div>
            <h1 class="pv-title">🛒 Finalizar <span class="pv-highlight">Compra</span></h1>
            <p class="pv-subtitle">Revisa tu pedido y completa el pago.</p>
        </div>
    </div>
</div>

<div class="pv-layout">
    <!-- Formulario -->
    <div class="pv-main">
        <form action="<?= BASE_URL ?>/index.php?controller=cliente&action=confirmarPedido"
              method="POST" id="checkoutForm">
            <?= csrfField() ?>

            <!-- Dirección -->
            <div class="pv-section animate-fade-in-up">
                <div class="pv-section-header">
                    <span class="pv-section-num">1</span>
                    <h3>Datos de envio</h3>
                </div>
                <div class="form-group">
                    <label class="form-label">Direccion de envio *</label>
                    <input type="text" name="direccion_envio" class="form-control"
                           placeholder="Calle, numero, ciudad" required
                           value="<?= e((getUser() ?? [])['direccion'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Notas adicionales</label>
                    <textarea name="notas" class="form-control" rows="3"
                              placeholder="Instrucciones especiales..."></textarea>
                </div>
            </div>

            <!-- Método de pago -->
            <div class="pv-section animate-fade-in-up">
                <div class="pv-section-header">
                    <span class="pv-section-num">2</span>
                    <h3>Metodo de pago</h3>
                </div>
                <div class="pv-payment-options">
                    <label class="pv-payment-opt">
                        <input type="radio" name="metodo_pago" value="efectivo" checked>
                        <span class="pv-payment-label"><i data-lucide="banknote"></i> Efectivo</span>
                    </label>
                    <label class="pv-payment-opt">
                        <input type="radio" name="metodo_pago" value="tarjeta">
                        <span class="pv-payment-label"><i data-lucide="credit-card"></i> Tarjeta</span>
                    </label>
                    <label class="pv-payment-opt">
                        <input type="radio" name="metodo_pago" value="transferencia">
                        <span class="pv-payment-label"><i data-lucide="building-2"></i> Transferencia bancaria</span>
                    </label>
                </div>
            </div>

            <!-- Botones -->
            <div class="pv-actions-footer animate-fade-in-up">
                <a href="<?= BASE_URL ?>/index.php?controller=carrito&action=index"
                   class="btn btn-secondary">Volver al carrito</a>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check-circle"></i> Confirmar pedido
                </button>
            </div>
        </form>
    </div>

    <!-- Resumen -->
    <div class="pv-sidebar">
        <div class="pv-section pv-summary animate-fade-in-up">
            <h4 class="pv-summary-title">Resumen del pedido</h4>
            <?php foreach ($items as $item): ?>
                <div class="pv-summary-item">
                    <span><?= e($item['nombre']) ?> x<?= $item['cantidad'] ?></span>
                    <span><?= formatPrice((float)$item['precio'] * $item['cantidad']) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="pv-summary-divider"></div>
            <div class="pv-summary-total">
                <span>Total</span>
                <span class="pv-total-value"><?= formatPrice($total) ?></span>
            </div>
        </div>
    </div>
</div>

<script src="<?= SCRIPTS_URL ?>/main.js"></script>
<script>lucide.createIcons();</script>
