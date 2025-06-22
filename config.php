<?php
// app/config/config.php

// DB Params
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Tu usuario de MySQL
define('DB_PASS', '');         // Tu contraseña de MySQL
define('DB_NAME', 'kalu');     // El nombre de tu base de datos

// App Root
// Define APPROOT para que apunte a la carpeta 'app'
define('APPROOT', dirname(dirname(__FILE__))); // Esto hace que APPROOT sea C:\xampp\htdocs\tu_proyecto_inventario\app

// URL Root (Ej: http://localhost/tu_proyecto_inventario/public)
// ¡MUY IMPORTANTE AJUSTAR ESTA LÍNEA A TU ENTORNO REAL!
define('URLROOT', 'http://localhost/tu_proyecto_inventario/public'); // <-- AJUSTA ESTO A TU RUTA REAL

// Site Name
define('SITENAME', 'Sistema de Inventario El Fénix');

// Iniciar sesión (necesario al inicio de la aplicación para manejar sesiones)
// Esta línea es mejor mantenerla en public/index.php, donde se inicia la sesión para toda la aplicación.
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }