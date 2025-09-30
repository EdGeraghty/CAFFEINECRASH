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
    public function createAdminUser(string $username, string $email, string $password, array $config = []): array {
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
            
            // Create .env file if config is provided
            if (!empty($config)) {
                $envResult = $this->createEnvFile($config);
                if (!$envResult['success']) {
                    $result['message'] = $envResult['message'];
                    return $result;
                }
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
     * Create or update .env file with configuration
     */
    public function createEnvFile(array $config): array {
        $result = [
            'success' => false,
            'message' => ''
        ];
        
        try {
            $envPath = PROJECT_ROOT . '/.env';
            
            // Check if .env already exists
            if (file_exists($envPath)) {
                // Don't overwrite existing .env file
                $result['success'] = true;
                $result['message'] = 'Using existing .env configuration';
                return $result;
            }
            
            // Set defaults for missing values
            $defaults = [
                'DB_PATH' => 'data/caffeinecrash.db',
                'SESSION_NAME' => 'CAFFEINECRASH_SESSION',
                'SESSION_LIFETIME' => '3600',
                'APP_NAME' => 'CAFFEINECRASH',
                'APP_URL' => 'http://localhost:8000',
                'DEBUG' => 'true',
                'HASH_ALGO' => 'PASSWORD_ARGON2ID'
            ];
            
            // Merge with provided config
            $config = array_merge($defaults, $config);
            
            // Build .env content
            $envContent = "# Database Configuration\n";
            $envContent .= "DB_PATH=" . $config['DB_PATH'] . "\n\n";
            
            $envContent .= "# Session Configuration\n";
            $envContent .= "SESSION_NAME=" . $config['SESSION_NAME'] . "\n";
            $envContent .= "SESSION_LIFETIME=" . $config['SESSION_LIFETIME'] . "\n\n";
            
            $envContent .= "# Application Settings\n";
            $envContent .= "APP_NAME=" . $config['APP_NAME'] . "\n";
            $envContent .= "APP_URL=" . $config['APP_URL'] . "\n";
            $envContent .= "DEBUG=" . $config['DEBUG'] . "\n\n";
            
            $envContent .= "# Security\n";
            $envContent .= "HASH_ALGO=" . $config['HASH_ALGO'] . "\n";
            
            // Write to file
            if (file_put_contents($envPath, $envContent) === false) {
                $result['message'] = 'Failed to write .env file. Check permissions.';
                return $result;
            }
            
            $result['success'] = true;
            $result['message'] = 'Configuration file created successfully';
            
        } catch (\Exception $e) {
            $result['message'] = 'Error creating .env file: ' . $e->getMessage();
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
