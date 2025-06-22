<?php

/**
 * Función para redirigir a una URL específica.
 *
 * @param string $page La página a la que redirigir (ej. 'users/index').
 */
function redirect($page){
    header('location: ' . URLROOT . '/' . $page);
    exit(); // Es crucial llamar a exit() después de un header() redirect
}