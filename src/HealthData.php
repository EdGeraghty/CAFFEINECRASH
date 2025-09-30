<?php
namespace App;

class HealthData {
    private \PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create(int $userId, string $dataType, float $value, ?string $unit = null, ?string $notes = null): int|false {
        $stmt = $this->db->prepare("
            INSERT INTO health_data (user_id, data_type, value, unit, notes)
            VALUES (:user_id, :data_type, :value, :unit, :notes)
        ");
        
        $result = $stmt->execute([
            'user_id' => $userId,
            'data_type' => $dataType,
            'value' => $value,
            'unit' => $unit,
            'notes' => $notes
        ]);
        
        return $result ? (int)$this->db->lastInsertId() : false;
    }
    
    public function getAll(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM health_data WHERE user_id = :user_id ORDER BY recorded_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    public function getByType(int $userId, string $dataType): array {
        $stmt = $this->db->prepare("
            SELECT * FROM health_data 
            WHERE user_id = :user_id AND data_type = :data_type 
            ORDER BY recorded_at DESC
        ");
        $stmt->execute(['user_id' => $userId, 'data_type' => $dataType]);
        return $stmt->fetchAll();
    }
    
    public function getLatest(int $userId, string $dataType): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM health_data 
            WHERE user_id = :user_id AND data_type = :data_type 
            ORDER BY recorded_at DESC LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId, 'data_type' => $dataType]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("
            DELETE FROM health_data WHERE id = :id AND user_id = :user_id
        ");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
    
    public function calculateBMI(float $weight, float $height): float {
        // weight in kg, height in meters
        return round($weight / ($height * $height), 2);
    }
    
    public function getAverage(int $userId, string $dataType, int $days = 30): ?float {
        $stmt = $this->db->prepare("
            SELECT AVG(value) as avg_value FROM health_data 
            WHERE user_id = :user_id AND data_type = :data_type 
            AND recorded_at >= datetime('now', '-' || :days || ' days')
        ");
        $stmt->execute(['user_id' => $userId, 'data_type' => $dataType, 'days' => $days]);
        $result = $stmt->fetch();
        return $result && $result['avg_value'] ? (float)$result['avg_value'] : null;
    }
}
