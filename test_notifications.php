<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Session.php';
require_once __DIR__ . '/core/Security.php';
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/core/NotificationEngine.php';

echo "<h1> Prueba del Motor de Notificaciones</h1>";
echo "<pre>";

try {
    $engine = new NotificationEngine();
    $results = $engine->run();
    
    echo "✅ Motor ejecutado correctamente\n";
    echo " Resultados:\n";
    echo "   - Enviados: {$results['sent']}\n";
    echo "   - Fallidos: {$results['failed']}\n";
    echo "   - Omitidos: {$results['skipped']}\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

echo "</pre>";