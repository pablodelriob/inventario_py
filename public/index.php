<?php
// public/index.php
// Load Config
require_once '../app/config/config.php'; // Carga APPROOT, URLROOT, etc.

// Iniciar sesión (para gestionar usuarios)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cargar la clase de base de datos directamente, o usar el autoloader si no la instancian directamente al inicio
// require_once '../app/core/Database.php'; // Ya no es necesario si el autoloader la encuentra
require_once '../app/helpers/SessionHelper.php'; // <--- ¡AÑADE ESTA LÍNEA!
require_once '../app/helpers/UrlHelper.php'; // <--- ¡AÑADE ESTA LÍNEA!
// --- Autocargador simple de clases (muy básico, para proyectos grandes se usaría Composer) ---
spl_autoload_register(function($className){
    // Busca en las carpetas de modelos, controladores y core
    if (file_exists('../app/models/' . $className . '.php')) {
        require_once '../app/models/' . $className . '.php';
    } elseif (file_exists('../app/controllers/' . $className . '.php')) {
        require_once '../app/controllers/' . $className . '.php';
    } elseif (file_exists('../app/core/' . $className . '.php')) { // <-- AGREGADO para cargar clases de 'core'
        require_once '../app/core/' . $className . '.php';
    }
});
// ------------------------------------------------------------------------------------------

// --- Enrutador muy básico ---
// Obtener la URL y limpiarla
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);
$url = explode('/', $url);

// Determinar el controlador y el método
$controllerName = !empty($url[0]) ? ucwords($url[0]) . 'Controller' : 'HomeController'; // 'HomeController' como predeterminado
$methodName = !empty($url[1]) ? $url[1] : 'index'; // 'index' como método predeterminado

// Eliminar los dos primeros elementos (controlador y método) de la URL para obtener los parámetros
unset($url[0]);
unset($url[1]);
$params = !empty($url) ? array_values($url) : [];

// Crear una instancia del controlador y llamar al método
// Usamos APPROOT para la ruta del controlador, para que sea más robusta
$controllerPath = APPROOT . '/controllers/' . $controllerName . '.php'; // <-- Uso de APPROOT aquí
if (file_exists($controllerPath)) {
    require_once $controllerPath;
    if (class_exists($controllerName)) {
        $controller = new $controllerName();

        if (method_exists($controller, $methodName)) {
            call_user_func_array([$controller, $methodName], $params);
        } else {
            // Método no encontrado
            echo "Error 404: Método '{$methodName}' no encontrado en '{$controllerName}'";
            // Aquí puedes cargar una vista de error 404
        }
    } else {
        // Clase de controlador no encontrada (aunque el archivo exista)
        echo "Error 404: Clase de controlador '{$controllerName}' no encontrada";
    }
} else {
    // Controlador no encontrado
    echo "Error 404: Controlador '{$controllerName}' no encontrado";
    // Aquí puedes cargar una vista de error 404
}
// ----------------------------
?>