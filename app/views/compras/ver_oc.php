<?php require_once APPROOT . '/views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card card-body bg-light mt-5">
                <h2>Detalles de Orden de Compra #<?php echo $data['orden_compra']->id_orden_compra; ?></h2>
                <hr>
                
                <?php if (!empty($data['orden_compra']->observaciones)): ?>
                    <div class="mb-3">
                        <p><strong>Observaciones:</strong> <?php echo nl2br(htmlspecialchars($data['orden_compra']->observaciones)); ?></p>
                    </div>
                <?php endif; ?>

                <hr>
                <h4>Productos en la Orden</h4>
                <?php if (empty($data['detalles'])): ?>
                    <p>No hay productos en esta orden de compra.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Producto (SKU)</th>
                                    <th>Cantidad Pedida</th>
                                    <th>Cantidad Recibida</th>
                                    <th>Costo Unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['detalles'] as $detalle): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($detalle->producto_nombre); ?> (<?php echo htmlspecialchars($detalle->producto_sku); ?>)</td>
                                        <td><?php echo $detalle->cantidad_pedida; ?></td>
                                        <td><?php echo $detalle->cantidad_recibida; ?></td>
                                        <td><?php echo number_format($detalle->costo_unitario, 2, ',', '.'); ?></td>
                                        <td><?php echo number_format($detalle->cantidad_pedida * $detalle->costo_unitario, 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="row mt-4">
                    <div class="col">
                        <a href="<?php echo URLROOT; ?>/compras" class="btn btn-secondary">Volver al Listado de OC</a>
                    </div>
                    <?php 
                    // Mostrar el botón "Recibir Mercancía" solo si la OC no está completamente recibida o cancelada
                    if ($data['orden_compra']->estado !== 'Recibida Completa' && $data['orden_compra']->estado !== 'Cancelada'): 
                    ?>
                        <div class="col text-right">
                            <form action="<?php echo URLROOT; ?>/compras/recibirMercancia/<?php echo $data['orden_compra']->id_orden_compra; ?>" method="post" onsubmit="return confirm('¿Confirmas la recepción de esta mercancía? Se actualizará el inventario.');">
                                <button type="submit" class="btn btn-success">Recibir Mercancía</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>