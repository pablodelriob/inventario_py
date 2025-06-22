<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Listado de Productos</h2>

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

    <?php if (isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'Administrador'): ?>
        <a href="<?= URLROOT ?>/product/create" class="btn btn-primary mb-3">Nuevo Producto</a>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>SKU</th>
                    <th>Nombre</th>
                    <th>Costo</th>
                    <th>P. Público</th>
                    <th>P. Mayorista</th>
                    <th>P. Distribuidor</th>
                    <th>Stock Mín.</th>
                    <th>Fecha Ingreso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?php if ($product->ruta_imagen): ?>
                                    <img src="<?= URLROOT ?>/<?= htmlspecialchars($product->ruta_imagen) ?>" alt="<?= htmlspecialchars($product->nombre_comercial) ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?= URLROOT ?>/img/no-image.png" alt="Sin imagen" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($product->sku) ?></td>
                            <td><?= htmlspecialchars($product->nombre_comercial) ?></td>
                            <td><?= htmlspecialchars(number_format($product->costo, 2)) ?></td>
                            <td><?= htmlspecialchars(number_format($product->precio_publico, 2)) ?></td>
                            <td><?= htmlspecialchars(number_format($product->precio_mayorista, 2)) ?></td>
                            <td><?= htmlspecialchars(number_format($product->precio_distribuidor, 2)) ?></td>
                            <td><?= htmlspecialchars($product->cantidad_minima) ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($product->fecha_ingreso))) ?></td>
                            <td>
                                <?php if (isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'Administrador'): ?>
                                    <a href="<?= URLROOT ?>/product/edit/<?= $product->id_producto ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <form action="<?= URLROOT ?>/product/delete/<?= $product->id_producto ?>" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este producto? Esto eliminará también su stock en todos los depósitos.');">
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Ver detalles</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center">No hay productos registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>