<?php
class Task {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll($filters = []) {
        $sql = "SELECT t.*, c.name AS client_name, tt.name AS type_name, u.full_name AS owner_name
                FROM tasks t
                LEFT JOIN clients c ON c.id = t.client_id
                LEFT JOIN task_types tt ON tt.id = t.task_type_id
                LEFT JOIN users u ON u.id = t.user_id
                WHERE 1=1";
        $params = [];

        if (!Auth::isAdmin()) {
            $sql .= " AND t.user_id = :uid";
            $params[':uid'] = Auth::id();
        }
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['client_id'])) {
            $sql .= " AND t.client_id = :cid";
            $params[':cid'] = $filters['client_id'];
        }
        if (!empty($filters['task_type_id'])) {
            $sql .= " AND t.task_type_id = :tid";
            $params[':tid'] = $filters['task_type_id'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (t.title LIKE :search OR c.name LIKE :search2)";
            $params[':search'] = '%' . $filters['search'] . '%';
            $params[':search2'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY t.end_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find($id) {
        $sql = "SELECT t.*, c.name AS client_name, tt.name AS type_name
                FROM tasks t
                LEFT JOIN clients c ON c.id = t.client_id
                LEFT JOIN task_types tt ON tt.id = t.task_type_id
                WHERE t.id = :id";
        if (!Auth::isAdmin()) $sql .= " AND t.user_id = " . (int)Auth::id();
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $errors = $this->validate($data);
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        // Validar que end_date sea posterior a start_date
        if (strtotime($data['end_date']) <= strtotime($data['start_date'])) {
            return ['success' => false, 'errors' => ['La fecha fin debe ser posterior a la fecha inicio.']];
        }

        $userId = Auth::isAdmin() ? ($data['user_id'] ?? Auth::id()) : Auth::id();
        $reminderDays = json_encode($data['reminder_days'] ?? []);

        // Determinar estado inicial
        $status = 'pendiente';
        if (strtotime($data['end_date']) < strtotime('today')) {
            $status = 'archivado';
        }

        $stmt = $this->db->prepare("INSERT INTO tasks 
            (client_id, user_id, task_type_id, title, description, start_date, end_date, reminder_days, status) 
            VALUES (:cid, :uid, :tid, :title, :desc, :sd, :ed, :rd, :status)");
        $stmt->execute([
            ':cid' => $data['client_id'], ':uid' => $userId,
            ':tid' => !empty($data['task_type_id']) ? $data['task_type_id'] : null,
            ':title' => $data['title'], ':desc' => $data['description'] ?? null,
            ':sd' => $data['start_date'], ':ed' => $data['end_date'],
            ':rd' => $reminderDays, ':status' => $status
        ]);

        $taskId = $this->db->lastInsertId();
        $this->scheduleReminders($taskId, $userId, $data['end_date'], $data['reminder_days'] ?? []);

        return ['success' => true, 'id' => $taskId];
    }

    public function update($id, $data) {
        $task = $this->find($id);
        if (!$task) return ['success' => false, 'errors' => ['Tarea no encontrada.']];

        $errors = $this->validate($data);
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        if (strtotime($data['end_date']) <= strtotime($data['start_date'])) {
            return ['success' => false, 'errors' => ['La fecha fin debe ser posterior a la fecha inicio.']];
        }

        $reminderDays = json_encode($data['reminder_days'] ?? []);

        // Auto-archivar si ya venció
        $newStatus = $data['status'] ?? $task['status'];
        if (strtotime($data['end_date']) < strtotime('today') && $newStatus !== 'cancelado') {
            $newStatus = 'archivado';
        }

        $sql = "UPDATE tasks SET client_id = :cid, task_type_id = :tid, title = :title, 
                description = :desc, start_date = :sd, end_date = :ed, 
                reminder_days = :rd, status = :status WHERE id = :id";
        if (Auth::isAdmin()) $sql = str_replace('WHERE id', ', user_id = :uid WHERE id', $sql);

        $params = [
            ':cid' => $data['client_id'],
            ':tid' => !empty($data['task_type_id']) ? $data['task_type_id'] : null,
            ':title' => $data['title'], ':desc' => $data['description'] ?? null,
            ':sd' => $data['start_date'], ':ed' => $data['end_date'],
            ':rd' => $reminderDays, ':status' => $newStatus, ':id' => $id
        ];
        if (Auth::isAdmin()) $params[':uid'] = $data['user_id'] ?? $task['user_id'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        // Re-programar recordatorios
        $this->deleteReminders($id);
        $this->scheduleReminders($id, $task['user_id'], $data['end_date'], $data['reminder_days'] ?? []);

        return ['success' => true];
    }

    public function archive($id) {
        $stmt = $this->db->prepare("UPDATE tasks SET status = 'archivado' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function reactivate($id) {
        $stmt = $this->db->prepare("UPDATE tasks SET status = 'pendiente' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function cancel($id) {
        $stmt = $this->db->prepare("UPDATE tasks SET status = 'cancelado' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    private function validate($data) {
        $errors = [];
        if (empty($data['title'])) $errors[] = 'El título es obligatorio.';
        if (empty($data['client_id'])) $errors[] = 'El cliente es obligatorio.';
        if (empty($data['start_date'])) $errors[] = 'La fecha inicio es obligatoria.';
        if (empty($data['end_date'])) $errors[] = 'La fecha fin es obligatoria.';
        return $errors;
    }

    private function scheduleReminders($taskId, $userId, $endDate, $days) {
        if (empty($days)) return;
        $endTs = strtotime($endDate);
        $user = (new User())->find($userId);
        $notifSettings = $this->getNotificationSettings($userId);

        foreach ($days as $day) {
            $day = (int)$day;
            if ($day <= 0) continue;
            $scheduledAt = date('Y-m-d 09:00:00', $endTs - ($day * 86400));
            $message = "⏰ Recordatorio: La tarea '{$this->getTaskTitle($taskId)}' vence en {$day} día(s) ({$endDate}).";

            $channels = [];
            if (!empty($notifSettings['email_enabled']) && !empty($notifSettings['email_address'])) {
                $channels[] = ['channel' => 'email', 'recipient' => $notifSettings['email_address']];
            }
            if (!empty($notifSettings['whatsapp_enabled']) && !empty($notifSettings['whatsapp_phone'])) {
                $channels[] = ['channel' => 'whatsapp', 'recipient' => $notifSettings['whatsapp_phone']];
            }
            if (!empty($notifSettings['telegram_enabled']) && !empty($notifSettings['telegram_chat_id'])) {
                $channels[] = ['channel' => 'telegram', 'recipient' => $notifSettings['telegram_chat_id']];
            }

            foreach ($channels as $ch) {
                $stmt = $this->db->prepare("INSERT INTO notification_queue 
                    (task_id, user_id, channel, recipient, message, scheduled_at, max_attempts, status) 
                    VALUES (:tid, :uid, :ch, :r, :msg, :sa, 3, 'pending')");
                $stmt->execute([
                    ':tid' => $taskId, ':uid' => $userId,
                    ':ch' => $ch['channel'], ':r' => $ch['recipient'],
                    ':msg' => $message, ':sa' => $scheduledAt
                ]);
            }
        }
    }

    private function deleteReminders($taskId) {
        $stmt = $this->db->prepare("DELETE FROM notification_queue WHERE task_id = :tid AND status = 'pending'");
        $stmt->execute([':tid' => $taskId]);
    }

    private function getTaskTitle($id) {
        $stmt = $this->db->prepare("SELECT title FROM tasks WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() ?: 'Tarea';
    }

    private function getNotificationSettings($userId) {
        $stmt = $this->db->prepare("SELECT * FROM notification_settings WHERE user_id = :uid");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetch() ?: [];
    }

    // Auto-archivar tareas vencidas (para cron)
    public function archiveExpired() {
        $sql = "UPDATE tasks SET status = 'archivado' 
                WHERE status IN ('activo','pendiente','en_proceso') 
                AND end_date < CURDATE()";
        return $this->db->exec($sql);
    }

    public function countByStatus() {
        $userId = Auth::id();
        $isAdmin = Auth::isAdmin();
        $userFilter = $isAdmin ? '' : "AND user_id = $userId";

        $sql = "SELECT status, COUNT(*) as total FROM tasks WHERE 1=1 $userFilter GROUP BY status";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}