-- =========================================================
-- BASE DE DATOS: central_box
-- Esquema compatible con los modelos PHP del proyecto
-- =========================================================

CREATE DATABASE IF NOT EXISTS central_box
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE central_box;

DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS persona;

-- =========================================================
-- 1. USUARIOS
-- =========================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100)        NOT NULL,
    email       VARCHAR(150)        NOT NULL UNIQUE,
    password    VARCHAR(255)        NOT NULL,
    rol         ENUM('admin','vendedor','cliente') NOT NULL DEFAULT 'cliente',
    telefono    VARCHAR(20)         NULL,
    direccion   VARCHAR(200)        NULL,
    avatar      VARCHAR(255)        NULL,
    activo      TINYINT(1)          NOT NULL DEFAULT 1,
    created_at  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 2. CATEGORIAS
-- =========================================================
CREATE TABLE IF NOT EXISTS categorias (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(80)         NOT NULL UNIQUE,
    descripcion VARCHAR(255)        NULL,
    imagen      VARCHAR(255)        NULL,
    activa      TINYINT(1)          NOT NULL DEFAULT 1,
    created_at  DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 3. PRODUCTOS
-- =========================================================
CREATE TABLE IF NOT EXISTS productos (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(100)       NOT NULL,
    descripcion  TEXT               NULL,
    precio       DECIMAL(12,2)      NOT NULL DEFAULT 0.00,
    stock        INT                NOT NULL DEFAULT 0,
    stock_minimo INT                NOT NULL DEFAULT 5,
    imagen       VARCHAR(255)       NULL,
    categoria_id INT UNSIGNED       NULL,
    vendedor_id  INT UNSIGNED       NULL,
    activo       TINYINT(1)         NOT NULL DEFAULT 1,
    created_at   DATETIME           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_producto_categoria
        FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        ON DELETE SET NULL ON UPDATE CASCADE,

    CONSTRAINT fk_producto_vendedor
        FOREIGN KEY (vendedor_id) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 4. PEDIDOS
-- =========================================================
CREATE TABLE IF NOT EXISTS pedidos (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id       INT UNSIGNED       NOT NULL,
    total            DECIMAL(12,2)      NOT NULL DEFAULT 0.00,
    estado           ENUM('confirmado','entregado','cancelado') NOT NULL DEFAULT 'confirmado',
    direccion_envio  VARCHAR(200)       NULL,
    metodo_pago      VARCHAR(50)        NOT NULL DEFAULT 'efectivo',
    notas            TEXT               NULL,
    created_at       DATETIME           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_pedido_cliente
        FOREIGN KEY (cliente_id) REFERENCES usuarios(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 5. DETALLE PEDIDOS
-- =========================================================
CREATE TABLE IF NOT EXISTS detalle_pedidos (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id        INT UNSIGNED       NOT NULL,
    producto_id      INT UNSIGNED       NOT NULL,
    cantidad         INT                NOT NULL DEFAULT 1,
    precio_unitario  DECIMAL(12,2)      NOT NULL,
    subtotal         DECIMAL(12,2)      NOT NULL,

    CONSTRAINT fk_detalle_pedido
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_detalle_producto
        FOREIGN KEY (producto_id) REFERENCES productos(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 6. MOVIMIENTOS DE INVENTARIO
-- =========================================================
CREATE TABLE IF NOT EXISTS movimientos_inventario (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    producto_id     INT UNSIGNED       NOT NULL,
    usuario_id      INT UNSIGNED       NOT NULL,
    tipo            ENUM('entrada','salida','ajuste') NOT NULL,
    cantidad        INT                NOT NULL,
    stock_anterior  INT                NOT NULL,
    stock_nuevo     INT                NOT NULL,
    motivo          VARCHAR(255)       NULL,
    created_at      DATETIME           NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_mov_producto
        FOREIGN KEY (producto_id) REFERENCES productos(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_mov_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- DATOS INICIALES
-- =========================================================

-- ── Administrador principal ──────────────────────────────
-- Email:      admin@central-box.com
-- Contraseña: Admin2025*
INSERT INTO usuarios (nombre, email, password, rol, activo)
VALUES (
    'Germán musse',
    'admin@central-box.com',
    '$2y$10$aPyUQgOSziAuSQ4t9oC9i.JHxb1ATgnQU9haZLx3Lsp6dZUHczdU.',
    'admin',
    1
);

-- ── Categorías de ejemplo ────────────────────────────────
INSERT INTO categorias (nombre, descripcion) VALUES
('Bebidas',     'Refrescos, jugos, agua y bebidas energéticas'),
('Tecnología',  'Accesorios y dispositivos electrónicos'),
('Papelería',   'Cuadernos, lápices, bolígrafos y más');

-- =========================================================
-- TABLA: proveedores
-- =========================================================
CREATE TABLE IF NOT EXISTS proveedores (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(150)  NOT NULL,
    contacto   VARCHAR(100)  NULL,
    email      VARCHAR(150)  NULL,
    telefono   VARCHAR(20)   NULL,
    direccion  VARCHAR(255)  NULL,
    activo     TINYINT(1)    NOT NULL DEFAULT 1,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- TABLA: pedidos_proveedor
-- Pedidos de compra realizados a proveedores
-- =========================================================
CREATE TABLE IF NOT EXISTS pedidos_proveedor (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proveedor_id   INT UNSIGNED    NOT NULL,
    usuario_id     INT UNSIGNED    NOT NULL,
    codigo         VARCHAR(30)     NOT NULL UNIQUE,
    estado         ENUM('pendiente','confirmado','pagado','recibido','cancelado')
                                   NOT NULL DEFAULT 'pendiente',
    total          DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
    motivo_cancel  VARCHAR(255)    NULL,
    notas          TEXT            NULL,
    fecha_entrega  DATE            NULL,
    created_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_pp_proveedor
        FOREIGN KEY (proveedor_id) REFERENCES proveedores(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    CONSTRAINT fk_pp_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- TABLA: detalle_pedido_proveedor
-- =========================================================
CREATE TABLE IF NOT EXISTS detalle_pedido_proveedor (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id       INT UNSIGNED    NOT NULL,
    producto_id     INT UNSIGNED    NOT NULL,
    cantidad        INT             NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(12,2)   NOT NULL,
    subtotal        DECIMAL(12,2)   NOT NULL,

    CONSTRAINT fk_dpp_pedido
        FOREIGN KEY (pedido_id) REFERENCES pedidos_proveedor(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_dpp_producto
        FOREIGN KEY (producto_id) REFERENCES productos(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS metodos_pago (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    estado ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO metodos_pago (nombre, descripcion, estado) VALUES
    ('Efectivo', 'Pago recibido en efectivo.', 'activo'),
    ('Tarjeta', 'Pago con tarjeta de credito o debito.', 'activo'),
    ('Transferencia', 'Pago mediante transferencia bancaria.', 'activo');

-- =========================================================
-- 8. PROMOCIONES Y SOLICITUDES DE PROMOCION
-- =========================================================
CREATE TABLE IF NOT EXISTS promociones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    producto_id INT UNSIGNED NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NULL,
    descuento DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('pendiente','activa','rechazada','eliminada') NOT NULL DEFAULT 'pendiente',
    activo TINYINT(1) NOT NULL DEFAULT 0,
    solicitado_por INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_promocion_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_promocion_usuario FOREIGN KEY (solicitado_por) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS solicitud_promocion (
    id_solicitud INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_empleado INT UNSIGNED NOT NULL,
    id_promocion INT UNSIGNED NULL,
    nombre_propuesta VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NULL,
    descuento_propuesto DECIMAL(5,2) NULL,
    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    CONSTRAINT fk_solicitud_empleado FOREIGN KEY (id_empleado) REFERENCES usuarios(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_solicitud_promocion FOREIGN KEY (id_promocion) REFERENCES promociones(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 9. PAGOS Y VENTAS
-- =========================================================
CREATE TABLE IF NOT EXISTS pago (
    id_pago INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_metodo_pago INT UNSIGNED NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    fecha_pago DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(30) NOT NULL DEFAULT 'pendiente',
    referencia VARCHAR(100) NULL,
    CONSTRAINT fk_pago_metodo FOREIGN KEY (id_metodo_pago) REFERENCES metodos_pago(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS venta (
    id_venta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT UNSIGNED NULL,
    id_usuario INT UNSIGNED NOT NULL,
    id_pago INT UNSIGNED NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    descuento DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    estado VARCHAR(30) NOT NULL DEFAULT 'completada',
    tipo_venta VARCHAR(30) NOT NULL DEFAULT 'PRESENCIAL',
    CONSTRAINT fk_venta_cliente FOREIGN KEY (id_cliente) REFERENCES usuarios(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_venta_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_venta_pago FOREIGN KEY (id_pago) REFERENCES pago(id_pago)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS detalle_venta (
    id_detalle_venta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_venta INT UNSIGNED NOT NULL,
    id_producto INT UNSIGNED NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_detalle_venta_venta FOREIGN KEY (id_venta) REFERENCES venta(id_venta)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detalle_venta_producto FOREIGN KEY (id_producto) REFERENCES productos(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- 10. CARRITO PERSISTENTE Y RECUPERACION DE CONTRASENA
-- =========================================================
CREATE TABLE IF NOT EXISTS carrito (
    id_carrito INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT UNSIGNED NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(30) NOT NULL DEFAULT 'activo',
    CONSTRAINT fk_carrito_cliente FOREIGN KEY (id_cliente) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS detalle_carrito (
    id_detalle_carrito INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_carrito INT UNSIGNED NOT NULL,
    id_producto INT UNSIGNED NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detalle_carrito_carrito FOREIGN KEY (id_carrito) REFERENCES carrito(id_carrito)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detalle_carrito_producto FOREIGN KEY (id_producto) REFERENCES productos(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY uq_detalle_carrito_producto (id_carrito, id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recuperacion_contrasena (
    id_recuperacion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion DATETIME NOT NULL,
    utilizada TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_recuperacion_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
