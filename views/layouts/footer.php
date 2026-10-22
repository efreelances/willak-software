        </div>
        <footer class="app-footer text-center py-3">
            <small>&copy; <?= date('Y') ?> <?= Security::e(getAppName()) ?> v1.0.0 — Todos los derechos reservados</small>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/app.js"></script>
<?php if (isset($extraJs)) echo $extraJs; ?>
</body>
</html>