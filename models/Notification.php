<?php
/**
 * Acceso a la cola real definida en willak.sql.
 * Este modelo no depende de tablas inexistentes como notifications o notification_channels.
 */
class Notification {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getDueQueue($limit = 50) {
        $limit = max(1, min(200, (int)$limit));
        $stmt = $this->db->prepare(
            "SELECT q.*, t.title AS task_title, t.end_date, t.status AS task_status,
                    u.full_name AS owner_name, u.email AS owner_email,
                    c.name AS client_name, tt.name AS type_name
             FROM notification_queue q
             INNER JOIN tasks t ON t.id = q.task_id
             INNER JOIN users u ON u.id = q.user_id
             LEFT JOIN clients c ON c.id = t.client_id
             LEFT JOIN task_types tt ON tt.id = t.task_type_id
             WHERE q.status = 'pending'
               AND q.scheduled_at <= NOW()
               AND t.status NOT IN ('cancelado', 'archivado')
             ORDER BY q.scheduled_at ASC, q.id ASC
             LIMIT {$limit}"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserSettings($userId) {
        $stmt = $this->db->prepare("SELECT * FROM notification_settings WHERE user_id = :uid LIMIT 1");
        $stmt->execute([':uid' => (int)$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getGlobalSettings() {
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function markSent(array $item) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "UPDATE notification_queue SET status='sent', sent_at=NOW(), error_message=NULL
                 WHERE id=:id AND status='pending'"
            );
            $stmt->execute([':id' => (int)$item['id']]);
            if ($stmt->rowCount() > 0) {
                $this->writeLog($item, 'success', null);
            }
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function markFailed(array $item, $error) {
        $attempts = ((int)($item['attempts'] ?? 0)) + 1;
        $maxAttempts = max(1, (int)($item['max_attempts'] ?? 3));
        $status = $attempts >= $maxAttempts ? 'failed' : 'pending';
        // Reintentos escalonados: 5, 15 y 60 minutos.
        $delay = $attempts === 1 ? 5 : ($attempts === 2 ? 15 : 60);
        $safeError = substr((string)$error, 0, 4000);
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "UPDATE notification_queue
                 SET attempts=:attempts, status=:status,
                     scheduled_at=CASE WHEN :status_retry='pending' THEN DATE_ADD(NOW(), INTERVAL {$delay} MINUTE) ELSE scheduled_at END,
                     error_message=:error
                 WHERE id=:id AND status='pending'"
            );
            $stmt->execute([
                ':attempts' => $attempts,
                ':status' => $status,
                ':status_retry' => $status,
                ':error' => $safeError,
                ':id' => (int)$item['id']
            ]);
            if ($stmt->rowCount() > 0) $this->writeLog($item, 'failed', $safeError);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    private function writeLog(array $item, $status, $error) {
        $stmt = $this->db->prepare(
            "INSERT INTO notification_log (task_id, user_id, channel, recipient, message, sent_at, status, error_message)
             VALUES (:task_id, :user_id, :channel, :recipient, :message, NOW(), :status, :error)"
        );
        $stmt->execute([
            ':task_id' => (int)$item['task_id'],
            ':user_id' => (int)$item['user_id'],
            ':channel' => $item['channel'],
            ':recipient' => $item['recipient'],
            ':message' => $item['message'],
            ':status' => $status,
            ':error' => $error
        ]);
    }

    public function markCancelled($id) {
        $stmt = $this->db->prepare("UPDATE notification_queue SET status='cancelled' WHERE id=:id AND status='pending'");
        return $stmt->execute([':id' => (int)$id]);
    }

    public function cancelPendingForTask($taskId) {
        $stmt = $this->db->prepare(
            "UPDATE notification_queue SET status='cancelled'
             WHERE task_id=:tid AND status='pending'"
        );
        return $stmt->execute([':tid' => (int)$taskId]);
    }

    public function getLogs($filters = []) {
        $sql = "SELECT n.*, u.username, u.full_name, t.title AS task_title
                FROM notification_log n
                LEFT JOIN users u ON u.id=n.user_id
                LEFT JOIN tasks t ON t.id=n.task_id WHERE 1=1";
        $params = [];
        if (!empty($filters['channel'])) {
            $sql .= " AND n.channel=:channel";
            $params[':channel'] = $filters['channel'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND n.status=:status";
            $params[':status'] = $filters['status'];
        }
        $sql .= " ORDER BY n.sent_at DESC LIMIT 100";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
