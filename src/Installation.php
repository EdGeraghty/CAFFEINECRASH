<?php
namespace App;

class Installation {
    private \PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Check if installation is complete
     */
    public function isInstalled(): bool {
        try {
            $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $stmt->execute(['installation_complete']);
            $result = $stmt->fetch();
            
            return $result && $result['setting_value'] === '1';
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Create the first admin user during installation
     */
    public function createAdminUser(string $username, string $email, string $password): array {
        $result = [
            'success' => false,
            'message' => ''
        ];
        
        try {
            // Check if admin user already exists
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
            $stmt->execute();
            $adminCount = $stmt->fetch()['count'];
            
            if ($adminCount > 0) {
                $result['message'] = 'An admin user already exists';
                return $result;
            }
            
            // Validate input
            if (empty($username) || empty($email) || empty($password)) {
                $result['message'] = 'All fields are required';
                return $result;
            }
            
            if (strlen($password) < 8) {
                $result['message'] = 'Password must be at least 8 characters';
                return $result;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $result['message'] = 'Invalid email format';
                return $result;
            }
            
            // Create admin user
            $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
            
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password_hash, is_admin, is_active) 
                VALUES (:username, :email, :password_hash, 1, 1)
            ");
            
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password_hash' => $passwordHash
            ]);
            
            // Mark installation as complete
            $stmt = $this->db->prepare("
                INSERT OR REPLACE INTO settings (setting_key, setting_value, updated_at) 
                VALUES ('installation_complete', '1', CURRENT_TIMESTAMP)
            ");
            $stmt->execute();
            
            $result['success'] = true;
            $result['message'] = 'Admin user created successfully!';
            
            // Log the installation
            $logger = new Logger();
            $logger->info('Initial admin user created during installation', ['username' => $username]);
            
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                $result['message'] = 'Username or email already exists';
            } else {
                $result['message'] = 'Database error: ' . $e->getMessage();
            }
        } catch (\Exception $e) {
            $result['message'] = 'Error: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Get the admin user (for display purposes, protected data)
     */
    public function getAdminUser(): ?array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, created_at 
                FROM users 
                WHERE is_admin = 1 
                ORDER BY id ASC 
                LIMIT 1
            ");
            $stmt->execute();
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
