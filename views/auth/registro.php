<?php
/**
 * Vista Registro — Formulario de registro de nuevos clientes
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
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
        <!-- Panel izquierdo -->
        <div class="auth-panel-left">
            <a href="<?= BASE_URL ?>/" class="auth-back-home-green">
                <i data-lucide="arrow-left"></i> Volver al inicio
            </a>
            <div class="auth-branding">
                <div class="auth-logo">
                    <img src="<?= IMG_URL ?>/logo.png" alt="central_box" class="auth-logo-img">
                </div>
                <p class="auth-brand-subtitle">Crea tu cuenta y empieza a comprar</p>
                <div class="auth-features">
                    <div class="auth-feature">
                        <div class="auth-feature-icon"><i data-lucide="user-plus"></i></div>
                        <div><strong>Facil</strong><span>Registro en menos de 1 minuto</span></div>
                    </div>
                    <div class="auth-feature">
                        <div><strong></strong><span></span></div>
                    </div>
                    <div class="auth-feature">
                        <div class="auth-feature-icon"><i data-lucide="heart"></i></div>
                        <div><strong>Favoritos</strong><span>Guarda tus productos preferidos</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel derecho -->
        <div class="auth-panel-right">
            <div class="auth-form-container">
                <div class="auth-form-header">
                    <h2>Crear Cuenta</h2>
                    <p>Completa tus datos para registrarte</p>
                </div>

                <?php $flashError = getFlash('error'); ?>
                <?php if ($flashError): ?>
                    <div class="alert alert-error">
                        <i data-lucide="alert-circle"></i>
                        <span><?= e($flashError) ?></span>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>/index.php?controller=auth&action=doRegistro"
                      method="POST" class="auth-form" id="registerForm">
                    <?= csrfField() ?>

                    <div class="form-group">
                        <label class="form-label">Nombre completo *</label>
                        <input type="text" class="form-control" name="nombre"
                               placeholder="Tu nombre completo" required minlength="3" autofocus>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Correo electronico *</label>
                        <input type="email" class="form-control" name="email"
                               placeholder="tu@email.com" required autocomplete="email">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Telefono <span class="text-muted">(opcional)</span></label>
                        <input type="tel" class="form-control" name="telefono"
                               placeholder="+57 300 000 0000">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Contrasena *</label>
                            <input type="password" class="form-control" name="password"
                                   placeholder="Minimo 6 caracteres" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirmar *</label>
                            <input type="password" class="form-control" name="password_confirm"
                                   placeholder="Repite tu contrasena" required>
                        </div>
                    </div>

                    <div class="password-strength">
                        <div class="password-strength-bar">
                            <div class="password-strength-fill" id="strengthFill"></div>
                        </div>
                        <span class="password-strength-text" id="strengthText"></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i data-lucide="user-plus"></i> Crear Cuenta
                    </button>
                </form>

                <div class="divider-text">o</div>
                <div class="auth-footer">
                    <p>Ya tienes cuenta?
                        <a href="<?= BASE_URL ?>/index.php?controller=auth&action=login" class="auth-link">
                            Inicia sesion
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= SCRIPTS_URL ?>/auth.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
