<?php
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/database.php';

$debug = getenv('WILLAK_DEBUG') === '1';
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');

spl_autoload_register(function ($class) {
    $file = BASE_PATH . 'core/' . $class . '.php';
    if (is_file($file)) require_once $file;
});
spl_autoload_register(function ($class) {
    $file = BASE_PATH . 'models/' . $class . '.php';
    if (is_file($file)) require_once $file;
});

if (PHP_SAPI !== 'cli' && class_exists('Session')) {
    Session::start();
}
