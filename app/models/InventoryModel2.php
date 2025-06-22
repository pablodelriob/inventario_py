<?php
// app/models/InventoryModel.php

class InventoryModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getTotalProductsPerDeposito() {
        $this->db->query('SELECT d.id_deposito, d.nombre_deposito, SUM(i.cantidad) AS total_unidades
                          FROM depositos d
                          LEFT JOIN inventario i ON d.id_deposito = i.id_deposito
                          GROUP BY d.id_deposito, d.nombre_deposito
                          ORDER BY d.nombre_deposito ASC');
        return $this->db->resultSet();
    }

    public function getDetailedInventory() {
        $this->db->query('SELECT
                            d.id_deposito,
                            d.nombre_deposito,
                            p.id_producto,
                            p.nombre_comercial,
                            p.sku,
                            i.cantidad
                          FROM inventario i
                          JOIN depositos d ON i.id_deposito = d.id_deposito
                          JOIN productos p ON i.id_producto = p.id_producto
                          ORDER BY d.nombre_deposito ASC, p.nombre_comercial ASC');
        return $this->db->resultSet();
    }

    public function getProductStockInDeposito($productId, $depositoId) {
        $this->db->query('SELECT cantidad FROM inventario WHERE id_producto = :product_id AND id_deposito = :deposito_id');
        $this->db->bind(':product_id', $productId);
        $this->db->bind(':deposito_id', $depositoId);
        $result = $this->db->single();
        return $result ? $result->cantidad : 0;
    }

    public function addOrUpdateStock($productId, $depositoId, $cantidad, $userId, $tipoMovimiento = 'Entrada', $observaciones = null) {
        $this->db->query("START TRANSACTION");

        try {
            $this->db->query('UPDATE inventario SET cantidad = cantidad + :cantidad WHERE id_producto = :product_id AND id_deposito = :deposito_id');
            $this->db->bind(':cantidad', $cantidad);
            $this->db->bind(':product_id', $productId);
            $this->db->bind(':deposito_id', $depositoId);
            $this->db->execute();

            if ($this->db->rowCount() == 0) {
                $this->db->query('INSERT INTO inventario (id_producto, id_deposito, cantidad) VALUES (:product_id, :deposito_id, :cantidad)');
                $this->db->bind(':product_id', $productId);
                $this->db->bind(':deposito_id', $depositoId);
                $this->db->bind(':cantidad', $cantidad);
                $this->db->execute();
            }

            $this->db->query('INSERT INTO movimientos_inventario (id_producto, id_deposito_origen, id_deposito_destino, tipo_movimiento, cantidad, id_usuario_responsable, observaciones)
                              VALUES (:id_producto, NULL, :id_deposito_destino, :tipo_movimiento, :cantidad, :id_usuario_responsable, :observaciones)');
            $this->db->bind(':id_producto', $productId);
            $this->db->bind(':id_deposito_destino', $depositoId);
            $this->db->bind(':tipo_movimiento', $tipoMovimiento);
            $this->db->bind(':cantidad', $cantidad);
            $this->db->bind(':id_usuario_responsable', $userId);
            $this->db->bind(':observaciones', $observaciones);
            $this->db->execute();

            $this->db->query("COMMIT");
            return true;
        } catch (PDOException $e) {
            $this->db->query("ROLLBACK");
            error_log("Error al añadir/actualizar stock: " . $e->getMessage());
            return false;
        }
    }

    public function countProductsInDeposito($depositoId) {
        $this->db->query('SELECT COUNT(*) AS total FROM inventario WHERE id_deposito = :deposito_id');
        $this->db->bind(':deposito_id', $depositoId);
        $result = $this->db->single();
        return $result ? $result->total : 0;
    }
}