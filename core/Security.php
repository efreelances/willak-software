<?php
class Security {
    // Sanitizar string
    public static function clean($value) {
        if (is_array($value)) {
            return array_map([self::class, 'clean'], $value);
        }
        $value = trim($value);
        $value = stripslashes($value);
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    // Generar token CSRF
    public static function csrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Input hidden CSRF
    public static function csrfField() {
        return '<input type="hidden" name="csrf_token" value="' . self::csrfToken() . '">';
    }

    // Validar CSRF
    public static function validateCsrf($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    // Validar email
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Validar password fuerte
    public static function validatePassword($password, &$errors = []) {
        $errors = [];
        if (strlen($password) < PWD_MIN_LENGTH) {
            $errors[] = "Mínimo " . PWD_MIN_LENGTH . " caracteres.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Debe contener al menos una mayúscula.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Debe contener al menos un número.";
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Debe contener al menos un carácter especial.";
        }
        return empty($errors);
    }

    // Verificar expiración de password
    public static function isPasswordExpired($changedAt) {
        if (!$changedAt) return false;
        $changed = strtotime($changedAt);
        $diff = (time() - $changed) / 86400;
        return $diff > PWD_EXPIRY_DAYS;
    }

    // Generar token aleatorio
    public static function randomToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    // Escape para salida HTML
    public static function e($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}