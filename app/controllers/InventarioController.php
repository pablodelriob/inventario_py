<?php
// app/controllers/InventarioController.php

class InventarioController {
    private $db;
    private $productModel;
    private $depositoModel;
    private $inventoryModel;

    public function __construct() {
        $this->db = new Database();
        $this->productModel = new ProductModel($this->db);
        $this->depositoModel = new DepositoModel($this->db);
        $this->inventoryModel = new InventoryModel($this->db);

        // Protección básica: solo usuarios logueados pueden acceder
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Debe iniciar sesión para acceder a esta página.";
            header('Location: ' . URLROOT . '/auth/login');
            exit();
        }
    }

    // Muestra el estado actual del inventario (productos con su stock por depósito)
    public function index() {
        // Acceso permitido solo a Administrador y Gerente de Almacén
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen' && $_SESSION['role_name'] !== 'Vendedor') {
            $_SESSION['error'] = "Acceso denegado. No tienes permisos para ver el inventario.";
            header('Location: ' . URLROOT . '/dashboard');
            exit();
        }

        $products = $this->productModel->getAllProducts();
        $depositos = $this->depositoModel->getAllDepositos();

        $inventoryData = [];
        foreach ($products as $product) {
            $product->stock_details = $this->inventoryModel->getProductStockDetails($product->id_producto);
            $product->total_stock = $this->productModel->getProductTotalStock($product->id_producto);
            $inventoryData[] = $product;
        }

        // Pasa los datos a la vista
        $data = [
            'inventoryData' => $inventoryData,
            'depositos' => $depositos
        ];
        require_once APPROOT . '/views/inventario/index.php';
    }

    // Muestra el formulario para registrar un movimiento de inventario
    public function movimiento() {
        // Acceso permitido solo a Administrador y Gerente de Almacén
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            $_SESSION['error'] = "Acceso denegado. No tienes permisos para registrar movimientos de inventario.";
            header('Location: ' . URLROOT . '/inventario/index');
            exit();
        }

        $products = $this->productModel->getAllProducts();
        $depositos = $this->depositoModel->getAllDepositos();

        $data = [
            'productos' => $products,
            'depositos' => $depositos,
            'form_data' => [] // Para rellenar el formulario en caso de errores
        ];
        require_once APPROOT . '/views/inventario/movimiento.php';
    }

    // Procesa el registro de un movimiento de inventario (Entrada/Salida/Ajuste/Transferencia)
    public function registrarMovimiento() {
        // Acceso permitido solo a Administrador y Gerente de Almacén
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            $_SESSION['error'] = "Acceso denegado.";
            header('Location: ' . URLROOT . '/inventario/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Limpiar y validar los datos del formulario
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data_form = [
                'id_producto' => (int)$_POST['id_producto'],
                'tipo_movimiento' => trim($_POST['tipo_movimiento']),
                'cantidad' => (int)$_POST['cantidad'],
                'id_deposito_origen' => isset($_POST['id_deposito_origen']) && $_POST['id_deposito_origen'] !== '' ? (int)$_POST['id_deposito_origen'] : null,
                'id_deposito_destino' => isset($_POST['id_deposito_destino']) && $_POST['id_deposito_destino'] !== '' ? (int)$_POST['id_deposito_destino'] : null,
                'observaciones' => trim($_POST['observaciones'] ?? ''),
                'id_orden_compra' => isset($_POST['id_orden_compra']) && $_POST['id_orden_compra'] !== '' ? (int)$_POST['id_orden_compra'] : null, // Si aplica
                'id_usuario_responsable' => $_SESSION['user_id'],
            ];

            // Validaciones básicas
            if (!$data_form['id_producto'] || !$data_form['cantidad'] || $data_form['cantidad'] <= 0 || empty($data_form['tipo_movimiento'])) {
                $_SESSION['error'] = "Datos de movimiento inválidos. Producto, cantidad y tipo de movimiento son obligatorios, y la cantidad debe ser positiva.";
                $this->loadMovimientoViewWithErrors($data_form);
                return;
            }

            // Validar depósitos según el tipo de movimiento
            $deposito_para_stock_update = null; // Para saber qué depósito actualizar en updateOrCreateStock
            $operacion_stock = '';

            switch ($data_form['tipo_movimiento']) {
                case 'entrada':
                case 'ajuste_positivo':
                    if (!$data_form['id_deposito_destino']) {
                        $_SESSION['error'] = "Para entradas y ajustes positivos, el depósito de destino es obligatorio.";
                        $this->loadMovimientoViewWithErrors($data_form);
                        return;
                    }
                    $data_form['id_deposito_origen'] = null; // No hay origen en estos tipos de movimiento
                    $deposito_para_stock_update = $data_form['id_deposito_destino'];
                    $operacion_stock = 'sum';
                    break;
                case 'salida':
                case 'ajuste_negativo':
                    if (!$data_form['id_deposito_origen']) {
                        $_SESSION['error'] = "Para salidas y ajustes negativos, el depósito de origen es obligatorio.";
                        $this->loadMovimientoViewWithErrors($data_form);
                        return;
                    }
                    $data_form['id_deposito_destino'] = null; // No hay destino en estos tipos de movimiento
                    $deposito_para_stock_update = $data_form['id_deposito_origen'];
                    $operacion_stock = 'subtract';
                    break;
                case 'transferencia':
                    if (!$data_form['id_deposito_origen'] || !$data_form['id_deposito_destino']) {
                        $_SESSION['error'] = "Para transferencias, los depósitos de origen y destino son obligatorios.";
                        $this->loadMovimientoViewWithErrors($data_form);
                        return;
                    }
                    if ($data_form['id_deposito_origen'] == $data_form['id_deposito_destino']) {
                        $_SESSION['error'] = "El depósito de origen y destino no pueden ser el mismo para una transferencia.";
                        $this->loadMovimientoViewWithErrors($data_form);
                        return;
                    }
                    // Para transferencia, primero restamos del origen y luego sumamos al destino.
                    // La lógica de updateOrCreateStock se llamará dos veces si la transferencia es exitosa.
                    break;
                default:
                    $_SESSION['error'] = "Tipo de movimiento no válido.";
                    $this->loadMovimientoViewWithErrors($data_form);
                    return;
            }

            // Iniciar una transacción de base de datos para asegurar atomicidad
            $this->db->beginTransaction();

            try {
                // Lógica principal de actualización de stock
                if ($data_form['tipo_movimiento'] !== 'transferencia') {
                    // Para entradas, salidas, ajustes
                    if (!$this->inventoryModel->updateOrCreateStock(
                        $data_form['id_producto'],
                        $deposito_para_stock_update,
                        $data_form['cantidad'],
                        $operacion_stock
                    )) {
                        throw new Exception("Error al actualizar el stock en el depósito.");
                    }
                } else {
                    // Lógica para transferencias: restar del origen y luego sumar al destino
                    // 1. Restar del depósito de origen
                    if (!$this->inventoryModel->updateOrCreateStock(
                        $data_form['id_producto'],
                        $data_form['id_deposito_origen'],
                        $data_form['cantidad'],
                        'subtract'
                    )) {
                        throw new Exception("Stock insuficiente en el depósito de origen para la transferencia.");
                    }
                    // 2. Sumar al depósito de destino
                    if (!$this->inventoryModel->updateOrCreateStock(
                        $data_form['id_producto'],
                        $data_form['id_deposito_destino'],
                        $data_form['cantidad'],
                        'sum'
                    )) {
                        throw new Exception("Error al sumar stock en el depósito de destino durante la transferencia.");
                    }
                }

                // Registrar el movimiento en la tabla `movimientos_inventario`
                if (!$this->inventoryModel->registrarMovimientoInventario($data_form)) {
                    throw new Exception("Error al registrar el movimiento de inventario.");
                }

                $this->db->commit(); // Confirmar la transacción
                $_SESSION['message'] = "Movimiento de inventario registrado exitosamente.";
                header('Location: ' . URLROOT . '/inventario/index');
                exit();

            } catch (Exception $e) {
                $this->db->rollBack(); // Deshacer la transacción en caso de error
                $_SESSION['error'] = "Error al procesar el movimiento: " . $e->getMessage();
                $this->loadMovimientoViewWithErrors($data_form); // Vuelve a cargar la vista con los datos y el error
                return;
            }

        } else {
            // Si se accede directamente a registrarMovimiento sin POST, redirigir
            header('Location: ' . URLROOT . '/inventario/movimiento');
            exit();
        }
    }

    // Método auxiliar para cargar la vista del formulario con datos y errores
    private function loadMovimientoViewWithErrors($form_data = []) {
        $products = $this->productModel->getAllProducts();
        $depositos = $this->depositoModel->getAllDepositos();

        $data = [
            'productos' => $products,
            'depositos' => $depositos,
            'form_data' => $form_data // Para repoblar el formulario
        ];
        require_once APPROOT . '/views/inventario/movimiento.php';
    }


    // --- Métodos de Venta (migrados de InventoryController.php) ---
    // NOTA: Si `decrementStock` en InventoryModel simplemente llama a `updateOrCreateStock`
    // con la operación 'subtract', entonces esta función `processSell` podría simplificarse.
    public function sellProduct() {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen' && $_SESSION['role_name'] !== 'Vendedor') {
            $_SESSION['error'] = "Acceso denegado. No tienes permisos para registrar ventas.";
            header('Location: ' . URLROOT . '/dashboard');
            exit();
        }

        $products = $this->productModel->getAllProducts();
        $depositos = $this->depositoModel->getAllDepositos();

        $data = [
            'productos' => $products,
            'depositos' => $depositos
        ];
        require_once APPROOT . '/views/inventario/sell.php';
    }

    public function processSell() {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen' && $_SESSION['role_name'] !== 'Vendedor') {
            $_SESSION['error'] = "Acceso denegado.";
            header('Location: ' . URLROOT . '/inventario/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $productId = (int)$_POST['product_id'];
            $depositoId = (int)$_POST['deposito_id'];
            $cantidad = (int)$_POST['cantidad'];
            $observaciones = trim($_POST['observaciones'] ?? '');
            $userId = $_SESSION['user_id'];

            if ($cantidad <= 0) {
                $_SESSION['error'] = "La cantidad a vender debe ser un número positivo.";
                header('Location: ' . URLROOT . '/inventario/sellProduct');
                exit();
            }

            // Iniciar transacción
            $this->db->beginTransaction();

            try {
                $currentStock = $this->inventoryModel->getProductStockInDeposito($productId, $depositoId);

                if ($currentStock < $cantidad) {
                    throw new Exception("Stock insuficiente en el depósito seleccionado. Stock actual: {$currentStock}.");
                }

                // Decrementar stock usando updateOrCreateStock con operación 'subtract'
                if (!$this->inventoryModel->updateOrCreateStock($productId, $depositoId, $cantidad, 'subtract')) {
                    throw new Exception("Error al actualizar el stock durante la venta.");
                }

                // Registrar el movimiento de inventario como 'Salida' por Venta
                $data_movimiento_venta = [
                    'id_producto' => $productId,
                    'id_deposito_origen' => $depositoId,
                    'id_deposito_destino' => null, // Es una salida, no un destino interno
                    'tipo_movimiento' => 'salida_venta', // Nuevo tipo de movimiento o 'salida'
                    'cantidad' => $cantidad,
                    'id_usuario_responsable' => $userId,
                    'observaciones' => "Venta de producto: " . $observaciones,
                    'id_orden_compra' => null // No aplica para ventas
                ];

                if (!$this->inventoryModel->registrarMovimientoInventario($data_movimiento_venta)) {
                    throw new Exception("Error al registrar el movimiento de venta.");
                }

                $this->db->commit();
                $_SESSION['message'] = "Venta registrada y stock actualizado exitosamente.";
                header('Location: ' . URLROOT . '/inventario/index');
                exit();

            } catch (Exception $e) {
                $this->db->rollBack();
                $_SESSION['error'] = "Error al registrar la venta: " . $e->getMessage();
                header('Location: ' . URLROOT . '/inventario/sellProduct');
                exit();
            }
        } else {
            header('Location: ' . URLROOT . '/inventario/sellProduct');
            exit();
        }
    }

    // --- Método de Reportes (migrado de InventoryController.php) ---
    public function reports() {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            $_SESSION['error'] = "Acceso denegado. No tienes permisos para ver los reportes.";
            header('Location: ' . URLROOT . '/dashboard');
            exit();
        }

        $products = $this->productModel->getAllProducts();
        $depositos = $this->depositoModel->getAllDepositos();
        $inventoryData = [];
        foreach ($products as $product) {
            $product->stock_details = $this->inventoryModel->getProductStockDetails($product->id_producto);
            $product->total_stock = $this->productModel->getProductTotalStock($product->id_producto);
            $inventoryData[] = $product;
        }

        $movimientos = $this->inventoryModel->getAllInventoryMovements();

        // Pasa los datos a la vista
        $data = [
            'inventoryData' => $inventoryData,
            'depositos' => $depositos,
            'movimientos' => $movimientos
        ];
        require_once APPROOT . '/views/inventario/reports.php';
    }
}