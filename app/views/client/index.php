<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Gestión de Clientes</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success" role="alert">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= $_SESSION['error'] ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['role_name']) && ($_SESSION['role_name'] === 'Administrador' || $_SESSION['role_name'] === 'Vendedor')): ?>
        <a href="<?= URLROOT ?>/client/create" class="btn btn-primary mb-3">Añadir Nuevo Cliente</a>
    <?php endif; ?>

    <?php if (!empty($clientes)): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?= htmlspecialchars($cliente->id_cliente) ?></td>
                            <td><?= htmlspecialchars($cliente->nombre) ?></td>
                            <td><?= htmlspecialchars($cliente->apellido) ?></td>
                            <td><?= htmlspecialchars($cliente->email ?? '-') ?></td>
                            <td><?= htmlspecialchars($cliente->telefono ?? '-') ?></td>
                            <td><?= htmlspecialchars($cliente->direccion ?? '-') ?></td>
                            <td>
                                <?php if (isset($_SESSION['role_name']) && ($_SESSION['role_name'] === 'Administrador' || $_SESSION['role_name'] === 'Vendedor')): ?>
                                    <a href="<?= URLROOT ?>/client/edit/<?= htmlspecialchars($cliente->id_cliente) ?>" class="btn btn-warning btn-sm">Editar</a>
                                <?php endif; ?>
                                <?php if (isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'Administrador'): ?>
                                    <form action="<?= URLROOT ?>/client/delete/<?= htmlspecialchars($cliente->id_cliente) ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este cliente?');">
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No hay clientes registrados.</div>
    <?php endif; ?>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>