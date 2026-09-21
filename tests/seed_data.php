<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Datos - central_box</title>
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
        
        .section {
            background: #252526;
            border-left: 3px solid #28a745;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .section h2 {
            color: #4ec9b0;
            margin-bottom: 15px;
        }
        
        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 0.9em;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .status.success {
            background: #28a745;
            color: white;
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
        
        .success-box {
            background: #213d26;
            color: #4ec9b0;
            padding: 15px;
            border-left: 3px solid #4ec9b0;
            border-radius: 4px;
            margin-top: 10px;
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
            <h1>📦 Cargar Datos de Ejemplo</h1>
            <p class="subtitle">Insertando datos iniciales en la base de datos</p>
        </div>
        
        <?php
        try {
            $conn = new PDO('mysql:host=127.0.0.1;port=3306;dbname=central_box;charset=utf8mb4', 'root', '');
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Usuarios adicionales
            echo '<div class="section">';
            echo '<h2>Insertando Usuarios...</h2>';
            
            $usuarios = [
                ['Carlos Mendoza', 'vendedor@central-box.com', 'Vendedor123', 'vendedor'],
                ['Juan Pérez', 'cliente1@central-box.com', 'Cliente123', 'cliente'],
                ['María García', 'cliente2@central-box.com', 'Cliente123', 'cliente'],
            ];
            
            $insertados = 0;
            foreach ($usuarios as $user) {
                try {
                    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
                    $stmt->execute([
                        $user[0],
                        $user[1],
                        password_hash($user[2], PASSWORD_DEFAULT),
                        $user[3]
                    ]);
                    $insertados++;
                    echo '<div class="output"><pre><span class="status success">✓</span> ' . $user[0] . ' (' . $user[1] . ') - ' . $user[3] . '</pre></div>';
                } catch (PDOException $e) {
                    if ($e->getCode() != 23000) { // Ignorar duplicados
                        throw $e;
                    }
                }
            }
            
            echo '<div class="success-box">✓ ' . $insertados . ' usuarios insertados</div>';
            echo '</div>';
            
            // Categorías
            echo '<div class="section">';
            echo '<h2>Insertando Categorías...</h2>';
            
            $categorias = [
                ['Electrónica', 'Dispositivos electrónicos modernos'],
                ['Ropa', 'Prendas de vestir variadas'],
                ['Hogar', 'Artículos para decoración y uso del hogar'],
                ['Deportes', 'Equipos y accesorios deportivos'],
                ['Libros', 'Libros y material de lectura'],
            ];
            
            $insertados = 0;
            foreach ($categorias as $cat) {
                try {
                    $stmt = $conn->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
                    $stmt->execute($cat);
                    $insertados++;
                    echo '<div class="output"><pre><span class="status success">✓</span> ' . $cat[0] . '</pre></div>';
                } catch (PDOException $e) {
                    if ($e->getCode() != 23000) { // Ignorar duplicados
                        throw $e;
                    }
                }
            }
            
            echo '<div class="success-box">✓ ' . $insertados . ' categorías insertadas</div>';
            echo '</div>';
            
            // Productos
            echo '<div class="section">';
            echo '<h2>Insertando Productos...</h2>';
            
            $productos = [
                ['Laptop Dell XPS 13', 'Laptop ultradelgada con pantalla 4K', 1, 2, 999.99, 15],
                ['iPhone 14 Pro', 'Smartphone premium de última generación', 1, 2, 1099.99, 8],
                ['Monitor LG 32 Pulgadas', 'Monitor 4K con HDR', 1, 2, 599.99, 12],
                ['Teclado Mecánico RGB', 'Teclado gaming con switches mecánicos', 1, 2, 149.99, 25],
                ['Camiseta Premium', 'Camiseta de algodón de alta calidad', 2, 2, 29.99, 50],
                ['Pantalones Vaqueros', 'Jeans clásicos varios talles', 2, 2, 59.99, 40],
                ['Lámpara LED Moderna', 'Lámpara de escritorio LED ajustable', 3, 2, 39.99, 20],
                ['Espejo de Pared', 'Espejo decorativo con marco', 3, 2, 49.99, 15],
                ['Balón de Fútbol', 'Balón profesional de competencia', 4, 2, 79.99, 10),
                ['Guantes de Boxeo', 'Guantes de boxeo profesionales', 4, 2, 89.99, 8],
            ];
            
            $insertados = 0;
            foreach ($productos as $prod) {
                try {
                    $stmt = $conn->prepare("
                        INSERT INTO productos (nombre, descripcion, categoria_id, vendedor_id, precio, stock, stock_minimo) 
                        VALUES (?, ?, ?, ?, ?, ?, 5)
                    ");
                    $stmt->execute([
                        $prod[0], $prod[1], $prod[2], $prod[3], $prod[4], $prod[5]
                    ]);
                    $insertados++;
                    echo '<div class="output"><pre><span class="status success">✓</span> ' . $prod[0] . ' - $' . number_format($prod[4], 2) . '</pre></div>';
                } catch (PDOException $e) {
                    if ($e->getCode() != 23000) {
                        throw $e;
                    }
                }
            }
            
            echo '<div class="success-box">✓ ' . $insertados . ' productos insertados</div>';
            echo '</div>';
            
            // Resumen final
            echo '<div class="section">';
            echo '<h2>Resumen de Datos</h2>';
            
            $totalUsuarios = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
            $totalCategorias = $conn->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
            $totalProductos = $conn->query("SELECT COUNT(*) FROM productos")->fetchColumn();
            $totalStock = $conn->query("SELECT COALESCE(SUM(stock), 0) FROM productos")->fetchColumn();
            
            echo '<div class="output"><pre>';
            echo "Usuarios:     " . $totalUsuarios . "\n";
            echo "Categorías:   " . $totalCategorias . "\n";
            echo "Productos:    " . $totalProductos . "\n";
            echo "Stock Total:  " . $totalStock . " unidades\n";
            echo '</pre></div>';
            
            echo '<div class="success-box">';
            echo '<strong>✓ DATOS CARGADOS EXITOSAMENTE</strong><br>';
            echo 'La base de datos está lista con datos de ejemplo';
            echo '</div>';
            
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="section">';
            echo '<h2>✗ Error</h2>';
            echo '<div class="output"><pre style="color: #f48771;">' . $e->getMessage() . '</pre></div>';
            echo '</div>';
        }
        ?>
        
        <a href="index_test.php" class="back-link">← Volver al Panel</a>
    </div>
</body>
</html>
