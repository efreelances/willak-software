<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Tipos de Tarea</h1>
        <p class="page-subtitle">Lista de Categorías</p>
    </div>
    <a href="<?= APP_URL ?>/tasktypes/create" class="btn btn-accent">
        <i class="bi bi-plus-lg me-1"></i> Nuevo tipo
    </a>
</div>

<?php if ($msg = Session::flash('success')): ?>
    <div class="alert alert-success alert-dismissible"><?= Security::e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>#</th><th>Nombre</th><th>Descripción</th><th>Estado</th><th class="text-end">Acciones</th></tr>
                </thead>
                <tbody>
                <?php if (empty($types)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No hay tipos registrados.</td></tr>
                <?php else: foreach ($types as $t): ?>
                    <tr>
                        <td><?= $t['id'] ?></td>
                        <td><strong><?= Security::e($t['name']) ?></strong></td>
                        <td><?= Security::e($t['description'] ?: '-') ?></td>
                        <td><span class="badge-status badge-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= APP_URL ?>/tasktypes/edit/<?= $t['id'] ?>" class="btn btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                <?php if ($t['status']==='activo'): ?>
                                    <a href="<?= APP_URL ?>/tasktypes/deactivate/<?= $t['id'] ?>" class="btn btn-outline-danger" data-confirm="¿Desactivar?"><i class="bi bi-archive"></i></a>
                                <?php else: ?>
                                    <a href="<?= APP_URL ?>/tasktypes/reactivate/<?= $t['id'] ?>" class="btn btn-outline-success" data-confirm="¿Reactivar?"><i class="bi bi-archive-restore"></i></a>
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