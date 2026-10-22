<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Reportes</h1>
        <p class="page-subtitle">Genera reportes y estadísticas del sistema</p>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-check2-square display-4 text-primary mb-3"></i>
                <h5 class="card-title">Reporte de Tareas</h5>
                <p class="card-text">Visualiza tareas por estado, cliente, tipo y fecha</p>
                <a href="<?= APP_URL ?>/reports/tasks" class="btn btn-primary">
                    <i class="bi bi-eye me-1"></i> Ver reporte
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-people display-4 text-success mb-3"></i>
                <h5 class="card-title">Reporte de Clientes</h5>
                <p class="card-text">Lista de clientes con sus tareas asociadas</p>
                <a href="<?= APP_URL ?>/reports/clients" class="btn btn-success">
                    <i class="bi bi-eye me-1"></i> Ver reporte
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="bi bi-clock-history display-4 text-warning mb-3"></i>
                <h5 class="card-title">Tareas Vencidas</h5>
                <p class="card-text">Reporte de tareas que ya vencieron</p>
                <a href="<?= APP_URL ?>/reports/tasks?status=archivado" class="btn btn-warning">
                    <i class="bi bi-eye me-1"></i> Ver reporte
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                    <i class="bi bi-download me-2"></i>Exportar
            </div>
                <div class="card-body">
                        <p>Los reportes se pueden exportar a:</p>
                        <ul class="mb-0">
                            <li><strong>CSV</strong> - Compatible con Excel, Google Sheets</li>
                            <li><strong>Excel</strong> - Formato .xls nativo</li>
                            <li><strong>PDF</strong> - Usando la función de impresión del navegador</li>
                        </ul>
                        <small class="text-muted">Los filtros aplicados se mantienen en la exportación.</small>
                </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-download me-2"></i>Exportar
            </div>
            <div class="card-body">
                <p>Próximamente podrás exportar a:</p>
                <ul class="mb-0">
                    <li>PDF</li>
                    <li>Excel (XLSX)</li>
                    <li>CSV</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>