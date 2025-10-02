<?php
namespace App;

class Share {
    private \PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create(int $ownerId, int $sharedWithUserId, string $shareType, array $data): int|false {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO shares (owner_id, shared_with_user_id, share_type, data)
                VALUES (:owner_id, :shared_with_user_id, :share_type, :data)
            ");
            
            $result = $stmt->execute([
                'owner_id' => $ownerId,
                'shared_with_user_id' => $sharedWithUserId,
                'share_type' => $shareType,
                'data' => json_encode($data)
            ]);
            
            return $result ? (int)$this->db->lastInsertId() : false;
        } catch (\PDOException $e) {
            return false;
        }
    }
    
    public function getSharedWithMe(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT s.*, u.username as owner_username 
            FROM shares s
            JOIN users u ON s.owner_id = u.id
            WHERE s.shared_with_user_id = :user_id 
            ORDER BY s.created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        $shares = $stmt->fetchAll();
        
        // Decode JSON data
        foreach ($shares as &$share) {
            $share['data'] = json_decode($share['data'], true);
        }
        
        return $shares;
    }
    
    public function getSharedByMe(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT s.*, u.username as shared_with_username 
            FROM shares s
            JOIN users u ON s.shared_with_user_id = u.id
            WHERE s.owner_id = :user_id 
            ORDER BY s.created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        $shares = $stmt->fetchAll();
        
        // Decode JSON data
        foreach ($shares as &$share) {
            $share['data'] = json_decode($share['data'], true);
        }
        
        return $shares;
    }
    
    public function delete(int $id, int $userId): bool {
        $stmt = $this->db->prepare("
            DELETE FROM shares WHERE id = :id AND owner_id = :user_id
        ");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
}
