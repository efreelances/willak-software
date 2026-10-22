<?php
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/database.php';

// Errores (en producción poner en 0)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoload de core
spl_autoload_register(function ($class) {
    $file = BASE_PATH . 'core/' . $class . '.php';
    if (file_exists($file)) require_once $file;
});

// Autoload de modelos
spl_autoload_register(function ($class) {
    $file = BASE_PATH . 'models/' . $class . '.php';
    if (file_exists($file)) require_once $file;
});

// Iniciar sesión segura
Session::start();