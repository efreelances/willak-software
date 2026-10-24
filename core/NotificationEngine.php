<?php
require_once BASE_PATH . 'models/Notification.php';
require_once BASE_PATH . 'core/channels/EmailChannel.php';
require_once BASE_PATH . 'core/channels/WhatsAppChannel.php';
require_once BASE_PATH . 'core/channels/TelegramChannel.php';

/**
 * Procesa únicamente los recordatorios que ya fueron programados en notification_queue.
 * No crea avisos duplicados ni consulta tablas ajenas al esquema SQL de Willak.
 */
class NotificationEngine {
    private $model;

    public function __construct() {
        $this->model = new Notification();
    }

    public function run($limit = 50) {
        $result = ['sent' => 0, 'failed' => 0, 'skipped' => 0, 'processed' => 0];
        $items = $this->model->getDueQueue($limit);
        $globalSettings = $this->model->getGlobalSettings();

        foreach ($items as $item) {
            $result['processed']++;
            try {
                $globalActiveKey = 'notification_' . $item['channel'] . '_active';
                if (array_key_exists($globalActiveKey, $globalSettings) && (string)$globalSettings[$globalActiveKey] !== '1') {
                    $this->model->markCancelled($item['id']);
                    $result['skipped']++;
                    continue;
                }
                $userSettings = $this->model->getUserSettings($item['user_id']);
                $this->assertChannelEnabled($item['channel'], $userSettings);
                $sendResult = $this->sendItem($item, $userSettings, $globalSettings);

                if (!is_array($sendResult) || empty($sendResult['success'])) {
                    $error = is_array($sendResult) ? ($sendResult['error'] ?? 'El proveedor no confirmó el envío.') : 'Respuesta inválida del canal.';
                    $this->model->markFailed($item, $error);
                    $result['failed']++;
                    continue;
                }

                $this->model->markSent($item);
                $result['sent']++;
            } catch (Throwable $e) {
                // Guardar el fallo y continuar con el resto de la cola.
                try {
                    $this->model->markFailed($item, $e->getMessage());
                } catch (Throwable $logError) {
                    error_log('[Willak notifications] No se pudo actualizar la cola #' . (int)$item['id'] . ': ' . $logError->getMessage());
                }
                error_log('[Willak notifications] Falló cola #' . (int)$item['id'] . ': ' . $e->getMessage());
                $result['failed']++;
            }
        }
        return $result;
    }

    private function assertChannelEnabled($channel, array $settings) {
        $enabledKey = $channel . '_enabled';
        $recipientKey = [
            'email' => 'email_address',
            'whatsapp' => 'whatsapp_phone',
            'telegram' => 'telegram_chat_id'
        ][$channel] ?? null;

        if (!$recipientKey) throw new RuntimeException('Canal no soportado: ' . $channel);
        if (empty($settings[$enabledKey])) throw new RuntimeException('El canal ' . $channel . ' está deshabilitado para este usuario.');
        if (empty($settings[$recipientKey]) || trim((string)$settings[$recipientKey]) === '') {
            throw new RuntimeException('Falta el destinatario configurado para ' . $channel . '.');
        }
    }

    private function sendItem(array $item, array $user, array $settings) {
        $channel = $item['channel'];
        $recipient = trim((string)$item['recipient']);
        $message = (string)$item['message'];
        if ($recipient === '') return ['success' => false, 'error' => 'El destinatario de la cola está vacío.'];

        switch ($channel) {
            case 'email':
                $config = json_decode($settings['notification_email_config'] ?? '{}', true) ?: [];
                $subject = '[Willak] Recordatorio: ' . ($item['task_title'] ?? 'tarea próxima a vencer');
                $body = '<p>' . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . '</p>';
                return (new EmailChannel($config))->send($recipient, $subject, $body);

            case 'telegram':
                $telegramConfig = json_decode($settings['notification_telegram_config'] ?? '{}', true) ?: [];
                $token = trim((string)($user['telegram_bot_token'] ?? ($telegramConfig['bot_token'] ?? '')));
                if ($token === '') return ['success' => false, 'error' => 'Falta configurar el token del bot de Telegram.'];
                return (new TelegramChannel(['bot_token' => $token]))->send(
                    $recipient,
                    htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                );

            case 'whatsapp':
                $provider = $user['whatsapp_provider'] ?? 'meta';
                $config = json_decode($settings['notification_whatsapp_config'] ?? '{}', true) ?: [];
                $config['provider'] = $provider;
                $config['phone'] = $recipient; // El destinatario de la cola es la fuente de verdad.
                return (new WhatsAppChannel($config))->send($recipient, $message);

            default:
                return ['success' => false, 'error' => 'Canal no soportado: ' . $channel];
        }
    }

    public function sendTest($channel, $to, $message) {
        $settings = $this->model->getGlobalSettings();
        switch ($channel) {
            case 'email':
                $config = json_decode($settings['notification_email_config'] ?? '{}', true) ?: [];
                return (new EmailChannel($config))->send($to, 'Prueba Willak', $message);
            case 'whatsapp':
                return ['success' => false, 'error' => 'La prueba de WhatsApp requiere cargar la configuración del proveedor y del destinatario.'];
            case 'telegram':
                return ['success' => false, 'error' => 'La prueba de Telegram debe usar el token y chat ID del usuario configurado.'];
            default:
                return ['success' => false, 'error' => 'Canal no soportado.'];
        }
    }
}
