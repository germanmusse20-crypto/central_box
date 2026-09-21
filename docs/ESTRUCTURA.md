# Estructura del proyecto central_box

```
central_box/
├── .htaccess                  # Reescritura de URLs (raíz)
│
├── config/
│   ├── config.php             # Constantes, rutas, helpers globales
│   └── database.php           # Conexión PDO (Singleton)
│
├── controllers/               # Un controlador por módulo (PascalCase)
│   ├── AuthController.php
│   ├── CarritoController.php
│   ├── CategoriaController.php
│   ├── DashboardController.php
│   ├── HomeController.php
│   ├── InventarioController.php
│   ├── PagosController.php
│   ├── PedidoController.php
│   ├── ProductoController.php
│   ├── PromocionesController.php
│   ├── ProveedoresController.php
│   ├── ReportesController.php
│   ├── UsuariosController.php
│   └── VentasController.php
│
├── models/                    # Un modelo por entidad (PascalCase)
│   ├── Carrito.php            # Solo $_SESSION, sin BD
│   ├── Categoria.php
│   ├── Inventario.php
│   ├── Pago.php
│   ├── Pedido.php
│   ├── Producto.php
│   ├── Promocion.php
│   ├── Proveedor.php
│   ├── Reporte.php
│   └── Usuario.php
│
├── views/
│   ├── home.php               # Página de inicio pública
│   │
│   ├── auth/                  # Vistas públicas (sin login)
│   │   ├── login.php          # Formulario de login
│   │   └── registro_admin.php # Registro de administrador
│   │
│   ├── Layouts/               # Plantillas compartidas
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── sidebar.php
│   │
│   ├── compartido/            # Vistas para TODOS los roles autenticados
│   │   ├── dashboard/
│   │   │   └── index.php      # Dashboard contextual por rol
│   │   ├── pedidos/
│   │   │   ├── index.php
│   │   │   └── detalle.php
│   │   ├── productos/
│   │   │   └── lista.php
│   │   ├── inventario/
│   │   │   └── index.php
│   │   └── perfil.php         # Perfil de usuario
│   │
│   ├── admin/                 # Vistas exclusivas del ADMINISTRADOR
│   │   ├── usuarios/
│   │   │   ├── lista.php
│   │   │   └── nuevo.php
│   │   ├── categorias/
│   │   │   ├── lista.php
│   │   │   └── form.php
│   │   ├── reportes/
│   │   │   └── index.php
│   │   ├── pagos/
│   │   │   └── index.php
│   │   └── promociones/
│   │       └── index.php
│   │
│   ├── vendedor/              # Vistas para ADMIN y VENDEDOR
│   │   ├── ventas/
│   │   │   ├── online.php
│   │   │   └── punto_venta.php
│   │   └── proveedores/
│   │       ├── index.php
│   │       ├── detalle.php
│   │       └── nuevo.php
│   │
│   └── cliente/               # Vistas exclusivas del CLIENTE
│       ├── pedidos/           # Mis pedidos
│       └── carrito/           # Carrito de compras
│
├── Styles/                    # CSS del proyecto
│   ├── main.css               # Design system (variables, layout, sidebar)
│   ├── components.css         # Botones, cards, tablas, alertas
│   ├── auth.css               # Login y registro
│   ├── home.css               # Página de inicio pública
│   ├── dashboard.css          # Dashboard
│   ├── pedidos.css            # Módulo pedidos
│   ├── proveedores.css        # Módulo proveedores
│   ├── ventas.css             # Módulo ventas y POS
│   └── pagos.css              # Módulo pagos
│
├── scripts/                   # JavaScript del proyecto
│   ├── main.js                # Sidebar, toasts, helpers globales
│   ├── auth.js                # Login/registro validaciones
│   ├── dashboard.js           # Animación contadores
│   ├── pos.js                 # Punto de venta POS
│   └── punto_venta.js         # Lógica formulario punto de venta
│
├── img/                       # Imágenes del proyecto
│   ├── logo.png               # Logo central_box
│   └── productos/             # Imágenes de productos
│
├── sql/
│   └── central_box.sql        # Esquema completo de BD
│
├── public/                    # Único punto de entrada web
│   ├── .htaccess              # Reescritura de URLs
│   └── index.php              # Front controller
│
├── docs/                      # Documentación del proyecto
│   └── ESTRUCTURA.md          # Este archivo
│
└── tests/                     # Scripts de prueba y utilidades (NO en producción)
    ├── login_auto.php         # Acceso rápido por rol para pruebas
    ├── seed_data.php          # Datos de prueba
    ├── seed_proveedores.php   # Datos proveedores de prueba
    └── ...
```

## Roles y acceso

| Rol       | Dashboard | Ventas | Pedidos | Inventario | Proveedores | Admin |
|-----------|-----------|--------|---------|------------|-------------|-------|
| admin     | ✓         | ✓      | ✓       | ✓          | ✓           | ✓     |
| vendedor  | ✓         | ✓      | ✓       | ✓          | ✓           | ✗     |
| cliente   | ✓         | ✗      | Solo propios | ✗     | ✗           | ✗     |

## Acceso local (desarrollo)

- **Login normal:** `http://localhost/central_box/public/index.php?controller=auth&action=login`
- **Acceso rápido admin:** `http://localhost/central_box/tests/login_auto.php?rol=admin`
- **Acceso rápido vendedor:** `http://localhost/central_box/tests/login_auto.php?rol=vendedor`
- **Acceso rápido cliente:** `http://localhost/central_box/tests/login_auto.php?rol=cliente`

## Credenciales de administrador

- **Email:** `admin@central-box.com`
- **Contraseña:** `Admin2025*`
