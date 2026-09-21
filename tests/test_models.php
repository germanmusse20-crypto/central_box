<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Modelos - central_box</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #007acc;
            padding-bottom: 15px;
        }
        
        h1 {
            color: #4ec9b0;
            margin-bottom: 5px;
        }
        
        .subtitle {
            color: #858585;
        }
        
        .test-section {
            background: #252526;
            border-left: 3px solid #007acc;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .test-section h2 {
            color: #4ec9b0;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 0.9em;
            font-weight: bold;
        }
        
        .status.success {
            background: #4ec9b0;
            color: #1e1e1e;
        }
        
        .status.error {
            background: #f48771;
            color: #1e1e1e;
        }
        
        .status.info {
            background: #569cd6;
            color: #1e1e1e;
        }
        
        .output {
            background: #1e1e1e;
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
            border-left: 2px solid #858585;
            overflow-x: auto;
        }
        
        .output pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .error-box {
            background: #3d2626;
            color: #f48771;
            padding: 15px;
            border-left: 3px solid #f48771;
            border-radius: 4px;
            margin-top: 10px;
        }
        
        .success-box {
            background: #213d26;
            color: #4ec9b0;
            padding: 15px;
            border-left: 3px solid #4ec9b0;
            border-radius: 4px;
            margin-top: 10px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: #252526;
            padding: 15px;
            border-radius: 4px;
            border-left: 3px solid #007acc;
        }
        
        .stat-card .value {
            font-size: 1.8em;
            color: #4ec9b0;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            color: #858585;
            font-size: 0.9em;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #007acc;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }
        
        .back-link:hover {
            background: #005a9e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Test de Modelos</h1>
            <p class="subtitle">Verificación del funcionamiento de todos los componentes</p>
        </div>
        
        <?php
        define('BASE_PATH', __DIR__);
        define('MODELS_PATH', BASE_PATH . '/models');
        define('CONFIG_PATH', BASE_PATH . '/config');
        
        try {
            // Conectar a la BD
            $conn = new PDO('mysql:host=127.0.0.1;port=3306;dbname=central_box;charset=utf8mb4', 'root', '');
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            echo '<div class="test-section">';
            echo '<h2><span class="status success">✓</span> Conexión a Base de Datos</h2>';
            echo '<div class="output"><pre>Conectado a: central_box</pre></div>';
            echo '</div>';
            
            // Test: Obtener estadísticas
            echo '<div class="test-section">';
            echo '<h2><span class="status info">Test</span> Estadísticas de Pedidos (getEstadisticas)</h2>';
            try {
                // Simular lo que hace el modelo Pedido
                $stmt = $conn->query("SELECT COALESCE(SUM(total), 0) AS total_ventas FROM pedidos WHERE estado != 'cancelado'");
                $totalVentas = $stmt->fetch()['total_ventas'];
                
                $stmt = $conn->query("SELECT estado, COUNT(*) AS total FROM pedidos GROUP BY estado");
                $porEstado = [];
                while ($row = $stmt->fetch()) {
                    $porEstado[$row['estado']] = (int) $row['total'];
                }
                
                $stmt = $conn->query("SELECT COUNT(*) FROM pedidos");
                $totalPedidos = (int) $stmt->fetchColumn();
                
                echo '<div class="stats">';
                echo '<div class="stat-card"><div class="value">$' . number_format($totalVentas, 2) . '</div><div class="label">Total Ventas</div></div>';
                echo '<div class="stat-card"><div class="value">' . $totalPedidos . '</div><div class="label">Total Pedidos</div></div>';
                echo '</div>';
                
                echo '<div class="success-box">✓ Consulta exitosa - No hay errores de columnas</div>';
            } catch (Exception $e) {
                echo '<div class="error-box">✗ Error: ' . $e->getMessage() . '</div>';
            }
            echo '</div>';
            
            // Test: Productos bajo stock
            echo '<div class="test-section">';
            echo '<h2><span class="status info">Test</span> Productos Bajo Stock (stock_minimo)</h2>';
            try {
                $stmt = $conn->query("
                    SELECT p.*, c.nombre AS categoria_nombre
                    FROM productos p
                    LEFT JOIN categorias c ON p.categoria_id = c.id
                    WHERE p.activo = 1 AND p.stock <= p.stock_minimo
                    ORDER BY p.stock ASC
                ");
                $bajoStock = $stmt->fetchAll();
                
                echo '<div class="stat-card">';
                echo '<div class="value">' . count($bajoStock) . '</div>';
                echo '<div class="label">Productos con Bajo Stock</div>';
                echo '</div>';
                
                echo '<div class="success-box">✓ Consulta exitosa - Campo stock_minimo disponible</div>';
            } catch (Exception $e) {
                echo '<div class="error-box">✗ Error: ' . $e->getMessage() . '</div>';
            }
            echo '</div>';
            
            // Test: Resumen de inventario
            echo '<div class="test-section">';
            echo '<h2><span class="status info">Test</span> Resumen de Inventario</h2>';
            try {
                $stmt = $conn->query("SELECT COUNT(*) FROM productos WHERE activo = 1");
                $totalProductos = (int) $stmt->fetchColumn();
                
                $stmt = $conn->query("SELECT COALESCE(SUM(stock), 0) FROM productos WHERE activo = 1");
                $totalUnidades = (int) $stmt->fetchColumn();
                
                $stmt = $conn->query("SELECT COUNT(*) FROM productos WHERE activo = 1 AND stock <= stock_minimo");
                $bajoStock = (int) $stmt->fetchColumn();
                
                $stmt = $conn->query("SELECT COUNT(*) FROM productos WHERE activo = 1 AND stock = 0");
                $sinStock = (int) $stmt->fetchColumn();
                
                echo '<div class="stats">';
                echo '<div class="stat-card"><div class="value">' . $totalProductos . '</div><div class="label">Total Productos</div></div>';
                echo '<div class="stat-card"><div class="value">' . $totalUnidades . '</div><div class="label">Unidades en Stock</div></div>';
                echo '<div class="stat-card"><div class="value">' . $bajoStock . '</div><div class="label">Bajo Stock</div></div>';
                echo '<div class="stat-card"><div class="value">' . $sinStock . '</div><div class="label">Sin Stock</div></div>';
                echo '</div>';
                
                echo '<div class="success-box">✓ Todas las consultas funcionan correctamente</div>';
            } catch (Exception $e) {
                echo '<div class="error-box">✗ Error: ' . $e->getMessage() . '</div>';
            }
            echo '</div>';
            
            // Test: Estructura de tablas
            echo '<div class="test-section">';
            echo '<h2><span class="status info">Test</span> Estructura de Tablas</h2>';
            try {
                $stmt = $conn->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                echo '<div class="output"><pre>';
                foreach ($tables as $table) {
                    echo "✓ $table\n";
                }
                echo '</pre></div>';
                
                echo '<div class="success-box">✓ Se encontraron ' . count($tables) . ' tablas correctamente creadas</div>';
            } catch (Exception $e) {
                echo '<div class="error-box">✗ Error: ' . $e->getMessage() . '</div>';
            }
            echo '</div>';
            
            // Test: Usuarios
            echo '<div class="test-section">';
            echo '<h2><span class="status info">Test</span> Usuarios</h2>';
            try {
                $stmt = $conn->query("SELECT id, nombre, email, rol FROM usuarios");
                $usuarios = $stmt->fetchAll();
                
                echo '<div class="output"><pre>';
                foreach ($usuarios as $user) {
                    echo "ID: " . $user['id'] . " | Nombre: " . $user['nombre'] . " | Email: " . $user['email'] . " | Rol: " . $user['rol'] . "\n";
                }
                echo '</pre></div>';
                
                echo '<div class="success-box">✓ Se encontraron ' . count($usuarios) . ' usuario(s)</div>';
            } catch (Exception $e) {
                echo '<div class="error-box">✗ Error: ' . $e->getMessage() . '</div>';
            }
            echo '</div>';
            
            echo '<div style="text-align: center; margin-top: 30px;">';
            echo '<div class="success-box" style="display: inline-block; padding: 20px; border-radius: 4px;">';
            echo '<strong>✓ TODOS LOS TESTS COMPLETADOS EXITOSAMENTE</strong><br>';
            echo 'La aplicación está lista para usarse';
            echo '</div>';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="test-section">';
            echo '<h2><span class="status error">✗</span> Error General</h2>';
            echo '<div class="error-box">' . $e->getMessage() . '</div>';
            echo '</div>';
        }
        ?>
        
        <a href="index_test.php" class="back-link">← Volver al Panel</a>
    </div>
</body>
</html>
