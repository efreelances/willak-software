<?php
/**
 * Comprobación segura de acceso a la base de datos y de la cola.
 * No envía notificaciones. Para producción, use cron/check_tasks.php.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Acceso denegado. Ejecuta esta comprobación desde CLI.\n");
}

require_once __DIR__ . '/config/app.php';

try {
    $db = Database::getInstance()->getConnection();
    $queue = $db->query(
        "SELECT status, COUNT(*) AS total
         FROM notification_queue
         GROUP BY status"
    )->fetchAll(PDO::FETCH_ASSOC);

    echo "Willak: comprobación de cola (modo seguro; no envía mensajes)\n";
    foreach ($queue as $row) {
        echo $row['status'] . ': ' . (int)$row['total'] . "\n";
    }
    if (!$queue) {
        echo "La cola está vacía.\n";
    }
} catch (Throwable $e) {
    error_log('[Willak test] ' . $e->getMessage());
    fwrite(STDERR, "No se pudo comprobar la cola. Revisa los logs del servidor.\n");
    exit(1);
}
