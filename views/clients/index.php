<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Clientes</h1>
        <p class="page-subtitle">Personas y empresas gestionadas</p>
    </div>
    <a href="<?= APP_URL ?>/clients/create" class="btn btn-accent">
        <i class="bi bi-plus-lg me-1"></i> Nuevo cliente
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
                    <tr>
                        <th>#</th><th>Nombre</th><th>Documento</th><th>Email</th>
                        <th>Teléfono</th><th>Estado</th>
                        <?php if (Auth::isAdmin()): ?><th>Propietario</th><?php endif; ?>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($clients)): ?>
                    <tr><td colspan="<?= Auth::isAdmin()?8:7 ?>" class="text-center text-muted py-4">No hay clientes.</td></tr>
                <?php else: foreach ($clients as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><strong><?= Security::e($c['name']) ?></strong></td>
                        <td><?= Security::e($c['document_type']) ?>: <?= Security::e($c['document'] ?: '-') ?></td>
                        <td><?= Security::e($c['email'] ?: '-') ?></td>
                        <td><?= Security::e($c['phone'] ?: '-') ?></td>
                        <td><span class="badge-status badge-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
                        <?php if (Auth::isAdmin()): ?><td><?= Security::e($c['owner_name']) ?></td><?php endif; ?>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= APP_URL ?>/clients/view/<?= $c['id'] ?>" class="btn btn-outline-primary"><i class="bi bi-eye"></i></a>
                                <a href="<?= APP_URL ?>/clients/edit/<?= $c['id'] ?>" class="btn btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                <?php if ($c['status']==='activo'): ?>
                                    <a href="<?= APP_URL ?>/clients/deactivate/<?= $c['id'] ?>" class="btn btn-outline-danger" data-confirm="¿Archivar este cliente?"><i class="bi bi-archive"></i></a>
                                <?php else: ?>
                                    <a href="<?= APP_URL ?>/clients/reactivate/<?= $c['id'] ?>" class="btn btn-outline-success" data-confirm="¿Reactivar este cliente?"><i class="bi bi-archive-restore"></i></a>
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