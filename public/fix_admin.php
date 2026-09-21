<?php
/**
 * Script de diagnóstico y reparación del admin
 * Acceder: http://localhost:8000/fix_admin.php
 * ELIMINAR después de usar por seguridad
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$conn = Database::getInstance()->getConnection();

echo "<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#eee;}
.ok{color:#2ecc71;} .err{color:#e74c3c;} .warn{color:#f39c12;}
pre{background:#16213e;padding:12px;border-radius:6px;margin:8px 0;}
h2{color:#3498db;border-bottom:1px solid #333;padding-bottom:8px;}
.box{background:#16213e;padding:16px;border-radius:8px;margin:12px 0;border-left:4px solid #3498db;}
</style>";

echo "<h1>🔧 Diagnóstico de Login — central_box</h1>";

// 1. Verificar si la tabla usuarios existe
echo "<h2>1. Tabla 'usuarios'</h2>";
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() > 0) {
        echo "<pre class='ok'>✓ Tabla 'usuarios' existe</pre>";
    } else {
        echo "<pre class='err'>✗ Tabla 'usuarios' NO existe — ejecuta seed_data.php primero</pre>";
        exit;
    }
} catch (Exception $e) {
    echo "<pre class='err'>✗ Error: " . $e->getMessage() . "</pre>";
    exit;
}

// 2. Mostrar todos los usuarios
echo "<h2>2. Usuarios en la base de datos</h2>";
$stmt = $conn->query("SELECT id, nombre, email, rol, activo FROM usuarios ORDER BY id");
$usuarios = $stmt->fetchAll();

if (empty($usuarios)) {
    echo "<pre class='warn'>⚠ No hay usuarios en la tabla</pre>";
} else {
    echo "<pre>";
    echo str_pad("ID", 5) . str_pad("Nombre", 25) . str_pad("Email", 35) . str_pad("Rol", 12) . "Activo\n";
    echo str_repeat("-", 80) . "\n";
    foreach ($usuarios as $u) {
        $activo = ($u['activo'] ?? 1) ? 'SI' : 'NO';
        echo str_pad($u['id'], 5) . str_pad($u['nombre'], 25) . str_pad($u['email'], 35) . str_pad($u['rol'], 12) . $activo . "\n";
    }
    echo "</pre>";
}

// 3. Crear/actualizar admin
echo "<h2>3. Crear / Resetear Admin</h2>";

$adminEmail    = 'admin@central-box.com';
$adminPassword = 'Admin123';
$adminHash     = password_hash($adminPassword, PASSWORD_DEFAULT);

// Verificar si admin existe
$stmt = $conn->prepare("SELECT id, activo FROM usuarios WHERE email = ?");
$stmt->execute([$adminEmail]);
$admin = $stmt->fetch();

if ($admin) {
    // Actualizar hash y activar
    $stmt = $conn->prepare("UPDATE usuarios SET password = ?, activo = 1, rol = 'admin' WHERE email = ?");
    $stmt->execute([$adminHash, $adminEmail]);
    echo "<pre class='ok'>✓ Admin encontrado (ID: {$admin['id']}) — contraseña reseteada y cuenta activada</pre>";
} else {
    // Crear admin nuevo
    // Detectar columnas disponibles
    $cols = $conn->query("DESCRIBE usuarios")->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('telefono', $cols)) {
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol, activo, telefono) VALUES (?, ?, ?, 'admin', 1, '')");
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol, activo) VALUES (?, ?, ?, 'admin', 1)");
    }
    $stmt->execute(['Administrador', $adminEmail, $adminHash]);
    echo "<pre class='ok'>✓ Admin creado exitosamente (ID: " . $conn->lastInsertId() . ")</pre>";
}

// 4. Verificar que el login funcionará
echo "<h2>4. Verificación de credenciales</h2>";
$stmt = $conn->prepare("SELECT id, nombre, email, rol, password, activo FROM usuarios WHERE email = ?");
$stmt->execute([$adminEmail]);
$user = $stmt->fetch();

if ($user) {
    $match = password_verify($adminPassword, $user['password']);
    $activo = ($user['activo'] ?? 1) ? 'SI' : 'NO';
    
    echo "<div class='box'>";
    echo "<p>Email: <strong>{$user['email']}</strong></p>";
    echo "<p>Rol: <strong>{$user['rol']}</strong></p>";
    echo "<p>Activo: <strong>$activo</strong></p>";
    echo "<p>Contraseña válida: <strong class='" . ($match ? 'ok' : 'err') . "'>" . ($match ? '✓ SÍ' : '✗ NO') . "</strong></p>";
    echo "</div>";
}

echo "<h2>✅ Resultado</h2>";
echo "<div class='box' style='border-color:#2ecc71'>";
echo "<p><strong>Email:</strong> <code>admin@central-box.com</code></p>";
echo "<p><strong>Contraseña:</strong> <code>Admin123</code></p>";
echo "<p style='margin-top:12px'>";
echo "<a href='/index.php?controller=auth&action=login' style='background:#2ecc71;color:#000;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold'>→ Ir al Login</a>";
echo "</p>";
echo "</div>";

echo "<p style='color:#e74c3c;margin-top:30px'>⚠ <strong>IMPORTANTE:</strong> Elimina este archivo después de usarlo.</p>";
