<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title"><?= Security::e($client['name']) ?></h1>
        <p class="page-subtitle">Detalle del cliente</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/clients/edit/<?= $client['id'] ?>" class="btn btn-warning"><i class="bi bi-pencil me-1"></i> Editar</a>
        <a href="<?= APP_URL ?>/clients" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Información</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th width="40%">ID</th><td><?= $client['id'] ?></td></tr>
                    <tr><th>Nombre</th><td><?= Security::e($client['name']) ?></td></tr>
                    <tr><th>Documento</th><td><?= Security::e($client['document_type']) ?>: <?= Security::e($client['document'] ?: '-') ?></td></tr>
                    <tr><th>Email</th><td><?= Security::e($client['email'] ?: '-') ?></td></tr>
                    <tr><th>Teléfono</th><td><?= Security::e($client['phone'] ?: '-') ?></td></tr>
                    <tr><th>Dirección</th><td><?= Security::e($client['address'] ?: '-') ?></td></tr>
                    <tr><th>Estado</th><td><span class="badge-status badge-<?= $client['status'] ?>"><?= ucfirst($client['status']) ?></span></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Resumen</div>
            <div class="card-body">
                <div class="stat-card accent p-3 rounded mb-3">
                    <div class="stat-label">Tareas asociadas</div>
                    <div class="stat-value"><?= (int)$taskCount ?></div>
                </div>
                <p class="text-muted small mb-0"><strong>Notas:</strong><br><?= nl2br(Security::e($client['notes'] ?: 'Sin notas.')) ?></p>
                <hr>
                <small class="text-muted">
                    Creado: <?= date('d/m/Y H:i', strtotime($client['created_at'])) ?><br>
                    Actualizado: <?= date('d/m/Y H:i', strtotime($client['updated_at'])) ?>
                </small>
            </div>
        </div>
    </div>
</div>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>