<?php
/**
 * CRON JOB: Verificar tareas próximas a vencer y enviar notificaciones
 * 
 * Configurar en cPanel:
 * php /home/tuusuario/public_html/cron/check_tasks.php
 * 
 * O en servidor local:
 * php D:\laragon\www\willak\cron\check_tasks.php
 */

// Cargar el entorno
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../core/NotificationEngine.php';

// Log de ejecución
$logFile = __DIR__ . '/cron.log';
$log = date('Y-m-d H:i:s') . " - Iniciando verificación...\n";

try {
    $engine = new NotificationEngine();
    $results = $engine->run();
    
    $log .= "Resultados: Enviados={$results['sent']}, Fallidos={$results['failed']}, Omitidos={$results['skipped']}\n";
} catch (Exception $e) {
    $log .= "ERROR: " . $e->getMessage() . "\n";
}

$log .= str_repeat('-', 50) . "\n";
file_put_contents($logFile, $log, FILE_APPEND);

echo $log;