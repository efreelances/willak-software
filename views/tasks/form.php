<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title"><?= $task ? 'Editar tarea' : 'Nueva tarea' ?></h1>
        <p class="page-subtitle"><?= $task ? 'Modifica los datos de la tarea' : 'Registra una nueva tarea con vigencia' ?></p>
    </div>
    <a href="<?= APP_URL ?>/tasks" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

<?php $errors = Session::flash('errors') ?? []; if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= Security::e($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= APP_URL ?>/tasks/<?= $task ? 'edit/'.$task['id'] : 'create' ?>">
            <?= Security::csrfField() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Título *</label>
                    <input type="text" name="title" class="form-control" required
                           value="<?= Security::e($task['title'] ?? '') ?>"
                           placeholder="Ej: Renovación SOAT - Placa ABC-123">
                </div>

                <?php if (Auth::isAdmin() && !empty($users)): ?>
                <div class="col-md-4">
                    <label class="form-label">Propietario</label>
                    <select name="user_id" class="form-select">
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= (($task['user_id'] ?? Auth::id())==$u['id'])?'selected':'' ?>><?= Security::e($u['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="col-md-4">
                    <label class="form-label">Cliente *</label>
                    <select name="client_id" class="form-select" required>
                        <option value="">-- Seleccionar --</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (($task['client_id'] ?? '')==$c['id'])?'selected':'' ?>><?= Security::e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tipo de tarea</label>
                    <select name="task_type_id" class="form-select">
                        <option value="">-- Sin tipo --</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= (($task['task_type_id'] ?? '')==$t['id'])?'selected':'' ?>><?= Security::e($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Fecha inicio *</label>
                    <input type="date" name="start_date" class="form-control" required
                           value="<?= Security::e($task['start_date'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Fecha fin *</label>
                    <input type="date" name="end_date" class="form-control" required
                           value="<?= Security::e($task['end_date'] ?? '') ?>">
                    <small class="text-muted">Al vencer, se archiva automáticamente.</small>
                </div>

                <?php if ($task): ?>
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <?php foreach (['activo','pendiente','en_proceso','cancelado','archivado'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($task['status'] ?? '')===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control" rows="3"><?= Security::e($task['description'] ?? '') ?></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label"><i class="bi bi-bell-fill text-warning"></i> Días antes del vencimiento para alertar</label>
                    <div class="d-flex flex-wrap gap-2 p-3 bg-light rounded">
                        <?php 
                        $selectedDays = json_decode($task['reminder_days'] ?? '[]', true) ?: [];
                        $presetDays = [7, 15, 30, 60, 90, 120, 180, 365];
                        foreach ($presetDays as $d): 
                        ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="reminder_days[]" value="<?= $d ?>" id="day<?= $d ?>"
                                    <?= in_array($d, $selectedDays) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="day<?= $d ?>"><?= $d ?> días</label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <small class="text-muted">Ej: Para una homologación de 1 año, marca 30, 15 y 7 días.</small>
                </div>
            </div>

            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
                <a href="<?= APP_URL ?>/tasks" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>