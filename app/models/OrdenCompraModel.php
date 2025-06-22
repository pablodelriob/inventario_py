<?php
// app/models/OrdenCompraModel.php

class OrdenCompraModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    // --- Métodos para la cabecera de la Orden de Compra ---

    // Obtener todas las órdenes de compra (con info básica de proveedor y usuario)
    public function getAllOrdenesCompra() {
        $this->db->query('
            SELECT 
                oc.*, 
                p.nombre_proveedor, 
                u.nombre_usuario 
            FROM 
                ordenes_compra oc
            JOIN 
                proveedores p ON oc.id_proveedor = p.id_proveedor
            JOIN 
                usuarios u ON oc.id_usuario_creacion = u.id_usuario
            ORDER BY 
                oc.fecha_creacion DESC, oc.id_orden_compra DESC
        ');
        return $this->db->resultSet();
    }

    // Obtener una orden de compra por su ID (cabecera)
    public function getOrdenCompraById($id_orden_compra) {
        $this->db->query('
            SELECT 
                oc.*, 
                p.nombre_proveedor, 
                u.nombre_usuario 
            FROM 
                ordenes_compra oc
            JOIN 
                proveedores p ON oc.id_proveedor = p.id_proveedor
            JOIN 
                usuarios u ON oc.id_usuario_creacion = u.id_usuario
            WHERE 
                oc.id_orden_compra = :id_orden_compra
        ');
        $this->db->bind(':id_orden_compra', $id_orden_compra);
        return $this->db->single();
    }

    // Crear una nueva orden de compra (solo la cabecera)
    public function createOrdenCompra($data) {
        $this->db->query('
            INSERT INTO ordenes_compra (id_proveedor, fecha_creacion, fecha_esperada_entrega, estado, id_usuario_creacion, observaciones, total_orden) 
            VALUES (:id_proveedor, :fecha_creacion, :fecha_esperada_entrega, :estado, :id_usuario_creacion, :observaciones, :total_orden)
        ');
        $this->db->bind(':id_proveedor', $data['id_proveedor']);
        $this->db->bind(':fecha_creacion', $data['fecha_creacion']);
        $this->db->bind(':fecha_esperada_entrega', $data['fecha_esperada_entrega']);
        $this->db->bind(':estado', $data['estado']);
        $this->db->bind(':id_usuario_creacion', $data['id_usuario_creacion']);
        $this->db->bind(':observaciones', $data['observaciones']);
        $this->db->bind(':total_orden', $data['total_orden']); // El total se calculará en el controlador

        if ($this->db->execute()) {
            return $this->db->lastInsertId(); // Retorna el ID de la OC recién creada
        }
        return false;
    }

    // Actualizar la cabecera de una orden de compra
    public function updateOrdenCompra($data) {
        $this->db->query('
            UPDATE ordenes_compra 
            SET 
                id_proveedor = :id_proveedor, 
                fecha_creacion = :fecha_creacion, 
                fecha_esperada_entrega = :fecha_esperada_entrega, 
                estado = :estado, 
                observaciones = :observaciones, 
                total_orden = :total_orden
            WHERE 
                id_orden_compra = :id_orden_compra
        ');
        $this->db->bind(':id_orden_compra', $data['id_orden_compra']);
        $this->db->bind(':id_proveedor', $data['id_proveedor']);
        $this->db->bind(':fecha_creacion', $data['fecha_creacion']);
        $this->db->bind(':fecha_esperada_entrega', $data['fecha_esperada_entrega']);
        $this->db->bind(':estado', $data['estado']);
        $this->db->bind(':observaciones', $data['observaciones']);
        $this->db->bind(':total_orden', $data['total_orden']);

        return $this->db->execute();
    }

    // Eliminar una orden de compra (cabecera y sus detalles por CASCADE)
    public function deleteOrdenCompra($id_orden_compra) {
        $this->db->query('DELETE FROM ordenes_compra WHERE id_orden_compra = :id_orden_compra');
        $this->db->bind(':id_orden_compra', $id_orden_compra);
        return $this->db->execute();
    }

    // --- Métodos para los detalles de la Orden de Compra ---

    // Obtener los detalles de una orden de compra específica
    public function getDetallesOrdenCompra($id_orden_compra) {
        $this->db->query('
            SELECT 
                ocd.*, 
                p.nombre_comercial AS producto_nombre,
                p.sku AS producto_sku
            FROM 
                ordenes_compra_detalles ocd
            JOIN 
                productos p ON ocd.id_producto = p.id_producto
            WHERE 
                ocd.id_orden_compra = :id_orden_compra
        ');
        $this->db->bind(':id_orden_compra', $id_orden_compra);
        return $this->db->resultSet();
    }

    // Agregar un detalle a una orden de compra
    public function addDetalleOrdenCompra($data) {
        $this->db->query('
            INSERT INTO ordenes_compra_detalles (id_orden_compra, id_producto, cantidad_pedida, costo_unitario) 
            VALUES (:id_orden_compra, :id_producto, :cantidad_pedida, :costo_unitario)
        ');
        $this->db->bind(':id_orden_compra', $data['id_orden_compra']);
        $this->db->bind(':id_producto', $data['id_producto']);
        $this->db->bind(':cantidad_pedida', $data['cantidad_pedida']);
        $this->db->bind(':costo_unitario', $data['costo_unitario']);
        return $this->db->execute();
    }

    // Actualizar un detalle de orden de compra
    public function updateDetalleOrdenCompra($data) {
        $this->db->query('
            UPDATE ordenes_compra_detalles 
            SET 
                id_producto = :id_producto, 
                cantidad_pedida = :cantidad_pedida, 
                cantidad_recibida = :cantidad_recibida, 
                costo_unitario = :costo_unitario 
            WHERE 
                id_detalle = :id_detalle
        ');
        $this->db->bind(':id_detalle', $data['id_detalle']);
        $this->db->bind(':id_producto', $data['id_producto']);
        $this->db->bind(':cantidad_pedida', $data['cantidad_pedida']);
        $this->db->bind(':cantidad_recibida', $data['cantidad_recibida']);
        $this->db->bind(':costo_unitario', $data['costo_unitario']);
        return $this->db->execute();
    }

    // Eliminar un detalle de orden de compra
    public function deleteDetalleOrdenCompra($id_detalle) {
        $this->db->query('DELETE FROM ordenes_compra_detalles WHERE id_detalle = :id_detalle');
        $this->db->bind(':id_detalle', $id_detalle);
        return $this->db->execute();
    }

    // Actualizar el total de una OC después de cambios en los detalles
    public function updateTotalOrdenCompra($id_orden_compra) {
        $this->db->query('
            UPDATE ordenes_compra oc
            SET oc.total_orden = (
                SELECT SUM(ocd.cantidad_pedida * ocd.costo_unitario)
                FROM ordenes_compra_detalles ocd
                WHERE ocd.id_orden_compra = oc.id_orden_compra
            )
            WHERE oc.id_orden_compra = :id_orden_compra
        ');
        $this->db->bind(':id_orden_compra', $id_orden_compra);
        return $this->db->execute();
    }
}