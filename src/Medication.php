<?php
namespace App;

class Medication {
    private \PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create(int $userId, array $data): int|false {
        $stmt = $this->db->prepare("
            INSERT INTO medications (user_id, name, dosage, frequency, prescriber, prescribed_for, notes)
            VALUES (:user_id, :name, :dosage, :frequency, :prescriber, :prescribed_for, :notes)
        ");
        
        $result = $stmt->execute([
            'user_id' => $userId,
            'name' => $data['name'],
            'dosage' => $data['dosage'] ?? null,
            'frequency' => $data['frequency'] ?? null,
            'prescriber' => $data['prescriber'] ?? null,
            'prescribed_for' => $data['prescribed_for'] ?? null,
            'notes' => $data['notes'] ?? null
        ]);
        
        return $result ? (int)$this->db->lastInsertId() : false;
    }
    
    public function getAll(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM medications WHERE user_id = :user_id ORDER BY created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    public function getById(int $id, int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM medications WHERE id = :id AND user_id = :user_id
        ");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function update(int $id, int $userId, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE medications 
            SET name = :name, dosage = :dosage, frequency = :frequency,
                prescriber = :prescriber, prescribed_for = :prescribed_for,
                notes = :notes, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND user_id = :user_id
        ");
        
        return $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'name' => $data['name'],
            'dosage' => $data['dosage'] ?? null,
            'frequency' => $data['frequency'] ?? null,
            'prescriber' => $data['prescriber'] ?? null,
            'prescribed_for' => $data['prescribed_for'] ?? null,
            'notes' => $data['notes'] ?? null
        ]);
    }
    
    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("
            DELETE FROM medications WHERE id = :id AND user_id = :user_id
        ");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
    
    public function search(int $userId, string $query): array {
        $stmt = $this->db->prepare("
            SELECT * FROM medications 
            WHERE user_id = :user_id AND (
                name LIKE :query OR 
                prescribed_for LIKE :query OR 
                notes LIKE :query
            )
            ORDER BY created_at DESC
        ");
        $stmt->execute(['user_id' => $userId, 'query' => "%$query%"]);
        return $stmt->fetchAll();
    }
}
