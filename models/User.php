<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($includeInactive = false) {
        $sql = "SELECT id, username, email, full_name, role, status, last_login, created_at 
                FROM users";
        if (!$includeInactive) {
            $sql .= " WHERE status = 'activo'";
        }
        $sql .= " ORDER BY full_name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $errors = [];
        
        if (empty($data['username'])) {
            $errors[] = 'El usuario es obligatorio.';
        }
        if (!Security::isValidEmail($data['email'] ?? '')) {
            $errors[] = 'Email inválido.';
        }
        if (!Security::validatePassword($data['password'] ?? '', $pwdErrors)) {
            $errors = array_merge($errors, $pwdErrors);
        }
        if (!in_array($data['role'] ?? '', ['admin', 'user'])) {
            $errors[] = 'Rol inválido.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Verificar duplicados
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :u OR email = :e");
        $stmt->execute([':u' => $data['username'], ':e' => $data['email']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'errors' => ['Usuario o email ya existen.']];
        }

        $hash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password_hash, full_name, role, status) 
            VALUES (:u, :e, :p, :n, :r, 'activo')
        ");
        
        $stmt->execute([
            ':u' => $data['username'],
            ':e' => $data['email'],
            ':p' => $hash,
            ':n' => $data['full_name'],
            ':r' => $data['role']
        ]);
        
        return ['success' => true, 'id' => $this->db->lastInsertId()];
    }

    public function update($id, $data) {
        $user = $this->find($id);
        if (!$user) {
            return ['success' => false, 'errors' => ['Usuario no encontrado.']];
        }

        $errors = [];
        if (empty($data['username'])) {
            $errors[] = 'El usuario es obligatorio.';
        }
        if (!Security::isValidEmail($data['email'] ?? '')) {
            $errors[] = 'Email inválido.';
        }
        if (!in_array($data['role'] ?? '', ['admin', 'user'])) {
            $errors[] = 'Rol inválido.';
        }
        if (!in_array($data['status'] ?? '', ['activo', 'inactivo'])) {
            $errors[] = 'Estado inválido.';
        }

        // Verificar duplicados (excluyendo el usuario actual)
        $stmt = $this->db->prepare("SELECT id FROM users WHERE (username = :u OR email = :e) AND id != :id");
        $stmt->execute([':u' => $data['username'], ':e' => $data['email'], ':id' => $id]);
        if ($stmt->fetch()) {
            return ['success' => false, 'errors' => ['Usuario o email ya existen.']];
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Construir SQL base
        $sql = "UPDATE users SET 
                username = :u, 
                email = :e, 
                full_name = :n, 
                role = :r, 
                status = :s 
                WHERE id = :id";
        
        $params = [
            ':u' => $data['username'],
            ':e' => $data['email'],
            ':n' => $data['full_name'],
            ':r' => $data['role'],
            ':s' => $data['status'],
            ':id' => $id
        ];

        // Si hay nueva contraseña, agregar al SQL
        if (!empty($data['password'])) {
            if (!Security::validatePassword($data['password'], $pwdErrors)) {
                return ['success' => false, 'errors' => $pwdErrors];
            }
            
            $sql .= ", password_hash = :p, password_changed_at = NOW()";
            $params[':p'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return ['success' => true];
    }

    public function deactivate($id) {
        $stmt = $this->db->prepare("UPDATE users SET status = 'inactivo' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function reactivate($id) {
        $stmt = $this->db->prepare("UPDATE users SET status = 'activo' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function resetPassword($id, $newPassword) {
        if (!Security::validatePassword($newPassword, $errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = :p, password_changed_at = NOW() WHERE id = :id");
        $stmt->execute([':p' => $hash, ':id' => $id]);
        
        return ['success' => true];
    }
    
}