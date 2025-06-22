<?php
// app/controllers/CategoriaController.php

class CategoriaController {
    private $db;
    private $categoriaModel;

    public function __construct() {
        $this->db = new Database();
        $this->categoriaModel = new CategoriaModel($this->db);

        // Protección básica: solo usuarios logueados pueden acceder
        if (!isset($_SESSION['user_id'])) {
            flash('error_message', 'Debe iniciar sesión para acceder a esta página.');
            redirect('auth/login');
            exit();
        }
        // Puedes añadir aquí control de roles si solo ciertos roles pueden gestionar categorías
        // if ($_SESSION['role_name'] !== 'Administrador') {
        //     flash('error_message', 'Acceso denegado. No tienes permisos para gestionar categorías.');
        //     redirect('dashboard/index'); // O a donde corresponda
        //     exit();
        // }
    }

    public function index() {
        $categorias = $this->categoriaModel->getAllCategorias();
        $data = ['categorias' => $categorias];
        $this->view('categoria/index', $data);
    }

    public function create() {
        // Asumiendo que solo administradores pueden crear categorías
        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado. No tienes permisos para crear categorías.');
            redirect('categoria/index');
            exit();
        }
        $data = [
            'nombre_categoria' => '',
            'descripcion' => ''
        ];
        $this->view('categoria/create_edit', $data);
    }

    public function store() {
        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado.');
            redirect('categoria/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'nombre_categoria' => trim($_POST['nombre_categoria']),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ];

            // Validaciones
            if (empty($data['nombre_categoria'])) {
                flash('error_message', 'El nombre de la categoría es obligatorio.');
                $this->view('categoria/create_edit', $data);
                exit();
            }
            // Validar nombre de categoría único
            if ($this->categoriaModel->findCategoriaByName($data['nombre_categoria'])) {
                flash('error_message', 'Ya existe una categoría con este nombre.');
                $this->view('categoria/create_edit', $data);
                exit();
            }

            if ($this->categoriaModel->addCategoria($data)) {
                flash('success_message', 'Categoría creada exitosamente.');
                redirect('categoria/index');
            } else {
                flash('error_message', 'Error al crear la categoría.');
                $this->view('categoria/create_edit', $data);
            }
        } else {
            redirect('categoria/index');
        }
    }

    public function edit($id) {
        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado. No tienes permisos para editar categorías.');
            redirect('categoria/index');
            exit();
        }

        $categoria = $this->categoriaModel->getCategoriaById($id);

        if (!$categoria) {
            flash('error_message', 'Categoría no encontrada.');
            redirect('categoria/index');
            exit();
        }

        $data = (array)$categoria;
        $this->view('categoria/create_edit', $data);
    }

    public function update($id) {
        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado. No tienes permisos para editar categorías.');
            redirect('categoria/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'id_categoria' => $id,
                'nombre_categoria' => trim($_POST['nombre_categoria']),
                'descripcion' => trim($_POST['descripcion'] ?? '')
            ];

            // Validaciones
            if (empty($data['nombre_categoria'])) {
                flash('error_message', 'El nombre de la categoría es obligatorio.');
                $this->view('categoria/create_edit', $data);
                exit();
            }
            // Validar nombre de categoría único (excepto para la categoría que estamos editando)
            $existingCategoriaByName = $this->categoriaModel->findCategoriaByName($data['nombre_categoria']);
            if ($existingCategoriaByName && $existingCategoriaByName->id_categoria != $id) {
                flash('error_message', 'Ya existe una categoría con este nombre.');
                $this->view('categoria/create_edit', $data);
                exit();
            }

            if ($this->categoriaModel->updateCategoria($data)) {
                flash('success_message', 'Categoría actualizada exitosamente.');
                redirect('categoria/index');
            } else {
                flash('error_message', 'Error al actualizar la categoría.');
                $this->view('categoria/create_edit', $data);
            }
        } else {
            redirect('categoria/index');
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            flash('error_message', 'Método de solicitud no permitido para eliminar.');
            redirect('categoria/index');
            exit();
        }

        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado. No tienes permisos para eliminar categorías.');
            redirect('categoria/index');
            exit();
        }

        $id_categoria = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id_categoria) {
            flash('error_message', 'ID de categoría inválido.');
            redirect('categoria/index');
            exit();
        }

        // --- Lógica de Verificación de Dependencias ANTES de Eliminar ---
        if ($this->categoriaModel->hasDependencies($id_categoria)) {
            flash('error_message', "No se puede eliminar la categoría porque tiene productos asociados.");
            redirect('categoria/index');
            exit();
        }
        // --- Fin Lógica de Verificación ---

        if ($this->categoriaModel->deleteCategoria($id_categoria)) {
            flash('success_message', 'Categoría eliminada exitosamente.');
        } else {
            flash('error_message', 'Error al eliminar la categoría.');
        }
        redirect('categoria/index');
    }

    /**
     * Helper para cargar vistas y pasar datos.
     * @param string $viewName Nombre de la vista (ej. 'categoria/index').
     * @param array $data Array asociativo de datos a pasar a la vista.
     */
    private function view($viewName, $data = []) {
        extract($data);
        require_once APPROOT . '/views/' . $viewName . '.php';
    }
}