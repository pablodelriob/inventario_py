<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITENAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= URLROOT ?>/home">S.I. El Fénix</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= URLROOT ?>/product/index">Productos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= URLROOT ?>/deposito/index">Depósitos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= URLROOT ?>/inventario/index">Inventario</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= URLROOT ?>/inventario/sellProduct">Registrar Venta/Salida</a>
                        </li>
                        
                        <?php if (isset($_SESSION['role_name']) && ($_SESSION['role_name'] === 'Administrador' || $_SESSION['role_name'] === 'Gerente_Almacen')): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownCompras" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Compras
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdownCompras">
                                    <li><a class="dropdown-item" href="<?= URLROOT ?>/compras">Órdenes de Compra</a></li>
                                    <li><a class="dropdown-item" href="<?= URLROOT ?>/compras/proveedores">Proveedores</a></li>
                                    </ul>
                            </li>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['role_name']) && ($_SESSION['role_name'] === 'Administrador' || $_SESSION['role_name'] === 'Gerente_Almacen')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= URLROOT ?>/inventario/reports">Reportes</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= URLROOT ?>/client/index">Clientes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= URLROOT ?>/order/index">Pedidos</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?> (<?= htmlspecialchars($_SESSION['role_name']) ?>)
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="#">Mi Perfil (próximamente)</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= URLROOT ?>/auth/logout">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= URLROOT ?>/auth/login">Iniciar Sesión</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= URLROOT ?>/auth/register">Registrarse</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main>