<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>central_box - Nuevo Diseño</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2ecc71 0%, #3498db 100%);
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
            max-width: 700px;
            width: 100%;
            padding: 50px 40px;
            text-align: center;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 2.5em;
        }

        .logo {
            font-size: 3.5em;
            margin-bottom: 20px;
            color: #2ecc71;
        }

        .subtitle {
            color: #7f8c8d;
            font-size: 1.2em;
            margin-bottom: 30px;
        }

        .updates {
            background: #ecf0f1;
            border-left: 4px solid #2ecc71;
            padding: 20px;
            border-radius: 8px;
            text-align: left;
            margin: 30px 0;
        }

        .updates h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .updates ul {
            list-style: none;
        }

        .updates li {
            padding: 8px 0;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .updates i {
            color: #2ecc71;
            font-weight: bold;
        }

        .features {
            margin: 30px 0;
            text-align: left;
        }

        .feature-item {
            padding: 12px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .feature-item i {
            color: #2ecc71;
            font-size: 1.2em;
        }

        .feature-item span {
            color: #2c3e50;
            font-weight: 500;
        }

        .button-group {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
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
            background: #ecf0f1;
            color: #2c3e50;
            border: 2px solid #2ecc71;
        }

        .btn-secondary:hover {
            background: #2ecc71;
            color: white;
            transform: translateY(-2px);
        }

        .divider {
            height: 1px;
            background: #ecf0f1;
            margin: 30px 0;
        }

        .footer-text {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-top: 20px;
        }

        .badge {
            display: inline-block;
            background: #2ecc71;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            margin-bottom: 15px;
            font-weight: 600;
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 1.8em;
            }

            .logo {
                font-size: 2.5em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <i class="fas fa-store"></i>
        </div>
        
        <h1>central_box</h1>
        <p class="subtitle">Tu tienda virtual de confianza</p>

        <span class="badge"><i class="fas fa-sparkles"></i> Nuevo Diseño Profesional</span>

        <div class="updates">
            <h3><i class="fas fa-star"></i> Mejoras del Diseño CSS</h3>
            <ul>
                <li><i class="fas fa-check"></i> <strong>Colores Premium:</strong> Verde y Azul con gradientes modernos</li>
                <li><i class="fas fa-check"></i> <strong>Animaciones Fluidas:</strong> Transiciones y efectos profesionales</li>
                <li><i class="fas fa-check"></i> <strong>Tipografía Mejorada:</strong> Fuentes grandes y legibles</li>
                <li><i class="fas fa-check"></i> <strong>Sombras Sofisticadas:</strong> Profundidad y elevación visual</li>
                <li><i class="fas fa-check"></i> <strong>Hover Effects:</strong> Interactividad en botones y tarjetas</li>
                <li><i class="fas fa-check"></i> <strong>Responsive Design:</strong> Perfecto en móvil, tablet y desktop</li>
            </ul>
        </div>

        <div class="features">
            <h3 style="color: #2c3e50; margin-bottom: 15px;">✨ Características Principales:</h3>
            <div class="feature-item">
                <i class="fas fa-palette"></i>
                <span>Paleta de colores moderna y coherente</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-magic"></i>
                <span>Animaciones y efectos suavizados</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-layer-group"></i>
                <span>Diseño en capas con gradientes sofisticados</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-mobile-alt"></i>
                <span>100% responsivo y optimizado para móvil</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-clock"></i>
                <span>Transiciones suaves y profesionales</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-code"></i>
                <span>CSS moderno sin dependencias externas</span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="button-group">
            <a href="http://localhost/central_box/public/index.php" class="btn btn-primary">
                <i class="fas fa-eye"></i> Ver Página de Inicio
            </a>
            
            <a href="http://localhost/central_box/index_test.php" class="btn btn-secondary">
                <i class="fas fa-cog"></i> Panel de Control
            </a>
        </div>

        <div class="divider"></div>

        <div style="background: #f0fdf4; padding: 20px; border-radius: 8px; border-left: 4px solid #2ecc71;">
            <p style="color: #2c3e50; margin-bottom: 10px;"><strong><i class="fas fa-info-circle"></i> Archivos Actualizados:</strong></p>
            <p style="color: #7f8c8d; font-size: 0.9em;">
                <i class="fas fa-file-code"></i> <code style="background: white; padding: 2px 6px; border-radius: 3px;">/Styles/home.css</code>
            </p>
        </div>

        <p class="footer-text">
            © 2025 central_box. Diseño profesional y responsivo.
        </p>
    </div>
</body>
</html>
