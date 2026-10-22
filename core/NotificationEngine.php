<?php
require_once BASE_PATH . 'models/Notification.php';
require_once BASE_PATH . 'core/channels/EmailChannel.php';
require_once BASE_PATH . 'core/channels/WhatsAppChannel.php';
require_once BASE_PATH . 'core/channels/TelegramChannel.php';

class NotificationEngine {
    private $model;
    private $channels;
    private $rules; // días de anticipación
    
    public function __construct() {
        $this->model = new Notification();
        $this->channels = $this->model->getActiveChannels();
        $this->rules = $this->loadRules();
    }

    /**
     * Cargar reglas desde configuración
     */
    private function loadRules() {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM app_settings WHERE setting_key LIKE 'notification_days_%'");
            $rules = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $rules[$row['setting_key']] = (int)$row['setting_value'];
            }
        } catch (Exception $e) {
            $rules = [];
        }
        
        return [
            'days_1' => $rules['notification_days_1'] ?? 30,
            'days_2' => $rules['notification_days_2'] ?? 15,
            'days_3' => $rules['notification_days_3'] ?? 7
        ];
    }

    /**
     * EJECUTAR: Revisar tareas y enviar notificaciones
     * Este es el método principal que llama el cron
     */
    public function run() {
        $results = ['sent' => 0, 'failed' => 0, 'skipped' => 0];
        
        foreach ($this->rules as $ruleKey => $days) {
            $tasks = $this->model->getTasksDueSoon($days);
            
            foreach ($tasks as $task) {
                $daysLeft = (strtotime($task['end_date']) - time()) / 86400;
                $daysLeft = floor($daysLeft);
                
                // Determinar qué canales usar según días restantes
                $channelsToSend = $this->getChannelsForDays($daysLeft);
                
                foreach ($channelsToSend as $channel) {
                    // Evitar duplicados
                    if ($this->model->alreadyNotified($task['id'], $channel, $daysLeft)) {
                        $results['skipped']++;
                        continue;
                    }
                    
                    // Verificar preferencias del usuario
                    $userPrefs = $this->model->getUserPreferences($task['user_id']);
                    if (empty($userPrefs[$channel])) {
                        $results['skipped']++;
                        continue;
                    }
                    
                    // Enviar notificación
                    $sent = $this->sendToChannel($channel, $task, $daysLeft);
                    
                    if ($sent['success']) {
                        $results['sent']++;
                    } else {
                        $results['failed']++;
                    }
                }
            }
        }
        
        return $results;
    }

    /**
     * Decidir qué canales usar según días restantes
     */
    private function getChannelsForDays($daysLeft) {
        $channels = [];
        
        if ($daysLeft <= $this->rules['days_1']) {
            $channels[] = 'email'; // Siempre email desde el primer aviso
        }
        if ($daysLeft <= $this->rules['days_2']) {
            $channels[] = 'whatsapp';
        }
        if ($daysLeft <= $this->rules['days_3']) {
            $channels[] = 'telegram';
        }
        
        // Filtrar solo canales activos
        return array_intersect($channels, array_keys($this->channels));
    }

    /**
     * Enviar a un canal específico
     */
    private function sendToChannel($channel, $task, $daysLeft) {
        $config = $this->channels[$channel] ?? null;
        if (!$config) {
            return ['success' => false, 'error' => 'Canal no configurado'];
        }

        // Registrar en cola
        $subject = "[Willak] Tarea próxima a vencer: {$task['title']}";
        $this->model->queue($task['id'], $task['user_id'], $channel, $subject, '');

        try {
            switch ($channel) {
                case 'email':
                    $channelObj = new EmailChannel($config);
                    $message = EmailChannel::buildHtml($task, $daysLeft);
                    $result = $channelObj->send($task['owner_email'], $subject, $message);
                    break;
                    
                case 'whatsapp':
                    $channelObj = new WhatsAppChannel($config);
                    $message = WhatsAppChannel::buildMessage($task, $daysLeft);
                    $result = $channelObj->send($task['owner_phone'] ?? '', $message);
                    break;
                    
                case 'telegram':
                    $channelObj = new TelegramChannel($config);
                    $message = TelegramChannel::buildMessage($task, $daysLeft);
                    $result = $channelObj->send('', $message);
                    break;
                    
                default:
                    $result = ['success' => false, 'error' => 'Canal desconocido'];
            }
            
            return $result;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Enviar notificación manual (desde el panel)
     */
    public function sendTest($channel, $to, $message) {
        if (!isset($this->channels[$channel])) {
            return ['success' => false, 'error' => 'Canal no activo'];
        }
        
        $config = $this->channels[$channel];
        
        switch ($channel) {
            case 'email':
                return (new EmailChannel($config))->send($to, 'Prueba Willak', $message);
            case 'whatsapp':
                return (new WhatsAppChannel($config))->send($to, $message);
            case 'telegram':
                return (new TelegramChannel($config))->send($to, $message);
        }
        
        return ['success' => false, 'error' => 'Canal no soportado'];
    }
}