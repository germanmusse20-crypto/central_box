<?php
/**
 * Vista Login — Formulario de inicio de sesión
 * No usa el layout (header/sidebar/footer) porque es página pública
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Iniciar sesión en central_box">
    <title>Iniciar Sesión — central_box</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= SCRIPTS_URL ?>/lucide.js"></script>

    <link rel="stylesheet" href="<?= STYLES_URL ?>/auth.css">
</head>
<body class="auth-body">
    <!-- Animated background -->
    <div class="auth-bg">
        <div class="auth-bg-orb auth-bg-orb-1"></div>
        <div class="auth-bg-orb auth-bg-orb-2"></div>
        <div class="auth-bg-orb auth-bg-orb-3"></div>
    </div>

    <div class="auth-container">
        <!-- Left panel — Branding -->
        <div class="auth-panel-left" style="position: relative;">
            <a href="<?= BASE_URL ?>/" class="auth-back-home-green" id="btnBackHome">
                <i data-lucide="arrow-left"></i>
                Volver al inicio
            </a>
            <div class="auth-branding">
                <div class="auth-logo">
                    <img src="<?= IMG_URL ?>/logo.png" alt="central_box" class="auth-logo-img">
                </div>
                <p class="auth-brand-subtitle">Tu tienda virtual de confianza</p>

                <div class="auth-features">
                    <div class="auth-feature">
                        <div class="auth-feature-icon"><i data-lucide="shield-check"></i></div>
                        <div>
                            <strong>Seguro</strong>
                            <span>Datos protegidos y encriptados</span>
                        </div>
                    </div>
                    <div class="auth-feature">
                        <div class="auth-feature-icon"><i data-lucide="zap"></i></div>
                        <div>
                            <strong>Rápido</strong>
                            <span>Gestión ágil de tu inventario</span>
                        </div>
                    </div>
                    <div class="auth-feature">
                        <div class="auth-feature-icon"><i data-lucide="bar-chart-3"></i></div>
                        <div>
                            <strong>Analítico</strong>
                            <span>Reportes y estadísticas en tiempo real</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right panel — Login form -->
        <div class="auth-panel-right">
            <div class="auth-form-container">

                <div class="auth-form-header">
                    <h2>Bienvenido de vuelta</h2>
                    <p>Ingresa tus credenciales para continuar</p>
                </div>

                <?php
                $flashError   = getFlash('error');
                $flashSuccess = getFlash('success');
                ?>

                <?php if ($flashError): ?>
                    <div class="alert alert-error animate-fade-in">
                        <i data-lucide="alert-circle"></i>
                        <span><?= e($flashError) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($flashSuccess): ?>
                    <div class="alert alert-success animate-fade-in">
                        <i data-lucide="check-circle"></i>
                        <span><?= e($flashSuccess) ?></span>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>/index.php?controller=auth&action=doLogin" method="POST" class="auth-form" id="loginForm">
                    <?= csrfField() ?>

                    <div class="form-group">
                        <label class="form-label" for="email">
                            <i data-lucide="mail" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>
                            Correo electrónico
                        </label>
                        <input type="email"
                               class="form-control"
                               id="email"
                               name="email"
                               placeholder="tu@email.com"
                               required
                               autocomplete="email"
                               autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">
                            <i data-lucide="lock" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>
                            Contraseña
                        </label>
                        <div class="password-wrapper">
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   placeholder="••••••••"
                                   required
                                   autocomplete="current-password">
                            <button type="button" class="password-toggle" id="togglePassword" tabindex="-1">
                                <i data-lucide="eye"></i>
                            </button>
                        </div>
                    </div>

                    <div style="text-align:right;margin:-8px 0 20px;">
                        <a href="<?= BASE_URL ?>/index.php?controller=auth&action=recuperarCliente"
                           class="auth-link" style="font-size:.82rem;">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="btnLogin">
                        <i data-lucide="log-in"></i>
                        Iniciar Sesión
                    </button>
                </form>

                <div class="divider-text">o</div>

                <div class="auth-footer">
                    <p>¿No tienes una cuenta?
                        <a href="<?= BASE_URL ?>/index.php?controller=auth&action=registro" class="auth-link">Regístrate aquí</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= SCRIPTS_URL ?>/auth.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
