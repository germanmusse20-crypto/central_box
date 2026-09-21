<?php
/**
 * Centro de ayuda y soporte del cliente.
 * Ofrece orientación de compra y canales de contacto reales.
 */
?>

<div class="cli-page-header animate-fade-in">
    <div>
        <div class="cli-chip"><i data-lucide="headphones"></i> CENTRO DE AYUDA</div>
        <h1 class="cli-page-title">Ayuda y <span>soporte</span></h1>
        <p class="cli-page-subtitle">Encuentra respuestas sobre tus compras o comunícate con central box.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=misOrders" class="cli-btn cli-btn-secondary"><i data-lucide="receipt"></i> Mis pedidos</a>
</div>

<div class="cli-checkout-grid animate-fade-in-up">
    <section class="cli-card">
        <div class="cli-card-header"><h2 class="cli-card-title"><i data-lucide="circle-help"></i> Preguntas frecuentes</h2></div>
        <details style="padding:14px 0;border-bottom:1px solid var(--cli-border);">
            <summary style="cursor:pointer;font-weight:700;color:var(--cli-text);">¿Cómo realizo una compra?</summary>
            <p class="cli-page-subtitle" style="margin-top:9px;line-height:1.6;">Selecciona un producto en el catálogo, agrégalo al carrito y continúa con el proceso de checkout para indicar dirección y método de pago.</p>
        </details>
        <details style="padding:14px 0;border-bottom:1px solid var(--cli-border);">
            <summary style="cursor:pointer;font-weight:700;color:var(--cli-text);">¿Qué métodos de pago están disponibles?</summary>
            <p class="cli-page-subtitle" style="margin-top:9px;line-height:1.6;">Puedes consultar los métodos habilitados desde el menú Métodos de pago. La selección se realiza al finalizar la compra.</p>
        </details>
        <details style="padding:14px 0;border-bottom:1px solid var(--cli-border);">
            <summary style="cursor:pointer;font-weight:700;color:var(--cli-text);">¿Dónde consulto el estado de mi pedido?</summary>
            <p class="cli-page-subtitle" style="margin-top:9px;line-height:1.6;">Ingresa a Mis pedidos para filtrar por estado y abrir el detalle de cualquier compra realizada.</p>
        </details>
        <details style="padding:14px 0;">
            <summary style="cursor:pointer;font-weight:700;color:var(--cli-text);">¿Cómo funcionan las promociones?</summary>
            <p class="cli-page-subtitle" style="margin-top:9px;line-height:1.6;">Las promociones activas muestran su descuento, vigencia y productos aplicables. Revisa sus condiciones antes de finalizar la compra.</p>
        </details>
    </section>

    <aside class="cli-summary-box">
        <h2 class="cli-summary-title">Contacta con nosotros</h2>
        <p class="cli-page-subtitle" style="line-height:1.6;margin-bottom:16px;">Nuestro equipo puede ayudarte con pedidos, pagos y disponibilidad de productos.</p>
        <a href="https://wa.me/573013619644" target="_blank" rel="noopener noreferrer" class="cli-btn cli-btn-primary cli-btn-block" style="margin-bottom:10px;"><i data-lucide="message-circle"></i> WhatsApp</a>
        <a href="mailto:germanmusse20@gmail.com" class="cli-btn cli-btn-secondary cli-btn-block"><i data-lucide="mail"></i> Enviar correo</a>
        <div class="cli-summary-divider"></div>
        <div class="cli-summary-item"><span>Teléfono</span><strong>+57 301 361 9644</strong></div>
        <div class="cli-summary-item"><span>Correo</span><strong style="font-size:.72rem;">germanmusse20@gmail.com</strong></div>
    </aside>
</div>

<div class="cli-card animate-fade-in-up" style="margin-top:18px;">
    <div class="cli-card-header"><h2 class="cli-card-title"><i data-lucide="navigation"></i> Accesos rápidos</h2></div>
    <div class="cli-quick-grid">
        <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=catalogo" class="cli-quick-card"><i data-lucide="store"></i><span>Ver catálogo</span></a>
        <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=carrito" class="cli-quick-card"><i data-lucide="shopping-cart"></i><span>Ir al carrito</span></a>
        <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=metodosPago" class="cli-quick-card"><i data-lucide="credit-card"></i><span>Métodos de pago</span></a>
        <a href="<?= BASE_URL ?>/index.php?controller=cliente&action=promociones" class="cli-quick-card"><i data-lucide="tag"></i><span>Promociones</span></a>
    </div>
</div>
