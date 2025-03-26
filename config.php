<?php
// Set error handling
error_reporting(E_ALL);

// Check if we're in development environment
$is_development = getenv('APP_ENV') === 'development' || 
                 $_SERVER['SERVER_NAME'] === 'localhost' || 
                 $_SERVER['SERVER_NAME'] === '127.0.0.1';

if ($is_development) {
    // Show errors in development
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    // Hide errors in production
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// Always log errors
ini_set('log_errors', 1);
$error_log_path = __DIR__ . '/error.log';
ini_set('error_log', $error_log_path);

// Ensure error log file exists and is writable
if (!file_exists($error_log_path)) {
    touch($error_log_path);
    chmod($error_log_path, 0666);
}

// Log startup information
error_log("PHP Version: " . PHP_VERSION);
error_log("Server Software: " . $_SERVER['SERVER_SOFTWARE']);
error_log("Document Root: " . $_SERVER['DOCUMENT_ROOT']);
error_log("Script Filename: " . $_SERVER['SCRIPT_FILENAME']);

// Custom error handler
function secureErrorHandler($errno, $errstr, $errfile, $errline) {
    $is_development = getenv('APP_ENV') === 'development' || 
                     $_SERVER['SERVER_NAME'] === 'localhost' || 
                     $_SERVER['SERVER_NAME'] === '127.0.0.1';
    
    $error_message = "Error [$errno]: $errstr in $errfile on line $errline";
    error_log($error_message);
    
    if ($is_development) {
        // Show detailed errors in development
        return false; // Let PHP handle the error display
    } else {
        // Log but don't show errors in production
        return true;
    }
}
set_error_handler('secureErrorHandler');

// Get the DATABASE_URL environment variable
$databaseUrl = getenv('DATABASE_URL');
error_log("Database URL: " . ($databaseUrl ? "Set" : "Not set"));

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
    error_log("API keys file found");
    // Verify file permissions
    if (substr(sprintf('%o', fileperms($api_keys_file)), -4) !== '0600') {
        error_log('Warning: api_keys.php has incorrect permissions. Should be 600.');
    }
    
    // Include the file
    require_once $api_keys_file;
} else {
    error_log("API keys file not found");
}

// Only attempt database connection if DATABASE_URL is set
if ($databaseUrl) {
    try {
        // Parse the DATABASE_URL
        $dbopts = parse_url($databaseUrl);
        error_log("Database URL parsed successfully");

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
        error_log("Database connection successful");
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        // Create a dummy connection object to prevent null reference errors
        $conn = new stdClass();
        $conn->error = true;
        $conn->error_message = "Database connection failed";
    }
} else {
    error_log("DATABASE_URL environment variable not set. Database features will be unavailable.");
    // Create a dummy connection object to prevent null reference errors
    $conn = new stdClass();
    $conn->error = true;
    $conn->error_message = "Database URL not configured";
}
?>
