<?php
// Valores seguros por defecto
$nombre = $usuario['nombre'] ?? 'Usuario';
$email = $usuario['email'] ?? 'No disponible';
$telefono = $usuario['telefono'] ?? 'No registrado';
$rol = $usuario['rol'] ?? 'Usuario';
$activo = isset($usuario['activo']) ? (int)$usuario['activo'] : 1;

// Inicial del usuario
$inicial = strtoupper(substr(trim($nombre), 0, 1));
$avatar = $usuario['avatar'] ?? '';

// Nombre del rol para mostrar
$rolMostrar = match ($rol) {
    'admin' => 'Administrador',
    'vendedor' => 'Vendedor',
    default => ucfirst($rol)
};
$perfilReturnUrl = $perfilReturnUrl ?? BASE_URL . '/index.php?controller=dashboard&action=index';
$perfilEditUrl = $perfilEditUrl ?? '#';
?>

<style>
    .perfil-container {
        padding: 30px;
        max-width: 1100px;
        margin: 0 auto;
    }

    .perfil-header {
        margin-bottom: 25px;
    }

    .perfil-header h1 {
        margin: 0 0 8px;
        font-size: 32px;
        font-weight: 700;
        color: #10251c;
    }

    .perfil-header p {
        margin: 0;
        font-size: 15px;
        color: #6f8479;
    }

    .perfil-grid {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 24px;
    }

    .perfil-card {
        background: #ffffff;
        border: 1px solid #dceee5;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(30, 80, 55, 0.05);
    }

    /* Tarjeta izquierda */
    .perfil-resumen {
        padding: 30px;
        text-align: center;
    }

    .perfil-avatar {
        width: 110px;
        height: 110px;
        margin: 0 auto 18px;
        border-radius: 50%;
        background: #20bb69;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 42px;
        font-weight: 700;
        box-shadow: 0 10px 25px rgba(32, 187, 105, 0.20);
    }
    .perfil-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }

    .perfil-nombre {
        font-size: 22px;
        font-weight: 700;
        color: #163126;
        margin-bottom: 7px;
    }

    .perfil-email {
        font-size: 14px;
        color: #7b8d84;
        word-break: break-word;
    }

    .perfil-rol {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 16px;
        padding: 7px 14px;
        border-radius: 30px;
        background: #e9f9f0;
        color: #14a85b;
        font-size: 13px;
        font-weight: 600;
    }

    .perfil-estado {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #edf3ef;
        font-size: 13px;
        color: #6f8178;
    }

    .estado-activo {
        color: #16a65b;
        font-weight: 600;
    }

    .estado-inactivo {
        color: #dc4f4f;
        font-weight: 600;
    }

    /* Tarjeta derecha */
    .perfil-datos {
        padding: 30px;
    }

    .perfil-seccion-titulo {
        margin: 0 0 22px;
        font-size: 19px;
        font-weight: 700;
        color: #183229;
    }

    .perfil-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .perfil-info {
        padding: 18px;
        border-radius: 14px;
        background: #f7fbf9;
        border: 1px solid #e4efe9;
    }

    .perfil-info-label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #81948a;
    }

    .perfil-info-value {
        font-size: 15px;
        font-weight: 600;
        color: #243b31;
    }

    .perfil-acciones {
        display: flex;
        gap: 12px;
        margin-top: 28px;
        padding-top: 24px;
        border-top: 1px solid #e8f0ec;
    }

    .btn-perfil {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 18px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: .2s ease;
        cursor: pointer;
    }

    .btn-editar {
        background: #20bb69;
        color: #fff;
    }

    .btn-editar:hover {
        background: #17a75c;
        transform: translateY(-1px);
    }

    .btn-volver {
        background: #f0f5f2;
        color: #4d685b;
    }

    .btn-volver:hover {
        background: #e5eee9;
    }

    @media (max-width: 800px) {
        .perfil-grid {
            grid-template-columns: 1fr;
        }

        .perfil-info-grid {
            grid-template-columns: 1fr;
        }

        .perfil-container {
            padding: 20px;
        }
    }
</style>

<div class="perfil-container">

    <div class="perfil-header">
        <h1>Mi perfil</h1>
        <p>Consulta y administra la información de tu cuenta.</p>
    </div>

    <div class="perfil-grid">

        <!-- RESUMEN -->
        <div class="perfil-card perfil-resumen">

            <div class="perfil-avatar">
                <?php if ($avatar): ?>
                    <img src="<?= IMG_URL ?>/empleados/<?= e($avatar) ?>" alt="Foto de <?= e($nombre) ?>">
                <?php else: ?>
                    <?= htmlspecialchars($inicial) ?>
                <?php endif; ?>
            </div>

            <div class="perfil-nombre">
                <?= htmlspecialchars($nombre) ?>
            </div>

            <div class="perfil-email">
                <?= htmlspecialchars($email) ?>
            </div>

            <div class="perfil-rol">
                <span>●</span>
                <?= htmlspecialchars($rolMostrar) ?>
            </div>

            <div class="perfil-estado">
                Estado:
                <?php if ($activo): ?>
                    <span class="estado-activo">Activo</span>
                <?php else: ?>
                    <span class="estado-inactivo">Inactivo</span>
                <?php endif; ?>
            </div>

        </div>

        <!-- INFORMACIÓN -->
        <div class="perfil-card perfil-datos">

            <h2 class="perfil-seccion-titulo">
                Información personal
            </h2>

            <div class="perfil-info-grid">

                <div class="perfil-info">
                    <span class="perfil-info-label">
                        Nombre completo
                    </span>

                    <div class="perfil-info-value">
                        <?= htmlspecialchars($nombre) ?>
                    </div>
                </div>

                <div class="perfil-info">
                    <span class="perfil-info-label">
                        Correo electrónico
                    </span>

                    <div class="perfil-info-value">
                        <?= htmlspecialchars($email) ?>
                    </div>
                </div>

                <div class="perfil-info">
                    <span class="perfil-info-label">
                        Teléfono
                    </span>

                    <div class="perfil-info-value">
                        <?= htmlspecialchars($telefono) ?>
                    </div>
                </div>

                <div class="perfil-info">
                    <span class="perfil-info-label">
                        Rol
                    </span>

                    <div class="perfil-info-value">
                        <?= htmlspecialchars($rolMostrar) ?>
                    </div>
                </div>

            </div>

            <div class="perfil-acciones">

                <a
                    href="<?= e($perfilReturnUrl) ?>"
                    class="btn-perfil btn-volver"
                >
                    ← Volver
                </a>

                <a
                    href="<?= e($perfilEditUrl) ?>"
                    class="btn-perfil btn-editar"
                >
                    ✎ Editar perfil
                </a>

            </div>

        </div>

    </div>

</div>