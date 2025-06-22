<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Crear Nuevo Pedido</h2>

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

    <form id="createOrderForm" action="<?= URLROOT ?>/order/store" method="POST">
        <div class="mb-3">
            <label for="id_cliente" class="form-label">Cliente:</label>
            <select class="form-select" id="id_cliente" name="id_cliente" required>
                <option value="">Selecciona un cliente</option>
                <?php if (!empty($clientes)): ?>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= htmlspecialchars($cliente->id_cliente) ?>" <?= (isset($_POST['id_cliente']) && $_POST['id_cliente'] == $cliente->id_cliente) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cliente->nombre . ' ' . $cliente->apellido) ?> (<?= htmlspecialchars($cliente->email ?? 'N/A') ?>)
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="">No hay clientes registrados. <a href="<?= URLROOT ?>/client/create">Crear cliente</a></option>
                <?php endif; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="observaciones" class="form-label">Observaciones del Pedido (Opcional):</label>
            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?= htmlspecialchars($_POST['observaciones'] ?? '') ?></textarea>
        </div>

        <hr>
        <h4>Productos del Pedido</h4>
        <div id="product_items_container">
            </div>

        <button type="button" class="btn btn-secondary mb-3" id="add_product_item">Añadir Producto al Pedido</button>

        <input type="hidden" name="items_pedido" id="items_pedido_json">

        <button type="submit" class="btn btn-primary">Guardar Pedido</button>
        <a href="<?= URLROOT ?>/order/index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addProductItemBtn = document.getElementById('add_product_item');
    const productItemsContainer = document.getElementById('product_items_container');
    const form = document.getElementById('createOrderForm');
    const itemsPedidoJsonInput = document.getElementById('items_pedido_json');

    let itemCounter = 0;

    addProductItemBtn.addEventListener('click', function() {
        itemCounter++;
        const newItemDiv = document.createElement('div');
        newItemDiv.classList.add('row', 'mb-3', 'align-items-end', 'product-item');
        newItemDiv.setAttribute('data-item-id', itemCounter);

        newItemDiv.innerHTML = `
            <div class="col-md-5">
                <label for="product_${itemCounter}" class="form-label">Producto:</label>
                <select class="form-select product-select" id="product_${itemCounter}" data-item-id="${itemCounter}" required>
                    <option value="">Selecciona un producto</option>
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?= htmlspecialchars($producto->id_producto) ?>" data-precio="<?= htmlspecialchars($producto->precio_venta) ?>">
                                <?= htmlspecialchars($producto->nombre_comercial) ?> (SKU: <?= htmlspecialchars($producto->sku) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="deposito_${itemCounter}" class="form-label">Depósito:</label>
                <select class="form-select deposito-select" id="deposito_${itemCounter}" required>
                    <option value="">Selecciona depósito</option>
                    <?php if (!empty($depositos)): ?>
                        <?php foreach ($depositos as $deposito): ?>
                            <option value="<?= htmlspecialchars($deposito->id_deposito) ?>">
                                <?= htmlspecialchars($deposito->nombre_deposito) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="cantidad_${itemCounter}" class="form-label">Cantidad:</label>
                <input type="number" class="form-control cantidad-input" id="cantidad_${itemCounter}" min="1" value="1" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-product-item">Eliminar</button>
            </div>
        `;
        productItemsContainer.appendChild(newItemDiv);
    });

    productItemsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-product-item')) {
            e.target.closest('.product-item').remove();
        }
    });

    form.addEventListener('submit', function(e) {
        const items = [];
        let allItemsValid = true;

        document.querySelectorAll('.product-item').forEach(itemDiv => {
            const itemId = itemDiv.getAttribute('data-item-id');
            const productId = itemDiv.querySelector(`#product_${itemId}`).value;
            const depositoId = itemDiv.querySelector(`#deposito_${itemId}`).value;
            const cantidad = itemDiv.querySelector(`#cantidad_${itemId}`).value;
            const precioVenta = itemDiv.querySelector(`#product_${itemId}`).selectedOptions[0].getAttribute('data-precio');

            if (!productId || !depositoId || !cantidad || cantidad <= 0) {
                allItemsValid = false;
                alert('Por favor, completa todos los campos de producto, depósito y cantidad para cada artículo del pedido.');
                return; // Detener el bucle si hay campos vacíos
            }

            items.push({
                product_id: productId,
                deposito_id: depositoId,
                cantidad: cantidad,
                precio_venta: precioVenta // Incluir el precio de venta en el JSON
            });
        });

        if (!allItemsValid || items.length === 0) {
            e.preventDefault(); // Detener el envío del formulario
            if (items.length === 0) {
                alert('Debe añadir al menos un producto al pedido.');
            }
            return;
        }




// AÑADE ESTE CONSOLE.LOG AQUÍ
    console.log('Items a enviar:', items);
    console.log('JSON a enviar:', JSON.stringify(items));


        itemsPedidoJsonInput.value = JSON.stringify(items);
    });

    // Añadir un ítem inicial al cargar la página si no hay errores previos
    <?php if (empty($_SESSION['error']) && empty($_SESSION['message'])): ?>
        addProductItemBtn.click(); // Simular un clic para añadir el primer ítem
    <?php endif; ?>
});
</script>

<?php require_once '../app/views/layouts/footer.php'; ?>