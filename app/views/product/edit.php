<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Editar Producto</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= $_SESSION['error'] ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="<?= URLROOT ?>/product/update/<?= htmlspecialchars($product->id_producto) ?>" method="POST">
        <input type="hidden" name="id_producto" value="<?= htmlspecialchars($product->id_producto) ?>">

        <div class="mb-3">
            <label for="nombre_comercial" class="form-label">Nombre Comercial:</label>
            <input type="text" class="form-control" id="nombre_comercial" name="nombre_comercial" value="<?= htmlspecialchars($product->nombre_comercial ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="sku" class="form-label">SKU (Código de Barras/Referencia):</label>
            <input type="text" class="form-control" id="sku" name="sku" value="<?= htmlspecialchars($product->sku ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción (Opcional):</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($product->descripcion ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label for="precio_compra" class="form-label">Precio de Compra:</label>
            <input type="number" step="0.01" class="form-control" id="precio_compra" name="precio_compra" value="<?= htmlspecialchars($product->precio_compra ?? '0.00') ?>" required>
        </div>
        <div class="mb-3">
            <label for="precio_venta" class="form-label">Precio de Venta:</label>
            <input type="number" step="0.01" class="form-control" id="precio_venta" name="precio_venta" value="<?= htmlspecialchars($product->precio_venta ?? '0.00') ?>" required>
        </div>
        <div class="mb-3">
            <label for="unidad_medida" class="form-label">Unidad de Medida (Ej: ud, kg, m, L):</label>
            <input type="text" class="form-control" id="unidad_medida" name="unidad_medida" value="<?= htmlspecialchars($product->unidad_medida ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="proveedor" class="form-label">Proveedor (Opcional):</label>
            <input type="text" class="form-control" id="proveedor" name="proveedor" value="<?= htmlspecialchars($product->proveedor ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Producto</button>
        <a href="<?= URLROOT ?>/product/index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>