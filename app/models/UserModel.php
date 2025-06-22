<?php
// app/models/UserModel.php

class UserModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function findUserByUsername($username) {
        $this->db->query('SELECT u.*, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol WHERE u.nombre_usuario = :username');
        $this->db->bind(':username', $username);
        return $this->db->single();
    }

    public function findUserByEmail($email) {
        $this->db->query('SELECT * FROM usuarios WHERE email = :email');
        $this->db->bind(':email', $email);
        return $this->db->single();
    }

    public function registerUser($username, $email, $password_hash, $role_id) {
        $this->db->query('INSERT INTO usuarios (nombre_usuario, email, contrasena_hash, id_rol) VALUES (:username, :email, :password_hash, :role_id)');
        $this->db->bind(':username', $username);
        $this->db->bind(':email', $email);
        $this->db->bind(':password_hash', $password_hash);
        $this->db->bind(':role_id', $role_id);

        return $this->db->execute();
    }

    public function getAllRoles() {
        $this->db->query('SELECT * FROM roles ORDER BY nombre_rol ASC');
        return $this->db->resultSet();
    }

    public function getRoleNameById($roleId) {
        $this->db->query('SELECT nombre_rol FROM roles WHERE id_rol = :id_rol');
        $this->db->bind(':id_rol', $roleId);
        $result = $this->db->single();
        return $result ? $result->nombre_rol : null;
    }

    // Nuevo método: Obtener todos los usuarios (útil para dropdowns, etc.)
    public function getAllUsers() {
        $this->db->query('SELECT id_usuario, nombre_usuario, id_rol FROM usuarios ORDER BY nombre_usuario ASC');
        return $this->db->resultSet();
    }
}