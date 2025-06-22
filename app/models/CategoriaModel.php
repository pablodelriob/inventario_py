<?php
// app/models/CategoriaModel.php

class CategoriaModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    // Método para obtener todas las categorías
    public function getAllCategorias() {
        $this->db->query('SELECT * FROM categorias ORDER BY nombre_categoria ASC');
        return $this->db->resultSet();
    }

    // Método para obtener una categoría por su ID
    public function getCategoriaById($id_categoria) {
        $this->db->query('SELECT * FROM categorias WHERE id_categoria = :id_categoria');
        $this->db->bind(':id_categoria', $id_categoria);
        return $this->db->single();
    }

    // Método para agregar una nueva categoría
    public function addCategoria($data) {
        $this->db->query('INSERT INTO categorias (nombre_categoria, descripcion) VALUES (:nombre_categoria, :descripcion)');
        
        $this->db->bind(':nombre_categoria', $data['nombre_categoria']);
        $this->db->bind(':descripcion', $data['descripcion'] ?? null); // La descripción puede ser nula

        return $this->db->execute();
    }

    // Método para actualizar una categoría existente
    public function updateCategoria($data) {
        $this->db->query('UPDATE categorias SET nombre_categoria = :nombre_categoria, descripcion = :descripcion WHERE id_categoria = :id_categoria');
        
        $this->db->bind(':nombre_categoria', $data['nombre_categoria']);
        $this->db->bind(':descripcion', $data['descripcion'] ?? null);
        $this->db->bind(':id_categoria', $data['id_categoria']);

        return $this->db->execute();
    }

    /**
     * Verifica si una categoría tiene productos asociados.
     * @param int $id_categoria ID de la categoría.
     * @return bool True si tiene productos asociados, false en caso contrario.
     */
    public function hasDependencies($id_categoria) {
        // Verificar productos asociados a esta categoría
        $this->db->query('SELECT COUNT(*) FROM productos WHERE id_categoria = :id_categoria');
        $this->db->bind(':id_categoria', $id_categoria);
        if ($this->db->single()->{'COUNT(*)'} > 0) {
            return true;
        }

        return false; // No se encontraron dependencias
    }

    // Método para eliminar una categoría
    public function deleteCategoria($id_categoria) {
        // Antes de eliminar, verificar si tiene dependencias (productos asociados)
        if ($this->hasDependencies($id_categoria)) {
            return false; // No se puede eliminar si hay productos asociados
        }

        $this->db->query('DELETE FROM categorias WHERE id_categoria = :id_categoria');
        $this->db->bind(':id_categoria', $id_categoria);
        return $this->db->execute();
    }

    // Opcional: Buscar categoría por nombre para evitar duplicados
    public function findCategoriaByName($nombre_categoria) {
        $this->db->query('SELECT * FROM categorias WHERE nombre_categoria = :nombre_categoria');
        $this->db->bind(':nombre_categoria', $nombre_categoria);
        return $this->db->single();
    }
}