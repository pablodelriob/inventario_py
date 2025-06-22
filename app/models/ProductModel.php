<?php
// app/models/ProductModel.php

class ProductModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    // Obtener todos los productos
    public function getAllProducts() {
        $this->db->query('SELECT * FROM productos ORDER BY nombre_comercial ASC');
        return $this->db->resultSet();
    }

    // Obtener un producto por su ID
    public function getProductById($id) {
        $this->db->query('SELECT * FROM productos WHERE id_producto = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Crear un nuevo producto
    public function createProduct($data) {
        // Asegurarse de que todas las columnas NOT NULL de kalu.sql estén incluidas
        // También incluir id_proveedor e id_categoria que son FK y NOT NULL
        $this->db->query('INSERT INTO productos (
                                sku,
                                nombre_comercial,
                                descripcion,
                                costo,
                                cantidad_minima,
                                id_usuario_ingreso,
                                id_proveedor,          -- <--- AÑADIDO: Consistente con kalu.sql
                                id_categoria,          -- <--- AÑADIDO: Consistente con kalu.sql
                                precio_publico,
                                precio_mayorista,
                                precio_distribuidor,
                                porcentaje_precio_publico,
                                porcentaje_precio_mayorista,
                                porcentaje_precio_distribuidor,
                                ruta_imagen,
                                precio_venta
                            ) VALUES (
                                :sku,
                                :nombre_comercial,
                                :descripcion,
                                :costo,
                                :cantidad_minima,
                                :id_usuario_ingreso,
                                :id_proveedor,         -- <--- AÑADIDO
                                :id_categoria,         -- <--- AÑADIDO
                                :precio_publico,
                                :precio_mayorista,
                                :precio_distribuidor,
                                :porcentaje_precio_publico,
                                :porcentaje_precio_mayorista,
                                :porcentaje_precio_distribuidor,
                                :ruta_imagen,
                                :precio_venta
                            )');

        $this->db->bind(':sku', $data['sku']);
        $this->db->bind(':nombre_comercial', $data['nombre_comercial']);
        $this->db->bind(':descripcion', $data['descripcion']);
        $this->db->bind(':costo', $data['costo']);
        $this->db->bind(':cantidad_minima', $data['cantidad_minima']);
        $this->db->bind(':id_usuario_ingreso', $data['id_usuario_ingreso']);
        $this->db->bind(':id_proveedor', $data['id_proveedor']);   // <--- AÑADIDO
        $this->db->bind(':id_categoria', $data['id_categoria']);   // <--- AÑADIDO

        $this->db->bind(':porcentaje_precio_publico', $data['porcentaje_precio_publico'] ?? null);
        $this->db->bind(':porcentaje_precio_mayorista', $data['porcentaje_precio_mayorista'] ?? null);
        $this->db->bind(':porcentaje_precio_distribuidor', $data['porcentaje_precio_distribuidor'] ?? null);
        $this->db->bind(':precio_publico', $data['precio_publico'] ?? null);
        $this->db->bind(':precio_mayorista', $data['precio_mayorista'] ?? null);
        $this->db->bind(':precio_distribuidor', $data['precio_distribuidor'] ?? null);
        $this->db->bind(':ruta_imagen', $data['ruta_imagen'] ?? null);
        $this->db->bind(':precio_venta', $data['precio_venta']);

        if ($this->db->execute()) {
            return true;
        }
        return false;
    }

    // Actualizar un producto existente
    public function updateProduct($data) {
        // Asegurarse de que todas las columnas de kalu.sql estén incluidas
        $this->db->query('UPDATE productos SET
                                sku = :sku,
                                nombre_comercial = :nombre_comercial,
                                descripcion = :descripcion,
                                costo = :costo,
                                cantidad_minima = :cantidad_minima,
                                id_proveedor = :id_proveedor,          -- <--- AÑADIDO
                                id_categoria = :id_categoria,          -- <--- AÑADIDO
                                porcentaje_precio_publico = :porcentaje_precio_publico,
                                porcentaje_precio_mayorista = :porcentaje_precio_mayorista,
                                porcentaje_precio_distribuidor = :porcentaje_precio_distribuidor,
                                precio_publico = :precio_publico,
                                precio_mayorista = :precio_mayorista,
                                precio_distribuidor = :precio_distribuidor,
                                ruta_imagen = :ruta_imagen,
                                precio_venta = :precio_venta
                            WHERE id_producto = :id_producto');

        $this->db->bind(':id_producto', $data['id_producto']);
        $this->db->bind(':sku', $data['sku']);
        $this->db->bind(':nombre_comercial', $data['nombre_comercial']);
        $this->db->bind(':descripcion', $data['descripcion']);
        $this->db->bind(':costo', $data['costo']);
        $this->db->bind(':cantidad_minima', $data['cantidad_minima']);
        $this->db->bind(':id_proveedor', $data['id_proveedor']);   // <--- AÑADIDO
        $this->db->bind(':id_categoria', $data['id_categoria']);   // <--- AÑADIDO

        $this->db->bind(':porcentaje_precio_publico', $data['porcentaje_precio_publico'] ?? null);
        $this->db->bind(':porcentaje_precio_mayorista', $data['porcentaje_precio_mayorista'] ?? null);
        $this->db->bind(':porcentaje_precio_distribuidor', $data['porcentaje_precio_distribuidor'] ?? null);
        $this->db->bind(':precio_publico', $data['precio_publico'] ?? null);
        $this->db->bind(':precio_mayorista', $data['precio_mayorista'] ?? null);
        $this->db->bind(':precio_distribuidor', $data['precio_distribuidor'] ?? null);
        $this->db->bind(':ruta_imagen', $data['ruta_imagen'] ?? null);
        $this->db->bind(':precio_venta', $data['precio_venta']);

        if ($this->db->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar un producto
    public function deleteProduct($id) {
        // Delegamos la verificación de dependencias a un método auxiliar
        if ($this->hasDependencies($id)) {
            return false; // No se puede eliminar si hay registros relacionados
        }

        $this->db->query('DELETE FROM productos WHERE id_producto = :id_producto');
        $this->db->bind(':id_producto', $id);

        if ($this->db->execute()) {
            return true;
        }
        return false;
    }

    // Buscar un producto por SKU
    public function findProductBySku($sku) {
        $this->db->query('SELECT * FROM productos WHERE sku = :sku');
        $this->db->bind(':sku', $sku);
        return $this->db->single();
    }

    /**
     * Verifica si un producto tiene dependencias activas en otras tablas.
     * @param int $id_producto ID del producto.
     * @return bool True si tiene dependencias, false en caso contrario.
     */
    public function hasDependencies($id_producto) {
        // Verificar stock en inventario
        $this->db->query('SELECT COUNT(*) FROM inventario WHERE id_producto = :id_producto');
        $this->db->bind(':id_producto', $id_producto);
        if ($this->db->single()->{'COUNT(*)'} > 0) {
            return true;
        }

        // Verificar en detalle_pedidos (ventas)
        $this->db->query('SELECT COUNT(*) FROM detalle_pedidos WHERE id_producto = :id_producto');
        $this->db->bind(':id_producto', $id_producto);
        if ($this->db->single()->{'COUNT(*)'} > 0) {
            return true;
        }

        // Verificar en ordenes_compra_detalles (compras a proveedores)
        $this->db->query('SELECT COUNT(*) FROM ordenes_compra_detalles WHERE id_producto = :id_producto');
        $this->db->bind(':id_producto', $id_producto);
        if ($this->db->single()->{'COUNT(*)'} > 0) {
            return true;
        }

        // Considerar movimientos_inventario: si solo es un historial, quizás no bloquee la eliminación.
        // Si necesitas que los movimientos de historial también impidan la eliminación:
        /*
        $this->db->query('SELECT COUNT(*) FROM movimientos_inventario WHERE id_producto = :id_producto');
        $this->db->bind(':id_producto', $id_producto);
        if ($this->db->single()->{'COUNT(*)'} > 0) {
            return true;
        }
        */

        return false; // No se encontraron dependencias
    }

    // Obtener el stock total de un producto en todos los depósitos
    public function getProductTotalStock($id_producto) {
        $this->db->query('SELECT SUM(cantidad_disponible) AS total_stock FROM inventario WHERE id_producto = :id_producto');
        $this->db->bind(':id_producto', $id_producto);
        $result = $this->db->single();
        return $result && $result->total_stock !== null ? (int)$result->total_stock : 0;
    }
}