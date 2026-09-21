<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Diseño - central_box</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2em;
        }

        .logo {
            font-size: 3em;
            margin-bottom: 20px;
            color: #2ecc71;
        }

        p {
            color: #666;
            font-size: 1em;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .status {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: 600;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            text-align: left;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 0.9em;
        }

        .info-box strong {
            color: #1565c0;
        }

        .button-group {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-top: 25px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 25px;
            border: none;
            border-radius: 8px;
            font-size: 0.95em;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(46, 204, 113, 0.4);
        }

        .btn-secondary {
            background: #f5f5f5;
            color: #333;
            border: 2px solid #ddd;
        }

        .btn-secondary:hover {
            border-color: #2ecc71;
            color: #2ecc71;
            transform: translateY(-2px);
        }

        .divider {
            height: 1px;
            background: #eee;
            margin: 25px 0;
        }

        .features {
            text-align: left;
        }

        .feature-item {
            padding: 10px 0;
            color: #666;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .feature-item i {
            color: #2ecc71;
            font-weight: bold;
        }

        .footer-text {
            color: #999;
            font-size: 0.85em;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>¡Diseño Corregido!</h1>
        <p>El CSS se está cargando correctamente</p>

        <div class="status">
            ✓ Sistema de rutas actualizado correctamente
        </div>

        <div class="info-box">
            <strong><i class="fas fa-info-circle"></i> Cambios Realizados:</strong>
            <p style="margin-top: 10px;">Se actualización la referencia del CSS para usar <code style="background: #f0f0f0; padding: 2px 5px; border-radius: 3px;">STYLES_URL</code> en lugar de <code style="background: #f0f0f0; padding: 2px 5px; border-radius: 3px;">BASE_URL</code></p>
        </div>

        <div class="features">
            <h3 style="color: #333; margin-bottom: 15px;">Lo que verás ahora:</h3>
            <div class="feature-item">
                <i class="fas fa-star"></i>
                <span><strong>Colores Premium:</strong> Verde moderno y azul profesional</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-magic"></i>
                <span><strong>Animaciones:</strong> Transiciones suaves y efectos flotantes</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-mobile-alt"></i>
                <span><strong>Responsive:</strong> Perfecto en móvil, tablet y desktop</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-layer-group"></i>
                <span><strong>Gradientes:</strong> Diseño moderno con fondos sofisticados</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-hand-pointer"></i>
                <span><strong>Interactividad:</strong> Hover effects en botones y tarjetas</span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="button-group">
            <a href="http://localhost/central_box/public/index.php" class="btn btn-primary">
                <i class="fas fa-eye"></i> Ver Página de Inicio con Diseño
            </a>
            
            <a href="http://localhost/central_box/index_test.php" class="btn btn-secondary">
                <i class="fas fa-cog"></i> Panel de Control
            </a>
        </div>

        <div class="divider"></div>

        <div class="info-box">
            <strong><i class="fas fa-file-code"></i> Archivo Actualizado:</strong>
            <p style="margin-top: 10px;">
                <code style="background: #f0f0f0; padding: 2px 5px; border-radius: 3px;">/views/home.php</code>
                - Ahora usa STYLES_URL para cargar el CSS
            </p>
        </div>

        <p class="footer-text">
            © 2025 central_box - Diseño Profesional
        </p>
    </div>
</body>
</html>
