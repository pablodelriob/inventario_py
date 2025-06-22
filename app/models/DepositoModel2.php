<?php
// app/models/DepositoModel.php

class DepositoModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getAllDepositos() {
        $this->db->query('SELECT * FROM depositos ORDER BY nombre_deposito ASC');
        return $this->db->resultSet();
    }

    public function getDepositoById($id) {
        $this->db->query('SELECT * FROM depositos WHERE id_deposito = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function findDepositoByName($nombre) {
        $this->db->query('SELECT * FROM depositos WHERE nombre_deposito = :nombre');
        $this->db->bind(':nombre', $nombre);
        return $this->db->single();
    }

    public function addDeposito($nombre, $ubicacion, $capacidad_maxima) {
        $this->db->query('INSERT INTO depositos (nombre_deposito, ubicacion, capacidad_maxima) VALUES (:nombre, :ubicacion, :capacidad_maxima)');
        $this->db->bind(':nombre', $nombre);
        $this->db->bind(':ubicacion', $ubicacion);
        $this->db->bind(':capacidad_maxima', $capacidad_maxima);
        return $this->db->execute();
    }

    public function updateDeposito($id, $nombre, $ubicacion, $capacidad_maxima) {
        $this->db->query('UPDATE depositos SET nombre_deposito = :nombre, ubicacion = :ubicacion, capacidad_maxima = :capacidad_maxima WHERE id_deposito = :id');
        $this->db->bind(':nombre', $nombre);
        $this->db->bind(':ubicacion', $ubicacion);
        $this->db->bind(':capacidad_maxima', $capacidad_maxima);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function deleteDeposito($id) {
        // Asegúrate de que no haya inventario o movimientos asociados antes de eliminar un depósito
        $this->db->query("START TRANSACTION");
        try {
            // Eliminar entradas en inventario para este depósito
            $this->db->query('DELETE FROM inventario WHERE id_deposito = :id');
            $this->db->bind(':id', $id);
            $this->db->execute();

            // Eliminar movimientos de inventario donde este depósito sea origen o destino
            $this->db->query('DELETE FROM movimientos_inventario WHERE id_deposito_origen = :id OR id_deposito_destino = :id');
            $this->db->bind(':id', $id);
            $this->db->execute();

            // Eliminar el depósito
            $this->db->query('DELETE FROM depositos WHERE id_deposito = :id');
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->db->query("COMMIT");
            return true;
        } catch (PDOException $e) {
            $this->db->query("ROLLBACK");
            error_log("Error al eliminar depósito: " . $e->getMessage());
            return false;
        }
    }
}