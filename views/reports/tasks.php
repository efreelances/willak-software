<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Reporte de Tareas</h1>
        <p class="page-subtitle">Filtros y visualización de tareas</p>
    </div>
    <div class="d-flex gap-2">
        <!-- Botones de exportación -->
        <div class="btn-group">
            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i> Exportar
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="<?= APP_URL ?>/reports/export-tasks?<?= http_build_query(array_merge($_GET, ['format' => 'csv'])) ?>">
                        <i class="bi bi-filetype-csv me-2 text-success"></i> CSV
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= APP_URL ?>/reports/export-tasks?<?= http_build_query(array_merge($_GET, ['format' => 'excel'])) ?>">
                        <i class="bi bi-filetype-xlsx me-2 text-primary"></i> Excel
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="#" onclick="window.print(); return false;">
                        <i class="bi bi-filetype-pdf me-2 text-danger"></i> PDF (Imprimir)
                    </a>
                </li>
            </ul>
        </div>

        <a href="<?= APP_URL ?>/reports" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="<?= APP_URL ?>/reports/tasks" class="row g-2">
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
            <div class="col-md-3">
                <select name="task_type_id" class="form-select">
                    <option value="">Todos los tipos</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($_GET['task_type_id'] ?? '')==$t['id']?'selected':'' ?>><?= Security::e($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="<?= Security::e($_GET['date_from'] ?? '') ?>" placeholder="Desde">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th><th>Título</th><th>Cliente</th><th>Tipo</th>
                        <th>Inicio</th><th>Fin</th><th>Estado</th><th>Responsable</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($tasks)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No hay tareas que mostrar.</td></tr>
                <?php else: foreach ($tasks as $t): ?>
                    <tr>
                        <td><?= $t['id'] ?></td>
                        <td><strong><?= Security::e($t['title']) ?></strong></td>
                        <td><?= Security::e($t['client_name'] ?? '-') ?></td>
                        <td><span class="badge bg-secondary"><?= Security::e($t['type_name'] ?? 'Sin tipo') ?></span></td>
                        <td><?= date('d/m/Y', strtotime($t['start_date'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($t['end_date'])) ?></td>
                        <td><span class="badge-status badge-<?= $t['status'] ?>"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span></td>
                        <td><?= Security::e($t['owner_name'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>