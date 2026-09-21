-- Estados permitidos para pedidos de clientes.
-- No aplica a pedidos_proveedor, que tiene su propio flujo.

UPDATE pedidos
SET estado = 'confirmado'
WHERE estado IN ('pendiente', 'enviado');

ALTER TABLE pedidos
    MODIFY COLUMN estado ENUM('confirmado','entregado','cancelado')
    NOT NULL DEFAULT 'confirmado';
