<?php
// app/controllers/ClientController.php

class ClientController {
    private $db;
    private $clientModel;

    public function __construct() {
        $this->db = new Database();
        $this->clientModel = new ClientModel($this->db);

        // Protección básica: solo usuarios logueados pueden acceder a clientes
        if (!isset($_SESSION['user_id'])) {
            flash('error_message', 'Debe iniciar sesión para acceder a esta página.');
            redirect('auth/login');
            exit();
        }
    }

    // Mostrar todos los clientes
    public function index() {
        // En esta etapa, permitimos a todos los logueados ver clientes.
        // Después refinaremos los roles.
        $clientes = $this->clientModel->getAllClients();
        $data = ['clientes' => $clientes]; // Empaqueta los datos en un array para la vista
        $this->view('client/index', $data); // Asumiendo que tienes un método 'view'
    }

    // Mostrar el formulario para crear un nuevo cliente
    public function create() {
        // Solo Administrador y Vendedor pueden crear clientes por ahora
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Vendedor') {
            flash('error_message', 'Acceso denegado. No tienes permisos para crear clientes.');
            redirect('client/index');
            exit();
        }
        $data = [ // Inicializa los campos para evitar errores de 'Undefined variable' en la vista
            'nombre' => '',
            'apellido' => '',
            'email' => '',
            'telefono' => '',
            'direccion' => ''
        ];
        $this->view('client/create', $data);
    }

    // Procesar el envío del formulario para crear un cliente
    public function store() {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Vendedor') {
            flash('error_message', 'Acceso denegado.');
            redirect('client/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS); // Usar FILTER_SANITIZE_FULL_SPECIAL_CHARS

            $data = [
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'email' => trim($_POST['email']),
                'telefono' => trim($_POST['telefono']),
                'direccion' => trim($_POST['direccion'])
            ];

            // Validación simple
            if (empty($data['nombre']) || empty($data['apellido'])) {
                flash('error_message', "Nombre y Apellido son obligatorios.");
                $this->view('client/create', $data); // Pasa datos para repoblar el formulario
                exit();
            }

            if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                flash('error_message', "Formato de email inválido.");
                $this->view('client/create', $data);
                exit();
            }

            // Aquí el findClientByEmail solo verifica existencia, no devuelve el objeto cliente.
            // Para la validación de email único en creación, esto es correcto.
            if (!empty($data['email']) && $this->clientModel->findClientByEmail($data['email'])) {
                flash('error_message', "El email ya está registrado para otro cliente.");
                $this->view('client/create', $data);
                exit();
            }

            if ($this->clientModel->createClient($data)) {
                flash('success_message', "Cliente añadido exitosamente.");
                redirect('client/index');
            } else {
                flash('error_message', "Error al añadir cliente.");
                $this->view('client/create', $data);
            }
        } else {
            redirect('client/index');
        }
    }

    // Mostrar el formulario para editar un cliente
    public function edit($id) {
        // Solo Administrador y Vendedor pueden editar clientes por ahora
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Vendedor') {
            flash('error_message', 'Acceso denegado. No tienes permisos para editar clientes.');
            redirect('client/index');
            exit();
        }

        $client = $this->clientModel->getClientById($id);

        if (!$client) {
            flash('error_message', "Cliente no encontrado.");
            redirect('client/index');
            exit();
        }
        $this->view('client/edit', (array)$client); // Pasar el objeto cliente como array
    }

    // Procesar el envío del formulario para actualizar un cliente
    public function update($id) {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Vendedor') {
            flash('error_message', 'Acceso denegado.');
            redirect('client/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'id_cliente' => $id,
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'email' => trim($_POST['email']),
                'telefono' => trim($_POST['telefono']),
                'direccion' => trim($_POST['direccion'])
            ];

            // Validación simple
            if (empty($data['nombre']) || empty($data['apellido'])) {
                flash('error_message', "Nombre y Apellido son obligatorios.");
                $client = $this->clientModel->getClientById($id); // Recargar para la vista
                $this->view('client/edit', (array)$client);
                exit();
            }

            // Validación: Si el email es único, validar que no se duplique con otro cliente (excepto consigo mismo)
            if (!empty($data['email'])) {
                // Modificar esta lógica para usar el método findClientByEmail
                // y comparar el ID del cliente encontrado con el ID actual.
                $this->db->query('SELECT id_cliente FROM clientes WHERE email = :email'); // Se puede mover esto a un método en el modelo
                $this->db->bind(':email', $data['email']);
                $clientWithSameEmail = $this->db->single();

                if ($clientWithSameEmail && $clientWithSameEmail->id_cliente != $id) {
                    flash('error_message', "El email ya está registrado para otro cliente.");
                    $client = $this->clientModel->getClientById($id);
                    $this->view('client/edit', (array)$client);
                    exit();
                }
            }


            if ($this->clientModel->updateClient($data)) {
                flash('success_message', "Cliente actualizado exitosamente.");
                redirect('client/index');
            } else {
                flash('error_message', "Error al actualizar cliente.");
                $client = $this->clientModel->getClientById($id);
                $this->view('client/edit', (array)$client);
            }
        } else {
            redirect('client/index');
        }
    }

    // Eliminar un cliente
    public function delete($id) {
        // Solo Administrador puede eliminar clientes por ahora
        if ($_SESSION['role_name'] !== 'Administrador') {
            flash('error_message', 'Acceso denegado. No tienes permisos para eliminar clientes.');
            redirect('client/index');
            exit();
        }

        // Se recomienda que la eliminación siempre sea por POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_cliente = filter_var($id, FILTER_VALIDATE_INT);

            if (!$id_cliente) {
                flash('error_message', 'ID de cliente inválido.');
                redirect('client/index');
                exit();
            }

            // --- Lógica de Verificación de Pedidos ANTES de Eliminar ---
            // Usa el nuevo método en el modelo para contar los pedidos del cliente
            $totalOrders = $this->clientModel->countOrdersByClient($id_cliente);

            if ($totalOrders > 0) {
                flash('error_message', "No se puede eliminar el cliente porque tiene {$totalOrders} pedidos asociados. Por favor, elimine o reasigne sus pedidos primero.");
                redirect('client/index');
                exit();
            }
            // --- Fin Lógica de Verificación ---


            if ($this->clientModel->deleteClient($id_cliente)) {
                flash('success_message', "Cliente eliminado exitosamente.");
                redirect('client/index');
            } else {
                flash('error_message', "Error al eliminar cliente. Puede que existan dependencias inesperadas.");
                redirect('client/index');
            }
        } else {
            flash('error_message', 'Método de solicitud no permitido.');
            redirect('client/index');
        }
    }

    // Método 'view' para cargar las vistas y pasar datos
    private function view($viewName, $data = []) {
        // Extraer los datos para que estén disponibles como variables en la vista
        extract($data);
        require_once APPROOT . '/views/' . $viewName . '.php';
    }
}