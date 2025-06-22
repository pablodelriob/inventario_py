<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - <?= SITENAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .register-container {
            max-width: 500px;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2 class="text-center mb-4">Registrar Usuario</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="<?= URLROOT ?>/auth/processRegister" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Usuario:</label>
                <input type="text" class="form-control" id="username" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico:</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmar Contraseña:</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="mb-3">
                <label for="role_id" class="form-label">Rol:</label>
                <select class="form-select" id="role_id" name="role_id" required>
                    <?php if (isset($roles) && is_array($roles)): ?>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role->id_rol ?>" <?= (isset($_POST['role_id']) && $_POST['role_id'] == $role->id_rol) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role->nombre_rol) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                         <option value="">No hay roles disponibles</option>
                    <?php endif; ?>
                </select>
                <small class="form-text text-muted">Para el primer usuario, por favor selecciona 'Administrador'.</small>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-success">Registrar</button>
            </div>
        </form>
        <p class="text-center mt-3">¿Ya tienes una cuenta? <a href="<?= URLROOT ?>/auth/login">Inicia Sesión</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>