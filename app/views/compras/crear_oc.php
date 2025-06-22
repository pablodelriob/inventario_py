<?php require_once APPROOT . '/views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card card-body bg-light mt-5">
                <h2>Crear Nueva Orden de Compra</h2>
                <p>Completa la información de la orden de compra y añade los productos.</p>
                <?php flash('message'); ?>
                <?php flash('error'); ?>
                <form id="ocForm" action="<?php echo URLROOT; ?>/compras/crearOrdenCompra" method="post">
                    <div class="form-group">
                        <label for="id_proveedor">Proveedor: <sup>*</sup></label>
                        <select name="id_proveedor" id="id_proveedor" class="form-control form-control-lg">
                            <option value="">Selecciona un proveedor</option>
                            <?php foreach ($data['proveedores'] as $proveedor): ?>
                                <option value="<?php echo $proveedor->id_proveedor; ?>" <?php echo (isset($_POST['id_proveedor']) && $_POST['id_proveedor'] == $proveedor->id_proveedor) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($proveedor->nombre_proveedor); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha_creacion">Fecha de Creación: <sup>*</sup></label>
                        <input type="date" name="fecha_creacion" id="fecha_creacion" class="form-control form-control-lg" value="<?php echo htmlspecialchars($data['fecha_creacion'] ?? date('Y-m-d')); ?>">
                    </div>

                    <div class="form-group">
                        <label for="fecha_esperada_entrega">Fecha Esperada de Entrega:</label>
                        <input type="date" name="fecha_esperada_entrega" id="fecha_esperada_entrega" class="form-control form-control-lg" value="<?php echo htmlspecialchars($data['fecha_esperada_entrega'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="observaciones">Observaciones:</label>
                        <textarea name="observaciones" id="observaciones" class="form-control form-control-lg" rows="3"><?php echo htmlspecialchars($data['observaciones'] ?? ''); ?></textarea>
                    </div>

                    <hr>
                    <h4>Detalles de la Orden de Compra</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Costo Unitario</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data['items'])): ?>
                                    <?php foreach ($data['items'] as $index => $item): ?>
                                        <tr data-index="<?php echo $index; ?>">
                                            <td>
                                                <select name="product_id[]" class="form-control product-select" required>
                                                    <option value="">Selecciona producto</option>
                                                    <?php foreach ($data['productos'] as $producto): ?>
                                                        <option value="<?php echo $producto->id_producto; ?>" data-sku="<?php echo htmlspecialchars($producto->sku); ?>" <?php echo ($item['id_producto'] == $producto->id_producto) ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($producto->nombre_comercial); ?> (<?php echo htmlspecialchars($producto->sku); ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><input type="number" name="cantidad[]" class="form-control quantity-input" value="<?php echo htmlspecialchars($item['cantidad']); ?>" min="1" required></td>
                                            <td><input type="number" name="costo_unitario[]" class="form-control cost-input" value="<?php echo htmlspecialchars($item['costo_unitario']); ?>" min="0.01" step="0.01" required></td>
                                            <td class="subtotal-cell"><?php echo number_format($item['cantidad'] * $item['costo_unitario'], 2, ',', '.'); ?></td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-item">Eliminar</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Total de la Orden:</strong></td>
                                    <td id="totalOrdenCell">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <button type="button" class="btn btn-secondary mt-3" id="addItemBtn">Añadir Producto</button>
                    
                    <input type="hidden" name="items_json" id="itemsJson">

                    <div class="row mt-4">
                        <div class="col">
                            <input type="submit" value="Crear Orden de Compra" class="btn btn-success btn-block">
                        </div>
                        <div class="col">
                            <a href="<?php echo URLROOT; ?>/compras" class="btn btn-secondary btn-block">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemsTableBody = document.querySelector('#itemsTable tbody');
    const addItemBtn = document.getElementById('addItemBtn');
    const itemsJsonInput = document.getElementById('itemsJson');
    const totalOrdenCell = document.getElementById('totalOrdenCell');
    const ocForm = document.getElementById('ocForm');

    let itemCounter = 0; // Para dar un ID único a cada fila si es necesario

    // Función para calcular el subtotal de una fila
    function calculateRowSubtotal(row) {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
        const subtotal = quantity * cost;
        row.querySelector('.subtotal-cell').textContent = subtotal.toLocaleString('es-PY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        return subtotal;
    }

    // Función para calcular el total general de la orden
    function calculateTotalOrden() {
        let total = 0;
        document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
            total += calculateRowSubtotal(row);
        });
        totalOrdenCell.textContent = total.toLocaleString('es-PY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Función para añadir una nueva fila de producto
    addItemBtn.addEventListener('click', function() {
        const newRow = document.createElement('tr');
        newRow.dataset.index = itemCounter++;
        newRow.innerHTML = `
            <td>
                <select name="product_id[]" class="form-control product-select" required>
                    <option value="">Selecciona producto</option>
                    <?php foreach ($data['productos'] as $producto): ?>
                        <option value="<?php echo $producto->id_producto; ?>" data-sku="<?php echo htmlspecialchars($producto->sku); ?>">
                            <?php echo htmlspecialchars($producto->nombre_comercial); ?> (<?php echo htmlspecialchars($producto->sku); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="number" name="cantidad[]" class="form-control quantity-input" value="1" min="1" required></td>
            <td><input type="number" name="costo_unitario[]" class="form-control cost-input" value="0.00" min="0.01" step="0.01" required></td>
            <td class="subtotal-cell">0.00</td>
            <td><button type="button" class="btn btn-danger btn-sm remove-item">Eliminar</button></td>
        `;
        itemsTableBody.appendChild(newRow);
        attachRowListeners(newRow); // Adjuntar listeners a la nueva fila
        calculateTotalOrden(); // Recalcular total
    });

    // Función para adjuntar listeners a una fila (para eliminar y calcular)
    function attachRowListeners(row) {
        row.querySelector('.remove-item').addEventListener('click', function() {
            row.remove();
            calculateTotalOrden();
        });

        const quantityInput = row.querySelector('.quantity-input');
        const costInput = row.querySelector('.cost-input');

        quantityInput.addEventListener('input', () => calculateTotalOrden());
        costInput.addEventListener('input', () => calculateTotalOrden());
    }

    // Adjuntar listeners a las filas existentes al cargar la página (si hay)
    document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
        attachRowListeners(row);
    });

    // Calcular el total inicial si hay ítems precargados (ej. por error de validación)
    calculateTotalOrden();

    // Antes de enviar el formulario, recolectar los datos de los ítems en JSON
    ocForm.addEventListener('submit', function(event) {
        const items = [];
        let allItemsValid = true;

        document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
            const productId = row.querySelector('.product-select').value;
            const quantity = parseFloat(row.querySelector('.quantity-input').value);
            const cost = parseFloat(row.querySelector('.cost-input').value);

            if (!productId || isNaN(quantity) || quantity <= 0 || isNaN(cost) || cost <= 0) {
                allItemsValid = false;
            }

            items.push({
                id_producto: productId,
                cantidad: quantity,
                costo_unitario: cost
            });
        });

        if (items.length === 0) {
            alert('Por favor, añade al menos un producto a la orden de compra.');
            event.preventDefault(); // Detener el envío del formulario
            return;
        }

        if (!allItemsValid) {
            alert('Por favor, asegúrate de que todos los campos de producto, cantidad y costo unitario estén llenos y sean válidos.');
            event.preventDefault();
            return;
        }

        itemsJsonInput.value = JSON.stringify(items);
    });
});
</script>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>