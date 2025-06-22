<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Añadir/Ajustar Stock</h2>
    <?php if (isset($product) && isset($deposito)): ?>
        <p><strong>Producto:</strong> <?= htmlspecialchars($product->nombre_comercial) ?> (SKU: <?= htmlspecialchars($product->sku) ?>)</p>
        <p><strong>Depósito:</strong> <?= htmlspecialchars($deposito->nombre_deposito) ?></p>
        <p><strong>Stock Actual:</strong> <?= htmlspecialchars($this->inventoryModel->getProductStockInDeposito($product->id_producto, $deposito->id_deposito)) ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= $_SESSION['error'] ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="<?= URLROOT ?>/inventory/processAddStock/<?= htmlspecialchars($product->id_producto ?? '') ?>/<?= htmlspecialchars($deposito->id_deposito ?? '') ?>" method="POST">
        <div class="mb-3">
            <label for="cantidad" class="form-label">Cantidad a añadir/restar:</label>
            <input type="number" class="form-control" id="cantidad" name="cantidad" required>
            <small class="form-text text-muted">Usa números positivos para añadir, negativos para restar (ajuste negativo).</small>
        </div>
        <div class="mb-3">
            <label for="tipo_movimiento" class="form-label">Tipo de Movimiento:</label>
            <select class="form-select" id="tipo_movimiento" name="tipo_movimiento" required>
                <option value="Entrada">Entrada (Compras/Producción)</option>
                <option value="Ajuste_Positivo">Ajuste Positivo (Inventario)</option>
                <option value="Ajuste_Negativo">Ajuste Negativo (Mermas/Daños)</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="observaciones" class="form-label">Observaciones:</label>
            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Guardar Stock</button>
        <a href="<?= URLROOT ?>/inventory/index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>