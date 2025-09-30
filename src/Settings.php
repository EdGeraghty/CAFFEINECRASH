<?php
namespace App;

class Settings {
    private \PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function get(string $key, string $default = ''): string {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        
        return $result ? $result['setting_value'] : $default;
    }
    
    public function set(string $key, string $value): bool {
        $stmt = $this->db->prepare("
            INSERT INTO settings (setting_key, setting_value, updated_at) 
            VALUES (?, ?, CURRENT_TIMESTAMP)
            ON CONFLICT(setting_key) 
            DO UPDATE SET setting_value = ?, updated_at = CURRENT_TIMESTAMP
        ");
        
        return $stmt->execute([$key, $value, $value]);
    }
    
    public function isRegistrationEnabled(): bool {
        return $this->get('registration_enabled', '1') === '1';
    }
    
    public function setRegistrationEnabled(bool $enabled): bool {
        return $this->set('registration_enabled', $enabled ? '1' : '0');
    }
}
