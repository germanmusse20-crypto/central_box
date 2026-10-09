<?php
/**
 * UH-27 — Login del empleado
 */
$rawError    = getFlash('error') ?? '';
$isBloqueado = $rawError === 'bloqueado';
$isVacios    = $rawError === 'campos_vacios';
$restantes   = null;
$errorMsg    = '';

if (str_starts_with($rawError, 'credenciales|')) {
    $restantes = (int)explode('|', $rawError)[1];
    $errorMsg  = 'Credenciales invalidas. Te quedan ' . $restantes . ' intento(s).';
} elseif ($isVacios) {
    $errorMsg = 'Por favor completa todos los campos.';
} elseif ($isBloqueado) {
    $restanteSeg = (int)($_SESSION['emp_bloqueo_restante'] ?? 300);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Empleado — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= SCRIPTS_URL ?>/lucide.js"></script>

    <link rel="stylesheet" href="<?= STYLES_URL ?>/empleado.css">
</head>
<body class="emp-body" style="background:linear-gradient(135deg,#fff 0%,#EEF2FF 60%,#D1FAE5 100%);">

<div class="emp-login-page">
    <div class="emp-login-card">

        <!-- Logo -->
        <div class="emp-login-logo">
            <div class="emp-login-logo-icon">
                <img src="<?= IMG_URL ?>/logo.png" alt="central_box"
                     onerror="this.style.display='none'">
            </div>
            <div>
                <div class="emp-login-logo-name"><?= APP_NAME ?></div>
                <div class="emp-login-logo-sub">Portal de Empleado</div>
            </div>
        </div>

        <?php if ($isBloqueado): ?>
            <!-- Estado bloqueado -->
            <div class="emp-lockout">
                <div class="emp-lockout-icon">🔒</div>
                <div class="emp-lockout-title">Cuenta bloqueada temporalmente</div>
                <div class="emp-lockout-text">Superaste el numero de intentos permitidos.</div>
                <div class="emp-lockout-timer" id="lockTimer"><?= gmdate('i:s', $restanteSeg ?? 300) ?></div>
            </div>
            <p style="text-align:center;font-size:.8rem;color:var(--emp-text-sec);">
                Espera o contacta al administrador.
            </p>
            <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=login"
               class="emp-btn emp-btn-ghost emp-btn-block" style="margin-top:16px;justify-content:center;">
                Intentar de nuevo
            </a>
        <?php else: ?>

            <h1 class="emp-login-title">Bienvenido de vuelta</h1>
            <p class="emp-login-subtitle">Ingresa tus credenciales para continuar</p>

            <?php if ($errorMsg): ?>
                <div class="emp-alert emp-alert-error emp-animate-in" style="margin-bottom:16px;">
                    <i data-lucide="alert-circle"></i>
                    <span><?= e($errorMsg) ?></span>
                </div>
            <?php endif; ?>

            <?php $flashSuccess = getFlash('success'); ?>
            <?php if ($flashSuccess): ?>
                <div class="emp-alert emp-alert-success emp-animate-in" style="margin-bottom:16px;">
                    <i data-lucide="check-circle"></i>
                    <span><?= e($flashSuccess) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/index.php?controller=empleado&action=doLogin"
                  method="POST" id="empLoginForm" novalidate>
                <?= csrfField() ?>

                <!-- Email -->
                <div class="emp-form-group">
                    <label class="emp-label" for="empEmail">
                        Correo electronico <span>*</span>
                    </label>
                    <div class="emp-input-icon">
                        <span class="emp-input-icon-left"><i data-lucide="mail"></i></span>
                        <input type="email" id="empEmail" name="email"
                               class="emp-input <?= $errorMsg ? 'error' : '' ?>"
                               placeholder="tu@central-box.com"
                               autocomplete="email" autofocus required>
                    </div>
                    <div class="emp-field-error" id="errEmail" style="display:none;">
                        <i data-lucide="alert-circle"></i> Ingresa un correo valido
                    </div>
                </div>

                <!-- Password -->
                <div class="emp-form-group">
                    <label class="emp-label" for="empPwd">
                        Contrasena <span>*</span>
                    </label>
                    <div class="emp-input-icon" style="position:relative;">
                        <span class="emp-input-icon-left"><i data-lucide="lock"></i></span>
                        <input type="password" id="empPwd" name="password"
                               class="emp-input <?= $errorMsg ? 'error' : '' ?>"
                               placeholder="••••••••"
                               autocomplete="current-password" required
                               style="padding-right:44px;">
                        <button type="button" class="emp-pwd-toggle" id="empPwdToggle">
                            <i data-lucide="eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <div class="emp-field-error" id="errPwd" style="display:none;">
                        <i data-lucide="alert-circle"></i> Ingresa tu contrasena
                    </div>
                </div>

                <!-- Enlace recuperar -->
                <div style="text-align:right;margin-bottom:20px;">
                    <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=recuperar"
                       style="font-size:.8rem;color:var(--emp-primary);font-weight:600;text-decoration:none;">
                        Olvide mi contrasena
                    </a>
                </div>

                <button type="submit" class="emp-btn emp-btn-primary emp-btn-lg emp-btn-block" id="empLoginBtn">
                    <i data-lucide="log-in"></i>
                    Iniciar sesion
                </button>
            </form>

            <div class="emp-divider"></div>
            <div class="emp-login-footer">
                <a href="<?= BASE_URL ?>/">← Volver al inicio</a>
            </div>

        <?php endif; ?>
    </div>
</div>

<script>lucide.createIcons();</script>
<script>
// Toggle password visibility
document.getElementById('empPwdToggle')?.addEventListener('click', function () {
    const input = document.getElementById('empPwd');
    const icon  = document.getElementById('eyeIcon');
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    icon.setAttribute('data-lucide', isPass ? 'eye-off' : 'eye');
    lucide.createIcons();
});

// Validación cliente
document.getElementById('empLoginForm')?.addEventListener('submit', function (e) {
    let ok = true;
    const email = document.getElementById('empEmail');
    const pwd   = document.getElementById('empPwd');
    const errE  = document.getElementById('errEmail');
    const errP  = document.getElementById('errPwd');

    if (!email.value.trim() || !/\S+@\S+\.\S+/.test(email.value)) {
        email.classList.add('error');
        errE.style.display = 'flex';
        ok = false;
    } else {
        email.classList.remove('error');
        errE.style.display = 'none';
    }

    if (!pwd.value.trim()) {
        pwd.classList.add('error');
        errP.style.display = 'flex';
        ok = false;
    } else {
        pwd.classList.remove('error');
        errP.style.display = 'none';
    }

    if (!ok) { e.preventDefault(); return; }

    // Loading state
    const btn = document.getElementById('empLoginBtn');
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" style="animation:spin 1s linear infinite;"></i> Ingresando...';
    lucide.createIcons();
});

// Countdown bloqueo
const timerEl = document.getElementById('lockTimer');
if (timerEl) {
    let secs = parseInt('<?= $restanteSeg ?? 300 ?>');
    const tick = setInterval(function () {
        secs--;
        if (secs <= 0) { clearInterval(tick); location.reload(); return; }
        const m = Math.floor(secs / 60);
        const s = secs % 60;
        timerEl.textContent = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    }, 1000);
}
</script>
</body>
</html>
