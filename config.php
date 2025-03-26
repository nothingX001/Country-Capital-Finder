<?php
// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Custom error handler
function secureErrorHandler($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno]: $errstr in $errfile on line $errline");
    return true;
}
set_error_handler('secureErrorHandler');

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

// Securely load API keys if file exists
$api_keys_file = __DIR__ . '/api_keys.php';
if (file_exists($api_keys_file)) {
    // Verify file permissions
    if (substr(sprintf('%o', fileperms($api_keys_file)), -4) !== '0600') {
        error_log('Warning: api_keys.php has incorrect permissions. Should be 600.');
    }
    
    // Include the file
    require_once $api_keys_file;
}

// Only attempt database connection if DATABASE_URL is set
if ($databaseUrl) {
    try {
        // Parse the DATABASE_URL
        $dbopts = parse_url($databaseUrl);

        $host = $dbopts["host"];
        $port = $dbopts["port"];
        $user = $dbopts["user"];
        $password = $dbopts["pass"];
        $dbname = ltrim($dbopts["path"], '/');

        // Create a new PDO instance
        $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]);
        
        // Set error mode
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        // Don't die here, allow scripts to continue without DB if needed
    }
} else {
    error_log("DATABASE_URL environment variable not set. Database features will be unavailable.");
}
?>
