<div class="emp-page-header emp-animate-in">
    <div>
        <h1 class="emp-page-title">Solicitar <span>promoción</span></h1>
        <p class="emp-page-subtitle">Envía una propuesta para revisión del administrador.</p>
    </div>
    <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=dashboard" class="emp-btn emp-btn-ghost"><i data-lucide="arrow-left"></i> Volver</a>
</div>
<div class="emp-card emp-animate-in" style="max-width:720px;">
    <form action="<?= BASE_URL ?>/index.php?controller=empleado&action=guardarSolicitudPromocion" method="POST">
        <?= csrfField() ?>
        <div class="emp-form-group"><label class="emp-label" for="nombre">Nombre de la promoción</label><input class="emp-input" id="nombre" name="nombre" required maxlength="150"></div>
        <div class="emp-form-group"><label class="emp-label" for="producto_id">Producto relacionado</label><select class="emp-select" id="producto_id" name="producto_id"><option value="0">Toda la tienda</option><?php foreach ($productos as $producto): ?><option value="<?= (int)$producto['id'] ?>"><?= e($producto['nombre']) ?></option><?php endforeach; ?></select></div>
        <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;">
            <div class="emp-form-group"><label class="emp-label" for="descuento">Descuento (%)</label><input class="emp-input" id="descuento" name="descuento" type="number" min="0.01" max="100" step="0.01" required></div>
            <div class="emp-form-group"><label class="emp-label" for="fecha_inicio">Desde</label><input class="emp-input" id="fecha_inicio" name="fecha_inicio" type="date" required></div>
            <div class="emp-form-group"><label class="emp-label" for="fecha_fin">Hasta</label><input class="emp-input" id="fecha_fin" name="fecha_fin" type="date" required></div>
        </div>
        <div class="emp-form-group"><label class="emp-label" for="descripcion">Descripción</label><textarea class="emp-input" id="descripcion" name="descripcion" rows="4" maxlength="1000"></textarea></div>
        <button type="submit" class="emp-btn emp-btn-primary"><i data-lucide="send"></i> Enviar solicitud</button>
    </form>
</div>
