<?php
namespace App;

class Analytics {
    private \PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getTotalUsers(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        return (int)$stmt->fetch()['count'];
    }
    
    public function getActiveUsers(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
        return (int)$stmt->fetch()['count'];
    }
    
    public function getAdminUsers(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
        return (int)$stmt->fetch()['count'];
    }
    
    public function getTotalMedications(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM medications");
        return (int)$stmt->fetch()['count'];
    }
    
    public function getTotalHealthRecords(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM health_data");
        return (int)$stmt->fetch()['count'];
    }
    
    public function getTotalReminders(): int {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM reminders");
        return (int)$stmt->fetch()['count'];
    }
    
    public function getRecentUsers(int $days = 7): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM users 
            WHERE created_at >= datetime('now', '-' || :days || ' days')
        ");
        $stmt->execute(['days' => $days]);
        return (int)$stmt->fetch()['count'];
    }
    
    public function getHealthDataByType(): array {
        $stmt = $this->db->query("
            SELECT data_type, COUNT(*) as count 
            FROM health_data 
            GROUP BY data_type 
            ORDER BY count DESC
        ");
        return $stmt->fetchAll();
    }
    
    public function getUserGrowth(int $months = 6): array {
        $stmt = $this->db->prepare("
            SELECT 
                strftime('%Y-%m', created_at) as month,
                COUNT(*) as count
            FROM users
            WHERE created_at >= datetime('now', '-' || :months || ' months')
            GROUP BY month
            ORDER BY month ASC
        ");
        $stmt->execute(['months' => $months]);
        return $stmt->fetchAll();
    }
    
    public function getTopMedications(int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT name, COUNT(*) as count
            FROM medications
            GROUP BY LOWER(name)
            ORDER BY count DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getDatabaseSize(): string {
        $dbPath = $_ENV['DB_PATH'];
        if (file_exists($dbPath)) {
            $bytes = filesize($dbPath);
            $units = ['B', 'KB', 'MB', 'GB'];
            $factor = floor((strlen((string)$bytes) - 1) / 3);
            return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
        }
        return 'Unknown';
    }
    
    public function getLogStats(): array {
        $stmt = $this->db->query("
            SELECT level, COUNT(*) as count
            FROM logs
            GROUP BY level
            ORDER BY count DESC
        ");
        return $stmt->fetchAll();
    }
}
