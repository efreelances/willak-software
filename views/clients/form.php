<?php include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="page-header">
    <div>
        <h1 class="page-title"><?= $client ? 'Editar cliente' : 'Nuevo cliente' ?></h1>
        <p class="page-subtitle"><?= $client ? 'Modifica los datos del cliente' : 'Registra un nuevo cliente' ?></p>
    </div>
    <a href="<?= APP_URL ?>/clients" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
</div>

<?php $errors = Session::flash('errors') ?? []; if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= Security::e($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= APP_URL ?>/clients/<?= $client ? 'edit/'.$client['id'] : 'create' ?>">
            <?= Security::csrfField() ?>
            <div class="row g-3">
                <?php if (Auth::isAdmin() && !empty($users)): ?>
                <div class="col-md-6">
                    <label class="form-label">Propietario (usuario) *</label>
                    <select name="user_id" class="form-select" required>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= (($client['user_id'] ?? Auth::id()) == $u['id']) ? 'selected' : '' ?>>
                                <?= Security::e($u['full_name']) ?> (<?= Security::e($u['username']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-6">
                    <label class="form-label">Nombre / Razón social *</label>
                    <input type="text" name="name" class="form-control" required
                           value="<?= Security::e($client['name'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo doc.</label>
                    <select name="document_type" class="form-select">
                        <?php foreach (['DNI','RUC','PASAPORTE','OTRO'] as $t): ?>
                            <option value="<?= $t ?>" <?= (($client['document_type'] ?? 'DNI')===$t)?'selected':'' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nro. documento</label>
                    <input type="text" name="document" class="form-control"
                           value="<?= Security::e($client['document'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= Security::e($client['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= Security::e($client['phone'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Dirección</label>
                    <input type="text" name="address" class="form-control"
                           value="<?= Security::e($client['address'] ?? '') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Notas</label>
                    <textarea name="notes" class="form-control" rows="3"><?= Security::e($client['notes'] ?? '') ?></textarea>
                </div>
                <?php if ($client): ?>
                <div class="col-md-6">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="activo" <?= $client['status']==='activo'?'selected':'' ?>>Activo</option>
                        <option value="inactivo" <?= $client['status']==='inactivo'?'selected':'' ?>>Inactivo</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            <hr>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Guardar</button>
                <a href="<?= APP_URL ?>/clients" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>