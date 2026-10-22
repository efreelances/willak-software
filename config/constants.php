<?php
// Rutas base
define('BASE_PATH', dirname(__DIR__) . '/');
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));
define('APP_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . BASE_URL);

// App
define('APP_NAME', 'Willak');
define('APP_VERSION', '1.0.0');

// Sesión
define('SESSION_LIFETIME', 1800); // 30 min
define('REMEMBER_ME_DAYS', 7);

// Password policy
define('PWD_MIN_LENGTH', 8);
define('PWD_EXPIRY_DAYS', 180); // 6 meses

// Upload
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Timezone
date_default_timezone_set('America/Lima');