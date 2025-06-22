<?php require_once APPROOT . '/views/layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card card-body bg-light mt-5">
                <h2>Agregar Nuevo Proveedor</h2>
                <p>Por favor, completa el formulario para añadir un nuevo proveedor.</p>
                <?php flash('message'); ?>
                <?php flash('error'); ?>
                <form action="<?php echo URLROOT; ?>/compras/agregarProveedor" method="post">
                    <div class="form-group">
                        <label for="nombre_proveedor">Nombre del Proveedor: <sup>*</sup></label>
                        <input type="text" name="nombre_proveedor" class="form-control form-control-lg <?php echo (!empty($data['nombre_proveedor_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($data['nombre_proveedor'] ?? ''); ?>">
                        <span class="invalid-feedback"><?php echo $data['nombre_proveedor_err'] ?? ''; ?></span>
                    </div>
                    <div class="form-group">
                        <label for="contacto_persona">Persona de Contacto:</label>
                        <input type="text" name="contacto_persona" class="form-control form-control-lg" value="<?php echo htmlspecialchars($data['contacto_persona'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="contacto_email">Email de Contacto:</label>
                        <input type="email" name="contacto_email" class="form-control form-control-lg" value="<?php echo htmlspecialchars($data['contacto_email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="contacto_telefono">Teléfono de Contacto:</label>
                        <input type="text" name="contacto_telefono" class="form-control form-control-lg" value="<?php echo htmlspecialchars($data['contacto_telefono'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección:</label>
                        <textarea name="direccion" class="form-control form-control-lg"><?php echo htmlspecialchars($data['direccion'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="ruc">RUC / NIF:</label>
                        <input type="text" name="ruc" class="form-control form-control-lg" value="<?php echo htmlspecialchars($data['ruc'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="condiciones_pago">Condiciones de Pago:</label>
                        <input type="text" name="condiciones_pago" class="form-control form-control-lg" value="<?php echo htmlspecialchars($data['condiciones_pago'] ?? ''); ?>" placeholder="Ej: 30 días netos">
                    </div>
                    
                    <div class="row">
                        <div class="col">
                            <input type="submit" value="Agregar Proveedor" class="btn btn-success btn-block">
                        </div>
                        <div class="col">
                            <a href="<?php echo URLROOT; ?>/compras/proveedores" class="btn btn-secondary btn-block">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>