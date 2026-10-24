<?php
/**
 * Procesador de la cola de notificaciones.
 * Ejecución recomendada: php /ruta/absoluta/willak/cron/check_tasks.php
 * Este script solo se ejecuta desde CLI, no desde el navegador.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Acceso denegado.\n");
}

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../core/NotificationEngine.php';

$logFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'willak-cron.log';
$startedAt = date('Y-m-d H:i:s');
$log = $startedAt . " - Inicio del procesador de notificaciones.\n";

try {
    $db = Database::getInstance()->getConnection();
    $lock = $db->query("SELECT GET_LOCK('willak_notification_queue', 0)")->fetchColumn();
    if ((int)$lock !== 1) {
        $log .= "Omitido: ya existe otra ejecución activa.\n";
    } else {
        try {
            $engine = new NotificationEngine();
            $results = $engine->run(50);
            $log .= "Procesadas={$results['processed']}; enviadas={$results['sent']}; fallidas={$results['failed']}; omitidas={$results['skipped']}.\n";
        } finally {
            $db->query("SELECT RELEASE_LOCK('willak_notification_queue')");
        }
    }
} catch (Throwable $e) {
    $log .= "ERROR: " . $e->getMessage() . "\n";
    error_log('[Willak cron] ' . $e->getMessage());
}
$log .= str_repeat('-', 48) . "\n";
@file_put_contents($logFile, $log, FILE_APPEND | LOCK_EX);
echo $log;
