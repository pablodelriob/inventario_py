<?php require APPROOT . '/views/layouts/header.php'; ?>
<?php flash('error_message'); ?>

<div class="card card-body bg-light mt-5">
    <h2><?= (isset($id_proveedor) || isset($id_categoria)) ? 'Editar' : 'Añadir'; ?> <?= (isset($id_proveedor)) ? 'Proveedor' : 'Categoría'; ?></h2>
    <p>Por favor, complete este formulario para <?= (isset($id_proveedor) || isset($id_categoria)) ? 'editar' : 'añadir'; ?> un <?= (isset($id_proveedor)) ? 'proveedor' : 'categoría'; ?>.</p>
    <form action="<?= URLROOT; ?>/<?= (isset($id_proveedor)) ? 'proveedor' : 'categoria'; ?>/<?= (isset($id_proveedor) || isset($id_categoria)) ? 'update/' . (isset($id_proveedor) ? $id_proveedor : $id_categoria) : 'store'; ?>" method="post">

        <?php if (isset($nombre_proveedor)): ?>
            <div class="form-group">
                <label for="nombre_proveedor">Nombre del Proveedor: <sup>*</sup></label>
                <input type="text" name="nombre_proveedor" class="form-control form-control-lg" value="<?= $nombre_proveedor; ?>">
            </div>
            <div class="form-group">
                <label for="contacto_persona">Persona de Contacto:</label>
                <input type="text" name="contacto_persona" class="form-control form-control-lg" value="<?= $contacto_persona; ?>">
            </div>
            <div class="form-group">
                <label for="contacto_email">Email:</label>
                <input type="email" name="contacto_email" class="form-control form-control-lg" value="<?= $contacto_email; ?>">
            </div>
            <div class="form-group">
                <label for="contacto_telefono">Teléfono:</label>
                <input type="text" name="contacto_telefono" class="form-control form-control-lg" value="<?= $contacto_telefono; ?>">
            </div>
            <div class="form-group">
                <label for="direccion">Dirección:</label>
                <input type="text" name="direccion" class="form-control form-control-lg" value="<?= $direccion; ?>">
            </div>
            <div class="form-group">
                <label for="ruc">RUC:</label>
                <input type="text" name="ruc" class="form-control form-control-lg" value="<?= $ruc; ?>">
            </div>
            <div class="form-group">
                <label for="condiciones_pago">Condiciones de Pago:</label>
                <input type="text" name="condiciones_pago" class="form-control form-control-lg" value="<?= $condiciones_pago; ?>">
            </div>
        <?php elseif (isset($nombre_categoria)): ?>
            <div class="form-group">
                <label for="nombre_categoria">Nombre de la Categoría: <sup>*</sup></label>
                <input type="text" name="nombre_categoria" class="form-control form-control-lg" value="<?= $nombre_categoria; ?>">
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea name="descripcion" class="form-control form-control-lg"><?= $descripcion; ?></textarea>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col">
                <input type="submit" value="Guardar" class="btn btn-success btn-block">
            </div>
            <div class="col">
                <a href="<?= URLROOT; ?>/<?= (isset($id_proveedor)) ? 'proveedor' : 'categoria'; ?>/index" class="btn btn-secondary btn-block">Cancelar</a>
            </div>
        </div>
    </form>
</div>

<?php require APPROOT . '/views/layouts/footer.php'; ?>