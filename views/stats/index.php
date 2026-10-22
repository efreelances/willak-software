<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Estadísticas</h1>
        <p class="page-subtitle">Visualización de datos y métricas del sistema</p>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card">
            <div class="card-body">
                <div class="stat-label">Total Tareas</div>
                <div class="stat-value"><?= array_sum($chartData['tasksByStatus']) ?></div>
                <i class="bi bi-check2-square position-absolute top-0 end-0 m-3 fs-2 text-primary opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="stat-label">Clientes Activos</div>
                <div class="stat-value"><?= $totalClients ?></div>
                <i class="bi bi-people position-absolute top-0 end-0 m-3 fs-2 text-success opacity-25"></i>
            </div>
        </div>
    </div>
    <?php if ($isAdmin): ?>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card accent">
            <div class="card-body">
                <div class="stat-label">Usuarios Activos</div>
                <div class="stat-value"><?= $totalUsers ?></div>
                <i class="bi bi-person-badge position-absolute top-0 end-0 m-3 fs-2 opacity-25"></i>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="col-md-3 col-sm-6">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="stat-label">Tareas Vencidas</div>
                <div class="stat-value"><?= $chartData['timeline']['overdue'] ?></div>
                <i class="bi bi-exclamation-triangle position-absolute top-0 end-0 m-3 fs-2 text-danger opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Gráfico de tareas por estado -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pie-chart me-2"></i>Tareas por Estado
            </div>
            <div class="card-body">
                <canvas id="tasksByStatusChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfico de tareas por tipo -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart me-2"></i>Tareas por Tipo
            </div>
            <div class="card-body">
                <canvas id="tasksByTypeChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Gráfico de timeline -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i>Estado de Vigencia
            </div>
            <div class="card-body">
                <canvas id="timelineChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Top clientes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-trophy me-2"></i>Top 10 Clientes con más Tareas
            </div>
            <div class="card-body">
                <canvas id="tasksByClientChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Tareas por mes -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-graph-up me-2"></i>Tareas Creadas por Mes (Últimos 6 meses)
            </div>
            <div class="card-body">
                <canvas id="tasksByMonthChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Colores del tema
const colors = {
    primary: '#0d47a1',
    accent: '#ff6f00',
    success: '#198754',
    warning: '#ffc107',
    danger: '#dc3545',
    info: '#0dcaf0',
    secondary: '#6c757d'
};

// 1. Tareas por Estado (Doughnut)
const tasksByStatusCtx = document.getElementById('tasksByStatusChart').getContext('2d');
new Chart(tasksByStatusCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($chartData['tasksByStatus'])) ?>,
        datasets: [{
            data: <?= json_encode(array_values($chartData['tasksByStatus'])) ?>,
            backgroundColor: [colors.primary, colors.warning, colors.info, colors.danger, colors.secondary]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// 2. Tareas por Tipo (Bar)
const tasksByTypeCtx = document.getElementById('tasksByTypeChart').getContext('2d');
new Chart(tasksByTypeCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($chartData['tasksByType'], 'name')) ?>,
        datasets: [{
            label: 'Total',
            data: <?= json_encode(array_column($chartData['tasksByType'], 'total')) ?>,
            backgroundColor: colors.primary
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// 3. Timeline (Pie)
const timelineCtx = document.getElementById('timelineChart').getContext('2d');
new Chart(timelineCtx, {
    type: 'pie',
    data: {
        labels: ['Vencidas', 'Por vencer (7 días)', 'Al día'],
        datasets: [{
            data: [
                <?= $chartData['timeline']['overdue'] ?>,
                <?= $chartData['timeline']['dueSoon'] ?>,
                <?= $chartData['timeline']['onTime'] ?>
            ],
            backgroundColor: [colors.danger, colors.warning, colors.success]
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// 4. Top Clientes (Horizontal Bar)
const tasksByClientCtx = document.getElementById('tasksByClientChart').getContext('2d');
new Chart(tasksByClientCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($chartData['tasksByClient'], 'name')) ?>,
        datasets: [{
            label: 'Tareas',
            data: <?= json_encode(array_column($chartData['tasksByClient'], 'total')) ?>,
            backgroundColor: colors.accent
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// 5. Tareas por Mes (Line)
const tasksByMonthCtx = document.getElementById('tasksByMonthChart').getContext('2d');
new Chart(tasksByMonthCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($chartData['tasksByMonth'], 'month')) ?>,
        datasets: [{
            label: 'Tareas creadas',
            data: <?= json_encode(array_column($chartData['tasksByMonth'], 'total')) ?>,
            borderColor: colors.primary,
            backgroundColor: colors.primary + '20',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>

<?php include BASE_PATH . 'views/layouts/footer.php'; ?>