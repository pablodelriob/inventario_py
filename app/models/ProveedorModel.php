<?php
// app/models/ProveedorModel.php

class ProveedorModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    // Método para obtener todos los proveedores
    public function getAllProveedores() {
        $this->db->query('SELECT * FROM proveedores ORDER BY nombre_proveedor ASC');
        return $this->db->resultSet();
    }

    // Método para obtener un proveedor por su ID
    public function getProveedorById($id_proveedor) {
        $this->db->query('SELECT * FROM proveedores WHERE id_proveedor = :id_proveedor');
        $this->db->bind(':id_proveedor', $id_proveedor);
        return $this->db->single();
    }

    // Método para agregar un nuevo proveedor
    public function addProveedor($data) {
        $this->db->query('INSERT INTO proveedores (nombre_proveedor, contacto_persona, contacto_email, contacto_telefono, direccion, ruc, condiciones_pago) VALUES (:nombre_proveedor, :contacto_persona, :contacto_email, :contacto_telefono, :direccion, :ruc, :condiciones_pago)');
        
        $this->db->bind(':nombre_proveedor', $data['nombre_proveedor']);
        $this->db->bind(':contacto_persona', $data['contacto_persona']);
        $this->db->bind(':contacto_email', $data['contacto_email']);
        $this->db->bind(':contacto_telefono', $data['contacto_telefono']);
        $this->db->bind(':direccion', $data['direccion']);
        $this->db->bind(':ruc', $data['ruc']);
        $this->db->bind(':condiciones_pago', $data['condiciones_pago']);

        return $this->db->execute();
    }

    // Método para actualizar un proveedor existente
    public function updateProveedor($data) {
        $this->db->query('UPDATE proveedores SET nombre_proveedor = :nombre_proveedor, contacto_persona = :contacto_persona, contacto_email = :contacto_email, contacto_telefono = :contacto_telefono, direccion = :direccion, ruc = :ruc, condiciones_pago = :condiciones_pago WHERE id_proveedor = :id_proveedor');
        
        $this->db->bind(':nombre_proveedor', $data['nombre_proveedor']);
        $this->db->bind(':contacto_persona', $data['contacto_persona']);
        $this->db->bind(':contacto_email', $data['contacto_email']);
        $this->db->bind(':contacto_telefono', $data['contacto_telefono']);
        $this->db->bind(':direccion', $data['direccion']);
        $this->db->bind(':ruc', $data['ruc']);
        $this->db->bind(':condiciones_pago', $data['condiciones_pago']);
        $this->db->bind(':id_proveedor', $data['id_proveedor']);

        return $this->db->execute();
    }

    /**
     * Verifica si un proveedor tiene dependencias (productos o órdenes de compra) asociadas.
     * @param int $id_proveedor ID del proveedor.
     * @return bool True si tiene dependencias, false en caso contrario.
     */
    public function hasDependencies($id_proveedor) {
        // Verificar productos asociados
        $this->db->query('SELECT COUNT(*) FROM productos WHERE id_proveedor = :id_proveedor');
        $this->db->bind(':id_proveedor', $id_proveedor);
        if ($this->db->single()->{'COUNT(*)'} > 0) {
            return true;
        }

        // Verificar órdenes de compra asociadas
        $this->db->query('SELECT COUNT(*) FROM ordenes_compra WHERE id_proveedor = :id_proveedor');
        $this->db->bind(':id_proveedor', $id_proveedor);
        if ($this->db->single()->{'COUNT(*)'} > 0) {
            return true;
        }

        return false; // No se encontraron dependencias
    }

    // Método para eliminar un proveedor
    public function deleteProveedor($id_proveedor) {
        // Antes de eliminar, verificar si tiene dependencias
        if ($this->hasDependencies($id_proveedor)) {
            // Podrías lanzar una excepción o simplemente retornar false
            // El controlador será el encargado de manejar el mensaje al usuario
            return false;
        }

        $this->db->query('DELETE FROM proveedores WHERE id_proveedor = :id_proveedor');
        $this->db->bind(':id_proveedor', $id_proveedor);
        return $this->db->execute();
    }
}