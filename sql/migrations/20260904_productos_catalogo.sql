-- Catalogo inicial para pruebas de central_box.
-- Agrega 17 productos y completa 20 productos en total.

INSERT INTO productos
(nombre, descripcion, precio, stock, stock_minimo, imagen, categoria_id, vendedor_id, activo)
VALUES
('Gaseosa Cola 1.5L', 'Bebida gaseosa sabor cola para compartir.', 6500.00, 24, 5, 'gaseosa-cola.svg', 1, 1, 1),
('Jugo de Naranja 1L', 'Jugo de naranja listo para consumir.', 7000.00, 18, 5, 'jugo-naranja.svg', 1, 1, 1),
('Te Helado Limon 500ml', 'Te helado refrescante con sabor a limon.', 4500.00, 30, 6, 'te-helado.svg', 1, 1, 1),
('Cuaderno Profesional', 'Cuaderno de 100 hojas para estudio y oficina.', 12000.00, 20, 5, 'cuaderno-profesional.svg', 3, 1, 1),
('Lapiceros Pack x3', 'Paquete de lapiceros de tinta azul, negra y roja.', 5500.00, 35, 8, 'lapiceros-pack.svg', 3, 1, 1),
('Marcadores de Colores', 'Set de marcadores para resaltar y dibujar.', 9000.00, 16, 4, 'marcadores-colores.svg', 3, 1, 1),
('Carpeta Tamano Oficio', 'Carpeta plastica resistente para documentos.', 4500.00, 28, 6, 'carpeta-oficio.svg', 3, 1, 1),
('Camiseta Basica Unisex', 'Camiseta comoda de uso diario.', 28000.00, 14, 4, 'camiseta-basica.svg', 4, 1, 1),
('Jean Clasico', 'Jean de corte clasico para uso casual.', 85000.00, 10, 3, 'jean-clasico.svg', 4, 1, 1),
('Gorra Unisex', 'Gorra ajustable para complementar cualquier estilo.', 22000.00, 12, 3, 'gorra-unisex.svg', 4, 1, 1),
('Chaqueta Impermeable', 'Chaqueta liviana para protegerse de la lluvia.', 110000.00, 7, 3, 'chaqueta-impermeable.svg', 4, 1, 1),
('Teclado USB', 'Teclado de conexion USB para computador.', 38000.00, 9, 3, 'teclado-usb.svg', 2, 1, 1),
('Mouse Inalambrico', 'Mouse ergonomico con conexion inalambrica.', 42000.00, 11, 3, 'mouse-inalambrico.svg', 2, 1, 1),
('Audifonos Bluetooth', 'Audifonos inalambricos para musica y llamadas.', 65000.00, 8, 3, 'audifonos-bluetooth.svg', 2, 1, 1),
('Cargador USB-C', 'Cargador rapido compatible con dispositivos USB-C.', 32000.00, 15, 4, 'cargador-usb-c.svg', 2, 1, 1),
('Memoria USB 64GB', 'Memoria portatil para guardar archivos.', 30000.00, 13, 4, 'memoria-usb-64gb.svg', 2, 1, 1),
('Cable HDMI 2m', 'Cable HDMI para conectar pantallas y computadores.', 26000.00, 17, 4, 'cable-hdmi.svg', 2, 1, 1);
