<?php
/**
 * Recuperación de contraseña exclusiva para clientes.
 */
$paso = !empty($_SESSION['cliente_recovery_token']) ? 2 : 1;
$flashError = getFlash('error');
$flashSuccess = getFlash('success');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Recuperar contraseña de cliente en central_box">
    <title>Recuperar contraseña — central_box</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="<?= STYLES_URL ?>/auth.css">
</head>
<body class="auth-body">
    <div class="auth-bg">
        <div class="auth-bg-orb auth-bg-orb-1"></div>
        <div class="auth-bg-orb auth-bg-orb-2"></div>
        <div class="auth-bg-orb auth-bg-orb-3"></div>
    </div>

    <div class="auth-container">
        <div class="auth-panel-left" style="position:relative;">
            <a href="<?= BASE_URL ?>/index.php?controller=auth&action=login" class="auth-back-home-green">
                <i data-lucide="arrow-left"></i> Volver al inicio de sesión
            </a>
            <div class="auth-branding">
                <div class="auth-logo"><img src="<?= IMG_URL ?>/logo.png" alt="central_box" class="auth-logo-img"></div>
                <p class="auth-brand-subtitle">Recupera el acceso a tu cuenta cliente</p>
                <div class="auth-features">
                    <div class="auth-feature"><div class="auth-feature-icon"><i data-lucide="shield-check"></i></div><div><strong>Solo clientes</strong><span>Este acceso no modifica cuentas de empleados.</span></div></div>
                    <div class="auth-feature"><div class="auth-feature-icon"><i data-lucide="clock-3"></i></div><div><strong>Recuperación temporal</strong><span>El proceso es válido durante 30 minutos.</span></div></div>
                </div>
            </div>
        </div>

        <div class="auth-panel-right">
            <div class="auth-form-container">
                <div class="auth-form-header">
                    <h2><?= $paso === 2 ? 'Crea una nueva contraseña' : 'Recupera tu contraseña' ?></h2>
                    <p><?= $paso === 2 ? 'Usa una contraseña que puedas recordar.' : 'Verifica tu correo de cliente para continuar.' ?></p>
                </div>

                <?php if ($flashError): ?><div class="alert alert-error animate-fade-in"><i data-lucide="alert-circle"></i><span><?= e($flashError) ?></span></div><?php endif; ?>
                <?php if ($flashSuccess): ?><div class="alert alert-success animate-fade-in"><i data-lucide="check-circle"></i><span><?= e($flashSuccess) ?></span></div><?php endif; ?>

                <?php if ($paso === 2): ?>
                    <form action="<?= BASE_URL ?>/index.php?controller=auth&action=actualizarPasswordCliente" method="POST" class="auth-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="paso" value="2">
                        <div class="form-group"><label class="form-label" for="password"><i data-lucide="lock"></i> Nueva contraseña</label><input type="password" class="form-control" id="password" name="password" minlength="6" required autocomplete="new-password" placeholder="Mínimo 6 caracteres"></div>
                        <div class="form-group"><label class="form-label" for="password_confirm"><i data-lucide="lock-keyhole"></i> Confirmar contraseña</label><input type="password" class="form-control" id="password_confirm" name="password_confirm" minlength="6" required autocomplete="new-password" placeholder="Repite la contraseña"></div>
                        <button type="submit" class="btn btn-primary btn-lg btn-block"><i data-lucide="check-circle"></i> Actualizar contraseña</button>
                    </form>
                <?php else: ?>
                    <form action="<?= BASE_URL ?>/index.php?controller=auth&action=actualizarPasswordCliente" method="POST" class="auth-form">
                        <?= csrfField() ?>
                        <input type="hidden" name="paso" value="1">
                        <div class="form-group"><label class="form-label" for="email"><i data-lucide="mail"></i> Correo de cliente</label><input type="email" class="form-control" id="email" name="email" required autocomplete="email" autofocus placeholder="tu@email.com"></div>
                        <button type="submit" class="btn btn-primary btn-lg btn-block"><i data-lucide="arrow-right"></i> Continuar</button>
                    </form>
                <?php endif; ?>

                <div class="divider-text">o</div>
                <div class="auth-footer"><p><a href="<?= BASE_URL ?>/index.php?controller=auth&action=login" class="auth-link">Volver al inicio de sesión</a></p></div>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
