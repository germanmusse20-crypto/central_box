<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>central_box - Página de Inicio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fa-question-circle./6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2.5em;
        }

        .logo {
            font-size: 3em;
            margin-bottom: 20px;
            color: #4CAF50;
        }

        p {
            color: #666;
            font-size: 1.1em;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .button-group {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin: 30px 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px 30px;
            border: none;
            border-radius: 6px;
            font-size: 1em;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-primary {
            background: #4CAF50;
            color: white;
        }

        .btn-primary:hover {
            background: #388E3C;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }

        .btn-secondary {
            background: #667eea;
            color: white;
        }

        .btn-secondary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: #666;
            border: 2px solid #ddd;
        }

        .btn-outline:hover {
            border-color: #667eea;
            color: #667eea;
        }

        .info-box {
            background: #f5f5f5;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            text-align: left;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 0.9em;
        }

        .info-box strong {
            color: #333;
        }

        .info-box p {
            margin: 5px 0;
            font-size: 0.9em;
        }

        .feature-list {
            text-align: left;
            margin: 20px 0;
            list-style: none;
        }

        .feature-list li {
            padding: 10px 0;
            color: #666;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .feature-list i {
            color: #4CAF50;
            font-size: 1.2em;
        }

        .divider {
            height: 1px;
            background: #ddd;
            margin: 30px 0;
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
            <i class="fas fa-store"></i>
        </div>
        
        <h1>central_box</h1>
        <p>Tu tienda virtual de confianza</p>

        <div class="info-box">
            <strong><i class="fas fa-check-circle"></i> Página de Inicio Creada</strong>
            <p>La página de inicio está completamente diseñada y lista para usar.</p>
        </div>

        <ul class="feature-list">
            <li><i class="fas fa-paint-brush"></i> <strong>Diseño Responsivo:</strong> Se adapta a cualquier dispositivo</li>
            <li><i class="fas fa-code"></i> <strong>CSS Moderno:</strong> Estilos guardados en <code>Styles/home.css</code></li>
            <li><i class="fas fa-shopping-bag"></i> <strong>Características Completas:</strong> Navegación, hero, productos y footer</li>
            <li><i class="fas fa-lock"></i> <strong>Sin Login Requerido:</strong> Accesible para todos los visitantes</li>
        </ul>

        <div class="divider"></div>

        <div class="button-group">
            <a href="http://localhost/central_box/public/index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Ir a la Página de Inicio
            </a>
            
            <a href="http://localhost/central_box/index_test.php" class="btn btn-secondary">
                <i class="fas fa-cog"></i> Panel de Control
            </a>
        </div>

        <div class="divider"></div>

        <div class="info-box">
            <strong><i class="fas fa-info-circle"></i> Información de Archivos</strong>
            <p><strong>Vista:</strong> /views/home.php</p>
            <p><strong>Estilos:</strong> /Styles/home.css</p>
            <p><strong>Controlador:</strong> /controllers/HomeController.php</p>
        </div>

        <p class="footer-text">
            © 2025 central_box. Todos los derechos reservados.
        </p>
    </div>
</body>
</html>
