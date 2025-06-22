<?php

// Iniciar la sesión si aún no está iniciada (esto debería hacerse al principio de tu aplicación,
// por ejemplo, en bootstrap.php, pero lo incluyo aquí como recordatorio).
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Función para establecer y mostrar mensajes flash en la sesión.
 *
 * @param string $name    Nombre del mensaje (ej. 'success_message', 'error_message').
 * @param string $message Contenido del mensaje. Si está vacío, la función intentará mostrar un mensaje existente.
 * @param string $class   Clases CSS para el div del mensaje (ej. 'alert alert-success', 'alert alert-danger').
 */
function flash($name = '', $message = '', $class = 'alert alert-success'){
    // SETEAR MENSAJE FLASH
    if (!empty($name) && !empty($message)) {
        // Si ya existe un mensaje con este nombre, lo eliminamos primero para no tener duplicados o conflictos.
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
        if (isset($_SESSION[$name . '_class'])) {
            unset($_SESSION[$name . '_class']);
        }

        // Establecer el nuevo mensaje y su clase
        $_SESSION[$name] = $message;
        $_SESSION[$name . '_class'] = $class;

    // MOSTRAR MENSAJE FLASH
    } elseif (!empty($name) && empty($message)) {
        // Si el nombre del mensaje existe en la sesión
        if (isset($_SESSION[$name])) {
            $class_to_display = isset($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
            echo '<div class="'.$class_to_display.'" id="msg-flash">'.$_SESSION[$name].'</div>';
            
            // Eliminar el mensaje de la sesión después de mostrarlo
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}