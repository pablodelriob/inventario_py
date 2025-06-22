<?php
// app/controllers/DepositoController.php

class DepositoController {
    private $db;
    private $depositoModel;
    private $inventoryModel; // <-- ¡Añadir esta declaración!

    public function __construct() {
        $this->db = new Database();
        $this->depositoModel = new DepositoModel($this->db);
        $this->inventoryModel = new InventoryModel($this->db); // <-- ¡Instanciar el InventoryModel!

        // Protección básica: solo usuarios logueados pueden acceder
        if (!isset($_SESSION['user_id'])) {
            // Usar flash y redirect para mensajes consistentes
            flash('error_message', 'Debe iniciar sesión para acceder a esta página.');
            redirect('auth/login');
            exit();
        }
    }

    // Muestra la lista de todos los depósitos
    public function index() {
        // Acceso permitido solo a Administrador y Gerente de Almacén
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            flash('error_message', 'Acceso denegado. No tienes permisos para ver los depósitos.');
            redirect('dashboard');
            exit();
        }

        $depositos = $this->depositoModel->getAllDepositos();
        $data = [
            'depositos' => $depositos
        ];
        // Asegúrate de que tu helper `view` o `require_once` pueda manejar el paso de `$data`
        $this->view('deposito/index', $data); // Asumiendo que tienes un método 'view'
    }

    // Muestra el formulario para crear un nuevo depósito
    public function create() {
        // Solo Administrador puede crear depósitos
        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado. No tienes permisos para crear depósitos.');
            redirect('deposito/index');
            exit();
        }
        // No pasamos $deposito, por lo que la vista mostrará el formulario de creación
        $data = ['nombre_deposito' => '', 'ubicacion' => '', 'capacidad_maxima' => '']; // Para evitar 'Undefined variable' en la vista
        $this->view('deposito/create_edit', $data);
    }

    // Procesa el formulario de creación de depósitos
    public function store() {
        // Solo Administrador puede guardar depósitos
        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado.');
            redirect('deposito/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Limpiar y validar los datos del formulario
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'nombre_deposito' => trim($_POST['nombre_deposito']),
                'ubicacion' => trim($_POST['ubicacion'] ?? ''),
                'capacidad_maxima' => filter_var($_POST['capacidad_maxima'], FILTER_VALIDATE_INT)
            ];

            // Validación
            if (empty($data['nombre_deposito'])) {
                flash('error_message', "El nombre del depósito es obligatorio.");
                $this->view('deposito/create_edit', $data); // Pasa $data para repoblar el formulario
                exit();
            }
            if ($this->depositoModel->findDepositoByName($data['nombre_deposito'])) {
                flash('error_message', "Ya existe un depósito con ese nombre.");
                $this->view('deposito/create_edit', $data);
                exit();
            }
            // filter_var con FILTER_VALIDATE_INT devuelve false para valores no enteros,
            // pero también 0 para '0'. Si no quieres 0 válido, añade un chequeo.
            if ($data['capacidad_maxima'] === false && !empty($_POST['capacidad_maxima'])) {
                flash('error_message', "La capacidad máxima debe ser un número entero válido.");
                $this->view('deposito/create_edit', $data);
                exit();
            }
            if ($data['capacidad_maxima'] < 0) {
                flash('error_message', "La capacidad máxima no puede ser negativa.");
                $this->view('deposito/create_edit', $data);
                exit();
            }

            if ($this->depositoModel->createDeposito($data)) {
                flash('success_message', "Depósito creado exitosamente.");
                redirect('deposito/index');
            } else {
                flash('error_message', "Error al crear el depósito.");
                $this->view('deposito/create_edit', $data);
            }
        } else {
            redirect('deposito/index'); // Si no es POST, redirigir
        }
    }

    // Muestra el formulario para editar un depósito existente
    public function edit($id) {
        // Solo Administrador puede editar depósitos
        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado. No tienes permisos para editar depósitos.');
            redirect('deposito/index');
            exit();
        }

        $deposito = $this->depositoModel->getDepositoById($id);

        if (!$deposito) {
            flash('error_message', "Depósito no encontrado.");
            redirect('deposito/index');
            exit();
        }
        $this->view('deposito/create_edit', (array)$deposito); // Convertir objeto a array para la vista si es necesario
    }

    // Procesa el formulario de actualización de depósitos
    public function update($id) {
        // Solo Administrador puede actualizar depósitos
        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado.');
            redirect('deposito/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'id_deposito' => $id,
                'nombre_deposito' => trim($_POST['nombre_deposito']),
                'ubicacion' => trim($_POST['ubicacion'] ?? ''),
                'capacidad_maxima' => filter_var($_POST['capacidad_maxima'], FILTER_VALIDATE_INT)
            ];

            // Validación
            if (empty($data['nombre_deposito'])) {
                flash('error_message', "El nombre del depósito es obligatorio.");
                $deposito = $this->depositoModel->getDepositoById($id); // Recargar para la vista
                $this->view('deposito/create_edit', (array)$deposito);
                exit();
            }

            // Validar nombre único (excepto para el depósito que estamos editando)
            $existingDeposito = $this->depositoModel->findDepositoByName($data['nombre_deposito']);
            if ($existingDeposito && $existingDeposito->id_deposito != $id) {
                flash('error_message', "Ya existe un depósito con ese nombre.");
                $deposito = $this->depositoModel->getDepositoById($id);
                $this->view('deposito/create_edit', (array)$deposito);
                exit();
            }
            if ($data['capacidad_maxima'] === false && !empty($_POST['capacidad_maxima'])) {
                flash('error_message', "La capacidad máxima debe ser un número entero válido.");
                $deposito = $this->depositoModel->getDepositoById($id);
                $this->view('deposito/create_edit', (array)$deposito);
                exit();
            }
            if ($data['capacidad_maxima'] < 0) {
                flash('error_message', "La capacidad máxima no puede ser negativa.");
                $deposito = $this->depositoModel->getDepositoById($id);
                $this->view('deposito/create_edit', (array)$deposito);
                exit();
            }

            if ($this->depositoModel->updateDeposito($data)) {
                flash('success_message', "Depósito actualizado exitosamente.");
                redirect('deposito/index');
            } else {
                flash('error_message', "Error al actualizar el depósito.");
                $deposito = $this->depositoModel->getDepositoById($id);
                $this->view('deposito/create_edit', (array)$deposito);
            }
        } else {
            redirect('deposito/index');
        }
    }

    // Elimina un depósito
    public function delete($id) {
        // Solo Administrador puede eliminar depósitos
        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado. No tienes permisos para eliminar depósitos.');
            redirect('deposito/index');
            exit();
        }

        // Se recomienda que la eliminación siempre sea por POST para evitar eliminaciones accidentales
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_deposito = filter_var($id, FILTER_VALIDATE_INT);

            if (!$id_deposito) {
                flash('error_message', 'ID de depósito inválido.');
                redirect('deposito/index');
                exit();
            }

            // --- Lógica de Verificación de Stock ANTES de Eliminar ---
            // Usamos el inventoryModel para verificar si hay stock en este depósito
            $totalStockInDeposito = $this->inventoryModel->countProductsInDeposito($id_deposito);

            if ($totalStockInDeposito > 0) {
                flash('error_message', "No se puede eliminar el depósito porque aún contiene {$totalStockInDeposito} unidades de stock. Por favor, vacíelo primero.");
                redirect('deposito/index');
                exit();
            }
            // --- Fin Lógica de Verificación ---

            if ($this->depositoModel->deleteDeposito($id_deposito)) {
                flash('success_message', 'Depósito eliminado exitosamente.');
                redirect('deposito/index');
            } else {
                flash('error_message', 'Error al eliminar el depósito. Puede que existan productos asociados a este depósito.');
                redirect('deposito/index');
            }
        } else {
            flash('error_message', 'Método de solicitud no permitido.');
            redirect('deposito/index');
        }
    }

    // Asumiendo que tienes un método 'view' para cargar las vistas y pasar datos
    private function view($viewName, $data = []) {
        // Extraer los datos para que estén disponibles como variables en la vista
        extract($data);
        require_once APPROOT . '/views/' . $viewName . '.php';
    }
}