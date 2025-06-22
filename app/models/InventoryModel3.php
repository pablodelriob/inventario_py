<?php
// app/models/InventoryModel.php

class InventoryModel {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    /**
     * Obtiene el total de productos (sumatoria de cantidades) por cada depósito.
     * @return array Array de objetos con el ID y nombre del depósito, y el total de unidades.
     */
    public function getTotalProductsPerDeposito() {
        $this->db->query('SELECT d.id_deposito, d.nombre_deposito, SUM(i.cantidad) AS total_unidades
                          FROM depositos d
                          LEFT JOIN inventario i ON d.id_deposito = i.id_deposito
                          GROUP BY d.id_deposito, d.nombre_deposito
                          ORDER BY d.nombre_deposito ASC');
        return $this->db->resultSet();
    }

    /**
     * Obtiene el inventario detallado por producto y depósito.
     * Muestra la cantidad de cada producto en cada depósito donde hay stock.
     * @return array Array de objetos con el detalle del inventario.
     */
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

    /**
     * Obtiene la cantidad actual de un producto específico en un depósito dado.
     * @param int $productId ID del producto.
     * @param int $depositoId ID del depósito.
     * @return int La cantidad de stock, o 0 si no se encuentra.
     */
    public function getProductStockInDeposito($productId, $depositoId) {
        $this->db->query('SELECT cantidad FROM inventario WHERE id_producto = :product_id AND id_deposito = :deposito_id');
        $this->db->bind(':product_id', $productId);
        $this->db->bind(':deposito_id', $depositoId);
        $result = $this->db->single();
        return $result ? $result->cantidad : 0;
    }

    /**
     * Actualiza el stock de un producto en un depósito y registra el movimiento.
     * Maneja incrementos (entradas/ajustes positivos) y decrementos (salidas/ajustes negativos).
     * @param int $productId ID del producto.
     * @param int $depositoId ID del depósito principal afectado por el movimiento.
     * @param int $cantidadCambio La cantidad a añadir (positivo) o restar (negativo) al stock.
     * @param int $userId ID del usuario responsable del movimiento.
     * @param string $tipoMovimiento Tipo de movimiento ('Entrada', 'Salida', 'Ajuste Positivo', 'Ajuste Negativo', 'Transferencia').
     * @param string|null $observaciones Observaciones del movimiento.
     * @param int|null $depositoOrigenId ID del depósito de origen para transferencias o salidas.
     * @param int|null $depositoDestinoId ID del depósito de destino para transferencias o entradas.
     * @return bool True si la operación fue exitosa, false en caso contrario.
     */
    public function updateProductStock($productId, $depositoId, $cantidadCambio, $userId, $tipoMovimiento, $observaciones = null, $depositoOrigenId = null, $depositoDestinoId = null) {
        $this->db->query("START TRANSACTION");

        try {
            // 1. Actualizar el stock en la tabla 'inventario'
            // Si es una salida, la cantidadCambio será negativa
            // Si es una entrada, la cantidadCambio será positiva
            $this->db->query('UPDATE inventario SET cantidad = cantidad + :cantidad WHERE id_producto = :product_id AND id_deposito = :deposito_id');
            $this->db->bind(':cantidad', $cantidadCambio);
            $this->db->bind(':product_id', $productId);
            $this->db->bind(':deposito_id', $depositoId);
            $this->db->execute();

            // Si no se encontró el registro para actualizar (es la primera vez que se añade stock a este producto en este depósito)
            if ($this->db->rowCount() == 0) {
                // Solo inserta si la cantidad es positiva (entrada inicial)
                if ($cantidadCambio > 0) {
                    $this->db->query('INSERT INTO inventario (id_producto, id_deposito, cantidad) VALUES (:product_id, :deposito_id, :cantidad)');
                    $this->db->bind(':product_id', $productId);
                    $this->db->bind(':deposito_id', $depositoId);
                    $this->db->bind(':cantidad', $cantidadCambio);
                    $this->db->execute();
                } else {
                    // No se puede decrementar stock si no existe el registro de inventario (stock es 0)
                    $this->db->query("ROLLBACK");
                    return false; // Indicar que no se pudo realizar la operación
                }
            } else {
                 // Si la cantidad resultante es negativa después de una operación, revertir la transacción
                $currentStock = $this->getProductStockInDeposito($productId, $depositoId); // Obtener stock actualizado
                if ($currentStock < 0) {
                    $this->db->query("ROLLBACK");
                    error_log("Stock negativo detectado para Producto ID: {$productId} en Deposito ID: {$depositoId}. Cantidad resultante: {$currentStock}");
                    return false; // No permitir stock negativo
                }
            }

            // 2. Registrar el movimiento en 'movimientos_inventario'
            // La cantidad en movimientos_inventario siempre se registra como positiva (valor absoluto)
            // El tipo_movimiento define si fue entrada o salida.
            $this->db->query('INSERT INTO movimientos_inventario (id_producto, id_deposito_origen, id_deposito_destino, tipo_movimiento, cantidad, id_usuario_responsable, observaciones)
                              VALUES (:id_producto, :id_deposito_origen, :id_deposito_destino, :tipo_movimiento, :cantidad, :id_usuario_responsable, :observaciones)');
            $this->db->bind(':id_producto', $productId);
            $this->db->bind(':id_deposito_origen', $depositoOrigenId);
            $this->db->bind(':id_deposito_destino', $depositoDestinoId);
            $this->db->bind(':tipo_movimiento', $tipoMovimiento);
            $this->db->bind(':cantidad', abs($cantidadCambio)); // La cantidad en movimientos es siempre positiva
            $this->db->bind(':id_usuario_responsable', $userId);
            $this->db->bind(':observaciones', $observaciones);
            $this->db->execute();

            $this->db->query("COMMIT"); // Confirmar la transacción
            return true;
        } catch (PDOException $e) {
            $this->db->query("ROLLBACK"); // Revertir la transacción en caso de error
            error_log("Error en updateProductStock: " . $e->getMessage()); // Registrar el error
            return false;
        }
    }

    /**
     * Método para añadir o actualizar stock (entradas o ajustes positivos).
     * Es un wrapper para updateProductStock.
     * @param int $productId ID del producto.
     * @param int $depositoId ID del depósito.
     * @param int $cantidad Cantidad a añadir.
     * @param int $userId ID del usuario responsable.
     * @param string $tipoMovimiento Tipo de movimiento (por defecto 'Entrada').
     * @param string|null $observaciones Observaciones.
     * @return bool True si es exitoso, false en caso contrario.
     */
    public function addOrUpdateStock($productId, $depositoId, $cantidad, $userId, $tipoMovimiento = 'Entrada', $observaciones = null) {
        // Para entradas y ajustes positivos, el depósito de destino es el actual, el origen es NULL
        return $this->updateProductStock($productId, $depositoId, $cantidad, $userId, $tipoMovimiento, $observaciones, null, $depositoId);
    }

    /**
     * Método para decrementar stock (salidas o ventas).
     * Es un wrapper para updateProductStock.
     * @param int $productId ID del producto.
     * @param int $depositoId ID del depósito de donde sale el stock.
     * @param int $cantidad Cantidad a decrementar.
     * @param int $userId ID del usuario responsable.
     * @param string|null $observaciones Observaciones.
     * @return bool True si es exitoso, false si no hay stock suficiente o error.
     */
    public function decrementStock($productId, $depositoId, $cantidad, $userId, $observaciones = null) {
        // Obtener el stock actual para verificar que haya suficiente
        $currentStock = $this->getProductStockInDeposito($productId, $depositoId);

        if ($currentStock < $cantidad) {
            // No hay suficiente stock
            return false;
        }
        // La cantidad de cambio es negativa para decrementar
        $cantidadCambio = -$cantidad;
        // El tipo de movimiento es 'Salida'
        // El depósito de origen es el actual, el destino es NULL
        return $this->updateProductStock($productId, $depositoId, $cantidadCambio, $userId, 'Salida', $observaciones, $depositoId, null);
    }

    /**
     * Cuenta el número total de registros de inventario para un depósito específico.
     * @param int $depositoId ID del depósito.
     * @return int El número de productos con stock en ese depósito.
     */
    public function countProductsInDeposito($depositoId) {
        $this->db->query('SELECT COUNT(*) AS total FROM inventario WHERE id_deposito = :deposito_id');
        $this->db->bind(':deposito_id', $depositoId);
        $result = $this->db->single();
        return $result ? $result->total : 0;
    }

    /**
     * Obtiene todos los movimientos registrados en el historial de inventario.
     * Incluye detalles del producto, depósitos (origen/destino) y usuario responsable.
     * @return array Array de objetos con todos los movimientos de inventario.
     */
    public function getAllInventoryMovements() {
        $this->db->query('SELECT
                            mi.fecha_movimiento,
                            mi.tipo_movimiento,
                            mi.cantidad,
                            mi.observaciones,
                            p.nombre_comercial AS producto_nombre,
                            p.sku,
                            do.nombre_deposito AS deposito_origen,
                            dd.nombre_deposito AS deposito_destino,
                            u.nombre_usuario AS usuario_responsable
                          FROM movimientos_inventario mi
                          JOIN productos p ON mi.id_producto = p.id_producto
                          LEFT JOIN depositos do ON mi.id_deposito_origen = do.id_deposito
                          LEFT JOIN depositos dd ON mi.id_deposito_destino = dd.id_deposito
                          JOIN usuarios u ON mi.id_usuario_responsable = u.id_usuario
                          ORDER BY mi.fecha_movimiento DESC');
        return $this->db->resultSet();
    }
}