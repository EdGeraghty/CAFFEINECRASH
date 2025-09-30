<?php
namespace App;

class AdminUser {
    private \PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAllUsers(int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("
            SELECT id, username, email, is_admin, is_active, created_at, updated_at
            FROM users
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getTotalUsers(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        return (int)$stmt->fetch()['count'];
    }
    
    public function toggleAdmin(int $userId): bool {
        $stmt = $this->db->prepare("
            UPDATE users SET is_admin = NOT is_admin WHERE id = :id
        ");
        return $stmt->execute(['id' => $userId]);
    }
    
    public function toggleActive(int $userId): bool {
        $stmt = $this->db->prepare("
            UPDATE users SET is_active = NOT is_active WHERE id = :id
        ");
        return $stmt->execute(['id' => $userId]);
    }
    
    public function resetPassword(int $userId, string $newPassword): bool {
        $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
        $stmt = $this->db->prepare("
            UPDATE users SET password_hash = :password_hash WHERE id = :id
        ");
        return $stmt->execute(['password_hash' => $passwordHash, 'id' => $userId]);
    }
    
    public function updateUser(int $userId, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET username = :username, email = :email, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $userId,
            'username' => $data['username'],
            'email' => $data['email']
        ]);
    }
    
    public function deleteUser(int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $userId]);
    }
    
    public function getUserStats(int $userId): array {
        $stats = [];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM medications WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $stats['medications'] = (int)$stmt->fetch()['count'];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM health_data WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $stats['health_data'] = (int)$stmt->fetch()['count'];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM reminders WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $stats['reminders'] = (int)$stmt->fetch()['count'];
        
        return $stats;
    }
}
