<?php require_once APPROOT . '/views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Registrar Venta</h2>
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

    <form action="<?= URLROOT ?>/inventario/processSell" method="POST">
        <div class="mb-3">
            <label for="product_id" class="form-label">Producto:</label>
            <select class="form-select" id="product_id" name="product_id" required>
                <option value="">Seleccione un producto</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= htmlspecialchars($product->id_producto) ?>">
                        <?= htmlspecialchars($product->nombre_comercial) ?> (SKU: <?= htmlspecialchars($product->sku) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="deposito_id" class="form-label">Depósito de Salida:</label>
            <select class="form-select" id="deposito_id" name="deposito_id" required>
                <option value="">Seleccione el depósito de donde sale el stock</option>
                <?php foreach ($depositos as $deposito): ?>
                    <option value="<?= htmlspecialchars($deposito->id_deposito) ?>">
                        <?= htmlspecialchars($deposito->nombre_deposito) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="cantidad" class="form-label">Cantidad a Vender:</label>
            <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
        </div>

        <div class="mb-3">
            <label for="observaciones" class="form-label">Observaciones (Opcional):</label>
            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Registrar Venta</button>
        <a href="<?= URLROOT ?>/inventario/index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>