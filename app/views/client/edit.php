<?php require_once '../app/views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Editar Cliente</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= $_SESSION['error'] ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="<?= URLROOT ?>/client/update/<?= htmlspecialchars($client->id_cliente) ?>" method="POST">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre:</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($client->nombre) ?>" required>
        </div>
        <div class="mb-3">
            <label for="apellido" class="form-label">Apellido:</label>
            <input type="text" class="form-control" id="apellido" name="apellido" value="<?= htmlspecialchars($client->apellido) ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($client->email ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono:</label>
            <input type="text" class="form-control" id="telefono" name="telefono" value="<?= htmlspecialchars($client->telefono ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección:</label>
            <textarea class="form-control" id="direccion" name="direccion" rows="3"><?= htmlspecialchars($client->direccion ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Cliente</button>
        <a href="<?= URLROOT ?>/client/index" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<?php require_once '../app/views/layouts/footer.php'; ?>