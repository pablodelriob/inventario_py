<?php require_once APPROOT . '/views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Reportes de Inventario</h2>
    <hr>

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

    <ul class="nav nav-tabs mb-4" id="inventoryReportsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="current-stock-tab" data-bs-toggle="tab" data-bs-target="#current-stock" type="button" role="tab" aria-controls="current-stock" aria-selected="true">Stock Actual Detallado</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="movements-history-tab" data-bs-toggle="tab" data-bs-target="#movements-history" type="button" role="tab" aria-controls="movements-history" aria-selected="false">Historial de Movimientos</button>
        </li>
        </ul>

    <div class="tab-content" id="inventoryReportsTabContent">
        <div class="tab-pane fade show active" id="current-stock" role="tabpanel" aria-labelledby="current-stock-tab">
            <h3>Stock Actual por Producto y Depósito</h3>
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
                                        $stockByDepositoId[$stock_detail->id_deposito] = $stock_detail->cantidad;
                                    }
                                    ?>
                                    <?php foreach ($depositos as $deposito): ?>
                                        <td><?= htmlspecialchars($stockByDepositoId[$deposito->id_deposito] ?? 0) ?></td>
                                    <?php endforeach; ?>
                                    <td><strong><?= htmlspecialchars($product->total_stock) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="movements-history" role="tabpanel" aria-labelledby="movements-history-tab">
            <h3>Historial de Movimientos de Inventario</h3>
            <?php if (empty($movimientos)): ?>
                <p>No hay movimientos de inventario registrados. Asegúrate de tener la tabla `movimientos_inventario` y que se estén registrando.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th>Usuario</th>
                                </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimientos as $movimiento): ?>
                                <tr>
                                    <td><?= htmlspecialchars($movimiento->fecha_movimiento) ?></td>
                                    <td><?= htmlspecialchars($movimiento->producto_nombre) ?> (<?= htmlspecialchars($movimiento->producto_sku) ?>)</td>
                                    <td><?= htmlspecialchars($movimiento->tipo_movimiento) ?></td>
                                    <td><?= htmlspecialchars($movimiento->cantidad) ?></td>
                                    <td><?= htmlspecialchars($movimiento->origen_nombre ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($movimiento->destino_nombre ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($movimiento->usuario_nombre) ?></td>
                                    </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>