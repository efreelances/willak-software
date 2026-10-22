<?php $pageTitle = 'Error 404'; include BASE_PATH . 'views/layouts/header.php'; ?>
<div class="text-center py-5">
    <i class="bi bi-exclamation-triangle-fill display-1 text-warning"></i>
    <h2 class="mt-3">Página no encontrada</h2>
    <p class="text-muted">El recurso solicitado no existe.</p>
    <a href="<?= APP_URL ?>/dashboard" class="btn btn-primary">Volver al inicio</a>
</div>
<?php include BASE_PATH . 'views/layouts/footer.php'; ?>