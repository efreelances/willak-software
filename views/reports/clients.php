<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title">Reporte de Clientes</h1>
        <p class="page-subtitle">Lista de clientes y sus tareas</p>
    </div>
    <div class="d-flex gap-2">
        <!-- Botones de exportación -->
        <div class="btn-group">
            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-download me-1"></i> Exportar
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="<?= APP_URL ?>/reports/export-clients?<?= http_build_query(array_merge($_GET, ['format' => 'csv'])) ?>">
                        <i class="bi bi-filetype-csv me-2 text-success"></i> CSV
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="<?= APP_URL ?>/reports/export-clients?<?= http_build_query(array_merge($_GET, ['format' => 'excel'])) ?>">
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

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th><th>Nombre</th><th>Documento</th><th>Email</th>
                        <th>Teléfono</th><th>Total Tareas</th><th>Propietario</th><th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($clients)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No hay clientes registrados.</td></tr>
                <?php else: foreach ($clients as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><strong><?= Security::e($c['name']) ?></strong></td>
                        <td><?= Security::e($c['document_type']) ?>: <?= Security::e($c['document'] ?: '-') ?></td>
                        <td><?= Security::e($c['email'] ?: '-') ?></td>
                        <td><?= Security::e($c['phone'] ?: '-') ?></td>
                        <td><span class="badge bg-primary"><?= (int)$c['total_tasks'] ?></span></td>
                        <td><?= Security::e($c['owner_name'] ?? '-') ?></td>
                        <td><span class="badge-status badge-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>