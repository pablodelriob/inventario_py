<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Resumen General de Inventario</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success" role="alert">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= $_SESSION['error'] ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['role_name']) && ($_SESSION['role_name'] === 'Administrador' || $_SESSION['role_name'] === 'Gerente_Almacen')): ?>
        <h3 class="mt-4">Gestión de Depósitos</h3>
        <a href="<?= URLROOT ?>/inventory/createDeposito" class="btn btn-primary mb-3">Nuevo Depósito</a>
    <?php endif; ?>

    <h3 class="mt-4">Unidades Totales por Depósito</h3>
    <?php if (!empty($depositos)): // Usar la variable $depositos del controlador ?>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Depósito</th>
                        <th>Total de Unidades</th>
                        <?php if (isset($_SESSION['role_name']) && ($_SESSION['role_name'] === 'Administrador' || $_SESSION['role_name'] === 'Gerente_Almacen')): ?>
                            <th>Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($depositos as $deposito_item): // Renombrado para evitar conflicto con $deposito en create_edit ?>
                        <tr>
                            <td><?= htmlspecialchars($deposito_item->nombre_deposito) ?></td>
                            <td>
                                <?php
                                $total_unidades = 0;
                                foreach ($inventario_por_deposito as $inv_sum) {
                                    if ($inv_sum->id_deposito === $deposito_item->id_deposito) {
                                        $total_unidades = $inv_sum->total_unidades;
                                        break;
                                    }
                                }
                                echo htmlspecialchars($total_unidades ?? 0) . ' productos';
                                ?>
                            </td>
                            <?php if (isset($_SESSION['role_name']) && ($_SESSION['role_name'] === 'Administrador' || $_SESSION['role_name'] === 'Gerente_Almacen')): ?>
                                <td>
                                    <a href="<?= URLROOT ?>/inventory/editDeposito/<?= $deposito_item->id_deposito ?>" class="btn btn-warning btn-sm">Editar Depósito</a>
                                    <form action="<?= URLROOT ?>/inventory/deleteDeposito/<?= $deposito_item->id_deposito ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este depósito? Esto no será posible si tiene productos.');">
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar Depósito</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No hay depósitos registrados.</div>
    <?php endif; ?>


<h3 class="mt-4">Detalle de Inventario por Producto y Depósito</h3>
    <?php if (!empty($products_without_stock_info) && !empty($depositos)): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Producto (SKU)</th>
                        <?php foreach ($depositos as $deposito_item): ?>
                            <th><?= htmlspecialchars($deposito_item->nombre_deposito) ?></th>
                        <?php endforeach; ?>
                        <?php if (isset($_SESSION['role_name']) && ($_SESSION['role_name'] === 'Administrador' || $_SESSION['role_name'] === 'Gerente_Almacen')): ?>
                             <th>Acciones por Depósito</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products_without_stock_info as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product->nombre_comercial) ?> (<?= htmlspecialchars($product->sku) ?>)</td>
                            <?php
                            $stocks_por_deposito = [];
                            foreach ($depositos as $deposito_item) {
                                $stock_found = false;
                                foreach ($inventario_detallado as $item) {
                                    if ($item->id_producto == $product->id_producto && $item->id_deposito == $deposito_item->id_deposito) {
                                        $stocks_por_deposito[$deposito_item->id_deposito] = $item->cantidad;
                                        $stock_found = true;
                                        break;
                                    }
                                }
                                if (!$stock_found) {
                                    $stocks_por_deposito[$deposito_item->id_deposito] = 0; // Si no hay entrada, el stock es 0
                                }
                            }
                            ?>
                            <?php foreach ($depositos as $deposito_item): ?>
                                <td><?= htmlspecialchars($stocks_por_deposito[$deposito_item->id_deposito]) ?></td>
                            <?php endforeach; ?>

                            <?php if (isset($_SESSION['role_name']) && ($_SESSION['role_name'] === 'Administrador' || $_SESSION['role_name'] === 'Gerente_Almacen')): ?>
                                <td>
                                    <?php foreach ($depositos as $deposito_item): ?>
                                        <a href="<?= URLROOT ?>/inventory/addStock/<?= htmlspecialchars($product->id_producto) ?>/<?= htmlspecialchars($deposito_item->id_deposito) ?>" class="btn btn-info btn-sm mb-1" style="width: 100%;">
                                            Ajustar Stock en <?= htmlspecialchars($deposito_item->nombre_deposito) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif (empty($products_without_stock_info) && !empty($depositos)): ?>
        <div class="alert alert-warning">No hay productos registrados. Por favor, <a href="<?= URLROOT ?>/product/create">crea un producto</a> primero.</div>
    <?php elseif (!empty($products_without_stock_info) && empty($depositos)): ?>
         <div class="alert alert-warning">No hay depósitos registrados. Por favor, <a href="<?= URLROOT ?>/inventory/createDeposito">crea un depósito</a> primero.</div>
    <?php else: ?>
        <div class="alert alert-info">No hay productos ni depósitos para mostrar inventario.</div>
    <?php endif; ?>
<?php require_once '../app/views/layouts/footer.php'; ?>