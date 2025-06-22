<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Detalles del Pedido #<?= htmlspecialchars($pedido->id_pedido) ?></h2>

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

    <div class="card mb-4">
        <div class="card-header">
            Información del Pedido
        </div>
        <div class="card-body">
            <p><strong>Cliente:</strong> <?= htmlspecialchars($pedido->nombre_cliente . ' ' . $pedido->apellido_cliente) ?></p>
            <p><strong>Email Cliente:</strong> <?= htmlspecialchars($pedido->email_cliente ?? 'N/A') ?></p>
            <p><strong>Teléfono Cliente:</strong> <?= htmlspecialchars($pedido->telefono_cliente ?? 'N/A') ?></p>
            <p><strong>Dirección Cliente:</strong> <?= htmlspecialchars($pedido->direccion_cliente ?? 'N/A') ?></p>
            <p><strong>Fecha del Pedido:</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($pedido->fecha_pedido))) ?></p>
            <p><strong>Estado:</strong> <span class="badge bg-info"><?= htmlspecialchars($pedido->estado) ?></span></p>
            <p><strong>Total del Pedido:</strong> <?= htmlspecialchars(number_format($pedido->total_pedido, 2, ',', '.')) ?></p>
            <p><strong>Creado Por:</strong> <?= htmlspecialchars($pedido->nombre_usuario_creacion) ?></p>
            <p><strong>Observaciones:</strong> <?= htmlspecialchars($pedido->observaciones ?? 'Ninguna') ?></p>

            <?php if (isset($_SESSION['role_name']) && ($_SESSION['role_name'] === 'Administrador' || $_SESSION['role_name'] === 'Gerente_Almacen')): ?>
                <hr>
                <h5>Cambiar Estado del Pedido</h5>
                <form action="<?= URLROOT ?>/order/changeStatus/<?= htmlspecialchars($pedido->id_pedido) ?>" method="POST" class="d-flex">
                    <select name="new_status" class="form-select me-2" style="max-width: 200px;">
                        <option value="Pendiente" <?= ($pedido->estado == 'Pendiente') ? 'selected' : '' ?>>Pendiente</option>
                        <option value="Procesando" <?= ($pedido->estado == 'Procesando') ? 'selected' : '' ?>>Procesando</option>
                        <option value="Completado" <?= ($pedido->estado == 'Completado') ? 'selected' : '' ?>>Completado</option>
                        <option value="Cancelado" <?= ($pedido->estado == 'Cancelado') ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-primary">Actualizar Estado</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Productos en el Pedido
        </div>
        <div class="card-body">
            <?php if (!empty($detalles)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>SKU</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $detalle): ?>
                                <tr>
                                    <td><?= htmlspecialchars($detalle->producto_nombre) ?></td>
                                    <td><?= htmlspecialchars($detalle->sku) ?></td>
                                    <td><?= htmlspecialchars($detalle->cantidad) ?></td>
                                    <td><?= htmlspecialchars(number_format($detalle->precio_unitario, 2, ',', '.')) ?></td>
                                    <td><?= htmlspecialchars(number_format($detalle->subtotal, 2, ',', '.')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No hay productos en este pedido.</div>
            <?php endif; ?>
        </div>
    </div>

    <a href="<?= URLROOT ?>/order/index" class="btn btn-secondary">Volver a Pedidos</a>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>