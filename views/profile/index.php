<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Mi Perfil</h1>
        <p class="page-subtitle">Actualiza tu información personal y contraseña</p>
    </div>
</div>

<?php if ($msg = Session::flash('success')): ?>
    <div class="alert alert-success alert-dismissible"><?= Security::e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($msg = Session::flash('error')): ?>
    <div class="alert alert-danger alert-dismissible"><?= Security::e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php $errors = Session::flash('errors') ?? []; if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= Security::e($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person me-2"></i>Información personal
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/profile">
                    <?= Security::csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre completo *</label>
                            <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($user['full_name'] ?? Session::get('user_name', 'Administrador')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Usuario *</label>
                            <input type="text" name="username" class="form-control" required
                                value="<?= Security::e($user['username'] ?: Session::get('user_username', '')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required
                                value="<?= Security::e($user['email'] ?: Session::get('user_email', '')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nueva contraseña <small class="text-muted">(dejar vacío para no cambiar)</small></label>
                            <input type="password" name="password" class="form-control"
                                minlength="8" placeholder="Mín. 8 caracteres, mayúscula, número y especial">
                            <small class="text-muted">Debe contener mayúscula, número y carácter especial.</small>
                        </div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Guardar cambios
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Información de la cuenta
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0 small">
                    <tr>
                        <th>Rol</th>
                        <td><span class="badge bg-<?= $user['role']==='admin'?'primary':'secondary' ?>"><?= ucfirst($user['role']) ?></span></td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td><span class="badge-status badge-<?= $user['status'] ?? 'activo' ?>"><?= ucfirst($user['status'] ?? 'activo') ?></span></td>
                    </tr>
                    <tr>
                        <th>Último acceso</th>
                        <td>
                            <?php if (!empty($user['last_login'])): ?>
                                <?= date('d/m/Y H:i', strtotime($user['last_login'])) ?>
                            <?php else: ?>
                                <span class="text-muted">Nunca</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Cuenta creada</th>
                        <td>
                            <?php if (!empty($user['created_at'])): ?>
                                <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                            <?php else: ?>
                                <span class="text-muted">Sin registro</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-shield-lock me-2"></i>Seguridad
            </div>
            <div class="card-body">
                <p class="small mb-2">
                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                    Contraseña segura
                </p>
                <p class="small mb-0 text-muted">
                    Cambia tu contraseña regularmente para mantener tu cuenta segura.
                </p>
            </div>
        </div>
    </div>
</div>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>