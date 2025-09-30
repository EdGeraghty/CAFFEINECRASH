<?php
namespace App;

class Auth {
    private \PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function register(string $username, string $email, string $password): bool {
        try {
            $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
            
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password_hash) 
                VALUES (:username, :email, :password_hash)
            ");
            
            return $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password_hash' => $passwordHash
            ]);
        } catch (\PDOException $e) {
            return false;
        }
    }
    
    public function login(string $username, string $password): bool {
        $stmt = $this->db->prepare("
            SELECT id, password_hash FROM users WHERE username = :username
        ");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            return true;
        }
        
        return false;
    }
    
    public function logout(): void {
        session_destroy();
        session_start();
    }
    
    public function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }
    
    public function getCurrentUserId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }
    
    public function getCurrentUsername(): ?string {
        return $_SESSION['username'] ?? null;
    }
    
    public function getUserById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT id, username, email, created_at FROM users WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }
    
    public function searchUsers(string $query): array {
        $stmt = $this->db->prepare("
            SELECT id, username, email FROM users 
            WHERE username LIKE :query OR email LIKE :query
            LIMIT 10
        ");
        $stmt->execute(['query' => "%$query%"]);
        return $stmt->fetchAll();
    }
}
