<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Detalle de usuario</h1>
        <p class="page-subtitle"><?= Security::e($user['full_name'] ?? 'Usuario') ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/users/edit/<?= $user['id'] ?>" class="btn btn-warning"><i class="bi bi-pencil me-1"></i> Editar</a>
        <a href="<?= APP_URL ?>/users" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Información general</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th width="40%">ID</th><td><?= (int)($user['id'] ?? 0) ?></td></tr>
                    <tr><th>Nombre</th><td><?= Security::e($user['full_name'] ?? 'Sin nombre') ?></td></tr>
                    <tr><th>Usuario</th><td><?= Security::e($user['username'] ?? '-') ?></td></tr>
                    <tr><th>Email</th><td><?= Security::e($user['email'] ?? '-') ?></td></tr>
                    <tr><th>Rol</th><td><span class="badge bg-<?= ($user['role'] ?? '')==='admin'?'primary':'secondary' ?>"><?= ucfirst($user['role'] ?? 'user') ?></span></td></tr>
                    <tr>
                        <th>Estado</th>
                        <td><span class="badge-status badge-<?= $user['status'] ?? 'activo' ?>"><?= ucfirst($user['status'] ?? 'activo') ?></span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Seguridad y actividad</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th width="40%">2FA</th>
                        <td><?= !empty($user['totp_enabled']) ? '<span class="text-success">Activo</span>' : '<span class="text-muted">No activo</span>' ?></td>
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
                        <th>Password cambiado</th>
                        <td>
                            <?php if (!empty($user['password_changed_at'])): ?>
                                <?= date('d/m/Y H:i', strtotime($user['password_changed_at'])) ?>
                            <?php else: ?>
                                <span class="text-muted">Sin registro</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Creado</th>
                        <td>
                            <?php if (!empty($user['created_at'])): ?>
                                <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                            <?php else: ?>
                                <span class="text-muted">Sin registro</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Actualizado</th>
                        <td>
                            <?php if (!empty($user['updated_at'])): ?>
                                <?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?>
                            <?php else: ?>
                                <span class="text-muted">Sin registro</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>