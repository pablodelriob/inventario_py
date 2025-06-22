<?php
// app/config/config.php

// DB Params
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Tu usuario de MySQL
define('DB_PASS', '');     // Tu contraseña de MySQL
define('DB_NAME', 'kalu'); // El nombre de tu base de datos

// App Root
define('APPROOT', dirname(dirname(__FILE__)));

// URL Root (Ej: http://localhost/tu_proyecto_inventario/public)
// ¡MUY IMPORTANTE AJUSTAR ESTA LÍNEA A TU ENTORNO REAL!
define('URLROOT', 'http://localhost/tu_proyecto_inventario/public');

// Site Name
define('SITENAME', 'Sistema de Inventario El Fénix');

// Iniciar sesión (necesario al inicio de la aplicación para manejar sesiones)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}