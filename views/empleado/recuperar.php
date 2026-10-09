<?php
/**
 * UH-28 — Recuperar contraseña (3 pasos)
 */
$paso     = (int)($_GET['paso'] ?? 1);
$rawError = getFlash('error') ?? '';
$rawOk    = getFlash('success') ?? '';

$pasoLabels = ['1' => 'Solicitar', '2' => 'Verificar', '3' => 'Nueva clave'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña — <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="<?= SCRIPTS_URL ?>/lucide.js"></script>

    <link rel="stylesheet" href="<?= STYLES_URL ?>/empleado.css">
</head>
<body class="emp-body">
<div class="emp-login-page">
    <div class="emp-login-card" style="max-width:480px;">

        <!-- Logo -->
        <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=login"
           style="display:inline-flex;align-items:center;gap:6px;font-size:.82rem;color:var(--emp-primary);font-weight:600;text-decoration:none;margin-bottom:24px;">
            <i data-lucide="arrow-left" style="width:15px;height:15px;"></i>
            Volver al login
        </a>

        <h1 class="emp-login-title">Recuperar contrasena</h1>
        <p class="emp-login-subtitle">Restablece el acceso a tu cuenta en unos pasos.</p>

        <!-- Stepper -->
        <div class="emp-recovery-steps" style="margin-bottom:28px;">
            <?php for ($i = 1; $i <= 3; $i++): ?>
                <div class="emp-step <?= $i === $paso ? 'active' : ($i < $paso ? 'done' : '') ?>">
                    <div class="emp-step-num">
                        <?= $i < $paso ? '✓' : $i ?>
                    </div>
                    <span><?= $pasoLabels[$i] ?></span>
                </div>
                <?php if ($i < 3): ?>
                    <div class="emp-step-line <?= $i < $paso ? 'done' : '' ?>"></div>
                <?php endif; ?>
            <?php endfor; ?>
        </div>

        <?php if ($rawError): ?>
            <?php
            $msgMap = [
                'email_invalido'  => 'Ingresa un correo electronico valido.',
                'email_no_existe' => 'No existe una cuenta con ese correo.',
                'min_8'           => 'La contrasena debe tener al menos 8 caracteres.',
                'mayuscula'       => 'Incluye al menos una mayuscula.',
                'numero'          => 'Incluye al menos un numero.',
                'no_coincide'     => 'Las contrasenas no coinciden.',
            ];
            $parts = explode('|', $rawError);
            $msgs  = array_map(fn($k) => $msgMap[$k] ?? $k, $parts);
            ?>
            <div class="emp-alert emp-alert-error emp-animate-in" style="margin-bottom:16px;">
                <i data-lucide="alert-circle"></i>
                <div><?= implode('<br>', array_map('e', $msgs)) ?></div>
            </div>
        <?php endif; ?>

        <?php if ($rawOk === 'correo_enviado'): ?>
            <div class="emp-alert emp-alert-success emp-animate-in" style="margin-bottom:16px;">
                <i data-lucide="check-circle"></i>
                <span>Enlace enviado. Revisa tu correo.</span>
            </div>
        <?php endif; ?>

        <!-- ── PASO 1: Solicitar ── -->
        <?php if ($paso === 1): ?>
            <form action="<?= BASE_URL ?>/index.php?controller=empleado&action=doRecuperar"
                  method="POST">
                <?= csrfField() ?>

                <div class="emp-form-group">
                    <label class="emp-label">Correo electronico <span>*</span></label>
                    <div class="emp-input-icon">
                        <span class="emp-input-icon-left"><i data-lucide="mail"></i></span>
                        <input type="email" name="email" class="emp-input"
                               placeholder="tu@central-box.com" required autofocus>
                    </div>
                </div>

                <button type="submit" class="emp-btn emp-btn-primary emp-btn-lg emp-btn-block">
                    <i data-lucide="send"></i> Enviar enlace de recuperacion
                </button>
            </form>

        <!-- ── PASO 2: Confirmación de envío ── -->
        <?php elseif ($paso === 2): ?>
            <div style="text-align:center;padding:20px 0;">
                <div style="font-size:3rem;margin-bottom:16px;">📧</div>
                <h3 style="font-size:1.1rem;font-weight:800;margin-bottom:8px;">Revisa tu correo</h3>
                <p style="font-size:.875rem;color:var(--emp-text-sec);margin-bottom:24px;">
                    Te enviamos un enlace para restablecer tu contrasena. Puede tardar unos minutos.
                </p>
                <!-- En demo, acceso directo al paso 3 -->
                <a href="<?= BASE_URL ?>/index.php?controller=empleado&action=recuperar&paso=3"
                   class="emp-btn emp-btn-primary">
                    <i data-lucide="key"></i> Ingresar nueva contrasena
                </a>
            </div>

        <!-- ── PASO 3: Nueva contraseña ── -->
        <?php elseif ($paso === 3): ?>
            <form action="<?= BASE_URL ?>/index.php?controller=empleado&action=nuevaPassword"
                  method="POST" id="newPwdForm">
                <?= csrfField() ?>

                <div class="emp-form-group">
                    <label class="emp-label">Nueva contrasena <span>*</span></label>
                    <div class="emp-input-icon" style="position:relative;">
                        <span class="emp-input-icon-left"><i data-lucide="lock"></i></span>
                        <input type="password" id="newPwd" name="password" class="emp-input"
                               placeholder="Min. 8 caracteres" required style="padding-right:44px;">
                        <button type="button" class="emp-pwd-toggle" id="tgl1">
                            <i data-lucide="eye"></i>
                        </button>
                    </div>
                    <!-- Barra de fuerza -->
                    <div class="emp-pwd-strength" style="margin-top:8px;">
                        <div class="emp-pwd-bar"><div class="emp-pwd-fill" id="pwdFill"></div></div>
                        <span class="emp-pwd-text" id="pwdText" style="font-size:.72rem;color:var(--emp-text-sec);"></span>
                    </div>
                    <!-- Requisitos -->
                    <div class="emp-pwd-rules">
                        <div class="emp-pwd-rule" id="r_len"><i data-lucide="circle"></i> Al menos 8 caracteres</div>
                        <div class="emp-pwd-rule" id="r_may"><i data-lucide="circle"></i> Al menos una mayuscula</div>
                        <div class="emp-pwd-rule" id="r_num"><i data-lucide="circle"></i> Al menos un numero</div>
                    </div>
                </div>

                <div class="emp-form-group">
                    <label class="emp-label">Confirmar contrasena <span>*</span></label>
                    <div class="emp-input-icon" style="position:relative;">
                        <span class="emp-input-icon-left"><i data-lucide="lock"></i></span>
                        <input type="password" id="confPwd" name="password_confirm" class="emp-input"
                               placeholder="Repite la contrasena" required style="padding-right:44px;">
                        <button type="button" class="emp-pwd-toggle" id="tgl2">
                            <i data-lucide="eye"></i>
                        </button>
                    </div>
                    <div class="emp-field-error" id="errConf" style="display:none;">
                        <i data-lucide="alert-circle"></i> Las contrasenas no coinciden
                    </div>
                </div>

                <button type="submit" class="emp-btn emp-btn-primary emp-btn-lg emp-btn-block">
                    <i data-lucide="save"></i> Guardar nueva contrasena
                </button>
            </form>
        <?php endif; ?>

    </div>
</div>

<script>lucide.createIcons();</script>
<script>
// Toggle password
['tgl1','tgl2'].forEach(function(id) {
    const btn = document.getElementById(id);
    if (!btn) return;
    btn.addEventListener('click', function () {
        const input = this.previousElementSibling?.previousElementSibling || this.closest('.emp-input-icon').querySelector('input');
        const icon  = this.querySelector('i');
        const isP   = input.type === 'password';
        input.type  = isP ? 'text' : 'password';
        icon.setAttribute('data-lucide', isP ? 'eye-off' : 'eye');
        lucide.createIcons();
    });
});

// Password strength
const newPwdInput = document.getElementById('newPwd');
if (newPwdInput) {
    newPwdInput.addEventListener('input', function () {
        const v = this.value;
        const rules = [
            { id: 'r_len', ok: v.length >= 8 },
            { id: 'r_may', ok: /[A-Z]/.test(v) },
            { id: 'r_num', ok: /[0-9]/.test(v) },
        ];
        let score = rules.filter(r => r.ok).length;

        rules.forEach(function (r) {
            const el = document.getElementById(r.id);
            const ic = el?.querySelector('i');
            if (r.ok) {
                el?.classList.add('ok');
                ic?.setAttribute('data-lucide', 'check-circle');
            } else {
                el?.classList.remove('ok');
                ic?.setAttribute('data-lucide', 'circle');
            }
        });
        lucide.createIcons();

        const fill = document.getElementById('pwdFill');
        const text = document.getElementById('pwdText');
        const colors = ['', '#EF4444', '#F59E0B', '#10B981'];
        const labels = ['', 'Debil', 'Regular', 'Segura'];
        if (fill) { fill.style.width = (score / 3 * 100) + '%'; fill.style.background = colors[score] || '#D1D5DB'; }
        if (text) { text.textContent = labels[score] || ''; text.style.color = colors[score] || '#9CA3AF'; }
    });
}

// Validar confirmación
document.getElementById('newPwdForm')?.addEventListener('submit', function (e) {
    const p1 = document.getElementById('newPwd')?.value;
    const p2 = document.getElementById('confPwd')?.value;
    if (p1 !== p2) {
        e.preventDefault();
        document.getElementById('confPwd')?.classList.add('error');
        document.getElementById('errConf').style.display = 'flex';
        lucide.createIcons();
    }
});
</script>
</body>
</html>
