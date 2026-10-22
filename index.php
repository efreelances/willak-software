<?php
require_once __DIR__ . '/config/app.php';
require_once BASE_PATH . 'core/helpers.php';        // ← Helpers primero (son utilidades básicas)
require_once BASE_PATH . 'core/Auth.php';
require_once BASE_PATH . 'core/Router.php';

Router::dispatch();