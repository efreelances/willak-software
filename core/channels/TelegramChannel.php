<?php
class TelegramChannel {
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Enviar mensaje vía Telegram Bot API
     */
    public function send($chatId, $message) {
        $token = $this->config['bot_token'] ?? '';
        $targetChat = $chatId ?: ($this->config['chat_id'] ?? '');
        
        if (empty($token) || empty($targetChat)) {
            return ['success' => false, 'error' => 'Bot Token o Chat ID no configurados'];
        }

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        
        $data = [
            'chat_id' => $targetChat,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($httpCode === 200 && ($decoded['ok'] ?? false)) {
            return ['success' => true];
        }

        $error = $decoded['description'] ?? 'Desconocido';
        return ['success' => false, 'error' => 'Telegram: ' . $error];
    }

    /**
     * Construir mensaje HTML para Telegram
     */
    public static function buildMessage($task, $daysLeft) {
        $emoji = $daysLeft <= 7 ? '🔴' : ($daysLeft <= 15 ? '🟡' : '🔵');
        $urgency = $daysLeft <= 7 ? '<b>URGENTE</b>' : ($daysLeft <= 15 ? '<b>PRÓXIMO A VENCER</b>' : '<b>AVISO</b>');
        
        return "{$emoji} {$urgency}\n\n"
             . "📋 <b>{$task['title']}</b>\n"
             . "👤 Cliente: {$task['client_name']}\n"
             . "🏷️ Tipo: {$task['type_name']}\n"
             . "📅 Vence: " . date('d/m/Y', strtotime($task['end_date'])) . " (en {$daysLeft} día(s))\n"
             . "👨‍💼 Responsable: {$task['owner_name']}\n\n"
             . "— <i>Willak</i>";
    }
}