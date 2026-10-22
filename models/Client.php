<?php
class Client {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll($userId = null, $includeInactive = false) {
        $sql = "SELECT c.*, u.full_name AS owner_name 
                FROM clients c 
                INNER JOIN users u ON u.id = c.user_id 
                WHERE 1=1";
        $params = [];
        if (!Auth::isAdmin()) {
            $sql .= " AND c.user_id = :uid";
            $params[':uid'] = Auth::id();
        } elseif ($userId) {
            $sql .= " AND c.user_id = :uid";
            $params[':uid'] = $userId;
        }
        if (!$includeInactive) $sql .= " AND c.status = 'activo'";
        $sql .= " ORDER BY c.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find($id) {
        $sql = "SELECT * FROM clients WHERE id = :id";
        if (!Auth::isAdmin()) $sql .= " AND user_id = " . (int)Auth::id();
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $errors = $this->validate($data);
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $userId = Auth::isAdmin() ? ($data['user_id'] ?? Auth::id()) : Auth::id();

        $stmt = $this->db->prepare("INSERT INTO clients (user_id, name, document_type, document, email, phone, address, notes, status) 
                                    VALUES (:uid, :n, :dt, :d, :e, :p, :a, :no, 'activo')");
        $stmt->execute([
            ':uid' => $userId, ':n' => $data['name'], ':dt' => $data['document_type'] ?? 'DNI',
            ':d' => $data['document'] ?? null, ':e' => $data['email'] ?? null,
            ':p' => $data['phone'] ?? null, ':a' => $data['address'] ?? null,
            ':no' => $data['notes'] ?? null
        ]);
        return ['success' => true, 'id' => $this->db->lastInsertId()];
    }

    public function update($id, $data) {
        $client = $this->find($id);
        if (!$client) return ['success' => false, 'errors' => ['Cliente no encontrado.']];

        $errors = $this->validate($data);
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $sql = "UPDATE clients SET name = :n, document_type = :dt, document = :d, 
                email = :e, phone = :p, address = :a, notes = :no, status = :s WHERE id = :id";
        if (Auth::isAdmin()) $sql = str_replace('WHERE id', ', user_id = :uid WHERE id', $sql);

        $params = [
            ':n' => $data['name'], ':dt' => $data['document_type'] ?? 'DNI',
            ':d' => $data['document'] ?? null, ':e' => $data['email'] ?? null,
            ':p' => $data['phone'] ?? null, ':a' => $data['address'] ?? null,
            ':no' => $data['notes'] ?? null, ':s' => $data['status'] ?? 'activo',
            ':id' => $id
        ];
        if (Auth::isAdmin()) $params[':uid'] = $data['user_id'] ?? $client['user_id'];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ['success' => true];
    }

    public function deactivate($id) {
        $client = $this->find($id);
        if (!$client) return false;
        $stmt = $this->db->prepare("UPDATE clients SET status = 'inactivo' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function reactivate($id) {
        $client = $this->find($id);
        if (!$client) return false;
        $stmt = $this->db->prepare("UPDATE clients SET status = 'activo' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    private function validate($data) {
        $errors = [];
        if (empty($data['name'])) $errors[] = 'El nombre es obligatorio.';
        if (!empty($data['email']) && !Security::isValidEmail($data['email'])) $errors[] = 'Email inválido.';
        return $errors;
    }

    public function countByUser($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM clients WHERE user_id = :u AND status = 'activo'");
        $stmt->execute([':u' => $userId]);
        return $stmt->fetchColumn();
    }
}