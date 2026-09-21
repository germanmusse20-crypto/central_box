<?php
$nombre = $usuario['nombre'] ?? '';
$email = $usuario['email'] ?? '';
$telefono = $usuario['telefono'] ?? '';
$avatar = $usuario['avatar'] ?? '';
?>

<div class="emp-page-header emp-animate-in">
    <div>
        <div class="emp-nav-label" style="padding:0;margin-bottom:6px;">CUENTA</div>
        <h1 class="emp-page-title">Editar <span>perfil</span></h1>
        <p class="emp-page-subtitle">Actualiza tus datos personales y tu foto de perfil.</p>
    </div>
</div>

<div class="emp-profile-edit emp-animate-in">
    <form action="<?= BASE_URL ?>/index.php?controller=empleado&action=actualizarPerfil" method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <div class="emp-profile-edit-grid">
            <section class="emp-profile-photo-panel">
                <div class="emp-profile-avatar-preview" id="avatarPreview">
                    <?php if ($avatar): ?>
                        <img src="<?= IMG_URL ?>/empleados/<?= e($avatar) ?>" alt="Foto de perfil">
                    <?php else: ?>
                        <?= e(strtoupper(substr($nombre, 0, 1))) ?>
                    <?php endif; ?>
                </div>
                <h2>Foto de perfil</h2>
                <p>Usa una imagen clara para identificar tu cuenta.</p>
                <label class="emp-upload-button" for="avatar">
                    <i data-lucide="camera"></i> Elegir foto
                </label>
                <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/webp" hidden>
                <small>JPG, PNG o WEBP. Máximo 5 MB.</small>
            </section>

            <section class="emp-profile-form-panel">
                <div class="emp-pos-section-title"><i data-lucide="user-round"></i> Datos personales</div>
                <div class="emp-profile-fields">
                    <div>
                        <label class="emp-label" for="nombre">Nombre completo</label>
                        <input class="emp-input" type="text" id="nombre" name="nombre" value="<?= e($nombre) ?>" minlength="3" required>
                    </div>
                    <div>
                        <label class="emp-label" for="email">Correo electrónico</label>
                        <input class="emp-input" type="email" id="email" name="email" value="<?= e($email) ?>" required>
                    </div>
                    <div>
                        <label class="emp-label" for="telefono">Teléfono</label>
                        <input class="emp-input" type="tel" id="telefono" name="telefono" value="<?= e($telefono) ?>" maxlength="20">
                    </div>
                    <div class="emp-profile-readonly">
                        <span class="emp-label">Rol</span>
                        <strong><?= e(ucfirst($usuario['rol'] ?? 'vendedor')) ?></strong>
                        <small>El rol solo puede cambiarlo un administrador.</small>
                    </div>
                </div>
                <div class="emp-profile-actions">
                    <a href="<?= e($perfilReturnUrl) ?>" class="emp-btn emp-btn-ghost"><i data-lucide="arrow-left"></i> Cancelar</a>
                    <button type="submit" class="emp-btn emp-btn-primary"><i data-lucide="save"></i> Guardar cambios</button>
                </div>
            </section>
        </div>
    </form>
</div>

<style>
.emp-profile-edit { max-width: 980px; margin: 0 auto; }
.emp-profile-edit-grid { display: grid; grid-template-columns: 280px 1fr; gap: 20px; align-items: start; }
.emp-profile-photo-panel, .emp-profile-form-panel { background: #fff; border: 1px solid var(--emp-border); border-radius: var(--emp-radius); box-shadow: var(--emp-shadow); padding: 24px; }
.emp-profile-photo-panel { text-align: center; }
.emp-profile-avatar-preview { width: 128px; height: 128px; margin: 4px auto 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; overflow: hidden; background: var(--emp-gradient); color: #fff; font-size: 3rem; font-weight: 800; }
.emp-profile-avatar-preview img { width: 100%; height: 100%; object-fit: cover; }
.emp-profile-photo-panel h2 { font-size: 1rem; margin-bottom: 6px; }
.emp-profile-photo-panel p, .emp-profile-photo-panel small { display: block; color: var(--emp-text-sec); font-size: .75rem; line-height: 1.5; }
.emp-upload-button { display: inline-flex; align-items: center; gap: 7px; margin: 16px 0 8px; padding: 9px 13px; border-radius: 9px; color: #fff; background: var(--emp-primary); font-size: .78rem; font-weight: 700; cursor: pointer; }
.emp-upload-button svg { width: 15px; }
.emp-profile-form-panel .emp-pos-section-title { margin-bottom: 22px; }
.emp-profile-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
.emp-profile-fields > div { min-width: 0; }
.emp-profile-readonly { display: flex; flex-direction: column; justify-content: center; padding: 12px; border: 1px solid var(--emp-border); border-radius: 9px; background: #F9FAFB; }
.emp-profile-readonly .emp-label { margin-bottom: 5px; }
.emp-profile-readonly strong { font-size: .88rem; }
.emp-profile-readonly small { color: var(--emp-text-sec); font-size: .7rem; margin-top: 4px; }
.emp-profile-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 26px; padding-top: 20px; border-top: 1px solid var(--emp-border); }
@media (max-width: 700px) { .emp-profile-edit-grid, .emp-profile-fields { grid-template-columns: 1fr; } .emp-profile-actions { justify-content: stretch; flex-direction: column-reverse; } .emp-profile-actions .emp-btn { justify-content: center; } }
</style>
<script>
document.getElementById('avatar')?.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (event) {
        document.getElementById('avatarPreview').innerHTML = '<img src="' + event.target.result + '" alt="Nueva foto de perfil">';
    };
    reader.readAsDataURL(file);
});
</script>
