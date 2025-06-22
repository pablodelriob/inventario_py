<?php require_once APPROOT . '/views/layouts/header.php'; ?>
<?php //require_once APPROOT . '/views/layouts/navbar.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-9">
            <h2>Gestión de Proveedores</h2>
        </div>
        <div class="col-md-3 text-right">
            <a href="<?php echo URLROOT; ?>/compras/agregarProveedor" class="btn btn-primary">
                <i class="fa fa-plus"></i> Agregar Proveedor
            </a>
        </div>
    </div>
    <hr>
    <?php flash('message'); ?>
    <?php flash('error'); ?>

    <?php if (empty($proveedores)): ?>
        <p>No hay proveedores registrados aún.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>RUC</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proveedores as $proveedor): ?>
                        <tr>
                            <td><?php echo $proveedor->id_proveedor; ?></td>
                            <td><?php echo htmlspecialchars($proveedor->nombre_proveedor); ?></td>
                            <td><?php echo htmlspecialchars($proveedor->contacto_persona); ?></td>
                            <td><?php echo htmlspecialchars($proveedor->contacto_email); ?></td>
                            <td><?php echo htmlspecialchars($proveedor->contacto_telefono); ?></td>
                            <td><?php echo htmlspecialchars($proveedor->ruc); ?></td>
                            <td class="d-flex">
                                <a href="<?php echo URLROOT; ?>/compras/editarProveedor/<?php echo $proveedor->id_proveedor; ?>" class="btn btn-warning btn-sm mr-2">
                                    Editar
                                </a>
                                <form action="<?php echo URLROOT; ?>/compras/eliminarProveedor/<?php echo $proveedor->id_proveedor; ?>" method="post" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este proveedor? Esta acción es irreversible.');">
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>