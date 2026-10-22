<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Bienvenido, <?= Security::e(Auth::user()['name']) ?> 👋</p>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Total tareas</div>
                <div class="stat-value"><?= $stats['total_tasks'] ?></div>
                <i class="bi bi-check2-square position-absolute top-0 end-0 m-3 fs-2 text-primary opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="stat-label">Pendientes</div>
                <div class="stat-value"><?= $stats['pending'] ?></div>
                <i class="bi bi-hourglass-split position-absolute top-0 end-0 m-3 fs-2 opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card accent">
            <div class="card-body">
                <div class="stat-label">Por vencer (7 días)</div>
                <div class="stat-value"><?= $stats['due_soon'] ?></div>
                <i class="bi bi-bell position-absolute top-0 end-0 m-3 fs-2 opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="stat-label">Vencidas</div>
                <div class="stat-value"><?= $stats['overdue'] ?></div>
                <i class="bi bi-exclamation-triangle position-absolute top-0 end-0 m-3 fs-2 opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-calendar-event me-2"></i>Próximos vencimientos</span>
                <a href="<?= APP_URL ?>/tasks" class="btn btn-sm btn-outline-primary">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Tarea</th><th>Cliente</th><th>Vence</th><th>Días restantes</th><th>Estado</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($upcoming)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Sin tareas próximas.</td></tr>
                        <?php else: foreach ($upcoming as $t): 
                            $days = (strtotime($t['end_date']) - strtotime('today')) / 86400;
                            $badge = $days <= 3 ? 'danger' : ($days <= 7 ? 'warning' : 'success');
                        ?>
                            <tr>
                                <td><strong><?= Security::e($t['title']) ?></strong></td>
                                <td><?= Security::e($t['client_name']) ?></td>
                                <td><?= date('d/m/Y', strtotime($t['end_date'])) ?></td>
                                <td><span class="badge bg-<?= $badge ?>"><?= (int)$days ?> días</span></td>
                                <td><span class="badge-status badge-<?= $t['status'] ?>"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card success">
            <div class="card-body text-center">
                <i class="bi bi-people-fill display-4 text-success"></i>
                <div class="stat-value mt-2"><?= $stats['total_clients'] ?></div>
                <div class="stat-label">Clientes activos</div>
                <a href="<?= APP_URL ?>/clients" class="btn btn-sm btn-outline-success mt-3">Gestionar</a>
            </div>
        </div>
    </div>
</div>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>