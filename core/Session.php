<?php
class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
            ini_set('session.use_strict_mode', 1);
            session_name('WILLAK_SID');
            session_start();
        }
        // Timeout por inactividad
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
            self::destroy();
            header('Location: ' . APP_URL . '/login?expired=1');
            exit;
        }
        $_SESSION['last_activity'] = time();
    }

    public static function set($key, $value) { $_SESSION[$key] = $value; }
    public static function get($key, $default = null) { return $_SESSION[$key] ?? $default; }
    public static function has($key) { return isset($_SESSION[$key]); }
    public static function unset($key) { unset($_SESSION[$key]); }

    public static function destroy() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
        }
        session_destroy();
    }

    public static function flash($key, $value = null) {
        if ($value !== null) {
            $_SESSION['flash'][$key] = $value;
        } else {
            $val = $_SESSION['flash'][$key] ?? null;
            unset($_SESSION['flash'][$key]);
            return $val;
        }
    }
}