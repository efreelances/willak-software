<?php
$theme = $_COOKIE['willak_theme'] ?? 'light';
$user = Auth::user();
$currentPage = $currentPage ?? '';
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::e(getAppName()) ?> - <?= $pageTitle ?? 'Dashboard' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="app-wrapper">
    <!-- Sidebar -->
    <?php include BASE_PATH . 'views/layouts/sidebar.php'; ?>

    <!-- Main content -->
    <div class="main-content">
        <!-- Topbar -->
        <nav class="topbar navbar navbar-expand-lg">
            <div class="container-fluid">
                <button class="btn btn-link d-lg-none" id="sidebarToggle">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <div class="ms-auto d-flex align-items-center gap-3">
                    <button class="btn btn-icon" id="themeToggle" title="Cambiar tema">
                        <i class="bi bi-moon-stars-fill"></i>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-user dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>
                            <?= Security::e($user['name'] ?? '') ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small text-muted">Rol: <?= ucfirst($user['role'] ?? '') ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/profile"><i class="bi bi-person me-2"></i>Mi perfil</a></li>
                            <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/logout"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <div class="content-wrapper p-4">