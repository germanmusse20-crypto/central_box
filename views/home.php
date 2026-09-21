<?php
/**
 * Vista de Página de Inicio (Home)
 * Se carga sin necesidad de estar logueado
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="central_box Store — Tu tienda virtual de confianza. Bebidas, accesorios para dispositivos y papelería de la mejor calidad.">
    <title>central_box — Tu tienda virtual de confianza</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Estilos de la página -->
    <link rel="stylesheet" href="<?php echo STYLES_URL; ?>/home.css">
</head>
<body>

    <!-- ============================================
         HEADER & NAVEGACIÓN
         ============================================ -->
    <header>
        <div class="header-container">
            <a href="<?php echo BASE_URL; ?>" class="logo">
                <img src="<?php echo IMG_URL; ?>/logo.png" alt="central_box" class="logo-img">
            </a>

            <nav>
                <a href="#inicio" class="active">
                    <i class="fas fa-home"></i> Inicio
                </a>
                <a href="#productos">
                    <i class="fas fa-shopping-bag"></i> Productos
                </a>
                <a href="#categorias">
                    <i class="fas fa-th-large"></i> Categorías
                </a>
                <a href="#promociones">
                    <i class="fas fa-tag"></i> Promociones
                </a>
                <a href="#nosotros">
                    <i class="fas fa-info-circle"></i> Nosotros
                </a>
            </nav>

            <div class="nav-actions">
                <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=login" class="btn btn-login" id="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                </a>
                <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=registro" class="btn btn-register" id="btn-register">
                    <i class="fas fa-user-plus"></i> Regístrate
                </a>
            </div>
        </div>
    </header>

    <!-- ============================================
         HERO SECTION
         ============================================ -->
    <section class="hero" id="inicio">
        <div class="hero-container">
            <div class="hero-content">
                <p class="small-tag">
                    <i class="fas fa-star"></i>
                    Bienvenido a central_box
                </p>
                <h1>Tu tienda virtual de <span class="highlight">confianza</span></h1>
                <p>Encuentra bebidas, accesorios para dispositivos y artículos de papelería de la mejor calidad.</p>
                <p>Compra fácil, rápido y seguro desde la comodidad de tu hogar.</p>
                <a href="<?php echo BASE_URL; ?>/index.php?controller=productos" class="btn btn-hero" id="btn-comenzar">
                    <i class="fas fa-shopping-cart"></i> Comenzar a Comprar
                </a>
            </div>

            <div class="hero-image">
                <img src="<?php echo IMG_URL; ?>/hero-products.png"
                     alt="Productos de central_box"
                     class="hero-product-img"
                     loading="eager">
            </div>
        </div>
    </section>

    <!-- ============================================
         STATS BAR
         ============================================ -->
    <div class="stats-bar">
        <div class="stats-container">
            <div class="stat-item">
                <div class="stat-value">+500</div>
                <div class="stat-label">Productos</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">+1.2K</div>
                <div class="stat-label">Clientes felices</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">98%</div>
                <div class="stat-label">Satisfacción</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">24/7</div>
                <div class="stat-label">Soporte</div>
            </div>
        </div>
    </div>

    <!-- ============================================
         CARACTERÍSTICAS
         ============================================ -->
    <section class="features" id="caracteristicas">
        <div class="features-container">
            <div class="section-heading">
                <span class="section-badge"><i class="fas fa-bolt"></i> Por qué elegirnos</span>
                <h2>Todo lo que necesitas en un solo lugar</h2>
                <p>Diseñado para ofrecerte la mejor experiencia de compra online.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt" style="color: var(--primary);"></i>
                    </div>
                    <h3>Compras seguras</h3>
                    <p>Protegemos tus datos con cifrado de extremo a extremo en cada transacción.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-star" style="color: var(--primary);"></i>
                    </div>
                    <h3>Productos de calidad</h3>
                    <p>Solo trabajamos con proveedores verificados que ofrecen lo mejor del mercado.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-tag" style="color: var(--primary);"></i>
                    </div>
                    <h3>Promociones exclusivas</h3>
                    <p>Ofertas y descuentos especiales todos los días para nuestros clientes.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shopping-bag" style="color: var(--primary);"></i>
                    </div>
                    <h3>Variedad de productos</h3>
                    <p>Bebidas, tecnología y papelería en un solo lugar para tu comodidad.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-credit-card" style="color: var(--primary);"></i>
                    </div>
                    <h3>Pagos seguros</h3>
                    <p>Múltiples métodos de pago con total seguridad y respaldo garantizado.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset" style="color: var(--primary);"></i>
                    </div>
                    <h3>Soporte al cliente</h3>
                    <p>Estamos aquí para ayudarte en todo momento. Respuesta rápida garantizada.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================
         PRODUCTOS
         ============================================ -->
    <section id="productos" class="features" style="background: var(--bg-white);">
        <div class="features-container">
            <div class="section-heading">
                <span class="section-badge"><i class="fas fa-box-open"></i> Catálogo</span>
                <h2>Nuestros Productos</h2>
                <p>Descubre nuestra gran variedad de artículos de alta calidad listos para ti.</p>
            </div>
            <?php if (!$productos): ?>
                <div class="empty-placeholder">
                    <i class="fas fa-box-open"></i>
                    <p>Estamos actualizando nuestro inventario. Vuelve pronto.</p>
                </div>
            <?php else: ?>
                <div class="home-products-grid">
                    <?php foreach ($productos as $producto): ?>
                        <article class="home-product-card">
                            <a href="<?php echo BASE_URL; ?>/index.php?controller=productos&action=catalogo" class="home-product-media">
                                <img src="<?php echo IMG_URL; ?>/productos/<?php echo e($producto['imagen']); ?>"
                                     alt="<?php echo e($producto['nombre']); ?>" loading="lazy"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                <span class="home-product-fallback"><i class="fas fa-box-open"></i></span>
                                <span class="home-product-badge"><i class="fas fa-check"></i> Disponible</span>
                            </a>
                            <div class="home-product-info">
                                <span class="home-product-category"><?php echo e($producto['categoria_nombre'] ?? 'Producto'); ?></span>
                                <h3><?php echo e($producto['nombre']); ?></h3>
                                <div class="home-product-bottom">
                                    <strong><?php echo formatPrice((float)$producto['precio']); ?></strong>
                                    <span><?php echo (int)$producto['stock']; ?> disponibles</span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="home-products-cta">
                    <a href="<?php echo BASE_URL; ?>/index.php?controller=productos&action=catalogo" class="btn btn-primary">
                        <i class="fas fa-store"></i> Ver todo el catálogo
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ============================================
         CATEGORÍAS
         ============================================ -->
    <section id="categorias" class="features">
        <div class="features-container">
            <div class="section-heading">
                <span class="section-badge"><i class="fas fa-th-large"></i> Secciones</span>
                <h2>Explora por Categorías</h2>
                <p>Encuentra rápidamente lo que necesitas navegando por nuestras secciones especializadas.</p>
            </div>
            <?php if (!$categorias): ?>
                <div class="empty-placeholder">
                    <i class="fas fa-layer-group"></i>
                    <p>Estamos organizando nuestras categorías. Vuelve pronto.</p>
                </div>
            <?php else: ?>
                <div class="home-category-grid">
                    <?php foreach ($categorias as $categoria):
                        $nombreCategoria = strtolower((string)$categoria['nombre']);
                        $iconoCategoria = str_contains($nombreCategoria, 'bebid') ? 'fa-mug-hot' :
                            (str_contains($nombreCategoria, 'tecnolog') ? 'fa-laptop' :
                            (str_contains($nombreCategoria, 'papeler') ? 'fa-pen-ruler' :
                            (str_contains($nombreCategoria, 'ropa') ? 'fa-shirt' : 'fa-layer-group')));
                    ?>
                        <a href="<?php echo BASE_URL; ?>/index.php?controller=productos&action=catalogo&categoria=<?php echo (int)$categoria['id']; ?>" class="home-category-card">
                            <span class="home-category-icon"><i class="fas <?php echo $iconoCategoria; ?>"></i></span>
                            <span class="home-category-copy">
                                <strong><?php echo e($categoria['nombre']); ?></strong>
                                <span><?php echo (int)$categoria['total_productos']; ?> producto<?php echo (int)$categoria['total_productos'] === 1 ? '' : 's'; ?></span>
                            </span>
                            <i class="fas fa-arrow-right home-category-arrow"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ============================================
         BANNER PROMOCIONAL
         ============================================ -->
    <section class="banner-section" id="promociones">
        <div class="banner-content">
            <h2>¡Comenzar a Comprar es Fácil!</h2>
            <p>Descubre miles de productos a precios increíbles. Regístrate hoy y obtén un descuento especial en tu primera compra.</p>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=registro" class="btn btn-primary" id="btn-registro-banner">
                <i class="fas fa-user-plus"></i> Regístrate Ahora
            </a>
        </div>
    </section>

    <!-- ============================================
         FOOTER
         ============================================ -->
    <footer id="nosotros">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4><i class="fas fa-info-circle"></i> Acerca de Nosotros</h4>
                    <ul>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Quiénes somos</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Misión y Visión</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Blog</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Carreras</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4><i class="fas fa-shopping-bag"></i> Compras</h4>
                    <ul>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Productos</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Categorías</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Ofertas</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Promociones</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4><i class="fas fa-question-circle"></i> Ayuda</h4>
                    <ul>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Preguntas Frecuentes</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Envíos</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Devoluciones</a></li>
                        <li><a href="#"><i class="fas fa-angle-right"></i> Política de Privacidad</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4><i class="fas fa-phone"></i> Contacto</h4>
                    <ul>
                        <li>
                            <a href="https://wa.me/573013619644" target="_blank">
                                <i class="fab fa-whatsapp"></i> +57 301 361 9644
                            </a>
                        </li>
                        <li>
                            <a href="mailto:germanmusse20@gmail.com">
                                <i class="fas fa-envelope"></i> germanmusse20@gmail.com
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <i class="fas fa-map-marker-alt"></i> Calle 123 #45-67, Bogotá
                            </a>
                        </li>
                        <li>
                            <a href="https://facebook.com/centralbox_oficial" target="_blank">
                                <i class="fab fa-facebook"></i> Facebook
                            </a>
                        </li>
                        <li>
                            <a href="https://instagram.com/centralbox.co" target="_blank">
                                <i class="fab fa-instagram"></i> Instagram
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="footer-info">
                    <p><i class="fas fa-check-circle"></i> Compra 100% segura</p>
                    <p><i class="fas fa-phone"></i> Soporte: 301 361 9644</p>
                    <p><i class="fas fa-envelope"></i> germanmusse20@gmail.com</p>
                </div>
                <p class="footer-copyright">© 2025 central_box. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

</body>
</html>
