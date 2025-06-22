<?php
// app/controllers/OrderController.php

class OrderController {
    private $db;
    private $orderModel;
    private $clientModel;
    private $productModel;
    private $inventoryModel; // Necesario para manejar el stock

    public function __construct() {
        $this->db = new Database();
        $this->orderModel = new OrderModel($this->db);
        $this->clientModel = new ClientModel($this->db);
        $this->productModel = new ProductModel($this->db);
        $this->inventoryModel = new InventoryModel($this->db); // Inicializa el InventoryModel

        // Protección básica: solo usuarios logueados pueden acceder
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/login');
            exit();
        }
    }

    // Mostrar todos los pedidos
    public function index() {
        // Solo Administrador, Gerente de Almacén y Vendedor pueden ver pedidos
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen' && $_SESSION['role_name'] !== 'Vendedor') {
            $_SESSION['error'] = "Acceso denegado. No tienes permisos para ver pedidos.";
            header('Location: ' . URLROOT . '/home');
            exit();
        }
        $pedidos = $this->orderModel->getAllOrders();
        require_once '../app/views/order/index.php';
    }

    // Mostrar formulario para crear un nuevo pedido
    public function create() {
        // Solo Administrador y Vendedor pueden crear pedidos
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Vendedor') {
            $_SESSION['error'] = "Acceso denegado. No tienes permisos para crear pedidos.";
            header('Location: ' . URLROOT . '/order/index');
            exit();
        }
        $clientes = $this->clientModel->getAllClients();
        $productos = $this->productModel->getAllProducts();
        $this->db->query('SELECT id_deposito, nombre_deposito FROM depositos');
        $depositos = $this->db->resultSet(); // Ahora sí, llama a resultSet después de ejecutar la query.
        require_once '../app/views/order/create.php';
    }

    // Procesar el envío del formulario para crear un pedido
    public function store() {
        // Solo Administrador y Vendedor pueden crear pedidos
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Vendedor') {
            $_SESSION['error'] = "Acceso denegado.";
            header('Location: ' . URLROOT . '/order/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Solo sanear campos específicos, o sanear después de json_decode si es necesario
            // Para 'items_pedido', es mejor acceder directamente antes de la sanitización general si el JSON necesita caracteres especiales.
            $raw_items_pedido = $_POST['items_pedido'] ?? '[]'; // Capturar el JSON antes de la sanitización global

            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING); // Luego sanear el resto

            // Asegúrate de que $id_cliente y $observaciones se capturen antes o se manejen para que no afecte el JSON
            $id_cliente = (int)($_POST['id_cliente'] ?? 0); // Asegura que se captura antes o después de la sanitización
            $observaciones = trim($_POST['observaciones'] ?? ''); // Igual para observaciones

            $items_pedido = json_decode($raw_items_pedido, true); // Decodificar el JSON capturado sin sanitizar

            if (empty($id_cliente) || empty($items_pedido)) {
                $_SESSION['error'] = "Debe seleccionar un cliente y al menos un producto para el pedido.";
                header('Location: ' . URLROOT . '/order/create');
                exit();
            }

            $order_id = $this->orderModel->createOrder($id_cliente, $_SESSION['user_id'], $observaciones);

            if ($order_id) {
                $transaction_successful = true;
                foreach ($items_pedido as $item) {
                    $product_id = (int)$item['product_id'];
                    $deposito_id = (int)$item['deposito_id'];
                    $cantidad = (int)$item['cantidad'];

                    // Obtener precio actual del producto (usar el precio_venta si existe en tu tabla productos)
                    $product_info = $this->productModel->getProductById($product_id);
                    $precio_unitario = $product_info ? $product_info->precio_venta : 0; // Asume 'precio_venta' en productos

                    if ($precio_unitario <= 0) {
                         $_SESSION['error'] = "El producto '{$product_info->nombre_comercial}' no tiene un precio de venta válido.";
                         $this->orderModel->deleteOrder($order_id); // Revertir pedido
                         header('Location: ' . URLROOT . '/order/create');
                         exit();
                    }

                    // Verificar stock antes de añadir al detalle y decrementar
                    $current_stock = $this->inventoryModel->getProductStockInDeposito($product_id, $deposito_id);
                    if ($current_stock < $cantidad) {
                        $_SESSION['error'] = "Stock insuficiente para {$product_info->nombre_comercial} en el depósito seleccionado. Stock actual: {$current_stock}, solicitado: {$cantidad}.";
                        $this->orderModel->deleteOrder($order_id); // Revertir pedido
                        header('Location: ' . URLROOT . '/order/create');
                        exit();
                    }

                    // Añadir ítem al detalle del pedido
                    if (!$this->orderModel->addOrderItem($order_id, $product_id, $cantidad, $precio_unitario)) {
                        $transaction_successful = false;
                        break;
                    }

                    // Decrementar stock
                    if (!$this->inventoryModel->decrementStock($product_id, $deposito_id, $cantidad, $_SESSION['user_id'], "Venta - Pedido #{$order_id}")) {
                        $transaction_successful = false;
                        break;
                    }
                }

                if ($transaction_successful) {
                    // Actualizar el total del pedido después de añadir todos los ítems
                    $this->orderModel->updateOrderTotal($order_id);
                    $_SESSION['message'] = "Pedido creado y stock actualizado exitosamente.";
                    header('Location: ' . URLROOT . '/order/show/' . $order_id); // Redirigir a la vista del pedido
                    exit();
                } else {
                    $_SESSION['error'] = "Error al procesar los ítems del pedido. Se ha revertido el pedido.";
                    $this->orderModel->deleteOrder($order_id); // Revertir pedido si falla algún ítem o stock
                    header('Location: ' . URLROOT . '/order/create');
                    exit();
                }

            } else {
                $_SESSION['error'] = "Error al crear el pedido base.";
                header('Location: ' . URLROOT . '/order/create');
                exit();
            }
        } else {
            header('Location: ' . URLROOT . '/order/index');
            exit();
        }
    }

    // Mostrar detalles de un pedido específico
    public function show($id_pedido) {
        // Permisos para ver el detalle del pedido
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen' && $_SESSION['role_name'] !== 'Vendedor') {
            $_SESSION['error'] = "Acceso denegado. No tienes permisos para ver el detalle de pedidos.";
            header('Location: ' . URLROOT . '/home');
            exit();
        }
        $pedido = $this->orderModel->getOrderById($id_pedido);
        $detalles = $this->orderModel->getOrderDetails($id_pedido);

        if (!$pedido) {
            $_SESSION['error'] = "Pedido no encontrado.";
            header('Location: ' . URLROOT . '/order/index');
            exit();
        }
        require_once '../app/views/order/show.php';
    }

    // Método para cambiar el estado de un pedido (ej. de Pendiente a Completado)
    public function changeStatus($id_pedido) {
        // Solo Administrador y Gerente de Almacén pueden cambiar el estado
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            $_SESSION['error'] = "Acceso denegado. No tienes permisos para cambiar el estado de pedidos.";
            header('Location: ' . URLROOT . '/order/show/' . $id_pedido);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_status = trim($_POST['new_status']);
            if (empty($new_status)) {
                $_SESSION['error'] = "El estado no puede estar vacío.";
                header('Location: ' . URLROOT . '/order/show/' . $id_pedido);
                exit();
            }

            if ($this->orderModel->updateOrderStatus($id_pedido, $new_status)) {
                $_SESSION['message'] = "Estado del pedido actualizado a '{$new_status}' exitosamente.";
            } else {
                $_SESSION['error'] = "Error al actualizar el estado del pedido.";
            }
        }
        header('Location: ' . URLROOT . '/order/show/' . $id_pedido);
        exit();
    }

    // Eliminar un pedido
    public function delete($id_pedido) {
        // Solo Administrador puede eliminar pedidos
        if ($_SESSION['role_name'] !== 'Administrador') {
            $_SESSION['error'] = "Acceso denegado. No tienes permisos para eliminar pedidos.";
            header('Location: ' . URLROOT . '/order/index');
            exit();
        }

        if ($this->orderModel->deleteOrder($id_pedido)) {
            $_SESSION['message'] = "Pedido eliminado exitosamente.";
        } else {
            $_SESSION['error'] = "Error al eliminar el pedido.";
        }
        header('Location: ' . URLROOT . '/order/index');
        exit();
    }
}