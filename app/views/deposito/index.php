<?php require_once APPROOT . '/views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Gestión de Depósitos</h2>
        <?php if ($_SESSION['role_name'] === 'Administrador'): // Solo Administrador puede crear ?>
            <a href="<?= URLROOT ?>/deposito/create" class="btn btn-primary">Crear Nuevo Depósito</a>
        <?php endif; ?>
    </div>

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

    <?php if (empty($depositos)): ?>
        <p>No hay depósitos registrados. Puedes crear uno nuevo.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Ubicación</th>
                        <th>Capacidad Máxima</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($depositos as $deposito): ?>
                        <tr>
                            <td><?= htmlspecialchars($deposito->id_deposito) ?></td>
                            <td><?= htmlspecialchars($deposito->nombre_deposito) ?></td>
                            <td><?= htmlspecialchars($deposito->ubicacion ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($deposito->capacidad_maxima ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($deposito->fecha_creacion) ?></td>
                            <td>
                                <?php if ($_SESSION['role_name'] === 'Administrador'): ?>
                                    <a href="<?= URLROOT ?>/deposito/edit/<?= $deposito->id_deposito ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <form action="<?= URLROOT ?>/deposito/delete/<?= $deposito->id_deposito ?>" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este depósito? Se eliminará solo si no tiene productos asociados en inventario.');">
                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Sin acciones</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>