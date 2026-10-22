<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Usuarios</h1>
        <p class="page-subtitle">Gestión de usuarios del sistema</p>
    </div>
    <a href="<?= APP_URL ?>/users/create" class="btn btn-accent">
        <i class="bi bi-plus-lg me-1"></i> Nuevo usuario
    </a>
</div>

<?php if ($msg = Session::flash('success')): ?>
    <div class="alert alert-success alert-dismissible"><?= Security::e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($msg = Session::flash('error')): ?>
    <div class="alert alert-danger alert-dismissible"><?= Security::e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último acceso</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No hay usuarios registrados.</td></tr>
                <?php else: foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><strong><?= Security::e($u['full_name']) ?></strong></td>
                        <td><?= Security::e($u['username']) ?></td>
                        <td><?= Security::e($u['email']) ?></td>
                        <td><span class="badge bg-<?= $u['role']==='admin'?'primary':'secondary' ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td><span class="badge-status badge-<?= $u['status'] ?>"><?= ucfirst($u['status']) ?></span></td>
                        <td><?= $u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : '<span class="text-muted">Nunca</span>' ?></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= APP_URL ?>/users/view/<?= $u['id'] ?>" class="btn btn-outline-primary" title="Ver"><i class="bi bi-eye"></i></a>
                                <a href="<?= APP_URL ?>/users/edit/<?= $u['id'] ?>" class="btn btn-outline-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                                <?php if ($u['status'] === 'activo'): ?>
                                    <a href="<?= APP_URL ?>/users/deactivate/<?= $u['id'] ?>" class="btn btn-outline-danger" data-confirm="¿Desactivar este usuario?" title="Desactivar"><i class="bi bi-archive"></i></a>
                                <?php else: ?>
                                    <a href="<?= APP_URL ?>/users/reactivate/<?= $u['id'] ?>" class="btn btn-outline-success" data-confirm="¿Reactivar este usuario?" title="Reactivar"><i class="bi bi-archive-restore"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>