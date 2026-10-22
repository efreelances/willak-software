<?php
// Cargar PHPMailer automáticamente
if (file_exists(BASE_PATH . 'vendor/autoload.php')) {
    require_once BASE_PATH . 'vendor/autoload.php';
}

class EmailChannel {
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }

    public function send($to, $subject, $message) {
        $host = $this->config['host'] ?? '';
        $port = $this->config['port'] ?? 587;
        $username = $this->config['username'] ?? '';
        $password = $this->config['password'] ?? '';
        $encryption = $this->config['encryption'] ?? 'tls';
        $fromEmail = $this->config['from_email'] ?? $username;
        $fromName = $this->config['from_name'] ?? 'Willak';

        if (empty($host) || empty($username) || empty($password)) {
            return ['success' => false, 'error' => 'Configuración SMTP incompleta.'];
        }

        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return ['success' => false, 'error' => 'PHPMailer no está instalado. Ejecuta: composer require phpmailer/phpmailer'];
        }

        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password;
            
            if ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            }
            
            $mail->Port = $port;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            $mail->send();
            return ['success' => true];
            
        } catch (PHPMailer\PHPMailer\Exception $e) {
            return ['success' => false, 'error' => $mail->ErrorInfo];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function buildHtml($task, $daysLeft) {
        $color = $daysLeft <= 7 ? '#dc3545' : ($daysLeft <= 15 ? '#ffc107' : '#0d47a1');
        $urgency = $daysLeft <= 7 ? '🔴 URGENTE' : ($daysLeft <= 15 ? '🟡 PRÓXIMO A VENCER' : '🔵 AVISO');
        
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: {$color}; color: white; padding: 20px; text-align: center;'>
                <h2 style='margin: 0;'>{$urgency}</h2>
                <p style='margin: 10px 0 0 0;'>Tarea próxima a vencer en {$daysLeft} día(s)</p>
            </div>
            <div style='padding: 20px; background: #f8f9fa;'>
                <h3 style='color: #333;'>{$task['title']}</h3>
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Cliente:</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$task['client_name']}</td></tr>
                    <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Tipo:</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$task['type_name']}</td></tr>
                    <tr><td style='padding: 8px; border-bottom: 1px solid #ddd;'><strong>Fecha de vencimiento:</strong></td><td style='padding: 8px; border-bottom: 1px solid #ddd;'>" . date('d/m/Y', strtotime($task['end_date'])) . "</td></tr>
                    <tr><td style='padding: 8px;'><strong>Responsable:</strong></td><td style='padding: 8px;'>{$task['owner_name']}</td></tr>
                </table>
            </div>
            <div style='padding: 15px; text-align: center; background: #333; color: white; font-size: 12px;'>
                Enviado por Willak - Sistema de Gestión de Tareas
            </div>
        </div>";
    }
}