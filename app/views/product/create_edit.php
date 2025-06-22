<?php require_once APPROOT . '/views/layouts/header.php'; // Usa APPROOT para los layouts ?>

<div class="container mt-4">
    <h2><?= isset($product) ? 'Editar Producto: ' . htmlspecialchars($product->nombre_comercial) : 'Crear Nuevo Producto' ?></h2>

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

    <form action="<?= isset($product) ? URLROOT . '/product/update/' . $product->id_producto : URLROOT . '/product/store' ?>" method="POST" enctype="multipart/form-data">
        <?php if (isset($product)): ?>
            <input type="hidden" name="id_producto" value="<?= htmlspecialchars($product->id_producto) ?>">
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="sku" class="form-label">Código SKU:</label>
                <input type="text" class="form-control" id="sku" name="sku" value="<?= htmlspecialchars($product->sku ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="codigo_barras" class="form-label">Código de Barras:</label>
                <input type="text" class="form-control" id="codigo_barras" name="codigo_barras" value="<?= htmlspecialchars($product->codigo_barras ?? '') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label for="nombre_comercial" class="form-label">Nombre Comercial:</label>
            <input type="text" class="form-control" id="nombre_comercial" name="nombre_comercial" value="<?= htmlspecialchars($product->nombre_comercial ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción del Producto:</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($product->descripcion ?? '') ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="costo" class="form-label">Costo (USD):</label>
                <input type="number" step="0.01" class="form-control" id="costo" name="costo" value="<?= htmlspecialchars($product->costo ?? '') ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="cantidad_minima" class="form-label">Cantidad Mínima (Alerta):</label>
                <input type="number" class="form-control" id="cantidad_minima" name="cantidad_minima" value="<?= htmlspecialchars($product->cantidad_minima ?? 0) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="id_usuario_ingreso" class="form-label">Quién Ingresó:</label>
                <select class="form-select" id="id_usuario_ingreso" name="id_usuario_ingreso" disabled>
                    <option value="<?= $_SESSION['user_id'] ?>"><?= htmlspecialchars($_SESSION['username'] ?? 'Usuario Desconocido') ?></option>
                </select>
                <small class="form-text text-muted">Automáticamente se asigna al usuario que crea/edita el producto.</small>
            </div>
        </div>

        <h4>Configuración de Precios</h4>
        <div class="row">
              <div class="row">
            <div class="col-md-4 mb-3">
                <label for="precio_venta" class="form-label">Precio Venta (Manual):</label>
                <input type="number" step="0.01" class="form-control" id="precio_venta" name="precio_venta" value="<?= htmlspecialchars($product->precio_venta ?? '') ?>">
                <small class="form-text text-muted">Precio de venta general del producto.</small>
            </div>
        </div>
            <div class="col-md-4 mb-3">
                <label for="porcentaje_precio_publico" class="form-label">% P. Público:</label>
                <input type="number" step="0.01" class="form-control" id="porcentaje_precio_publico" name="porcentaje_precio_publico" value="<?= htmlspecialchars($product->porcentaje_precio_publico ?? '') ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label for="precio_publico" class="form-label">Precio Público (Manual):</label>
                <input type="number" step="0.01" class="form-control" id="precio_publico" name="precio_publico" value="<?= htmlspecialchars($product->precio_publico ?? '') ?>">
                <small class="form-text text-muted">Si se llena, anula el cálculo por porcentaje.</small>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="porcentaje_precio_mayorista" class="form-label">% P. Mayorista:</label>
                <input type="number" step="0.01" class="form-control" id="porcentaje_precio_mayorista" name="porcentaje_precio_mayorista" value="<?= htmlspecialchars($product->porcentaje_precio_mayorista ?? '') ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label for="precio_mayorista" class="form-label">Precio Mayorista (Manual):</label>
                <input type="number" step="0.01" class="form-control" id="precio_mayorista" name="precio_mayorista" value="<?= htmlspecialchars($product->precio_mayorista ?? '') ?>">
                <small class="form-text text-muted">Si se llena, anula el cálculo por porcentaje.</small>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="porcentaje_precio_distribuidor" class="form-label">% P. Distribuidor:</label>
                <input type="number" step="0.01" class="form-control" id="porcentaje_precio_distribuidor" name="porcentaje_precio_distribuidor" value="<?= htmlspecialchars($product->porcentaje_precio_distribuidor ?? '') ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label for="precio_distribuidor" class="form-label">Precio Distribuidor (Manual):</label>
                <input type="number" step="0.01" class="form-control" id="precio_distribuidor" name="precio_distribuidor" value="<?= htmlspecialchars($product->precio_distribuidor ?? '') ?>">
                <small class="form-text text-muted">Si se llena, anula el cálculo por porcentaje.</small>
            </div>
        </div>

        <h4>Imágenes del Producto</h4>
        <div class="mb-3">
            <label for="imagen_principal" class="form-label">Imagen Principal:</label>
            <input type="file" class="form-control" id="imagen_principal" name="imagen_principal" accept="image/*" <?= isset($product) ? '' : 'required' ?>>
            <?php if (isset($product) && $product->ruta_imagen): ?>
                <div class="mt-2">
                    <img src="<?= htmlspecialchars($product->ruta_imagen) ?>" alt="Imagen actual" style="max-width: 150px; height: auto;">
                    <small class="form-text text-muted">Imagen actual. Sube una nueva para reemplazarla.</small>
                    <input type="hidden" name="current_image_path" value="<?= htmlspecialchars($product->ruta_imagen) ?>">
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-success"><?= isset($product) ? 'Actualizar Producto' : 'Crear Producto' ?></button>
        <a href="<?= URLROOT ?>/product/index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php require_once APPROOT . '/views/layouts/footer.php'; // Usa APPROOT para los layouts ?>