<?php
// Test script para verificar la configuración

try {
    $conn = new PDO('mysql:host=127.0.0.1;port=3306;dbname=central_box;charset=utf8mb4', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "✓ Conexión a la BD exitosa\n\n";
    
    // Verificar tabla usuarios
    echo "=== TABLA USUARIOS ===\n";
    $result = $conn->query("DESCRIBE usuarios");
    echo "Columnas:\n";
    foreach ($result->fetchAll() as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    // Verificar usuario admin
    echo "\n=== USUARIO ADMIN ===\n";
    $stmt = $conn->prepare("SELECT id, nombre, email, rol FROM usuarios LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch();
    if ($user) {
        echo "ID: " . $user['id'] . "\n";
        echo "Nombre: " . $user['nombre'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Rol: " . $user['rol'] . "\n";
    }
    
    // Verificar tabla pedidos
    echo "\n=== TABLA PEDIDOS ===\n";
    $result = $conn->query("DESCRIBE pedidos");
    echo "Columnas:\n";
    foreach ($result->fetchAll() as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    
    // Test: obtener estadísticas de pedidos (la consulta que falló)
    echo "\n=== TEST: getEstadisticas() ===\n";
    $stmt = $conn->query("
        SELECT p.*, u.nombre AS cliente_nombre
        FROM pedidos p
        INNER JOIN usuarios u ON p.cliente_id = u.id
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $pedidos = $stmt->fetchAll();
    echo "Resultado: " . count($pedidos) . " pedidos (esperado 0 en BD vacía)\n";
    
    echo "\n✓ TODAS LAS PRUEBAS PASARON\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
?>
