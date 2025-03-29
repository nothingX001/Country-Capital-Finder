<?php
// Get the DATABASE_URL environment variable
$databaseUrl = getenv('DATABASE_URL');

// Database connection variables with defaults
$conn = null;
$host = '';
$port = '';
$user = '';
$password = '';
$dbname = '';

// Add OpenAI API key with empty default value
// This will be overridden by api_keys.php if it exists
$openai_api_key = '';

// Only attempt database connection if DATABASE_URL is set
if ($databaseUrl) {
    try {
        error_log("Attempting database connection with URL: " . preg_replace('/:[^:\/]+@/', ':***@', $databaseUrl));
        
        // Parse the DATABASE_URL
        $dbopts = parse_url($databaseUrl);
        
        if ($dbopts === false) {
            throw new Exception("Invalid DATABASE_URL format");
        }

        if (!isset($dbopts["host"]) || !isset($dbopts["port"]) || 
            !isset($dbopts["user"]) || !isset($dbopts["pass"]) || 
            !isset($dbopts["path"])) {
            throw new Exception("Missing required database connection parameters");
        }

        $host = $dbopts["host"];
        $port = $dbopts["port"];
        $user = $dbopts["user"];
        $password = $dbopts["pass"];
        $dbname = ltrim($dbopts["path"], '/');

        // Create a new PDO instance with secure options
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        error_log("Connecting to database at: $host:$port/$dbname");
        
        $conn = new PDO(
            $dsn,
            $user,
            $password,
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            )
        );
        
        // Test the connection
        $conn->query('SELECT 1');
        error_log("Database connection successful");
        
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        error_log("DSN used: pgsql:host=$host;port=$port;dbname=$dbname");
        $conn = null;
    } catch (Exception $e) {
        error_log("Database configuration error: " . $e->getMessage());
        $conn = null;
    }
} else {
    error_log("DATABASE_URL environment variable not set. Database features will be unavailable.");
}

// Prevent direct access to this file
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Direct access not permitted');
}
?>
