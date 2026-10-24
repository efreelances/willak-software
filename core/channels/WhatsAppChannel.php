<?php
class WhatsAppChannel {
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Enviar mensaje de WhatsApp según proveedor
     */
    public function send($phone, $message) {
        $provider = $this->config['provider'] ?? 'callmebot';
        
        switch ($provider) {
            case 'callmebot':
                return $this->sendCallMeBot($phone, $message);
            case 'meta':
                return $this->sendMeta($phone, $message);
            case 'twilio':
                return $this->sendTwilio($phone, $message);
            default:
                return ['success' => false, 'error' => 'Proveedor no válido'];
        }
    }

    /**
     * CallMeBot (gratis para pruebas)
     */
    private function sendCallMeBot($phone, $message) {
        $apiKey = $this->config['api_key'] ?? '';
        $configPhone = $this->config['phone'] ?? '';
        
        if (empty($apiKey) || empty($configPhone)) {
            return ['success' => false, 'error' => 'API Key o teléfono no configurados'];
        }

        $url = "https://api.callmebot.com/whatsapp.php?phone=" . urlencode($configPhone) 
             . "&text=" . urlencode($message) . "&apikey=" . urlencode($apiKey);
        
        $context = stream_context_create(['http' => ['timeout' => 15]]);
        $response = @file_get_contents($url, false, $context);
        
        if ($response && strpos($response, 'Message queued') !== false) {
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'CallMeBot: ' . ($response ?: 'Sin respuesta')];
    }

    /**
     * Meta Cloud API (oficial)
     */
    private function sendMeta($phone, $message) {
        $token = $this->config['meta_token'] ?? '';
        $phoneId = $this->config['meta_phone_id'] ?? '';
        $version = $this->config['meta_version'] ?? 'v18.0';
        
        if (empty($token) || empty($phoneId)) {
            return ['success' => false, 'error' => 'Token o Phone ID no configurados'];
        }

        $url = "https://graph.facebook.com/{$version}/{$phoneId}/messages";
        
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => preg_replace('/[^0-9]/', '', $phone),
            'type' => 'text',
            'text' => ['body' => $message]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['success' => false, 'error' => 'WhatsApp: error de conexión: ' . $curlError];
        }
        if ($httpCode === 200 || $httpCode === 201) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Meta API: HTTP ' . $httpCode . ' - ' . $response];
    }

    /**
     * Twilio (pago)
     */
    private function sendTwilio($phone, $message) {
        $sid = $this->config['twilio_sid'] ?? '';
        $token = $this->config['twilio_token'] ?? '';
        $from = $this->config['twilio_from'] ?? '';
        
        if (empty($sid) || empty($token) || empty($from)) {
            return ['success' => false, 'error' => 'Credenciales de Twilio incompletas'];
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'From' => $from,
            'To' => 'whatsapp:' . preg_replace('/[^0-9]/', '', $phone),
            'Body' => $message
        ]));
        curl_setopt($ch, CURLOPT_USERPWD, "{$sid}:{$token}");

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Twilio: HTTP ' . $httpCode . ' - ' . $response];
    }

    /**
     * Construir mensaje de texto
     */
    public static function buildMessage($task, $daysLeft) {
        $urgency = $daysLeft <= 7 ? ' URGENTE' : ($daysLeft <= 15 ? '🟡 PRÓXIMO A VENCER' : '🔵 AVISO');
        
        return "{$urgency}\n\n"
             . "Tarea: {$task['title']}\n"
             . "Cliente: {$task['client_name']}\n"
             . "Tipo: {$task['type_name']}\n"
             . "Vence: " . date('d/m/Y', strtotime($task['end_date'])) . " (en {$daysLeft} día(s))\n"
             . "Responsable: {$task['owner_name']}\n\n"
             . "— Willak";
    }
}