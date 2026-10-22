<?php
class Notification {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener canales activos con su configuración
     */
    public function getActiveChannels() {
        $stmt = $this->db->query("SELECT * FROM notification_channels WHERE is_active = 1");
        $channels = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $channels[$row['channel_key']] = json_decode($row['config_data'], true);
        }
        return $channels;
    }

    /**
     * Obtener preferencias de un usuario
     */
    public function getUserPreferences($userId) {
        $stmt = $this->db->prepare("SELECT channel, is_enabled FROM user_notification_prefs WHERE user_id = :id");
        $stmt->execute([':id' => $userId]);
        $prefs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $prefs[$row['channel']] = $row['is_enabled'];
        }
        return $prefs;
    }

    /**
     * Registrar una notificación en la cola
     */
    public function queue($taskId, $userId, $channel, $subject, $message) {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (task_id, user_id, channel, subject, message, status, attempts, max_attempts)
            VALUES (:task_id, :user_id, :channel, :subject, :message, 'pending', 0, 3)
        ");
        return $stmt->execute([
            ':task_id' => $taskId,
            ':user_id' => $userId,
            ':channel' => $channel,
            ':subject' => $subject,
            ':message' => $message
        ]);
    }

    /**
     * Obtener notificaciones pendientes de envío
     */
    public function getPending($limit = 50) {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications 
            WHERE status IN ('pending', 'retrying') 
            AND (next_retry_at IS NULL OR next_retry_at <= NOW())
            ORDER BY created_at ASC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marcar notificación como enviada
     */
    public function markSent($id) {
        $stmt = $this->db->prepare("UPDATE notifications SET status = 'sent', sent_at = NOW() WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Marcar notificación como fallida con reintento
     */
    public function markFailed($id, $error, $attempts) {
        $maxAttempts = 3;
        $status = $attempts >= $maxAttempts ? 'failed' : 'retrying';
        
        // Reintentos: 5 min y 1 hora después
        $retryMinutes = $attempts === 1 ? 5 : 60;
        $nextRetry = date('Y-m-d H:i:s', strtotime("+{$retryMinutes} minutes"));
        
        $stmt = $this->db->prepare("
            UPDATE notifications 
            SET status = :status, 
                attempts = :attempts, 
                last_error = :error,
                next_retry_at = :next_retry
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':status' => $status,
            ':attempts' => $attempts,
            ':error' => $error,
            ':next_retry' => $status === 'failed' ? null : $nextRetry
        ]);
    }

    /**
     * Obtener logs de notificaciones (para el panel)
     */
    public function getLogs($filters = []) {
        $sql = "SELECT n.*, u.username, u.full_name, t.title AS task_title 
                FROM notifications n
                LEFT JOIN users u ON u.id = n.user_id
                LEFT JOIN tasks t ON t.id = n.task_id
                WHERE 1=1";
        
        $params = [];
        if (!empty($filters['channel'])) {
            $sql .= " AND n.channel = :channel";
            $params[':channel'] = $filters['channel'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND n.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        $sql .= " ORDER BY n.created_at DESC LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener tareas próximas a vencer
     */
    public function getTasksDueSoon($days) {
        $stmt = $this->db->prepare("
            SELECT t.*, c.name AS client_name, tt.name AS type_name,
                   u.full_name AS owner_name, u.email AS owner_email, u.phone AS owner_phone
            FROM tasks t
            LEFT JOIN clients c ON c.id = t.client_id
            LEFT JOIN task_types tt ON tt.id = t.task_type_id
            LEFT JOIN users u ON u.id = t.user_id
            WHERE t.status IN ('activo', 'pendiente', 'en_proceso')
            AND t.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
            ORDER BY t.end_date ASC
        ");
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si ya se envió notificación para esta tarea+canal+días
     */
    public function alreadyNotified($taskId, $channel, $daysBefore) {
        $targetDate = date('Y-m-d', strtotime("+{$daysBefore} days"));
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE task_id = :task_id 
            AND channel = :channel 
            AND DATE(created_at) = :target_date
            AND status = 'sent'
        ");
        $stmt->execute([
            ':task_id' => $taskId,
            ':channel' => $channel,
            ':target_date' => $targetDate
        ]);
        return $stmt->fetchColumn() > 0;
    }
}