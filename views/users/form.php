<?php include BASE_PATH . 'views/layouts/header.php'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><?= $user ? 'Editar usuario' : 'Nuevo usuario' ?></h1>
        <p class="page-subtitle"><?= $user ? 'Modifica los datos del usuario' : 'Complete los datos del nuevo usuario' ?></p>
    </div>
    <a href="<?= APP_URL ?>/users" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

<?php
$errors = Session::flash('errors') ?? [];
$formData = Session::flash('form_data') ?? [];

if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= Security::e($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= APP_URL ?>/users/<?= $user ? 'edit/'.$user['id'] : 'create' ?>">
            <?= Security::csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre completo *</label>
                    <input type="text" name="full_name" class="form-control" required
                           value="<?= Security::e($formData['name'] ?? $user['name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Usuario *</label>
                    <input type="text" name="username" class="form-control" required
                           value="<?= Security::e($formData['username'] ?? $user['username'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required
                           value="<?= Security::e($formData['email'] ?? $user['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rol *</label>
                    <select name="role" class="form-select" required>
                        <option value="user" <?= ($formData['role'] ?? $user['role'] ?? '')==='user'?'selected':'' ?>>Usuario</option>
                        <option value="admin" <?= ($formData['role'] ?? $user['role'] ?? '')==='admin'?'selected':'' ?>>Administrador</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contraseña <?= $user ? '(dejar vacío para no cambiar)' : '*' ?></label>
                    <input type="password" name="password" class="form-control" <?= $user ? '' : 'required' ?>
                           minlength="8" placeholder="Mín. 8 caracteres, mayúscula, número y especial">
                    <small class="text-muted">Debe contener mayúscula, número y carácter especial.</small>
                </div>
                <?php if ($user): ?>
                <div class="col-md-6">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="activo" <?= ($user['status'] ?? 'activo')==='activo'?'selected':'' ?>>Activo</option>
                        <option value="inactivo" <?= ($user['status'] ?? '')==='inactivo'?'selected':'' ?>>Inactivo</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
                <a href="<?= APP_URL ?>/users" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>