<?php
/**
 * Obtener el nombre de la aplicación desde la base de datos
 */
function getAppName() {
    static $appName = null;
    
    if ($appName === null) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'app_name' LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetchColumn();
            $appName = $result ?: 'Willak'; // Valor por defecto si no existe
        } catch (Exception $e) {
            $appName = 'Willak'; // Fallback si hay error
        }
    }
    
    return $appName;
}

/**
 * Obtener cualquier configuración desde la base de datos
 */
function getSetting($key, $default = '') {
    static $settings = null;
    
    if ($settings === null) {
        $settings = [];
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            // Si falla, usar valores por defecto
        }
    }
    
    return $settings[$key] ?? $default;
}