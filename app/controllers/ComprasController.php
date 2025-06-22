<?php
// app/controllers/ComprasController.php

class ComprasController {
    private $db;
    private $proveedorModel;
    private $ordenCompraModel;
    private $productModel;
    private $inventoryModel; // Se añadirá para la recepción de mercancía

    public function __construct() {
        $this->db = new Database();
        $this->proveedorModel = new ProveedorModel($this->db);
        $this->ordenCompraModel = new OrdenCompraModel($this->db);
        $this->productModel = new ProductModel($this->db);
        $this->inventoryModel = new InventoryModel($this->db); // Instanciar InventoryModel

        // Protección básica: solo usuarios logueados pueden acceder
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Debes iniciar sesión para acceder a esta página.";
            header('Location: ' . URLROOT . '/auth/login');
            exit();
        }
    }

    /**
     * Muestra la página principal del módulo de Compras: listado de Órdenes de Compra.
     * Acceso: Administrador, Gerente_Almacen.
     */
    public function index() {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            $_SESSION['error'] = "Acceso denegado. No tienes permisos para ver las Órdenes de Compra.";
            header('Location: ' . URLROOT . '/dashboard');
            exit();
        }

        $ordenesCompra = $this->ordenCompraModel->getAllOrdenesCompra();
        require_once APPROOT . '/views/compras/index.php';
    }

    // --- Métodos para la Gestión de Proveedores ---

    /**
     * Muestra la lista de proveedores.
     * Acceso: Administrador, Gerente_Almacen.
     */
    public function proveedores() {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            $_SESSION['error'] = "Acceso denegado. No tienes permisos para gestionar proveedores.";
            header('Location: ' . URLROOT . '/dashboard');
            exit();
        }

        $proveedores = $this->proveedorModel->getAllProveedores();
        require_once APPROOT . '/views/compras/proveedores/index.php';
    }

    /**
     * Muestra el formulario para añadir un nuevo proveedor o procesa el envío del formulario.
     * Acceso: Administrador, Gerente_Almacen.
     */
    public function agregarProveedor() {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            $_SESSION['error'] = "Acceso denegado.";
            header('Location: ' . URLROOT . '/compras/proveedores');
            exit();
        }

        $data = [
            'nombre_proveedor' => '',
            'contacto_persona' => '',
            'contacto_email' => '',
            'contacto_telefono' => '',
            'direccion' => '',
            'ruc' => '',
            'condiciones_pago' => '',
            'nombre_proveedor_err' => '' // Para errores de validación
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'nombre_proveedor' => trim($_POST['nombre_proveedor']),
                'contacto_persona' => trim($_POST['contacto_persona']),
                'contacto_email' => trim($_POST['contacto_email']),
                'contacto_telefono' => trim($_POST['contacto_telefono']),
                'direccion' => trim($_POST['direccion']),
                'ruc' => trim($_POST['ruc']),
                'condiciones_pago' => trim($_POST['condiciones_pago']),
                'nombre_proveedor_err' => ''
            ];

            // Validación simple
            if (empty($data['nombre_proveedor'])) {
                $data['nombre_proveedor_err'] = "El nombre del proveedor es obligatorio.";
            }

            if (empty($data['nombre_proveedor_err'])) {
                if ($this->proveedorModel->addProveedor($data)) {
                    flash('message', "Proveedor agregado exitosamente.");
                    header('Location: ' . URLROOT . '/compras/proveedores');
                    exit();
                } else {
                    flash('error', "Error al agregar el proveedor. Intenta de nuevo.");
                }
            }
            require_once APPROOT . '/views/compras/proveedores/agregar.php';
        } else {
            require_once APPROOT . '/views/compras/proveedores/agregar.php';
        }
    }

    /**
     * Muestra el formulario para editar un proveedor o procesa el envío del formulario.
     * Acceso: Administrador, Gerente_Almacen.
     */
    public function editarProveedor($id) {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            $_SESSION['error'] = "Acceso denegado.";
            header('Location: ' . URLROOT . '/compras/proveedores');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'id_proveedor' => $id,
                'nombre_proveedor' => trim($_POST['nombre_proveedor']),
                'contacto_persona' => trim($_POST['contacto_persona']),
                'contacto_email' => trim($_POST['contacto_email']),
                'contacto_telefono' => trim($_POST['contacto_telefono']),
                'direccion' => trim($_POST['direccion']),
                'ruc' => trim($_POST['ruc']),
                'condiciones_pago' => trim($_POST['condiciones_pago']),
                'nombre_proveedor_err' => ''
            ];

            // Validación simple
            if (empty($data['nombre_proveedor'])) {
                $data['nombre_proveedor_err'] = "El nombre del proveedor es obligatorio.";
            }

            if (empty($data['nombre_proveedor_err'])) {
                if ($this->proveedorModel->updateProveedor($data)) {
                    flash('message', "Proveedor actualizado exitosamente.");
                    header('Location: ' . URLROOT . '/compras/proveedores');
                    exit();
                } else {
                    flash('error', "Error al actualizar el proveedor. Intenta de nuevo.");
                }
            }
            // Si hay errores, recargar la vista con los datos y errores
            $proveedor = (object)$data; // Asegurarse de que la vista reciba un objeto
            require_once APPROOT . '/views/compras/proveedores/editar.php';
        } else {
            $proveedor = $this->proveedorModel->getProveedorById($id);

            if (!$proveedor) {
                flash('error', "Proveedor no encontrado.");
                header('Location: ' . URLROOT . '/compras/proveedores');
                exit();
            }
            // Pasar los datos del proveedor a la vista como un array 'data' para consistencia con agregar
            $data = (array)$proveedor; // Convertir objeto a array
            require_once APPROOT . '/views/compras/proveedores/editar.php';
        }
    }

    /**
     * Elimina un proveedor.
     * Acceso: Administrador, Gerente_Almacen.
     */
    public function eliminarProveedor($id) {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            $_SESSION['error'] = "Acceso denegado.";
            header('Location: ' . URLROOT . '/compras/proveedores');
            exit();
        }

        if ($this->proveedorModel->deleteProveedor($id)) {
            flash('message', "Proveedor eliminado exitosamente.");
        } else {
            flash('error', "Error al eliminar el proveedor. Podría tener órdenes de compra asociadas.");
        }
        header('Location: ' . URLROOT . '/compras/proveedores');
        exit();
    }

    // --- Nuevos Métodos para la Gestión de Órdenes de Compra ---

    /**
     * Muestra el formulario para crear una nueva Orden de Compra o procesa el envío del formulario.
     * Acceso: Administrador, Gerente_Almacen.
     */
    public function crearOrdenCompra() {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            $_SESSION['error'] = "Acceso denegado. No tienes permisos para crear Órdenes de Compra.";
            header('Location: ' . URLROOT . '/compras');
            exit();
        }

        $proveedores = $this->proveedorModel->getAllProveedores();
        $productos = $this->productModel->getAllProducts();

        $data = [
            'proveedores' => $proveedores,
            'productos' => $productos,
            'id_proveedor' => '',
            'fecha_creacion' => date('Y-m-d'), // Fecha actual por defecto
            'fecha_esperada_entrega' => '',
            'observaciones' => '',
            'items' => [], // Para los productos añadidos dinámicamente
            'oc_err' => '' // Para errores generales de la OC
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Procesar el envío del formulario de la OC
            $data['id_proveedor'] = trim($_POST['id_proveedor']);
            $data['fecha_creacion'] = trim($_POST['fecha_creacion']);
            $data['fecha_esperada_entrega'] = trim($_POST['fecha_esperada_entrega']);
            $data['observaciones'] = trim($_POST['observaciones']);
            $data['items'] = json_decode($_POST['items_json'], true);

            // Validación de la cabecera
            if (empty($data['id_proveedor']) || empty($data['fecha_creacion'])) {
                $data['oc_err'] = "Por favor, selecciona un proveedor y una fecha de creación.";
            }
            if (empty($data['items'])) {
                $data['oc_err'] = "Debes añadir al menos un producto a la orden de compra.";
            }

            // Validación de ítems (básica)
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    if (empty($item['id_producto']) || !is_numeric($item['cantidad']) || $item['cantidad'] <= 0 || !is_numeric($item['costo_unitario']) || $item['costo_unitario'] <= 0) {
                        $data['oc_err'] = "Todos los productos deben tener un producto seleccionado, una cantidad válida y un costo unitario válido.";
                        break;
                    }
                }
            }
            
            if (empty($data['oc_err'])) {
                // Calcular el total de la orden
                $total_orden = 0;
                foreach ($data['items'] as $item) {
                    $total_orden += $item['cantidad'] * $item['costo_unitario'];
                }
                $data['total_orden'] = $total_orden;

                $data_oc_cabecera = [
                    'id_proveedor' => $data['id_proveedor'],
                    'fecha_creacion' => $data['fecha_creacion'],
                    'fecha_esperada_entrega' => $data['fecha_esperada_entrega'],
                    'estado' => 'Pendiente', // Estado inicial
                    'id_usuario_creacion' => $_SESSION['user_id'],
                    'observaciones' => $data['observaciones'],
                    'total_orden' => $data['total_orden']
                ];

                $this->db->beginTransaction();
                try {
                    $id_orden_compra = $this->ordenCompraModel->createOrdenCompra($data_oc_cabecera);

                    if (!$id_orden_compra) {
                        throw new Exception("Error al crear la cabecera de la Orden de Compra.");
                    }

                    foreach ($data['items'] as $item) {
                        $data_oc_detalle = [
                            'id_orden_compra' => $id_orden_compra,
                            'id_producto' => $item['id_producto'],
                            'cantidad_pedida' => $item['cantidad'],
                            'costo_unitario' => $item['costo_unitario']
                        ];
                        if (!$this->ordenCompraModel->addDetalleOrdenCompra($data_oc_detalle)) {
                            throw new Exception("Error al añadir un detalle de producto a la OC.");
                        }
                    }

                    $this->db->commit();
                    flash('message', "Orden de Compra creada exitosamente.");
                    header('Location: ' . URLROOT . '/compras');
                    exit();

                } catch (Exception $e) {
                    $this->db->rollBack();
                    flash('error', "Error al crear la Orden de Compra: " . $e->getMessage());
                }
            }
            require_once APPROOT . '/views/compras/crear_oc.php';
        } else {
            require_once APPROOT . '/views/compras/crear_oc.php';
        }
    }

    /**
     * Muestra los detalles de una Orden de Compra específica.
     * Acceso: Administrador, Gerente_Almacen.
     */
    public function verOrdenCompra($id_orden_compra) {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            $_SESSION['error'] = "Acceso denegado. No tienes permisos para ver Órdenes de Compra.";
            header('Location: ' . URLROOT . '/compras');
            exit();
        }

        $orden_compra = $this->ordenCompraModel->getOrdenCompraById($id_orden_compra);
        if (!$orden_compra) {
            flash('error', "Orden de Compra no encontrada.");
            header('Location: ' . URLROOT . '/compras');
            exit();
        }

        $detalles = $this->ordenCompraModel->getDetallesOrdenCompra($id_orden_compra);

        $data = [
            'orden_compra' => $orden_compra,
            'detalles' => $detalles
        ];
        require_once APPROOT . '/views/compras/ver_oc.php';
    }

    /**
     * Procesa la eliminación de una Orden de Compra.
     * Acceso: Administrador, Gerente_Almacen.
     */
    public function eliminarOrdenCompra($id_orden_compra) {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            $_SESSION['error'] = "Acceso denegado. No tienes permisos para eliminar Órdenes de Compra.";
            header('Location: ' . URLROOT . '/compras');
            exit();
        }

        // Antes de eliminar, podrías añadir una validación para no eliminar OCs recibidas, etc.
        // Por ahora, la FK con ON DELETE CASCADE en la BD ya elimina los detalles.
        if ($this->ordenCompraModel->deleteOrdenCompra($id_orden_compra)) {
            flash('message', "Orden de Compra eliminada exitosamente.");
        } else {
            flash('error', "Error al eliminar la Orden de Compra.");
        }
        header('Location: ' . URLROOT . '/compras');
        exit();
    }

    /**
     * Procesa la recepción de mercancía para una Orden de Compra.
     * Aquí se actualizará el stock y el estado de la OC.
     * Acceso: Administrador, Gerente_Almacen.
     */
    public function recibirMercancia($id_orden_compra) {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            $_SESSION['error'] = "Acceso denegado. No tienes permisos para recibir mercancía.";
            header('Location: ' . URLROOT . '/compras');
            exit();
        }

        $orden_compra = $this->ordenCompraModel->getOrdenCompraById($id_orden_compra);
        if (!$orden_compra) {
            flash('error', "Orden de Compra no encontrada.");
            header('Location: ' . URLROOT . '/compras');
            exit();
        }

        if ($orden_compra->estado === 'Recibida Completa' || $orden_compra->estado === 'Cancelada') {
            flash('error', "No se puede recibir mercancía para una orden ya completada o cancelada.");
            header('Location: ' . URLROOT . '/compras/verOrdenCompra/' . $id_orden_compra);
            exit();
        }

        $detalles = $this->ordenCompraModel->getDetallesOrdenCompra($id_orden_compra);
        // Necesitamos saber en qué depósito se va a recibir la mercancía.
        // Para simplificar por ahora, podemos asumir un depósito por defecto o pedirlo en un formulario.
        // Por ejemplo, podríamos hacer que el usuario seleccione el depósito al confirmar la recepción.
        // Para este ejemplo, vamos a asumir que recibimos todo en el depósito con ID 1 (depósito principal).
        // En una implementación real, esto debería ser más robusto (selección por UI).
        $id_deposito_destino = 1; // Asumiendo un depósito por defecto para la entrada. ¡AJUSTA ESTO!

        $this->db->beginTransaction();
        try {
            $all_items_received = true;
            foreach ($detalles as $detalle) {
                // Cantidad a recibir: Por ahora, asumimos que se recibe toda la cantidad pedida.
                // En un sistema más avanzado, un formulario permitiría especificar la cantidad recibida de cada item.
                $cantidad_a_recibir = $detalle->cantidad_pedida - $detalle->cantidad_recibida; 
                
                if ($cantidad_a_recibir > 0) {
                    // Actualizar cantidad recibida en el detalle de la OC
                    $data_update_detalle = [
                        'id_detalle' => $detalle->id_detalle,
                        'id_producto' => $detalle->id_producto, // Mantener estos campos
                        'cantidad_pedida' => $detalle->cantidad_pedida, // Mantener estos campos
                        'cantidad_recibida' => $detalle->cantidad_recibida + $cantidad_a_recibir,
                        'costo_unitario' => $detalle->costo_unitario
                    ];
                    if (!$this->ordenCompraModel->updateDetalleOrdenCompra($data_update_detalle)) {
                        throw new Exception("Error al actualizar la cantidad recibida para el producto " . $detalle->producto_nombre);
                    }

                    // Registrar movimiento de entrada en el inventario
                    // Aquí asumimos que no hay depósito origen (es una entrada externa)
                    $data_movimiento = [
                        'id_producto' => $detalle->id_producto,
                        'id_deposito_origen' => null, // No hay depósito de origen para una compra
                        'id_deposito_destino' => $id_deposito_destino,
                        'tipo_movimiento' => 'Entrada', // Tipo de movimiento para una compra
                        'cantidad' => $cantidad_a_recibir,
                        'id_usuario_responsable' => $_SESSION['user_id'],
                        'observaciones' => 'Recepción de OC #' . $id_orden_compra . '. Costo Unitario: ' . $detalle->costo_unitario
                        // Considera añadir 'id_orden_compra' a movimientos_inventario para trazabilidad directa
                    ];
                    if (!$this->inventoryModel->registrarMovimientoInventario($data_movimiento)) {
                        throw new Exception("Error al registrar el movimiento de inventario para el producto " . $detalle->producto_nombre);
                    }
                    
                    // Actualizar el stock del producto en el depósito
                    if (!$this->inventoryModel->updateOrCreateStock($detalle->id_producto, $id_deposito_destino, $cantidad_a_recibir, 'sum')) {
                        throw new Exception("Error al actualizar el stock para el producto " . $detalle->producto_nombre);
                    }
                }

                // Verificar si todos los ítems están completamente recibidos
                if (($detalle->cantidad_recibida + $cantidad_a_recibir) < $detalle->cantidad_pedida) {
                    $all_items_received = false;
                }
            }

            // Actualizar el estado de la OC
            $nuevo_estado = $all_items_received ? 'Recibida Completa' : 'Recibida Parcial';
            $data_update_oc = [
                'id_orden_compra' => $id_orden_compra,
                'id_proveedor' => $orden_compra->id_proveedor, // Mantener datos existentes
                'fecha_creacion' => $orden_compra->fecha_creacion,
                'fecha_esperada_entrega' => $orden_compra->fecha_esperada_entrega,
                'estado' => $nuevo_estado,
                'observaciones' => $orden_compra->observaciones,
                'total_orden' => $orden_compra->total_orden // El total no cambia al recibir, solo el estado
            ];
            if (!$this->ordenCompraModel->updateOrdenCompra($data_update_oc)) {
                throw new Exception("Error al actualizar el estado de la Orden de Compra.");
            }

            $this->db->commit();
            flash('message', "Mercancía recibida exitosamente. Estado de OC: " . $nuevo_estado);
            header('Location: ' . URLROOT . '/compras/verOrdenCompra/' . $id_orden_compra);
            exit();

        } catch (Exception $e) {
            $this->db->rollBack();
            flash('error', "Error al recibir mercancía: " . $e->getMessage());
            header('Location: ' . URLROOT . '/compras/verOrdenCompra/' . $id_orden_compra);
            exit();
        }
    }
    
    // --- Fin de Nuevos Métodos para la Gestión de Órdenes de Compra ---

}