<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2><?= isset($deposito) ? 'Editar Depósito: ' . htmlspecialchars($deposito->nombre_deposito) : 'Crear Nuevo Depósito' ?></h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= $_SESSION['error'] ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="<?= isset($deposito) ? URLROOT . '/inventory/updateDeposito/' . $deposito->id_deposito : URLROOT . '/inventory/storeDeposito' ?>" method="POST">
        <div class="mb-3">
            <label for="nombre_deposito" class="form-label">Nombre del Depósito:</label>
            <input type="text" class="form-control" id="nombre_deposito" name="nombre_deposito" value="<?= htmlspecialchars($deposito->nombre_deposito ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="ubicacion" class="form-label">Ubicación:</label>
            <input type="text" class="form-control" id="ubicacion" name="ubicacion" value="<?= htmlspecialchars($deposito->ubicacion ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="capacidad_maxima" class="form-label">Capacidad Máxima (unidades):</label>
            <input type="number" class="form-control" id="capacidad_maxima" name="capacidad_maxima" value="<?= htmlspecialchars($deposito->capacidad_maxima ?? '') ?>">
            <small class="form-text text-muted">Deja en blanco si no hay un límite de capacidad definido.</small>
        </div>

        <button type="submit" class="btn btn-success"><?= isset($deposito) ? 'Actualizar Depósito' : 'Crear Depósito' ?></button>
        <a href="<?= URLROOT ?>/inventory/index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>