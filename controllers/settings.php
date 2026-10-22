<?php
class SettingsController {
    private $db;
    
    public function __construct() {
        Auth::requireAdmin();
        $this->db = Database::getInstance()->getConnection();
    }

    public function actionIndex() 
    {
        $section = $_GET['section'] ?? 'general';
        $settings = $this->getAllSettings();
        
        // Cargar configuración de canales de notificación
        $channels = [];
        try {
            $stmt = $this->db->query("SELECT * FROM notification_channels ORDER BY id ASC");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $channels[$row['channel_key']] = [
                    'is_active' => $row['is_active'],
                    'config' => json_decode($row['config_data'], true) ?: []
                ];
            }
        } catch (Exception $e) {
            // Si no existe la tabla, ignorar
        }
        
        $currentPage = 'settings';
        $pageTitle = 'Configuración';
        include BASE_PATH . 'views/settings/index.php';
    }

    public function actionSave() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/settings');
            exit;
        }

        if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('error', 'Token de seguridad inválido.');
            header('Location: ' . APP_URL . '/settings');
            exit;
        }

        $section = $_POST['section'] ?? 'general';
        $data = $_POST['data'] ?? [];
        $channelConfig = $_POST['channel_config'] ?? [];
        $notificationRules = $_POST['notification_rules'] ?? [];

        try {
            $this->db->beginTransaction();

            // 1. Guardar configuración general
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                
                $stmt = $this->db->prepare("
                    INSERT INTO app_settings (setting_key, setting_value, category) 
                    VALUES (:key, :value, :category)
                    ON DUPLICATE KEY UPDATE setting_value = :value2
                ");
                $stmt->execute([
                    ':key' => $key,
                    ':value' => $value,
                    ':category' => $section,
                    ':value2' => $value
                ]);
            }

            // 2. Guardar reglas de notificación
            foreach ($notificationRules as $key => $value) {
                $stmt = $this->db->prepare("
                    INSERT INTO app_settings (setting_key, setting_value, category) 
                    VALUES (:key, :value, 'notifications')
                    ON DUPLICATE KEY UPDATE setting_value = :value2
                ");
                $stmt->execute([
                    ':key' => $key,
                    ':value' => (int)$value,
                    ':value2' => (int)$value
                ]);
            }

            // 3. Guardar configuración de canales
            foreach ($channelConfig as $channelKey => $config) {
                // Limpiar campos vacíos de contraseñas para no sobrescribir con vacío si no se cambió
                if ($channelKey === 'email' && empty($config['password'])) {
                    $stmt = $this->db->prepare("SELECT config_data FROM notification_channels WHERE channel_key = 'email'");
                    $stmt->execute();
                    $currentConfig = json_decode($stmt->fetchColumn(), true);
                    $config['password'] = $currentConfig['password'] ?? '';
                }

                $stmt = $this->db->prepare("
                    UPDATE notification_channels 
                    SET config_data = :config 
                    WHERE channel_key = :key
                ");
                $stmt->execute([
                    ':key' => $channelKey,
                    ':config' => json_encode($config)
                ]);
            }

            $this->db->commit();
            Session::flash('success', 'Configuración guardada correctamente.');
        } catch (Exception $e) {
            $this->db->rollBack();
            Session::flash('error', 'Error al guardar: ' . $e->getMessage());
        }

        header('Location: ' . APP_URL . '/settings?section=' . $section);
        exit;
    }

    public function actionTestEmail() {
        if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token inválido']);
            exit;
        }

        // Obtener configuración actual de la BD
        $stmt = $this->db->prepare("SELECT config_data FROM notification_channels WHERE channel_key = 'email'");
        $stmt->execute();
        $config = json_decode($stmt->fetchColumn(), true);

        if (empty($config['host']) || empty($config['username']) || empty($config['password'])) {
            echo json_encode(['success' => false, 'message' => 'Configuración SMTP incompleta. Completa los campos y guarda primero.']);
            exit;
        }

        // Email destino: el del usuario actual o el configurado como remitente
        $to = Auth::user()['email'] ?? $config['from_email'];
        
        if (empty($to)) {
            echo json_encode(['success' => false, 'message' => 'No hay email de destino configurado.']);
            exit;
        }

        // Cargar el canal de email
        require_once BASE_PATH . 'core/channels/EmailChannel.php';
        $channel = new EmailChannel($config);

        $subject = '[Willak] Prueba de notificación';
        $message = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #f8f9fa; padding: 20px;">
            <div style="background: #0d47a1; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
                <h2 style="margin: 0;">✅ Prueba de Email Exitosa</h2>
            </div>
            <div style="background: white; padding: 30px; border: 1px solid #e0e4e8;">
                <p style="font-size: 16px; color: #333;">Si estás viendo este mensaje, la configuración SMTP de <strong>Willak</strong> funciona correctamente.</p>
                <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
                    <tr><td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Destinatario:</strong></td><td style="padding: 8px; border-bottom: 1px solid #eee;">' . htmlspecialchars($to) . '</td></tr>
                    <tr><td style="padding: 8px; border-bottom: 1px solid #eee;"><strong>Fecha:</strong></td><td style="padding: 8px; border-bottom: 1px solid #eee;">' . date('d/m/Y H:i:s') . '</td></tr>
                    <tr><td style="padding: 8px;"><strong>Servidor SMTP:</strong></td><td style="padding: 8px;">' . htmlspecialchars($config['host']) . ':' . $config['port'] . '</td></tr>
                </table>
                <p style="margin-top: 20px; color: #666; font-size: 14px;">Este es un mensaje automático de prueba. No es necesario responder.</p>
            </div>
            <div style="padding: 15px; text-align: center; background: #333; color: white; font-size: 12px; border-radius: 0 0 8px 8px;">
                Enviado por Willak - Sistema de Gestión de Tareas
            </div>
        </div>';

        $result = $channel->send($to, $subject, $message);

        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Email de prueba enviado a ' . $to . '. Revisa tu bandeja (y spam).']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al enviar: ' . ($result['error'] ?? 'Desconocido')]);
        }
        exit;
    }

    public function actionTestTelegram() {
        if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token inválido']);
            exit;
        }

        $stmt = $this->db->prepare("SELECT config_data FROM notification_channels WHERE channel_key = 'telegram'");
        $stmt->execute();
        $config = json_decode($stmt->fetchColumn(), true);

        if (empty($config['bot_token']) || empty($config['chat_id'])) {
            echo json_encode(['success' => false, 'message' => 'Bot Token o Chat ID no configurados.']);
            exit;
        }

        require_once BASE_PATH . 'core/channels/TelegramChannel.php';
        $channel = new TelegramChannel($config);

        $message = "✅ <b>Prueba de Telegram Exitosa</b>\n\n"
                 . "Si estás viendo este mensaje, la configuración del Bot de Willak funciona correctamente.\n\n"
                 . "📅 Fecha: " . date('d/m/Y H:i:s') . "\n"
                 . "🤖 Bot configurado correctamente.\n\n"
                 . "— <i>Willak</i>";

        $result = $channel->send('', $message);

        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Mensaje de prueba enviado. Revisa Telegram.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . ($result['error'] ?? 'Desconocido')]);
        }
        exit;
    }

    private function getAllSettings() {
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM app_settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function actionResetColors() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::validateCsrf($_POST['csrf_token'] ?? '')) {
            $stmt = $this->db->prepare("UPDATE app_settings SET setting_value = :value WHERE setting_key = :key");
            $stmt->execute([':key' => 'primary_color', ':value' => '#0d47a1']);
            $stmt->execute([':key' => 'accent_color', ':value' => '#ff6f00']);
            
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['success' => false]);
        exit;
    }
}