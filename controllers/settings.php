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
            foreach (['email' => 'notification_email_config', 'whatsapp' => 'notification_whatsapp_config', 'telegram' => 'notification_telegram_config'] as $key => $settingKey) {
                $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1");
                $stmt->execute([':key' => $settingKey]);
                $raw = $stmt->fetchColumn();
                $activeStmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1");
                $activeStmt->execute([':key' => 'notification_' . $key . '_active']);
                $channels[$key] = [
                    'is_active' => (int)$activeStmt->fetchColumn(),
                    'config' => $raw ? (json_decode($raw, true) ?: []) : []
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
        if (!is_array($notificationRules)) {
            $notificationRules = [];
        }

        // Compatibilidad con ambos formatos de nombres de formulario usados
        // en versiones anteriores: notification_days_1 y days_1.
        $ruleAliases = [
            'notification_days_1' => ['notification_days_1', 'days_1'],
            'notification_days_2' => ['notification_days_2', 'days_2'],
            'notification_days_3' => ['notification_days_3', 'days_3'],
        ];
        foreach ($ruleAliases as $canonicalKey => $aliases) {
            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $notificationRules)) {
                    $notificationRules[$canonicalKey] = $notificationRules[$alias];
                    break;
                }
            }
        }

        // HTML no envía checkboxes desmarcados. Normalizarlos explícitamente
        // para que al guardar una sección también se persistan como 0.
        $checkboxesBySection = [
            'security' => ['pwd_require_uppercase', 'pwd_require_number', 'pwd_require_special', '2fa_enabled', '2fa_required_admin', '2fa_required_user'],
            'tasks' => ['task_require_type', 'task_require_description'],
            'clients' => ['client_require_document', 'client_require_email', 'client_require_phone', 'client_validate_email', 'client_unique_document'],
            'reports' => ['report_include_charts'],
            'system' => ['backup_enabled', 'maintenance_mode'],
        ];
        foreach ($checkboxesBySection[$section] ?? [] as $checkboxKey) {
            $data[$checkboxKey] = isset($data[$checkboxKey]) && (string)$data[$checkboxKey] === '1' ? '1' : '0';
        }
        // Si el usuario desmarca todos los formatos, guardar un array vacío,
        // en vez de conservar silenciosamente la selección anterior.
        if ($section === 'reports' && !isset($data['report_formats'])) {
            $data['report_formats'] = [];
        }

        try {
            $this->db->beginTransaction();

            // 1. Guardar configuración general
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                
                $stmt = $this->db->prepare("
                    INSERT INTO settings (setting_key, setting_value, description)
                    VALUES (:key, :value, :description)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                ");
                $stmt->execute([':key' => $key, ':value' => $value, ':description' => 'Configuración de Willak: ' . $section]);
            }

            // 2. Guardar reglas de notificación.
            // Las claves deben coincidir con las que lee la vista:
            // notification_days_1, notification_days_2 y notification_days_3.
            $allowedNotificationRules = [
                'notification_days_1' => 'Primer aviso',
                'notification_days_2' => 'Segundo aviso',
                'notification_days_3' => 'Aviso crítico',
            ];

            foreach ($allowedNotificationRules as $key => $label) {
                if ($section === 'notifications' && !array_key_exists($key, $notificationRules)) {
                    throw new InvalidArgumentException('No se recibió el campo de configuración: ' . $label . '. Recarga la página y vuelve a guardar.');
                }
                if (!array_key_exists($key, $notificationRules)) {
                    continue;
                }

                $rawValue = $notificationRules[$key];
                if (is_array($rawValue) || filter_var($rawValue, FILTER_VALIDATE_INT) === false) {
                    throw new InvalidArgumentException('El valor de "' . $label . '" debe ser un número entero.');
                }

                $days = (int)$rawValue;
                if ($days < 1 || $days > 365) {
                    throw new InvalidArgumentException('El valor de "' . $label . '" debe estar entre 1 y 365 días.');
                }
            }

            $stmtRule = $this->db->prepare("
                INSERT INTO settings (setting_key, setting_value, description)
                VALUES (:key, :value, :description)
                ON DUPLICATE KEY UPDATE
                    setting_value = VALUES(setting_value),
                    description = VALUES(description)
            ");
            foreach ($allowedNotificationRules as $key => $label) {
                if (array_key_exists($key, $notificationRules)) {
                    $stmtRule->execute([
                        ':key' => $key,
                        ':value' => (string)(int)$notificationRules[$key],
                        ':description' => 'Regla de notificaciones: ' . $label,
                    ]);
                }
            }

            // 3. Guardar configuración de canales en la tabla real `settings`.
            foreach ($channelConfig as $channelKey => $config) {
                if (!in_array($channelKey, ['email', 'whatsapp', 'telegram'], true) || !is_array($config)) continue;
                $settingKey = 'notification_' . $channelKey . '_config';
                $existingStmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1");
                $existingStmt->execute([':key' => $settingKey]);
                $existing = json_decode((string)$existingStmt->fetchColumn(), true) ?: [];
                if ($channelKey === 'email' && empty($config['password'])) {
                    $config['password'] = $existing['password'] ?? '';
                }
                $stmt = $this->db->prepare("
                    INSERT INTO settings (setting_key, setting_value, description)
                    VALUES (:key, :value, :description)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                ");
                $stmt->execute([
                    ':key' => $settingKey,
                    ':value' => json_encode($config, JSON_UNESCAPED_UNICODE),
                    ':description' => 'Configuración del canal ' . $channelKey
                ]);
            }

            $this->db->commit();
            Session::flash('success', 'Configuración guardada correctamente.');
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('[Willak Settings] ' . $e->getMessage());
            Session::flash('error', 'No se pudo guardar la configuración. Revisa los registros del servidor.');
        }

        header('Location: ' . APP_URL . '/settings?section=' . rawurlencode($section));
        exit;
    }

    public function actionTestEmail() {
        if (!Security::validateCsrf($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Token inválido']);
            exit;
        }

        // Obtener configuración actual de la BD
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'notification_email_config'");
        $stmt->execute();
        $config = json_decode((string)$stmt->fetchColumn(), true) ?: [];

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

        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'notification_telegram_config'");
        $stmt->execute();
        $config = json_decode((string)$stmt->fetchColumn(), true) ?: [];

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

    public function actionToggleChannel() {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::validateCsrf($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Solicitud no válida.']);
            exit;
        }
        $channel = $_POST['channel'] ?? '';
        if (!in_array($channel, ['email', 'whatsapp', 'telegram'], true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Canal no válido.']);
            exit;
        }
        $active = !empty($_POST['active']) ? '1' : '0';
        $stmt = $this->db->prepare("INSERT INTO settings (setting_key, setting_value, description)
            VALUES (:key, :value, :description)
            ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
        $stmt->execute([
            ':key' => 'notification_' . $channel . '_active',
            ':value' => $active,
            ':description' => 'Canal habilitado'
        ]);
        echo json_encode(['success' => true]);
        exit;
    }

    private function getAllSettings() {
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function actionResetColors() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::validateCsrf($_POST['csrf_token'] ?? '')) {
            $stmt = $this->db->prepare("UPDATE settings SET setting_value = :value WHERE setting_key = :key");
            $stmt->execute([':key' => 'primary_color', ':value' => '#0d47a1']);
            $stmt->execute([':key' => 'accent_color', ':value' => '#ff6f00']);
            
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['success' => false]);
        exit;
    }
}