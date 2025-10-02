<?php
namespace App;

class Database {
    private static ?\PDO $instance = null;
    
    public static function getInstance(): \PDO {
        if (self::$instance === null) {
            $dbPath = $_ENV['DB_PATH'];
            $dbDir = dirname($dbPath);
            
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            self::$instance = new \PDO("sqlite:$dbPath");
            self::$instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            
            // Enable foreign key constraints in SQLite
            self::$instance->exec("PRAGMA foreign_keys = ON");
            
            self::initSchema();
        }
        
        return self::$instance;
    }
    
    private static function initSchema(): void {
        $db = self::$instance;
        
        // Users table
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(100) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            is_admin BOOLEAN DEFAULT 0,
            is_active BOOLEAN DEFAULT 1,
            totp_secret VARCHAR(32),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Medications table
        $db->exec("CREATE TABLE IF NOT EXISTS medications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            name VARCHAR(255) NOT NULL,
            dosage VARCHAR(100),
            frequency VARCHAR(100),
            prescriber VARCHAR(255),
            prescribed_for TEXT,
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        // Health data table
        $db->exec("CREATE TABLE IF NOT EXISTS health_data (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            data_type VARCHAR(50) NOT NULL,
            value REAL NOT NULL,
            unit VARCHAR(20),
            recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            notes TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        // Reminders table
        $db->exec("CREATE TABLE IF NOT EXISTS reminders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            medication_id INTEGER,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            remind_at DATETIME NOT NULL,
            is_completed BOOLEAN DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (medication_id) REFERENCES medications(id) ON DELETE CASCADE
        )");
        
        // Shares table
        $db->exec("CREATE TABLE IF NOT EXISTS shares (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            owner_id INTEGER NOT NULL,
            shared_with_user_id INTEGER NOT NULL,
            share_type VARCHAR(50) NOT NULL,
            data TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (shared_with_user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        
        // Create indexes
        $db->exec("CREATE INDEX IF NOT EXISTS idx_medications_user_id ON medications(user_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_health_data_user_id ON health_data(user_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_health_data_type ON health_data(data_type)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_reminders_user_id ON reminders(user_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_shares_owner_id ON shares(owner_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_shares_shared_with ON shares(shared_with_user_id)");
        
        // Logs table for admin panel
        $db->exec("CREATE TABLE IF NOT EXISTS logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            level VARCHAR(20) NOT NULL,
            message TEXT NOT NULL,
            context TEXT,
            user_id INTEGER,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )");
        
        $db->exec("CREATE INDEX IF NOT EXISTS idx_logs_level ON logs(level)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_logs_created_at ON logs(created_at)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_logs_user_id ON logs(user_id)");
        
        // Settings table for system configuration
        $db->exec("CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT NOT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Initialize default settings
        $stmt = $db->prepare("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute(['registration_enabled', '1']);
    }
}
