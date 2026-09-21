<?php
/**
 * Vista Registro — Formulario de registro de nuevos usuarios
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Crear cuenta en central_box">
    <title>Crear Cuenta — central_box</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
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
                <p class="auth-brand-subtitle">Crea tu cuenta y empieza a comprar</p>

                <div class="auth-features">
                    <div class="auth-feature">
                        <div class="auth-feature-icon"><i data-lucide="user-plus"></i></div>
                        <div>
                            <strong>Fácil</strong>
                            <span>Registro en menos de 1 minuto</span>
                        </div>
                    </div>
                    <div class="auth-feature">
                        <div class="auth-feature-icon"><i data-lucide="truck"></i></div>
                        <div>
                            <strong>Envíos</strong>
                            <span>Seguimiento de tus pedidos</span>
                        </div>
                    </div>
                    <div class="auth-feature">
                        <div class="auth-feature-icon"><i data-lucide="heart"></i></div>
                        <div>
                            <strong>Favoritos</strong>
                            <span>Guarda tus productos preferidos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right panel — Register form -->
        <div class="auth-panel-right">
            <div class="auth-form-container">
                <div class="auth-form-header">
                    <h2>Crear Cuenta</h2>
                    <p>Completa tus datos para registrarte</p>
                </div>

                <?php $flashError = getFlash('error'); ?>
                <?php if ($flashError): ?>
                    <div class="alert alert-error animate-fade-in">
                        <i data-lucide="alert-circle"></i>
                        <span><?= e($flashError) ?></span>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>/index.php?controller=auth&action=doRegistro" method="POST" class="auth-form" id="registerForm">
                    <?= csrfField() ?>

                    <div class="form-group">
                        <label class="form-label" for="nombre">
                            <i data-lucide="user" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>
                            Nombre completo
                        </label>
                        <input type="text"
                               class="form-control"
                               id="nombre"
                               name="nombre"
                               placeholder="Tu nombre completo"
                               required
                               minlength="3"
                               autofocus>
                    </div>

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
                               autocomplete="email">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="telefono">
                            <i data-lucide="phone" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>
                            Teléfono <span class="text-muted">(opcional)</span>
                        </label>
                        <input type="tel"
                               class="form-control"
                               id="telefono"
                               name="telefono"
                               placeholder="+57 300 000 0000">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="password">
                                <i data-lucide="lock" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>
                                Contraseña
                            </label>
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   placeholder="Mínimo 6 caracteres"
                                   required
                                   minlength="6">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="password_confirm">
                                <i data-lucide="lock" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;"></i>
                                Confirmar
                            </label>
                            <input type="password"
                                   class="form-control"
                                   id="password_confirm"
                                   name="password_confirm"
                                   placeholder="Repite tu contraseña"
                                   required>
                        </div>
                    </div>

                    <div class="password-strength" id="passwordStrength">
                        <div class="password-strength-bar">
                            <div class="password-strength-fill" id="strengthFill"></div>
                        </div>
                        <span class="password-strength-text" id="strengthText"></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="btnRegister">
                        <i data-lucide="user-plus"></i>
                        Crear Cuenta
                    </button>
                </form>

                <div class="divider-text">o</div>

                <div class="auth-footer">
                    <p>¿Ya tienes una cuenta?
                        <a href="<?= BASE_URL ?>/index.php?controller=auth&action=login" class="auth-link">Inicia sesión</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= SCRIPTS_URL ?>/auth.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
