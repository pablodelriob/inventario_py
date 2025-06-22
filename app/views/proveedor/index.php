<?php require APPROOT . '/views/layouts/header.php'; ?>
<?php flash('success_message'); ?>
<?php flash('error_message'); ?>

<div class="row mb-3">
    <div class="col-md-6">
        <h1><?php echo isset($proveedores) ? 'Proveedores' : 'Categorías'; ?></h1>
    </div>
    <div class="col-md-6 text-right">
        <a href="<?= URLROOT; ?>/<?php echo isset($proveedores) ? 'proveedor/create' : 'categoria/create'; ?>" class="btn btn-primary">
            <i class="fa fa-plus"></i> Añadir <?php echo isset($proveedores) ? 'Proveedor' : 'Categoría'; ?>
        </a>
    </div>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <?php if (isset($proveedores)): ?>
                <th>Contacto</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>RUC</th>
            <?php else: ?>
                <th>Descripción</th>
            <?php endif; ?>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $items = isset($proveedores) ? $proveedores : $categorias;
        foreach ($items as $item): 
        ?>
            <tr>
                <td><?= isset($item->id_proveedor) ? $item->id_proveedor : $item->id_categoria; ?></td>
                <td><?= isset($item->nombre_proveedor) ? $item->nombre_proveedor : $item->nombre_categoria; ?></td>
                <?php if (isset($proveedores)): ?>
                    <td><?= $item->contacto_persona; ?></td>
                    <td><?= $item->contacto_email; ?></td>
                    <td><?= $item->contacto_telefono; ?></td>
                    <td><?= $item->ruc; ?></td>
                <?php else: ?>
                    <td><?= $item->descripcion; ?></td>
                <?php endif; ?>
                <td>
                    <a href="<?= URLROOT; ?>/<?php echo isset($proveedores) ? 'proveedor/edit' : 'categoria/edit'; ?>/<?= isset($item->id_proveedor) ? $item->id_proveedor : $item->id_categoria; ?>" class="btn btn-warning btn-sm">Editar</a>
                    <form action="<?= URLROOT; ?>/<?php echo isset($proveedores) ? 'proveedor/delete' : 'categoria/delete'; ?>/<?= isset($item->id_proveedor) ? $item->id_proveedor : $item->id_categoria; ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esto? Esto podría afectar la integridad de los datos si hay dependencias.');">
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require APPROOT . '/views/layouts/footer.php'; ?>