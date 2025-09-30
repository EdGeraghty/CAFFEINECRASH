<?php
namespace App;

class Logger {
    private \PDO $db;
    
    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function log(string $level, string $message, ?array $context = null, ?int $userId = null): void {
        $stmt = $this->db->prepare("
            INSERT INTO logs (level, message, context, user_id, ip_address, user_agent)
            VALUES (:level, :message, :context, :user_id, :ip_address, :user_agent)
        ");
        
        $stmt->execute([
            'level' => $level,
            'message' => $message,
            'context' => $context ? json_encode($context) : null,
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    public function debug(string $message, ?array $context = null, ?int $userId = null): void {
        $this->log(self::LEVEL_DEBUG, $message, $context, $userId);
    }
    
    public function info(string $message, ?array $context = null, ?int $userId = null): void {
        $this->log(self::LEVEL_INFO, $message, $context, $userId);
    }
    
    public function warning(string $message, ?array $context = null, ?int $userId = null): void {
        $this->log(self::LEVEL_WARNING, $message, $context, $userId);
    }
    
    public function error(string $message, ?array $context = null, ?int $userId = null): void {
        $this->log(self::LEVEL_ERROR, $message, $context, $userId);
    }
    
    public function critical(string $message, ?array $context = null, ?int $userId = null): void {
        $this->log(self::LEVEL_CRITICAL, $message, $context, $userId);
    }
    
    public function getLogs(int $page = 1, int $perPage = 50, ?string $level = null, ?int $userId = null): array {
        $offset = ($page - 1) * $perPage;
        
        $where = [];
        $params = [];
        
        if ($level) {
            $where[] = "level = :level";
            $params['level'] = $level;
        }
        
        if ($userId) {
            $where[] = "user_id = :user_id";
            $params['user_id'] = $userId;
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $stmt = $this->db->prepare("
            SELECT l.*, u.username
            FROM logs l
            LEFT JOIN users u ON l.user_id = u.id
            $whereClause
            ORDER BY l.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function getTotalLogs(?string $level = null, ?int $userId = null): int {
        $where = [];
        $params = [];
        
        if ($level) {
            $where[] = "level = :level";
            $params['level'] = $level;
        }
        
        if ($userId) {
            $where[] = "user_id = :user_id";
            $params['user_id'] = $userId;
        }
        
        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM logs $whereClause");
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();
        
        return (int)$stmt->fetch()['count'];
    }
    
    public function clearOldLogs(int $daysToKeep = 90): int {
        $stmt = $this->db->prepare("
            DELETE FROM logs WHERE created_at < datetime('now', '-' || :days || ' days')
        ");
        $stmt->execute(['days' => $daysToKeep]);
        return $stmt->rowCount();
    }
}
