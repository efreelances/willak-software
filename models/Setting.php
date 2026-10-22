<?php
class Setting {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection(); 
    }

    public function get($key, $default = null) {
        $stmt = $this->db->prepare("SELECT setting_value FROM app_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['setting_value'] : $default;
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM app_settings");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public function set($key, $value, $type = 'string', $category = 'general', $description = '') {
        $stmt = $this->db->prepare("
            INSERT INTO app_settings (setting_key, setting_value, setting_type, category, description) 
            VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP
        ");
        return $stmt->execute([$key, $value, $type, $category, $description, $value]);
    }

    public function setBatch($data, $category = 'general') {
        $stmt = $this->db->prepare("
            INSERT INTO app_settings (setting_key, setting_value, setting_type, category) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP
        ");
        foreach ($data as $key => $value) {
            $val = is_array($value) ? json_encode($value) : $value;
            $type = is_numeric($value) ? 'number' : 'string';
            $stmt->execute([$key, $val, $type, $category, $val]);
        }
        return true;
    }

    public function getChannels() {
        $stmt = $this->db->query("SELECT * FROM notification_channels");
        $channels = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $channels[$row['channel_key']] = [
                'channel_name' => $row['channel_name'],
                'is_active' => $row['is_active'],
                'config' => json_decode($row['config_data'], true) ?: []
            ];
        }
        return $channels;
    }

    public function updateChannel($channelKey, $isActive, $configData) {
        $stmt = $this->db->prepare("
            INSERT INTO notification_channels (channel_key, channel_name, is_active, config_data) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE is_active = ?, config_data = ?, updated_at = CURRENT_TIMESTAMP
        ");
        
        // Nombres legibles para cada canal
        $names = [
            'email' => 'Correo Electrónico',
            'whatsapp' => 'WhatsApp',
            'telegram' => 'Telegram'
        ];
        
        $channelName = $names[$channelKey] ?? ucfirst($channelKey);
        
        return $stmt->execute([
            $channelKey, 
            $channelName, 
            $isActive, 
            json_encode($configData), 
            $isActive, 
            json_encode($configData)
        ]);
    }
}