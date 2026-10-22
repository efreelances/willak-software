<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title"><?= Security::e($task['title']) ?></h1>
        <p class="page-subtitle">Detalle de la tarea</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/tasks/edit/<?= $task['id'] ?>" class="btn btn-warning"><i class="bi bi-pencil me-1"></i> Editar</a>
        <a href="<?= APP_URL ?>/tasks" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Información general</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr><th width="30%">ID</th><td><?= $task['id'] ?></td></tr>
                    <tr><th>Título</th><td><strong><?= Security::e($task['title']) ?></strong></td></tr>
                    <tr><th>Cliente</th><td><?= Security::e($task['client_name'] ?? '-') ?></td></tr>
                    <tr><th>Tipo</th><td><span class="badge bg-secondary"><?= Security::e($task['type_name'] ?? 'Sin tipo') ?></span></td></tr>
                    <tr><th>Descripción</th><td><?= nl2br(Security::e($task['description'] ?: 'Sin descripción')) ?></td></tr>
                    <tr><th>Estado</th><td><span class="badge-status badge-<?= $task['status'] ?>"><?= ucfirst(str_replace('_',' ',$task['status'])) ?></span></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Vigencia</div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Fecha inicio</small>
                    <div class="fs-5"><?= date('d/m/Y', strtotime($task['start_date'])) ?></div>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Fecha fin</small>
                    <div class="fs-5 fw-bold"><?= date('d/m/Y', strtotime($task['end_date'])) ?></div>
                </div>
                <?php 
                $daysLeft = (strtotime($task['end_date']) - strtotime('today')) / 86400;
                $colorClass = $daysLeft < 0 ? 'danger' : ($daysLeft <= 7 ? 'warning' : 'success');
                ?>
                <div class="alert alert-<?= $colorClass ?> mb-0">
                    <i class="bi bi-clock-history"></i>
                    <strong><?= $daysLeft < 0 ? 'Vencida hace ' . abs((int)$daysLeft) . ' días' : (int)$daysLeft . ' días restantes' ?></strong>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><i class="bi bi-bell-fill text-warning"></i> Alertas configuradas</div>
            <div class="card-body">
                <?php 
                $reminders = json_decode($task['reminder_days'] ?? '[]', true) ?: [];
                if (empty($reminders)): ?>
                    <p class="text-muted mb-0">Sin alertas configuradas.</p>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($reminders as $r): ?>
                            <span class="badge bg-primary fs-6"><?= $r ?> días antes</span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">Registro</div>
            <div class="card-body small">
                <strong>Creado:</strong> <?= date('d/m/Y H:i', strtotime($task['created_at'])) ?><br>
                <strong>Actualizado:</strong> <?= date('d/m/Y H:i', strtotime($task['updated_at'])) ?>
            </div>
        </div>
    </div>
</div>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>