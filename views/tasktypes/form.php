<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title"><?= $type ? 'Editar tipo' : 'Nuevo tipo de tarea' ?></h1>
    </div>
    <a href="<?= APP_URL ?>/tasktypes" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

<?php $errors = Session::flash('errors') ?? []; if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= Security::e($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= APP_URL ?>/tasktypes/<?= $type ? 'edit/'.$type['id'] : 'create' ?>">
            <?= Security::csrfField() ?>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="name" class="form-control" required
                           value="<?= Security::e($type['name'] ?? '') ?>"
                           placeholder="Ej: SOAT, Homologación, Contrato">
                </div>
                <?php if ($type): ?>
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="activo" <?= $type['status']==='activo'?'selected':'' ?>>Activo</option>
                        <option value="inactivo" <?= $type['status']==='inactivo'?'selected':'' ?>>Inactivo</option>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control" rows="3"><?= Security::e($type['description'] ?? '') ?></textarea>
                </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
            <a href="<?= APP_URL ?>/tasktypes" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>