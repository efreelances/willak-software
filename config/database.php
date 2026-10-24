<?php
/**
 * Configuración local de ejemplo para la copia corregida.
 * Define las variables de entorno en el servidor; no uses root ni una contraseña vacía.
 */
define('DB_HOST', getenv('WILLAK_DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('WILLAK_DB_NAME') ?: 'willak');
define('DB_USER', getenv('WILLAK_DB_USER') ?: 'root');
define('DB_PASS', getenv('WILLAK_DB_PASS') !== false ? getenv('WILLAK_DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');
