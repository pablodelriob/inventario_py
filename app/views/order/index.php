<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Gestión de Pedidos</h2>

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
        <a href="<?= URLROOT ?>/order/create" class="btn btn-primary mb-3">Crear Nuevo Pedido</a>
    <?php endif; ?>

    <?php if (!empty($pedidos)): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Cliente</th>
                        <th>Fecha Pedido</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Creado Por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?= htmlspecialchars($pedido->id_pedido) ?></td>
                            <td><?= htmlspecialchars($pedido->nombre_cliente . ' ' . $pedido->apellido_cliente) ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($pedido->fecha_pedido))) ?></td>
                            <td><?= htmlspecialchars($pedido->estado) ?></td>
                            <td><?= htmlspecialchars(number_format($pedido->total_pedido, 2, ',', '.')) ?></td>
                            <td><?= htmlspecialchars($pedido->nombre_usuario_creacion) ?></td>
                            <td>
                                <a href="<?= URLROOT ?>/order/show/<?= htmlspecialchars($pedido->id_pedido) ?>" class="btn btn-info btn-sm">Ver Detalles</a>
                                <?php if (isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'Administrador'): ?>
                                    <form action="<?= URLROOT ?>/order/delete/<?= htmlspecialchars($pedido->id_pedido) ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este pedido? Esto no revertirá el stock.');">
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
        <div class="alert alert-info">No hay pedidos registrados.</div>
    <?php endif; ?>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>