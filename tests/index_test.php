<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>central_box - Test & Setup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .card h2 {
            color: #667eea;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .status-badge.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-badge.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-list {
            list-style: none;
            font-size: 0.95em;
            line-height: 1.8;
        }
        
        .info-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-list li:last-child {
            border-bottom: none;
        }
        
        .info-list strong {
            color: #667eea;
            display: block;
        }
        
        .info-list span {
            color: #666;
        }
        
        .button-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 0.95em;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        
        .btn-wide {
            grid-column: 1 / -1;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #721c24;
            margin-top: 15px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #155724;
            margin-top: 15px;
        }
        
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        
        .icon {
            font-size: 1.2em;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.95em;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛒 central_box</h1>
            <p>Sistema de Gestión de Ventas - Centro de Control</p>
        </div>
        
        <div class="grid">
            <!-- Estado de la Base de Datos -->
            <div class="card">
                <h2><span class="icon">📊</span> Estado Base de Datos</h2>
                <?php
                try {
                    $conn = new PDO('mysql:host=127.0.0.1;port=3306;dbname=central_box;charset=utf8mb4', 'root', '');
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    echo '<span class="status-badge success">✓ Conectada</span>';
                    
                    // Verificar tablas
                    $stmt = $conn->query("SHOW TABLES");
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    echo '<ul class="info-list">';
                    echo '<li><strong>Tablas Creadas:</strong> <span>' . count($tables) . '</span></li>';
                    
                    // Contar registros
                    $usuarios = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
                    $productos = $conn->query("SELECT COUNT(*) FROM productos")->fetchColumn();
                    $pedidos = $conn->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
                    
                    echo '<li><strong>Usuarios:</strong> <span>' . $usuarios . '</span></li>';
                    echo '<li><strong>Productos:</strong> <span>' . $productos . '</span></li>';
                    echo '<li><strong>Pedidos:</strong> <span>' . $pedidos . '</span></li>';
                    echo '</ul>';
                    
                } catch (Exception $e) {
                    echo '<span class="status-badge error">✗ Error</span>';
                    echo '<div class="error-message">' . $e->getMessage() . '</div>';
                }
                ?>
            </div>
            
            <!-- Credenciales de Prueba -->
            <div class="card">
                <h2><span class="icon">🔐</span> Credenciales de Prueba</h2>
                <?php
                try {
                    $conn = new PDO('mysql:host=127.0.0.1;port=3306;dbname=central_box;charset=utf8mb4', 'root', '');
                    $stmt = $conn->prepare("SELECT id, nombre, email, rol FROM usuarios WHERE rol = 'admin' LIMIT 1");
                    $stmt->execute();
                    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($admin) {
                        echo '<span class="status-badge success">✓ Admin Disponible</span>';
                        echo '<ul class="info-list">';
                        echo '<li><strong>Email:</strong> <span><code>' . $admin['email'] . '</code></span></li>';
                        echo '<li><strong>Contraseña:</strong> <span><code>Admin123</code></span></li>';
                        echo '<li><strong>Rol:</strong> <span>' . ucfirst($admin['rol']) . '</span></li>';
                        echo '</ul>';
                    }
                } catch (Exception $e) {
                    echo '<div class="error-message">Error al cargar admin</div>';
                }
                ?>
            </div>
            
            <!-- Acciones Rápidas -->
            <div class="card">
                <h2><span class="icon">⚡</span> Acciones Rápidas</h2>
                <div class="button-group">
                    <a href="http://localhost/central_box/public/public/index.php?controller=auth&action=login" class="btn btn-primary">
                        🔓 Ir al Login
                    </a>
                    <form method="POST" action="login_auto.php" style="display: contents;">
                        <button type="submit" class="btn btn-success">
                            ⚡ Login Automático
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Panel de Control -->
        <div class="card full-width">
            <h2><span class="icon">🎮</span> Panel de Control</h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <!-- Test Modelos -->
                <div>
                    <h3 style="color: #667eea; margin-bottom: 15px;">Test de Modelos</h3>
                    <form method="POST" action="test_models.php">
                        <button type="submit" name="test" value="all" class="btn btn-secondary btn-wide">
                            🧪 Probar Todos los Modelos
                        </button>
                    </form>
                </div>
                
                <!-- Datos de Ejemplo -->
                <div>
                    <h3 style="color: #667eea; margin-bottom: 15px;">Insertar Datos</h3>
                    <form method="POST" action="seed_data.php">
                        <button type="submit" class="btn btn-secondary btn-wide">
                            📦 Cargar Datos de Ejemplo
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Información de Sistema -->
        <div class="card full-width" style="margin-top: 20px;">
            <h2><span class="icon">ℹ️</span> Información del Sistema</h2>
            <ul class="info-list">
                <li>
                    <strong>PHP Version:</strong>
                    <span><?php echo phpversion(); ?></span>
                </li>
                <li>
                    <strong>Base de Datos:</strong>
                    <span>MySQL 8.4.3</span>
                </li>
                <li>
                    <strong>Servidor Web:</strong>
                    <span>Apache (Laragon)</span>
                </li>
                <li>
                    <strong>URL Base:</strong>
                    <span><code>http://localhost/central_box/public</code></span>
                </li>
            </ul>
        </div>
    </div>
</body>
</html>
