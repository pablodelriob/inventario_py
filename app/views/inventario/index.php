<?php require_once APPROOT . '/views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Reporte de Inventario por Depósito</h2>
        <?php if ($_SESSION['role_name'] === 'Administrador' || $_SESSION['role_name'] === 'Gerente_Almacen'): ?>
            <a href="<?= URLROOT ?>/inventario/movimiento" class="btn btn-primary">Registrar Movimiento de Stock</a>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= $_SESSION['error'] ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success" role="alert">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (empty($inventoryData)): ?>
        <p>No hay productos registrados en el inventario o no se ha ingresado stock.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Producto (SKU)</th>
                        <th>Nombre Comercial</th>
                        <?php foreach ($depositos as $deposito): ?>
                            <th><?= htmlspecialchars($deposito->nombre_deposito) ?></th>
                        <?php endforeach; ?>
                        <th>Stock Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventoryData as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product->sku) ?></td>
                            <td><?= htmlspecialchars($product->nombre_comercial) ?></td>
                            <?php
                            // Mapear stock por depósito para fácil acceso
                            $stockByDepositoId = [];
                            foreach ($product->stock_details as $stock_detail) {
                                $stockByDepositoId[$stock_detail->id_deposito] = $stock_detail->cantidad_disponible;
                            }
                            ?>
                            <?php foreach ($depositos as $deposito): ?>
                                <td><?= htmlspecialchars($stockByDepositoId[$deposito->id_deposito] ?? 0) ?></td>
                            <?php endforeach; ?>
                            <td><strong><?= htmlspecialchars($product->total_stock) ?></strong></td>
                            <td>
                                <?php if ($_SESSION['role_name'] === 'Administrador' || $_SESSION['role_name'] === 'Gerente_Almacen'): ?>
                                    <a href="<?= URLROOT ?>/inventario/movimiento?product_id=<?= $product->id_producto ?>" class="btn btn-info btn-sm">Mover Stock</a>
                                <?php else: ?>
                                    <span class="text-muted">Ver</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>