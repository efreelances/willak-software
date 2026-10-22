<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Tareas</h1>
        <p class="page-subtitle">Gestión de tareas con vigencia y alertas</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/tasktypes" class="btn btn-outline-secondary">
            <i class="bi bi-tags me-1"></i> Tipos
        </a>
        <a href="<?= APP_URL ?>/tasks/create" class="btn btn-accent">
            <i class="bi bi-plus-lg me-1"></i> Nueva tarea
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= APP_URL ?>/tasks" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="🔍 Buscar..." value="<?= Security::e($_GET['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Todos los estados</option>
                    <?php foreach (['activo','pendiente','en_proceso','cancelado','archivado'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($_GET['status'] ?? '')===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="client_id" class="form-select">
                    <option value="">Todos los clientes</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($_GET['client_id'] ?? '')==$c['id']?'selected':'' ?>><?= Security::e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="task_type_id" class="form-select">
                    <option value="">Todos los tipos</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($_GET['task_type_id'] ?? '')==$t['id']?'selected':'' ?>><?= Security::e($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>
    </div>
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
                        <th>#</th><th>Título</th><th>Cliente</th><th>Tipo</th>
                        <th>Inicio</th><th>Fin</th><th>Alertas</th><th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($tasks)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No hay tareas registradas.</td></tr>
                <?php else: foreach ($tasks as $t):
                    $daysLeft = (strtotime($t['end_date']) - strtotime('today')) / 86400;
                    $alertClass = $daysLeft < 0 ? 'text-danger' : ($daysLeft <= 7 ? 'text-warning' : 'text-success');
                    $reminders = json_decode($t['reminder_days'] ?? '[]', true) ?: [];
                ?>
                    <tr>
                        <td><?= $t['id'] ?></td>
                        <td><strong><?= Security::e($t['title']) ?></strong></td>
                        <td><?= Security::e($t['client_name'] ?? '-') ?></td>
                        <td><span class="badge bg-secondary"><?= Security::e($t['type_name'] ?? 'Sin tipo') ?></span></td>
                        <td><?= date('d/m/Y', strtotime($t['start_date'])) ?></td>
                        <td class="<?= $alertClass ?>"><strong><?= date('d/m/Y', strtotime($t['end_date'])) ?></strong></td>
                        <td>
                            <?php foreach ($reminders as $r): ?>
                                <span class="badge bg-info"><?= $r ?>d</span>
                            <?php endforeach; ?>
                            <?php if (empty($reminders)): ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td><span class="badge-status badge-<?= $t['status'] ?>"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= APP_URL ?>/tasks/view/<?= $t['id'] ?>" class="btn btn-outline-primary" title="Ver"><i class="bi bi-eye"></i></a>
                                <a href="<?= APP_URL ?>/tasks/edit/<?= $t['id'] ?>" class="btn btn-outline-warning" title="Editar"><i class="bi bi-pencil"></i></a>
                                <?php if ($t['status'] !== 'archivado' && $t['status'] !== 'cancelado'): ?>
                                    <a href="<?= APP_URL ?>/tasks/cancel/<?= $t['id'] ?>" class="btn btn-outline-danger" data-confirm="¿Cancelar esta tarea?" title="Cancelar"><i class="bi bi-x-circle"></i></a>
                                    <a href="<?= APP_URL ?>/tasks/archive/<?= $t['id'] ?>" class="btn btn-outline-secondary" data-confirm="¿Archivar esta tarea?" title="Archivar"><i class="bi bi-archive"></i></a>
                                <?php else: ?>
                                    <a href="<?= APP_URL ?>/tasks/reactivate/<?= $t['id'] ?>" class="btn btn-outline-success" data-confirm="¿Reactivar esta tarea?" title="Reactivar"><i class="bi bi-arrow-counterclockwise"></i></a>
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