<?php
// app/controllers/ProveedorController.php

class ProveedorController {
    private $db;
    private $proveedorModel;

    public function __construct() {
        $this->db = new Database();
        $this->proveedorModel = new ProveedorModel($this->db);

        // Protección básica: solo usuarios logueados pueden acceder
        if (!isset($_SESSION['user_id'])) {
            flash('error_message', 'Debe iniciar sesión para acceder a esta página.');
            redirect('auth/login');
            exit();
        }
        // Puedes añadir aquí control de roles si solo ciertos roles pueden gestionar proveedores
        // if ($_SESSION['role_name'] !== 'Administrador') {
        //     flash('error_message', 'Acceso denegado. No tienes permisos para gestionar proveedores.');
        //     redirect('dashboard/index'); // O a donde corresponda
        //     exit();
        // }
    }

    public function index() {
        $proveedores = $this->proveedorModel->getAllProveedores();
        $data = ['proveedores' => $proveedores];
        $this->view('proveedor/index', $data);
    }

    public function create() {
        // Asumiendo que solo administradores pueden crear proveedores
        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado. No tienes permisos para crear proveedores.');
            redirect('proveedor/index');
            exit();
        }

        $data = [
            'nombre_proveedor' => '',
            'contacto_persona' => '',
            'contacto_email' => '',
            'contacto_telefono' => '',
            'direccion' => '',
            'ruc' => '',
            'condiciones_pago' => ''
        ];
        $this->view('proveedor/create_edit', $data);
    }

    public function store() {
        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado.');
            redirect('proveedor/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'nombre_proveedor' => trim($_POST['nombre_proveedor']),
                'contacto_persona' => trim($_POST['contacto_persona'] ?? ''),
                'contacto_email' => trim($_POST['contacto_email'] ?? ''),
                'contacto_telefono' => trim($_POST['contacto_telefono'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'ruc' => trim($_POST['ruc'] ?? ''),
                'condiciones_pago' => trim($_POST['condiciones_pago'] ?? '')
            ];

            // Validaciones
            if (empty($data['nombre_proveedor'])) {
                flash('error_message', 'El nombre del proveedor es obligatorio.');
                $this->view('proveedor/create_edit', $data);
                exit();
            }
            // Validar RUC único si es que la columna ruc es UNIQUE en la BD
            // if ($this->proveedorModel->findProveedorByRuc($data['ruc'])) { // Necesitarías este método en el modelo
            //     flash('error_message', 'El RUC ya está registrado para otro proveedor.');
            //     $this->view('proveedor/create_edit', $data);
            //     exit();
            // }

            if ($this->proveedorModel->addProveedor($data)) {
                flash('success_message', 'Proveedor creado exitosamente.');
                redirect('proveedor/index');
            } else {
                flash('error_message', 'Error al crear el proveedor.');
                $this->view('proveedor/create_edit', $data);
            }
        } else {
            redirect('proveedor/index');
        }
    }

    public function edit($id) {
        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado. No tienes permisos para editar proveedores.');
            redirect('proveedor/index');
            exit();
        }

        $proveedor = $this->proveedorModel->getProveedorById($id);

        if (!$proveedor) {
            flash('error_message', 'Proveedor no encontrado.');
            redirect('proveedor/index');
            exit();
        }

        // Convertir el objeto a array para pasar a la vista
        $data = (array)$proveedor;
        $this->view('proveedor/create_edit', $data);
    }

    public function update($id) {
        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado. No tienes permisos para editar proveedores.');
            redirect('proveedor/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'id_proveedor' => $id,
                'nombre_proveedor' => trim($_POST['nombre_proveedor']),
                'contacto_persona' => trim($_POST['contacto_persona'] ?? ''),
                'contacto_email' => trim($_POST['contacto_email'] ?? ''),
                'contacto_telefono' => trim($_POST['contacto_telefono'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'ruc' => trim($_POST['ruc'] ?? ''),
                'condiciones_pago' => trim($_POST['condiciones_pago'] ?? '')
            ];

            // Validaciones
            if (empty($data['nombre_proveedor'])) {
                flash('error_message', 'El nombre del proveedor es obligatorio.');
                $this->view('proveedor/create_edit', $data);
                exit();
            }
            // Validar RUC único (excepto para el proveedor que estamos editando)
            // $existingProveedorWithRuc = $this->proveedorModel->findProveedorByRuc($data['ruc']); // Necesitarías este método
            // if ($existingProveedorWithRuc && $existingProveedorWithRuc->id_proveedor != $id) {
            //     flash('error_message', 'El RUC ya está registrado para otro proveedor.');
            //     $this->view('proveedor/create_edit', $data);
            //     exit();
            // }

            if ($this->proveedorModel->updateProveedor($data)) {
                flash('success_message', 'Proveedor actualizado exitosamente.');
                redirect('proveedor/index');
            } else {
                flash('error_message', 'Error al actualizar el proveedor.');
                $this->view('proveedor/create_edit', $data);
            }
        } else {
            redirect('proveedor/index');
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            flash('error_message', 'Método de solicitud no permitido para eliminar.');
            redirect('proveedor/index');
            exit();
        }

        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado. No tienes permisos para eliminar proveedores.');
            redirect('proveedor/index');
            exit();
        }

        $id_proveedor = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id_proveedor) {
            flash('error_message', 'ID de proveedor inválido.');
            redirect('proveedor/index');
            exit();
        }

        // --- Lógica de Verificación de Dependencias ANTES de Eliminar ---
        if ($this->proveedorModel->hasDependencies($id_proveedor)) {
            flash('error_message', "No se puede eliminar el proveedor porque tiene productos u órdenes de compra asociados.");
            redirect('proveedor/index');
            exit();
        }
        // --- Fin Lógica de Verificación ---

        if ($this->proveedorModel->deleteProveedor($id_proveedor)) {
            flash('success_message', 'Proveedor eliminado exitosamente.');
        } else {
            flash('error_message', 'Error al eliminar el proveedor.');
        }
        redirect('proveedor/index');
    }

    /**
     * Helper para cargar vistas y pasar datos.
     * Asume que APPROOT está definido y apunta a la raíz de la aplicación (e.g., /app).
     * @param string $viewName Nombre de la vista (ej. 'proveedor/index').
     * @param array $data Array asociativo de datos a pasar a la vista.
     */
    private function view($viewName, $data = []) {
        extract($data);
        require_once APPROOT . '/views/' . $viewName . '.php';
    }
}