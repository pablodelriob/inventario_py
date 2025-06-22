<?php
// app/controllers/AuthController.php

class AuthController {
    private $db;
    private $userModel;

    public function __construct() {
        $this->db = new Database();
        $this->userModel = new UserModel($this->db);
    }

    public function login() {
        require_once '../app/views/auth/login.php';
    }

    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            if (empty($username) || empty($password)) {
                $error = "Por favor, ingresa tu usuario y contraseña.";
                require_once '../app/views/auth/login.php';
                return;
            }

            $user = $this->userModel->findUserByUsername($username);

            if ($user && password_verify($password, $user->contrasena_hash)) {
                $_SESSION['user_id'] = $user->id_usuario;
                $_SESSION['username'] = $user->nombre_usuario;
                $_SESSION['role_id'] = $user->id_rol;
                $_SESSION['role_name'] = $this->userModel->getRoleNameById($user->id_rol);

                header('Location: ' . URLROOT . '/home'); // Redirigir al home (que redirigirá a product/index)
                exit();
            } else {
                $error = "Usuario o contraseña incorrectos.";
                require_once '../app/views/auth/login.php';
                return;
            }
        } else {
            header('Location: ' . URLROOT . '/auth/login');
            exit();
        }
    }

    public function register() {
        $roles = $this->userModel->getAllRoles();
        require_once '../app/views/auth/register.php';
    }

    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);
            $role_id = $_POST['role_id'];

            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                $error = "Por favor, completa todos los campos.";
                $roles = $this->userModel->getAllRoles();
                require_once '../app/views/auth/register.php';
                return;
            }

            if ($password !== $confirm_password) {
                $error = "Las contraseñas no coinciden.";
                $roles = $this->userModel->getAllRoles();
                require_once '../app/views/auth/register.php';
                return;
            }

            if (strlen($password) < 6) {
                $error = "La contraseña debe tener al menos 6 caracteres.";
                $roles = $this->userModel->getAllRoles();
                require_once '../app/views/auth/register.php';
                return;
            }

            if ($this->userModel->findUserByUsername($username)) {
                $error = "Este nombre de usuario ya está en uso.";
                $roles = $this->userModel->getAllRoles();
                require_once '../app/views/auth/register.php';
                return;
            }

            if ($this->userModel->findUserByEmail($email)) {
                $error = "Este correo electrónico ya está registrado.";
                $roles = $this->userModel->getAllRoles();
                require_once '../app/views/auth/register.php';
                return;
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            if ($this->userModel->registerUser($username, $email, $hashed_password, $role_id)) {
                $_SESSION['message'] = "Usuario registrado exitosamente. Ahora puedes iniciar sesión.";
                header('Location: ' . URLROOT . '/auth/login');
                exit();
            } else {
                $error = "Hubo un error al registrar el usuario. Intenta de nuevo.";
                $roles = $this->userModel->getAllRoles();
                require_once '../app/views/auth/register.php';
                return;
            }
        } else {
            header('Location: ' . URLROOT . '/auth/register');
            exit();
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header('Location: ' . URLROOT . '/auth/login');
        exit();
    }
}