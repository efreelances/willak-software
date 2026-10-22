<?php
class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function attempt($username, $password, $remember = false) {
        // ✅ CORREGIDO: placeholders únicos :username y :email
        $stmt = $this->db->prepare("SELECT * FROM users WHERE (username = :username OR email = :email) AND status = 'activo' LIMIT 1");
        $stmt->execute([
            ':username' => $username,
            ':email'    => $username
        ]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Credenciales inválidas.'];
        }

        // Verificar expiración de contraseña
        if (Security::isPasswordExpired($user['password_changed_at'])) {
            return ['success' => false, 'message' => 'Tu contraseña ha expirado. Contacta al administrador.', 'expired' => true];
        }

        // Si tiene 2FA activo, pedir código (Fase 6)
        if ($user['totp_enabled']) {
            Session::set('2fa_pending_user_id', $user['id']);
            return ['success' => false, 'require_2fa' => true];
        }

        $this->loginUser($user, $remember);
        return ['success' => true];
    }

    public function loginUser($user, $remember = false) {
        session_regenerate_id(true);
        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['full_name']);
        Session::set('user_role', $user['role']);
        Session::set('user_email', $user['email']);
        Session::set('user_username', $user['username']);

        // Actualizar último login
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $stmt->execute([':id' => $user['id']]);

        if ($remember) {
            $token = Security::randomToken();
            $expires = date('Y-m-d H:i:s', strtotime('+' . REMEMBER_ME_DAYS . ' days'));
            $hash = hash('sha256', $token);
            $stmt = $this->db->prepare("UPDATE users SET remember_token = :t, remember_expires = :e WHERE id = :id");
            $stmt->execute([':t' => $hash, ':e' => $expires, ':id' => $user['id']]);
            
            // ✅ CORREGIDO: Detecta si es HTTPS para el flag 'secure' de la cookie
            $isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
            setcookie('remember_me', $user['id'] . ':' . $token, time() + (86400 * REMEMBER_ME_DAYS), '/', '', $isSecure, true);
        }
    }

    public function checkRememberMe() {
        if (Session::has('user_id')) return true;
        if (!isset($_COOKIE['remember_me'])) return false;

        $parts = explode(':', $_COOKIE['remember_me']);
        if (count($parts) !== 2) return false;

        [$userId, $token] = $parts;
        $hash = hash('sha256', $token);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id AND remember_token = :t AND remember_expires > NOW() AND status = 'activo'");
        $stmt->execute([':id' => $userId, ':t' => $hash]);
        $user = $stmt->fetch();

        if ($user) {
            $this->loginUser($user, false);
            return true;
        }
        return false;
    }

    public static function check() {
        return Session::has('user_id');
    }

    public static function user() {
        if (!self::check()) return null;
        return [
            'id'       => Session::get('user_id'),
            'name'     => Session::get('user_name'),
            'role'     => Session::get('user_role'),
            'email'    => Session::get('user_email'),
            'username' => Session::get('user_username'),
        ];
    }

    public static function isAdmin() {
        return Session::get('user_role') === 'admin';
    }

    public static function id() {
        return Session::get('user_id');
    }

    public function logout() {
        if (Session::has('user_id')) {
            $stmt = $this->db->prepare("UPDATE users SET remember_token = NULL, remember_expires = NULL WHERE id = :id");
            $stmt->execute([':id' => Session::get('user_id')]);
        }
        
        Session::destroy();
        
        // ✅ CORREGIDO: Borrar la cookie usando el mismo flag 'secure' que se usó al crearla
        if (isset($_COOKIE['remember_me'])) {
            $isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
            setcookie('remember_me', '', time() - 3600, '/', '', $isSecure, true);
        }
    }

    public static function requireLogin() {
        if (!self::check()) {
            $auth = new self();
            if (!$auth->checkRememberMe()) {
                header('Location: ' . APP_URL . '/login');
                exit;
            }
        }
    }

    public static function requireAdmin() {
        self::requireLogin();
        if (!self::isAdmin()) {
            http_response_code(403);
            include BASE_PATH . 'views/errors/403.php';
            exit;
        }
    }
}