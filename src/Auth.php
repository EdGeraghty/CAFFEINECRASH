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
            SELECT id, password_hash, is_admin, is_active, totp_secret FROM users WHERE username = :username
        ");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            if (!$user['is_active']) {
                return false;
            }
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            $_SESSION['is_admin'] = (bool)$user['is_admin'];
            $_SESSION['totp_verified'] = $user['totp_secret'] ? false : true;
            $_SESSION['totp_required'] = (bool)$user['totp_secret'];
            
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
            SELECT id, username, email, is_admin, is_active, totp_secret, created_at FROM users WHERE id = :id
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
    
    public function isAdmin(): bool {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }
    
    public function isTOTPVerified(): bool {
        return !isset($_SESSION['totp_required']) || 
               $_SESSION['totp_required'] === false || 
               (isset($_SESSION['totp_verified']) && $_SESSION['totp_verified'] === true);
    }
    
    public function verifyTOTP(string $code): bool {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        $user = $this->getUserById($_SESSION['user_id']);
        if (!$user || !$user['totp_secret']) {
            return false;
        }
        
        if (TOTP::verify($user['totp_secret'], $code)) {
            $_SESSION['totp_verified'] = true;
            return true;
        }
        
        return false;
    }
    
    public function enableTOTP(int $userId): string {
        $secret = TOTP::generateSecret();
        $stmt = $this->db->prepare("
            UPDATE users SET totp_secret = :secret WHERE id = :id
        ");
        $stmt->execute(['secret' => $secret, 'id' => $userId]);
        return $secret;
    }
    
    public function disableTOTP(int $userId): bool {
        $stmt = $this->db->prepare("
            UPDATE users SET totp_secret = NULL WHERE id = :id
        ");
        return $stmt->execute(['id' => $userId]);
    }
}
