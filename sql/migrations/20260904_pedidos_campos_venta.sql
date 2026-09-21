-- Actualiza bases creadas con una version anterior de central_box.
-- En versiones antiguas de MySQL/MariaDB se deben ejecutar solo las
-- sentencias de las columnas que aun no existan.

ALTER TABLE pedidos
    ADD COLUMN direccion_envio VARCHAR(200) NULL AFTER estado;

ALTER TABLE pedidos
    ADD COLUMN notas TEXT NULL AFTER metodo_pago;
