<?php
// Enable error display temporarily for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enable error logging
ini_set('log_errors', 1);
error_log("Starting config.php");

// Direct database connection without .env file
$databaseUrl = "postgres://country_capital_finder_postgresql_user:XqDlRUPvPZYEzasbiy9B5nR8m96sY3KP@dpg-csrani5umphs738ege3g-a.ohio-postgres.render.com:5432/country_capital_finder_postgresql";

// Database connection variables
$conn = null;

try {
    error_log("Attempting database connection...");
    
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

    // Create DSN with SSL required
    $dsn = sprintf(
        "pgsql:host=%s;port=%s;dbname=%s;sslmode=require",
        $host,
        $port,
        $dbname
    );
    
    error_log("Connecting with DSN: " . preg_replace('/user=.*?;/', 'user=***;', $dsn));
    
    // Create connection
    $conn = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Test connection
    $stmt = $conn->query('SELECT 1');
    if ($stmt === false) {
        throw new Exception("Connection test failed");
    }
    error_log("Database connection successful");
    
} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    die("Configuration error: " . $e->getMessage());
}

// Prevent direct access to this file
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Direct access not permitted');
}
?>
