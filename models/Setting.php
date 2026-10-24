<?php
class Setting {
    private $db;
    public function __construct() { $this->db = Database::getInstance()->getConnection(); }

    public function get($key, $default = null) {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $value = $stmt->fetchColumn();
        return $value === false ? $default : $value;
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) $settings[$row['setting_key']] = $row['setting_value'];
        return $settings;
    }

    public function set($key, $value, $type = 'string', $category = 'general', $description = '') {
        if (is_array($value)) $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        $stmt = $this->db->prepare("INSERT INTO settings (setting_key, setting_value, description)
            VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), description=VALUES(description)");
        return $stmt->execute([$key, (string)$value, $description ?: $category]);
    }

    public function setBatch($data, $category = 'general') {
        foreach ($data as $key => $value) $this->set($key, $value, null, $category);
        return true;
    }

    public function getChannels() {
        $channels = [];
        foreach (['email', 'whatsapp', 'telegram'] as $channel) {
            $config = json_decode((string)$this->get('notification_' . $channel . '_config', '{}'), true) ?: [];
            $channels[$channel] = [
                'channel_name' => ucfirst($channel),
                'is_active' => (int)$this->get('notification_' . $channel . '_active', 0),
                'config' => $config
            ];
        }
        return $channels;
    }

    public function updateChannel($channelKey, $isActive, $configData) {
        if (!in_array($channelKey, ['email', 'whatsapp', 'telegram'], true)) return false;
        $this->db->beginTransaction();
        try {
            $this->set('notification_' . $channelKey . '_config', $configData, null, 'notifications', 'Configuración del canal ' . $channelKey);
            $this->set('notification_' . $channelKey . '_active', (int)(bool)$isActive, null, 'notifications', 'Canal habilitado');
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}
