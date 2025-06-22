<?php
// app/controllers/HomeController.php

class HomeController {
    public function index() {
        if (isset($_SESSION['user_id'])) {
            // Si el usuario está logueado, redirigir a la lista de productos o un dashboard
            header('Location: ' . URLROOT . '/product/index'); // Usar URLROOT
            exit();
        } else {
            // Si no está logueado, mostrar la página de inicio simple o redirigir al login
            header('Location: ' . URLROOT . '/auth/login'); // Usar URLROOT
            exit();
        }
    }
}