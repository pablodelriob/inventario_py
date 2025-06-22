<?php
// app/models/ClientModel.php

class ClientModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    // Obtener todos los clientes
    public function getAllClients() {
        $this->db->query('SELECT * FROM clientes ORDER BY nombre ASC, apellido ASC');
        return $this->db->resultSet();
    }

    // Obtener un cliente por su ID
    public function getClientById($id) {
        $this->db->query('SELECT * FROM clientes WHERE id_cliente = :id_cliente');
        $this->db->bind(':id_cliente', $id);
        return $this->db->single();
    }

    // Crear un nuevo cliente
    public function createClient($data) {
        $this->db->query('INSERT INTO clientes (nombre, apellido, email, telefono, direccion) VALUES (:nombre, :apellido, :email, :telefono, :direccion)');
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':apellido', $data['apellido']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':telefono', $data['telefono']);
        $this->db->bind(':direccion', $data['direccion']);

        if ($this->db->execute()) {
            return true;
        }
        return false;
    }

    // Actualizar un cliente existente
    public function updateClient($data) {
        $this->db->query('UPDATE clientes SET nombre = :nombre, apellido = :apellido, email = :email, telefono = :telefono, direccion = :direccion WHERE id_cliente = :id_cliente');
        $this->db->bind(':id_cliente', $data['id_cliente']);
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':apellido', $data['apellido']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':telefono', $data['telefono']);
        $this->db->bind(':direccion', $data['direccion']);

        if ($this->db->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar un cliente
    public function deleteClient($id) {
        $this->db->query('DELETE FROM clientes WHERE id_cliente = :id_cliente');
        $this->db->bind(':id_cliente', $id);

        if ($this->db->execute()) {
            return true;
        }
        return false;
    }

    // Verificar si un email ya existe (para evitar duplicados si el email es UNIQUE)
    public function findClientByEmail($email) {
        $this->db->query('SELECT * FROM clientes WHERE email = :email');
        $this->db->bind(':email', $email);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Cuenta el número de pedidos asociados a un cliente.
     * @param int $id_cliente ID del cliente.
     * @return int El número de pedidos asociados.
     */
    public function countOrdersByClient($id_cliente) {
        $this->db->query('SELECT COUNT(*) AS total_pedidos FROM pedidos WHERE id_cliente = :id_cliente');
        $this->db->bind(':id_cliente', $id_cliente);
        $result = $this->db->single();
        return $result ? (int)$result->total_pedidos : 0;
    }
}