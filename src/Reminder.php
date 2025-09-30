<?php
namespace App;

class Reminder {
    private \PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create(int $userId, array $data): int|false {
        $stmt = $this->db->prepare("
            INSERT INTO reminders (user_id, medication_id, title, description, remind_at)
            VALUES (:user_id, :medication_id, :title, :description, :remind_at)
        ");
        
        $result = $stmt->execute([
            'user_id' => $userId,
            'medication_id' => $data['medication_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'remind_at' => $data['remind_at']
        ]);
        
        return $result ? (int)$this->db->lastInsertId() : false;
    }
    
    public function getAll(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT r.*, m.name as medication_name 
            FROM reminders r
            LEFT JOIN medications m ON r.medication_id = m.id
            WHERE r.user_id = :user_id 
            ORDER BY r.remind_at ASC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    public function getPending(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT r.*, m.name as medication_name 
            FROM reminders r
            LEFT JOIN medications m ON r.medication_id = m.id
            WHERE r.user_id = :user_id AND r.is_completed = 0
            ORDER BY r.remind_at ASC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    public function markCompleted(int $id, int $userId): bool {
        $stmt = $this->db->prepare("
            UPDATE reminders SET is_completed = 1 
            WHERE id = :id AND user_id = :user_id
        ");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
    
    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("
            DELETE FROM reminders WHERE id = :id AND user_id = :user_id
        ");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
}
