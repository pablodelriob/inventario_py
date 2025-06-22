<?php
// app/models/InventoryModel.php

class InventoryModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    /**
     * Obtener stock de un producto en un depósito específico.
     * Usará la columna `cantidad_disponible`.
     */
    public function getProductStockInDeposito($id_producto, $id_deposito) {
        $this->db->query('SELECT cantidad_disponible FROM inventario WHERE id_producto = :id_producto AND id_deposito = :id_deposito');
        $this->db->bind(':id_producto', $id_producto);
        $this->db->bind(':id_deposito', $id_deposito);
        $result = $this->db->single();
        return $result ? (int)$result->cantidad_disponible : 0;
    }

    /**
     * Obtener todo el stock de un producto, detallado por depósito.
     * Usará la columna `cantidad_disponible`.
     */
    public function getProductStockDetails($id_producto) {
        $this->db->query('SELECT
                                i.cantidad_disponible,
                                d.nombre_deposito,
                                d.id_deposito
                            FROM inventario i
                            JOIN depositos d ON i.id_deposito = d.id_deposito
                            WHERE i.id_producto = :id_producto
                            ORDER BY d.nombre_deposito ASC');
        $this->db->bind(':id_producto', $id_producto);
        return $this->db->resultSet();
    }

    /**
     * Obtiene todos los productos con su stock disponible sumado en todos los depósitos.
     */
    public function getAllProductsWithTotalStock() {
        $this->db->query('
            SELECT 
                p.id_producto,
                p.nombre_comercial,
                p.sku,
                SUM(i.cantidad_disponible) AS total_stock
            FROM 
                productos p
            LEFT JOIN 
                inventario i ON p.id_producto = i.id_producto
            GROUP BY
                p.id_producto, p.nombre_comercial, p.sku
            ORDER BY
                p.nombre_comercial ASC
        ');
        return $this->db->resultSet();
    }


    /**
     * Actualiza el stock de un producto en un depósito específico.
     * Si el registro no existe, lo crea.
     * @param int $id_producto ID del producto.
     * @param int $id_deposito ID del depósito.
     * @param int $cantidad Cantidad a sumar o restar.
     * @param string $operacion 'sum' para sumar, 'subtract' para restar.
     * @return bool True si el stock se actualizó/creó, false en caso contrario.
     */
    public function updateOrCreateStock($id_producto, $id_deposito, $cantidad, $operacion) {
        // Primero, intenta obtener el stock existente
        $this->db->query('SELECT id_inventario, cantidad_disponible FROM inventario WHERE id_producto = :id_producto AND id_deposito = :id_deposito');
        $this->db->bind(':id_producto', $id_producto);
        $this->db->bind(':id_deposito', $id_deposito);
        $current_stock = $this->db->single();

        if ($current_stock) {
            // Si el stock existe, actualízalo
            $nueva_cantidad = ($operacion == 'sum') ? $current_stock->cantidad_disponible + $cantidad : $current_stock->cantidad_disponible - $cantidad;
            
            // Evitar stock negativo si es una resta
            if ($operacion == 'subtract' && $nueva_cantidad < 0) {
                $nueva_cantidad = 0; // O puedes lanzar una excepción si prefieres
            }

            $this->db->query('UPDATE inventario SET cantidad_disponible = :cantidad_disponible, ultima_actualizacion = NOW() WHERE id_inventario = :id_inventario');
            $this->db->bind(':cantidad_disponible', $nueva_cantidad);
            $this->db->bind(':id_inventario', $current_stock->id_inventario);
        } else {
            // Si el stock no existe, créalo (solo si la operación es de suma y la cantidad es > 0)
            if ($operacion == 'sum' && $cantidad > 0) {
                $this->db->query('INSERT INTO inventario (id_producto, id_deposito, cantidad_disponible, ultima_actualizacion) VALUES (:id_producto, :id_deposito, :cantidad_disponible, NOW())');
                $this->db->bind(':id_producto', $id_producto);
                $this->db->bind(':id_deposito', $id_deposito);
                $this->db->bind(':cantidad_disponible', $cantidad);
            } else {
                // No se puede restar stock si no existe o sumar 0.
                return false; 
            }
        }
        return $this->db->execute();
    }

    /**
     * Registra un movimiento de inventario en la tabla `movimientos_inventario`.
     * Este es el método que usarás para registrar la entrada de mercancía por OC.
     * @param array $data Array asociativo con:
     * 'id_producto', 'id_deposito_origen' (nullable), 'id_deposito_destino' (nullable),
     * 'tipo_movimiento' (Ej: 'Entrada', 'Salida', 'Transferencia', 'Ajuste'),
     * 'cantidad', 'id_usuario_responsable', 'observaciones', 'id_orden_compra' (nullable)
     * @return bool True si el movimiento se registró exitosamente, false en caso contrario.
     */
    public function registrarMovimientoInventario($data) {
        $this->db->query('INSERT INTO movimientos_inventario (
                                id_producto, 
                                id_deposito_origen, 
                                id_deposito_destino, 
                                tipo_movimiento, 
                                cantidad, 
                                id_usuario_responsable, 
                                observaciones,
                                id_orden_compra,
                                fecha_movimiento
                            ) VALUES (
                                :id_producto, 
                                :id_deposito_origen, 
                                :id_deposito_destino, 
                                :tipo_movimiento, 
                                :cantidad, 
                                :id_usuario_responsable, 
                                :observaciones,
                                :id_orden_compra,
                                NOW()
                            )');
        
        $this->db->bind(':id_producto', $data['id_producto']);
        $this->db->bind(':id_deposito_origen', $data['id_deposito_origen'] ?? null); // Usar null si no se proporciona
        $this->db->bind(':id_deposito_destino', $data['id_deposito_destino'] ?? null); // Usar null si no se proporciona
        $this->db->bind(':tipo_movimiento', $data['tipo_movimiento']);
        $this->db->bind(':cantidad', $data['cantidad']);
        $this->db->bind(':id_usuario_responsable', $data['id_usuario_responsable']);
        $this->db->bind(':observaciones', $data['observaciones'] ?? null); // Usar null si no se proporciona
        $this->db->bind(':id_orden_compra', $data['id_orden_compra'] ?? null); // Usar null si no se proporciona
        
        return $this->db->execute();
    }

    /**
     * Método para obtener todos los movimientos de inventario (para reportes)
     * Asumiendo que tienes la tabla `movimientos_inventario`
     */
    public function getAllInventoryMovements() {
        $this->db->query('SELECT
                                mi.*,
                                p.nombre_comercial AS producto_nombre,
                                p.sku AS producto_sku,
                                do.nombre_deposito AS origen_nombre,
                                dd.nombre_deposito AS destino_nombre,
                                u.nombre_usuario AS usuario_nombre
                            FROM movimientos_inventario mi
                            JOIN productos p ON mi.id_producto = p.id_producto
                            LEFT JOIN depositos do ON mi.id_deposito_origen = do.id_deposito
                            LEFT JOIN depositos dd ON mi.id_deposito_destino = dd.id_deposito
                            JOIN usuarios u ON mi.id_usuario_responsable = u.id_usuario
                            ORDER BY mi.fecha_movimiento DESC');
        return $this->db->resultSet();
    }

    /**
     * Método para contar productos en un depósito (para la eliminación de depósitos)
     * Usará la columna `cantidad_disponible`.
     */
    public function countProductsInDeposito($id_deposito) {
        $this->db->query('SELECT SUM(cantidad_disponible) AS total FROM inventario WHERE id_deposito = :id_deposito');
        $this->db->bind(':id_deposito', $id_deposito);
        $result = $this->db->single();
        return $result && $result->total !== null ? (int)$result->total : 0;
    }
}