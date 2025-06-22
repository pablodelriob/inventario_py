<?php require_once APPROOT . '/views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Registrar Movimiento de Inventario</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= $_SESSION['error'] ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="<?= URLROOT ?>/inventario/registrarMovimiento" method="POST">
        <div class="mb-3">
            <label for="id_producto" class="form-label">Producto:</label>
            <select class="form-select" id="id_producto" name="id_producto" required>
                <option value="">Seleccione un producto</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= htmlspecialchars($product->id_producto) ?>" <?= (isset($_GET['product_id']) && $_GET['product_id'] == $product->id_producto) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($product->nombre_comercial) ?> (SKU: <?= htmlspecialchars($product->sku) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="tipo_movimiento" class="form-label">Tipo de Movimiento:</label>
            <select class="form-select" id="tipo_movimiento" name="tipo_movimiento" required>
                <option value="">Seleccione el tipo</option>
                <option value="entrada">Entrada (Compra/Devolución)</option>
                <option value="salida">Salida (Venta/Merma)</option>
                <option value="ajuste_positivo">Ajuste Positivo</option>
                <option value="ajuste_negativo">Ajuste Negativo</option>
                <option value="transferencia">Transferencia entre Depósitos</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="cantidad" class="form-label">Cantidad:</label>
            <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
        </div>

        <div class="mb-3" id="div_deposito_origen" style="display: none;">
            <label for="id_deposito_origen" class="form-label">Depósito de Origen:</label>
            <select class="form-select" id="id_deposito_origen" name="id_deposito_origen">
                <option value="">Seleccione depósito de origen</option>
                <?php foreach ($depositos as $deposito): ?>
                    <option value="<?= htmlspecialchars($deposito->id_deposito) ?>">
                        <?= htmlspecialchars($deposito->nombre_deposito) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3" id="div_deposito_destino" style="display: none;">
            <label for="id_deposito_destino" class="form-label">Depósito de Destino:</label>
            <select class="form-select" id="id_deposito_destino" name="id_deposito_destino">
                <option value="">Seleccione depósito de destino</option>
                <?php foreach ($depositos as $deposito): ?>
                    <option value="<?= htmlspecialchars($deposito->id_deposito) ?>">
                        <?= htmlspecialchars($deposito->nombre_deposito) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Registrar Movimiento</button>
        <a href="<?= URLROOT ?>/inventario/index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoMovimientoSelect = document.getElementById('tipo_movimiento');
    const divOrigen = document.getElementById('div_deposito_origen');
    const selectOrigen = document.getElementById('id_deposito_origen');
    const divDestino = document.getElementById('div_deposito_destino');
    const selectDestino = document.getElementById('id_deposito_destino');

    function toggleDepositoFields() {
        const tipo = tipoMovimientoSelect.value;

        // Resetear visibilidad y requerimiento
        divOrigen.style.display = 'none';
        selectOrigen.removeAttribute('required');
        divDestino.style.display = 'none';
        selectDestino.removeAttribute('required');

        if (tipo === 'entrada' || tipo === 'ajuste_positivo') {
            divDestino.style.display = 'block';
            selectDestino.setAttribute('required', 'required');
        } else if (tipo === 'salida' || tipo === 'ajuste_negativo') {
            divOrigen.style.display = 'block';
            selectOrigen.setAttribute('required', 'required');
        } else if (tipo === 'transferencia') {
            divOrigen.style.display = 'block';
            selectOrigen.setAttribute('required', 'required');
            divDestino.style.display = 'block';
            selectDestino.setAttribute('required', 'required');
        }
    }

    tipoMovimientoSelect.addEventListener('change', toggleDepositoFields);

    // Ejecutar al cargar la página por si hay un valor inicial (ej. desde un error de validación)
    toggleDepositoFields();

    // Si viene un product_id por GET, seleccionarlo automáticamente
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('product_id');
    if (productId) {
        document.getElementById('id_producto').value = productId;
    }
});
</script>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>