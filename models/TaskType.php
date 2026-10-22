<?php
class TaskType {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function getAll($includeInactive = false) {
        $sql = "SELECT * FROM task_types";
        if (!$includeInactive) $sql .= " WHERE status = 'activo'";
        $sql .= " ORDER BY name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM task_types WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $errors = [];
        if (empty($data['name'])) $errors[] = 'El nombre es obligatorio.';
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $stmt = $this->db->prepare("INSERT INTO task_types (name, description, status) VALUES (:n, :d, 'activo')");
        $stmt->execute([':n' => $data['name'], ':d' => $data['description'] ?? null]);
        return ['success' => true, 'id' => $this->db->lastInsertId()];
    }

    public function update($id, $data) {
        $errors = [];
        if (empty($data['name'])) $errors[] = 'El nombre es obligatorio.';
        if (!in_array($data['status'] ?? '', ['activo','inactivo'])) $errors[] = 'Estado inválido.';
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $stmt = $this->db->prepare("UPDATE task_types SET name = :n, description = :d, status = :s WHERE id = :id");
        $stmt->execute([
            ':n' => $data['name'], ':d' => $data['description'] ?? null,
            ':s' => $data['status'], ':id' => $id
        ]);
        return ['success' => true];
    }

    public function deactivate($id) {
        $stmt = $this->db->prepare("UPDATE task_types SET status = 'inactivo' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function reactivate($id) {
        $stmt = $this->db->prepare("UPDATE task_types SET status = 'activo' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}