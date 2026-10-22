<?php
$pageTitle = 'Iniciar sesión';
$errors = $errors ?? [];
$old = $old ?? [];
$flash = Session::flash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
<div class="login-card">
    <div class="login-brand">
        <i class="bi bi-shield-check"></i>
        <h2><?= APP_NAME ?></h2>
        <small class="text-muted">Sistema de gestión de tareas</small>
    </div>

    <?php if ($flash || isset($_GET['expired'])): ?>
        <div class="alert alert-warning">
            <?= $flash ?: 'Tu sesión ha expirado. Inicia sesión nuevamente.' ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $e): ?><li><?= Security::e($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/login">
        <?= Security::csrfField() ?>
        <div class="mb-3">
            <label class="form-label">Usuario o email</label>
            <input type="text" name="username" class="form-control" required
                   value="<?= Security::e($old['username'] ?? '') ?>" autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="remember" class="form-check-input" id="remember" value="1">
            <label class="form-check-label" for="remember">Recordarme</label>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión
        </button>
    </form>
    <div class="text-center mt-3">
        <small class="text-muted">¿Olvidaste tu contraseña? Contacta al administrador.</small>
    </div>
</div>
</body>
</html>