<?php
// Enable error display temporarily for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enable error logging at the start
ini_set('log_errors', 1);
error_log("Starting config.php");

// Load environment variables from .env file
function loadEnv() {
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    } else {
        error_log(".env file not found");
    }
}

// Load environment variables
loadEnv();

// Get the DATABASE_URL environment variable
$databaseUrl = getenv('DATABASE_URL');
error_log("DATABASE_URL environment variable " . ($databaseUrl ? "is set" : "is NOT set"));

// Try alternative methods to get environment variable if not found
if (!$databaseUrl) {
    // Try $_SERVER superglobal
    $databaseUrl = $_SERVER['DATABASE_URL'] ?? null;
    if ($databaseUrl) {
        error_log("Found DATABASE_URL in \$_SERVER");
    }
    
    // Try Apache getenv
    if (!$databaseUrl && function_exists('apache_getenv')) {
        $databaseUrl = apache_getenv('DATABASE_URL');
        if ($databaseUrl) {
            error_log("Found DATABASE_URL using apache_getenv()");
        }
    }
}

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
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
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

// Log loaded PHP modules and PostgreSQL availability
error_log("Loaded PHP modules: " . implode(", ", get_loaded_extensions()));
error_log("PostgreSQL extension " . (extension_loaded('pgsql') ? "is loaded" : "is NOT loaded"));
error_log("PDO PostgreSQL extension " . (extension_loaded('pdo_pgsql') ? "is loaded" : "is NOT loaded"));

// Prevent direct access to this file
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Direct access not permitted');
}
?>
