<?php require_once APPROOT . '/views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-9">
            <h2>Órdenes de Compra</h2>
        </div>
        <div class="col-md-3 text-right">
            <a href="<?php echo URLROOT; ?>/compras/crearOrdenCompra" class="btn btn-primary">
                <i class="fa fa-plus"></i> Crear Orden de Compra
            </a>
        </div>
    </div>
    <hr>
    <?php flash('message'); ?>
    <?php flash('error'); ?>

    <?php if (empty($ordenesCompra)): ?>
        <p>No hay órdenes de compra registradas aún.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID OC</th>
                        <th>Proveedor</th>
                        <th>Fecha Creación</th>
                        <th>Fecha Entrega Esperada</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th>Creado Por</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ordenesCompra as $oc): ?>
                        <tr>
                            <td><?php echo $oc->id_orden_compra; ?></td>
                            <td><?php echo htmlspecialchars($oc->nombre_proveedor); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($oc->fecha_creacion)); ?></td>
                            <td><?php echo $oc->fecha_esperada_entrega ? date('d/m/Y', strtotime($oc->fecha_esperada_entrega)) : 'N/A'; ?></td>
                            <td><span class="badge badge-<?php 
                                if ($oc->estado == 'Pendiente') echo 'warning'; 
                                else if ($oc->estado == 'Recibida Completa') echo 'success'; 
                                else if ($oc->estado == 'Recibida Parcial') echo 'info'; 
                                else if ($oc->estado == 'Cancelada') echo 'danger'; 
                                else echo 'secondary';
                            ?>"><?php echo htmlspecialchars($oc->estado); ?></span></td>
                            <td><?php echo number_format($oc->total_orden, 2, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($oc->nombre_usuario); ?></td>
                            <td class="d-flex">
                                <a href="<?php echo URLROOT; ?>/compras/verOrdenCompra/<?php echo $oc->id_orden_compra; ?>" class="btn btn-info btn-sm mr-2">Ver</a>
                                <?php if ($oc->estado == 'Pendiente'): ?>
                                    <?php endif; ?>
                                <form action="<?php echo URLROOT; ?>/compras/eliminarOrdenCompra/<?php echo $oc->id_orden_compra; ?>" method="post" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta Orden de Compra? Esta acción es irreversible y también eliminará los detalles.');">
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