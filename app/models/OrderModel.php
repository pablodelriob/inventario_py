<?php
// app/models/OrderModel.php

class OrderModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    // Obtener todos los pedidos con información del cliente y usuario
    public function getAllOrders() {
        $this->db->query('SELECT
                            p.id_pedido,
                            p.fecha_pedido,
                            p.estado,
                            p.total_pedido,
                            c.nombre AS nombre_cliente,
                            c.apellido AS apellido_cliente,
                            u.nombre_usuario AS nombre_usuario_creacion
                          FROM pedidos p
                          JOIN clientes c ON p.id_cliente = c.id_cliente
                          JOIN usuarios u ON p.id_usuario_creacion = u.id_usuario
                          ORDER BY p.fecha_pedido DESC');
        return $this->db->resultSet();
    }

    // Obtener un pedido por su ID con sus detalles
    public function getOrderById($id_pedido) {
        $this->db->query('SELECT
                            p.id_pedido,
                            p.id_cliente,
                            p.fecha_pedido,
                            p.estado,
                            p.total_pedido,
                            p.observaciones,
                            c.nombre AS nombre_cliente,
                            c.apellido AS apellido_cliente,
                            c.email AS email_cliente,
                            c.telefono AS telefono_cliente,
                            c.direccion AS direccion_cliente,
                            u.nombre_usuario AS nombre_usuario_creacion
                          FROM pedidos p
                          JOIN clientes c ON p.id_cliente = c.id_cliente
                          JOIN usuarios u ON p.id_usuario_creacion = u.id_usuario
                          WHERE p.id_pedido = :id_pedido');
        $this->db->bind(':id_pedido', $id_pedido);
        return $this->db->single();
    }

    // Obtener los detalles de los ítems de un pedido
    public function getOrderDetails($id_pedido) {
        $this->db->query('SELECT
                            dp.cantidad,
                            dp.precio_unitario,
                            dp.subtotal,
                            prod.nombre_comercial AS producto_nombre,
                            prod.sku
                          FROM detalle_pedidos dp
                          JOIN productos prod ON dp.id_producto = prod.id_producto
                          WHERE dp.id_pedido = :id_pedido');
        $this->db->bind(':id_pedido', $id_pedido);
        return $this->db->resultSet();
    }

    // Crear un nuevo pedido
    public function createOrder($id_cliente, $id_usuario_creacion, $observaciones = null) {
        $this->db->query('INSERT INTO pedidos (id_cliente, id_usuario_creacion, observaciones) VALUES (:id_cliente, :id_usuario_creacion, :observaciones)');
        $this->db->bind(':id_cliente', $id_cliente);
        $this->db->bind(':id_usuario_creacion', $id_usuario_creacion);
        $this->db->bind(':observaciones', $observaciones);

        // Ejecuta la consulta del pedido principal
if ($this->db->execute()) {
    // Obtener el ID del pedido recién insertado
    $orderId = $this->db->lastInsertId(); // <-- Esto ahora funcionará
    
    // ... (lógica para insertar los detalles del pedido usando $orderId) ...
    // foreach ($products as $product) {
    //    $this->db->query('INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario)');
    //    $this->db->bind(':id_pedido', $orderId);
    //    // ... bindeos para los detalles ...
    //    $this->db->execute();
    // }
    return $orderId; // O un booleano, dependiendo de tu implementación
}
return false;
    }

    // Añadir ítems al detalle de un pedido
    public function addOrderItem($id_pedido, $id_producto, $cantidad, $precio_unitario) {
        $subtotal = $cantidad * $precio_unitario;
        $this->db->query('INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario, :subtotal)');
        $this->db->bind(':id_pedido', $id_pedido);
        $this->db->bind(':id_producto', $id_producto);
        $this->db->bind(':cantidad', $cantidad);
        $this->db->bind(':precio_unitario', $precio_unitario);
        $this->db->bind(':subtotal', $subtotal);

        return $this->db->execute();
    }

    // Actualizar el total de un pedido
    public function updateOrderTotal($id_pedido) {
        $this->db->query('UPDATE pedidos p
                          SET total_pedido = (SELECT SUM(dp.subtotal) FROM detalle_pedidos dp WHERE dp.id_pedido = p.id_pedido)
                          WHERE p.id_pedido = :id_pedido');
        $this->db->bind(':id_pedido', $id_pedido);
        return $this->db->execute();
    }

    // Actualizar el estado de un pedido
    public function updateOrderStatus($id_pedido, $estado) {
        $this->db->query('UPDATE pedidos SET estado = :estado WHERE id_pedido = :id_pedido');
        $this->db->bind(':id_pedido', $id_pedido);
        $this->db->bind(':estado', $estado);
        return $this->db->execute();
    }

    // Eliminar un pedido y sus detalles (usar con precaución)
    public function deleteOrder($id_pedido) {
        $this->db->query("START TRANSACTION");
        try {
            // Primero eliminar los detalles del pedido
            $this->db->query('DELETE FROM detalle_pedidos WHERE id_pedido = :id_pedido');
            $this->db->bind(':id_pedido', $id_pedido);
            $this->db->execute();

            // Luego eliminar el pedido
            $this->db->query('DELETE FROM pedidos WHERE id_pedido = :id_pedido');
            $this->db->bind(':id_pedido', $id_pedido);
            $this->db->execute();

            $this->db->query("COMMIT");
            return true;
        } catch (PDOException $e) {
            $this->db->query("ROLLBACK");
            error_log("Error al eliminar pedido: " . $e->getMessage());
            return false;
        }
    }
}