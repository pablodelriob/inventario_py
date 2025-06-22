<?php
// app/models/DepositoModel.php

class DepositoModel {
    private $db;
    private $inventoryModel; // Añadir referencia a InventoryModel

    public function __construct(Database $db) {
        $this->db = $db;
        // Instanciar InventoryModel aquí para que DepositoModel pueda usarlo
        $this->inventoryModel = new InventoryModel($db);
    }

    // Obtener todos los depósitos
    public function getAllDepositos() {
        $this->db->query('SELECT * FROM depositos');
        return $this->db->resultSet();
    }

    // Obtener un depósito por su ID
    public function getDepositoById($id) {
        $this->db->query('SELECT * FROM depositos WHERE id_deposito = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Crear un nuevo depósito
    public function createDeposito($data) {
        $this->db->query('INSERT INTO depositos (nombre_deposito, ubicacion, capacidad_maxima) VALUES (:nombre_deposito, :ubicacion, :capacidad_maxima)');
        $this->db->bind(':nombre_deposito', $data['nombre_deposito']);
        $this->db->bind(':ubicacion', $data['ubicacion']);
        $this->db->bind(':capacidad_maxima', $data['capacidad_maxima']); // Puede ser null

        if ($this->db->execute()) {
            return true;
        }
        return false;
    }

    // Actualizar un depósito existente
    public function updateDeposito($data) {
        $this->db->query('UPDATE depositos SET nombre_deposito = :nombre_deposito, ubicacion = :ubicacion, capacidad_maxima = :capacidad_maxima WHERE id_deposito = :id_deposito');
        $this->db->bind(':id_deposito', $data['id_deposito']);
        $this->db->bind(':nombre_deposito', $data['nombre_deposito']);
        $this->db->bind(':ubicacion', $data['ubicacion']);
        $this->db->bind(':capacidad_maxima', $data['capacidad_maxima']);

        if ($this->db->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar un depósito
    public function deleteDeposito($id) {
        // Antes de eliminar, verificar si hay productos asociados a este depósito en el inventario
        // Usar InventoryModel para esta verificación
        if ($this->inventoryModel->countProductsInDeposito($id) > 0) {
            return false; // No se puede eliminar si tiene productos asociados
        }

        $this->db->query('DELETE FROM depositos WHERE id_deposito = :id_deposito');
        $this->db->bind(':id_deposito', $id);

        if ($this->db->execute()) {
            return true;
        }
        return false;
    }

    // Encontrar un depósito por nombre
    public function findDepositoByName($nombre) {
        $this->db->query('SELECT * FROM depositos WHERE nombre_deposito = :nombre_deposito');
        $this->db->bind(':nombre_deposito', $nombre);
        return $this->db->single();
    }

    // Obtener la cantidad total de un producto en un depósito específico
    // NOTA: Esta función se mantiene para propósitos específicos del DepositoModel
    // pero la lógica de inventario central está en InventoryModel.
    public function getProductCountInDeposito($deposito_id, $product_id) {
        $this->db->query('SELECT cantidad FROM inventario WHERE id_deposito = :deposito_id AND id_producto = :product_id');
        $this->db->bind(':deposito_id', $deposito_id);
        $this->db->bind(':product_id', $product_id);
        $result = $this->db->single();
        return $result ? (int)$result->cantidad : 0;
    }
}