<?php
$isAdmin = Auth::isAdmin();
$current = $currentPage ?? '';

// Obtener colores de configuración
$settings = [];
try {
    $stmt = Database::getInstance()->getConnection()->query("SELECT setting_key, setting_value FROM app_settings WHERE setting_key IN ('primary_color', 'accent_color')");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Si no existe la tabla, usar valores por defecto
}

$primaryColor = $settings['primary_color'] ?? '#0d47a1';
$accentColor = $settings['accent_color'] ?? '#ff6f00';
$appName = getAppName(); // ← Nombre dinámico desde BD
?>

<aside class="sidebar" id="sidebar" style="--willak-sidebar-bg: <?= $primaryColor ?>;">
    <div class="sidebar-header">
        <a href="<?= APP_URL ?>/dashboard" class="brand">
            <i class="bi bi-shield-check brand-icon" style="color: <?= $accentColor ?>;"></i>
            <span class="brand-text"><?= Security::e($appName) ?></span>
        </a>
    </div>
    <nav class="sidebar-nav">
        <a href="<?= APP_URL ?>/dashboard" class="nav-link <?= $current==='dashboard'?'active':'' ?>">
            <i class="bi bi-speedometer2"></i><span>Dashboard</span>
        </a>
        <a href="<?= APP_URL ?>/tasks" class="nav-link <?= $current==='tasks'?'active':'' ?>">
            <i class="bi bi-check2-square"></i><span>Tareas</span>
        </a>
        <a href="<?= APP_URL ?>/tasktypes" class="nav-link <?= $current==='tasktypes'?'active':'' ?>">
            <i class="bi bi-tags"></i><span>Tipos de Tarea</span>
        </a>
        <a href="<?= APP_URL ?>/clients" class="nav-link <?= $current==='clients'?'active':'' ?>">
            <i class="bi bi-people"></i><span>Clientes</span>
        </a>
        <?php if ($isAdmin): ?>
        <a href="<?= APP_URL ?>/users" class="nav-link <?= $current==='users'?'active':'' ?>">
            <i class="bi bi-person-badge"></i><span>Usuarios</span>
        </a>
        <?php endif; ?>
        <a href="<?= APP_URL ?>/reports" class="nav-link <?= $current==='reports'?'active':'' ?>">
            <i class="bi bi-file-earmark-bar-graph"></i><span>Reportes</span>
        </a>
        <a href="<?= APP_URL ?>/stats" class="nav-link <?= $current==='stats'?'active':'' ?>">
            <i class="bi bi-graph-up"></i><span>Estadísticas</span>
        </a>
        <a href="<?= APP_URL ?>/settings" class="nav-link <?= $current==='settings'?'active':'' ?>">
            <i class="bi bi-gear"></i><span>Configuración</span>
        </a>
    </nav>
</aside>

<style>
/* Inyectar colores dinámicos */
.sidebar {
    background: <?= $primaryColor ?> !important;
}
.brand-icon {
    color: <?= $accentColor ?> !important;
}
.nav-link.active {
    background: <?= $accentColor ?> !important;
}
</style>