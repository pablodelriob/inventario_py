<?php
// app/core/App.php

class App {
    protected $currentController = 'Home'; // Controlador por defecto
    protected $currentMethod = 'index';    // Método por defecto
    protected $params = [];                // Parámetros

    public function __construct() {
        $url = $this->getUrl(); // Obtener la URL

        // Cargar Controlador
        if (file_exists('../app/controllers/' . ucwords($url[0]) . 'Controller.php')) {
            $this->currentController = ucwords($url[0]) . 'Controller';
            unset($url[0]);
        } else {
            // Si el controlador no existe, manejar como 404 o redirigir
            // Podrías tener un ErrorController o simplemente mostrar un mensaje
            http_response_code(404);
            echo "Error 404: Controlador '" . ucwords($url[0]) . "Controller' no encontrado.";
            exit();
        }

        require_once '../app/controllers/' . $this->currentController . '.php';
        $this->currentController = new $this->currentController;

        // Cargar Método
        if (isset($url[1])) {
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            } else {
                 // Si el método no existe, manejar como 404
                http_response_code(404);
                echo "Error 404: Método '" . $this->currentMethod . "' no encontrado en '" . get_class($this->currentController) . "'.";
                exit();
            }
        }

        // Obtener Parámetros
        $this->params = $url ? array_values($url) : [];

        // Llamar al controlador con su método y parámetros
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/'); // Eliminar '/' final
            $url = filter_var($url, FILTER_SANITIZE_URL); // Limpiar URL
            $url = explode('/', $url); // Separar en array por '/'
            return $url;
        }
        return ['home']; // URL por defecto si no se especifica
    }
}