<?php
// app/controllers/ProductController.php

class ProductController {
    private $db;
    private $productModel;
    private $proveedorModel; // <--- AÑADIDO: Para obtener lista de proveedores
    private $categoriaModel; // <--- AÑADIDO: Para obtener lista de categorías

    public function __construct() {
        $this->db = new Database();
        $this->productModel = new ProductModel($this->db);
        // <--- IMPORTANTE: Instanciar Modelos de Proveedor y Categoría
        // Necesitarás crear estos archivos en app/models/
        $this->proveedorModel = new ProveedorModel($this->db);
        $this->categoriaModel = new CategoriaModel($this->db);


        // Protección básica: solo usuarios logueados pueden acceder
        if (!isset($_SESSION['user_id'])) {
            flash('error_message', 'Debe iniciar sesión para acceder a esta página.');
            redirect('auth/login');
            exit();
        }
    }

    public function index() {
        $products = $this->productModel->getAllProducts();
        $data = ['products' => $products]; // Empaqueta los datos para la vista
        $this->view('product/index', $data); // Usa el método view
    }

    public function create() {
        // Solo Administrador y Gerente de Almacen pueden crear productos
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            flash('error_message', 'Acceso denegado. No tienes permisos para crear productos.');
            redirect('product/index');
            exit();
        }

        // Obtener listas de proveedores y categorías para los selectores en el formulario
        $proveedores = $this->proveedorModel->getAllProveedores(); // <--- AÑADIDO
        $categorias = $this->categoriaModel->getAllCategorias();   // <--- AÑADIDO

        $data = [
            'sku' => '',
            'codigo_barras' => '',
            'nombre_comercial' => '',
            'descripcion' => '',
            'costo' => '',
            'cantidad_minima' => '',
            'id_proveedor' => '',    // <--- AÑADIDO: Para pre-seleccionar en el formulario si hay errores
            'id_categoria' => '',    // <--- AÑADIDO: Para pre-seleccionar en el formulario si hay errores
            'porcentaje_precio_publico' => '',
            'precio_publico' => '',
            'porcentaje_precio_mayorista' => '',
            'precio_mayorista' => '',
            'porcentaje_precio_distribuidor' => '',
            'precio_distribuidor' => '',
            'precio_venta' => '',
            'ruta_imagen' => '',
            'proveedores' => $proveedores, // <--- AÑADIDO para la vista
            'categorias' => $categorias   // <--- AÑADIDO para la vista
        ];

        $this->view('product/create_edit', $data); // Usa el método view
    }

    public function store() {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            flash('error_message', 'Acceso denegado.');
            redirect('product/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'sku' => trim($_POST['sku']),
                'codigo_barras' => trim($_POST['codigo_barras'] ?? ''),
                'nombre_comercial' => trim($_POST['nombre_comercial']),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'costo' => filter_var($_POST['costo'], FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'cantidad_minima' => filter_var($_POST['cantidad_minima'], FILTER_VALIDATE_INT),
                'id_usuario_ingreso' => $_SESSION['user_id'],
                'id_proveedor' => filter_var($_POST['id_proveedor'], FILTER_VALIDATE_INT), // <--- AÑADIDO
                'id_categoria' => filter_var($_POST['id_categoria'], FILTER_VALIDATE_INT), // <--- AÑADIDO
                'porcentaje_precio_publico' => filter_var($_POST['porcentaje_precio_publico'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'precio_publico' => filter_var($_POST['precio_publico'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'porcentaje_precio_mayorista' => filter_var($_POST['porcentaje_precio_mayorista'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'precio_mayorista' => filter_var($_POST['precio_mayorista'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'porcentaje_precio_distribuidor' => filter_var($_POST['porcentaje_precio_distribuidor'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'precio_distribuidor' => filter_var($_POST['precio_distribuidor'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'precio_venta' => filter_var($_POST['precio_venta'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'ruta_imagen' => null
            ];

            // Obtener listas de proveedores y categorías de nuevo para la vista en caso de error
            $data['proveedores'] = $this->proveedorModel->getAllProveedores(); // <--- AÑADIDO
            $data['categorias'] = $this->categoriaModel->getAllCategorias();   // <--- AÑADIDO

            // --- Validaciones ---
            if (empty($data['nombre_comercial']) || empty($data['sku'])) {
                flash('error_message', "El nombre comercial y el SKU son obligatorios.");
                $this->view('product/create_edit', $data);
                exit();
            }
            if ($data['costo'] === false || $data['costo'] < 0) {
                flash('error_message', "El costo debe ser un número válido mayor o igual a cero.");
                $this->view('product/create_edit', $data);
                exit();
            }
            if ($data['cantidad_minima'] === false || $data['cantidad_minima'] < 0) {
                flash('error_message', "La cantidad mínima debe ser un número entero válido mayor o igual a cero.");
                $this->view('product/create_edit', $data);
                exit();
            }
            if ($data['precio_venta'] === false || $data['precio_venta'] < 0) {
                flash('error_message', "El precio de venta debe ser un número válido mayor o igual a cero.");
                $this->view('product/create_edit', $data);
                exit();
            }
            // <--- IMPORTANTE: Validación para id_proveedor e id_categoria
            if ($data['id_proveedor'] === false || empty($data['id_proveedor'])) {
                flash('error_message', "Debe seleccionar un proveedor válido.");
                $this->view('product/create_edit', $data);
                exit();
            }
            if ($data['id_categoria'] === false || empty($data['id_categoria'])) {
                flash('error_message', "Debe seleccionar una categoría válida.");
                $this->view('product/create_edit', $data);
                exit();
            }

            // Validar SKU único
            if ($this->productModel->findProductBySku($data['sku'])) {
                flash('error_message', "El SKU ya está registrado para otro producto.");
                $this->view('product/create_edit', $data);
                exit();
            }

            // --- Manejo de la subida de imagen ---
            if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] == UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/products/';
                $uploadPath = APPROOT . '/../public/' . $uploadDir;
                $webPath = URLROOT . '/' . $uploadDir;

                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                $fileName = uniqid() . '_' . basename($_FILES['imagen_principal']['name']);
                $targetFilePath = $uploadPath . $fileName;

                if (move_uploaded_file($_FILES['imagen_principal']['tmp_name'], $targetFilePath)) {
                    $data['ruta_imagen'] = $webPath . $fileName;
                } else {
                    flash('error_message', "Error al subir la imagen.");
                    $this->view('product/create_edit', $data);
                    exit();
                }
            }


            if ($this->productModel->createProduct($data)) {
                flash('success_message', "Producto creado exitosamente.");
                redirect('product/index');
            } else {
                flash('error_message', "Error al crear el producto.");
                $this->view('product/create_edit', $data);
            }
        } else {
            redirect('product/index');
        }
    }

    public function edit($id) {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            flash('error_message', 'Acceso denegado. No tienes permisos para editar productos.');
            redirect('product/index');
            exit();
        }

        $product = $this->productModel->getProductById($id);

        if (!$product) {
            flash('error_message', "Producto no encontrado.");
            redirect('product/index');
            exit();
        }

        // Obtener listas de proveedores y categorías para los selectores en el formulario
        $proveedores = $this->proveedorModel->getAllProveedores(); // <--- AÑADIDO
        $categorias = $this->categoriaModel->getAllCategorias();   // <--- AÑADIDO

        // Convertir el objeto producto a array para pasarlo a la vista,
        // y añadir las listas de proveedores/categorias
        $data = (array)$product;
        $data['proveedores'] = $proveedores;
        $data['categorias'] = $categorias;

        $this->view('product/create_edit', $data);
    }

    public function update($id) {
        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            flash('error_message', 'Acceso denegado. No tienes permisos para editar productos.');
            redirect('product/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'id_producto' => $id,
                'sku' => trim($_POST['sku']),
                'codigo_barras' => trim($_POST['codigo_barras'] ?? ''),
                'nombre_comercial' => trim($_POST['nombre_comercial']),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'costo' => filter_var($_POST['costo'], FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'cantidad_minima' => filter_var($_POST['cantidad_minima'], FILTER_VALIDATE_INT),
                'id_usuario_ingreso' => $_SESSION['user_id'], // Mantener, o usar id_usuario_modificacion si lo tienes
                'id_proveedor' => filter_var($_POST['id_proveedor'], FILTER_VALIDATE_INT), // <--- AÑADIDO
                'id_categoria' => filter_var($_POST['id_categoria'], FILTER_VALIDATE_INT), // <--- AÑADIDO
                'porcentaje_precio_publico' => filter_var($_POST['porcentaje_precio_publico'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'precio_publico' => filter_var($_POST['precio_publico'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'porcentaje_precio_mayorista' => filter_var($_POST['porcentaje_precio_mayorista'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'precio_mayorista' => filter_var($_POST['precio_mayorista'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'porcentaje_precio_distribuidor' => filter_var($_POST['porcentaje_precio_distribuidor'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'precio_distribuidor' => filter_var($_POST['precio_distribuidor'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'precio_venta' => filter_var($_POST['precio_venta'] ?? null, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'ruta_imagen' => trim($_POST['current_image_path'] ?? null) // Imagen actual si no se sube una nueva
            ];

            // Obtener el producto original para repoblar la vista en caso de error
            $product = $this->productModel->getProductById($id);
            // Obtener listas de proveedores y categorías de nuevo para la vista en caso de error
            $data['proveedores'] = $this->proveedorModel->getAllProveedores(); // <--- AÑADIDO
            $data['categorias'] = $this->categoriaModel->getAllCategorias();   // <--- AÑADIDO


            // --- Validaciones ---
            if (empty($data['nombre_comercial']) || empty($data['sku'])) {
                flash('error_message', "El nombre comercial y el SKU son obligatorios.");
                $this->view('product/create_edit', $data); // Usa $data que ya tiene los datos del POST y las listas
                exit();
            }
            if ($data['costo'] === false || $data['costo'] < 0) {
                flash('error_message', "El costo debe ser un número válido mayor o igual a cero.");
                $this->view('product/create_edit', $data);
                exit();
            }
            if ($data['cantidad_minima'] === false || $data['cantidad_minima'] < 0) {
                flash('error_message', "La cantidad mínima debe ser un número entero válido mayor o igual a cero.");
                $this->view('product/create_edit', $data);
                exit();
            }
            if ($data['precio_venta'] === false || $data['precio_venta'] < 0) {
                flash('error_message', "El precio de venta debe ser un número válido mayor o igual a cero.");
                $this->view('product/create_edit', $data);
                exit();
            }
            // <--- IMPORTANTE: Validación para id_proveedor e id_categoria
            if ($data['id_proveedor'] === false || empty($data['id_proveedor'])) {
                flash('error_message', "Debe seleccionar un proveedor válido.");
                $this->view('product/create_edit', $data);
                exit();
            }
            if ($data['id_categoria'] === false || empty($data['id_categoria'])) {
                flash('error_message', "Debe seleccionar una categoría válida.");
                $this->view('product/create_edit', $data);
                exit();
            }

            // Validar SKU único (excepto para el producto que estamos editando)
            $existingProductWithSku = $this->productModel->findProductBySku($data['sku']);
            if ($existingProductWithSku && $existingProductWithSku->id_producto != $id) {
                flash('error_message', "El SKU ya está registrado para otro producto.");
                $this->view('product/create_edit', $data);
                exit();
            }

            // --- Manejo de la subida de nueva imagen ---
            if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] == UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/products/';
                $uploadPath = APPROOT . '/../public/' . $uploadDir;
                $webPath = URLROOT . '/' . $uploadDir;

                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                $fileName = uniqid() . '_' . basename($_FILES['imagen_principal']['name']);
                $targetFilePath = $uploadPath . $fileName;

                if (move_uploaded_file($_FILES['imagen_principal']['tmp_name'], $targetFilePath)) {
                    $data['ruta_imagen'] = $webPath . $fileName;
                    // Opcional: Eliminar la imagen antigua si existe y es diferente a la nueva
                    // Asegúrate de que $product contenga la ruta antigua si no viene en el POST
                    if ($product && !empty($product->ruta_imagen) && $data['ruta_imagen'] !== $product->ruta_imagen ) {
                        $oldImagePath = str_replace(URLROOT . '/', APPROOT . '/../public/', $product->ruta_imagen);
                        if (file_exists($oldImagePath)) {
                             unlink($oldImagePath);
                        }
                    }
                } else {
                    flash('error_message', "Error al subir la nueva imagen.");
                    $this->view('product/create_edit', $data);
                    exit();
                }
            }


            if ($this->productModel->updateProduct($data)) {
                flash('success_message', "Producto actualizado exitosamente.");
                redirect('product/index');
            } else {
                flash('error_message', "Error al actualizar el producto.");
                $this->view('product/create_edit', $data);
            }
        } else {
            redirect('product/index');
        }
    }

    public function delete($id) {
        // Asegurarse de que la solicitud sea POST, como se recomienda para eliminaciones
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            flash('error_message', 'Método de solicitud no permitido para eliminar.');
            redirect('product/index');
            exit();
        }

        if ($_SESSION['role_name'] !== 'Administrador' && $_SESSION['role_name'] !== 'Gerente_Almacen') {
            flash('error_message', 'Acceso denegado. No tienes permisos para eliminar productos.');
            redirect('product/index');
            exit();
        }

        $id_producto = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id_producto) {
            flash('error_message', 'ID de producto inválido.');
            redirect('product/index');
            exit();
        }

        // --- Lógica de Verificación de Dependencias ANTES de Eliminar ---
        // Usa el nuevo método hasDependencies que verifica todo
        if ($this->productModel->hasDependencies($id_producto)) {
            flash('error_message', "No se puede eliminar el producto porque tiene stock, pedidos o registros de compra asociados.");
            redirect('product/index');
            exit();
        }
        // --- Fin Lógica de Verificación ---

        // Obtener la ruta de la imagen antes de eliminar el producto de la DB
        $productToDelete = $this->productModel->getProductById($id_producto);
        $imagePathToDelete = null;
        if ($productToDelete && !empty($productToDelete->ruta_imagen)) {
            $imagePathToDelete = str_replace(URLROOT . '/', APPROOT . '/../public/', $productToDelete->ruta_imagen);
        }

        if ($this->productModel->deleteProduct($id_producto)) {
            // Eliminar la imagen física si existe
            if ($imagePathToDelete && file_exists($imagePathToDelete)) {
                unlink($imagePathToDelete);
            }
            flash('success_message', "Producto eliminado exitosamente.");
        } else {
            flash('error_message', "Error al eliminar el producto. Puede que existan dependencias inesperadas.");
        }
        redirect('product/index');
    }

    /**
     * Helper para cargar vistas y pasar datos.
     * Asume que APPROOT está definido y apunta a la raíz de la aplicación (e.g., /app).
     * @param string $viewName Nombre de la vista (ej. 'product/index').
     * @param array $data Array asociativo de datos a pasar a la vista.
     */
    private function view($viewName, $data = []) {
        extract($data); // Convierte las claves del array en variables
        require_once APPROOT . '/views/' . $viewName . '.php';
    }
}