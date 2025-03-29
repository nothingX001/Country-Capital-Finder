<?php
// Security middleware

class Security {
    private static $instance = null;
    private $conn;
    
    private function __construct($conn) {
        $this->conn = $conn;
    }
    
    public static function getInstance($conn) {
        if (self::$instance === null) {
            self::$instance = new self($conn);
        }
        return self::$instance;
    }
    
    public function validateCountryId($id) {
        if (!is_numeric($id)) {
            return false;
        }
        
        // Verify the ID exists in database
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM countries WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }
    
    public function sanitizeOutput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeOutput'], $data);
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    public function validateInput($input) {
        return filter_var($input, FILTER_SANITIZE_STRING);
    }
    
    public function rateLimit() {
        $ip = $_SERVER['REMOTE_ADDR'];
        $timeframe = 60; // 1 minute
        $max_requests = 60; // Maximum requests per minute
        
        $stmt = $this->conn->prepare('
            SELECT COUNT(*) 
            FROM access_logs 
            WHERE ip = ? 
            AND timestamp > NOW() - INTERVAL ? SECOND
        ');
        $stmt->execute([$ip, $timeframe]);
        
        if ($stmt->fetchColumn() > $max_requests) {
            http_response_code(429);
            die('Too many requests. Please try again later.');
        }
        
        // Log the access
        $stmt = $this->conn->prepare('
            INSERT INTO access_logs (ip, timestamp) 
            VALUES (?, NOW())
        ');
        $stmt->execute([$ip]);
    }
} 