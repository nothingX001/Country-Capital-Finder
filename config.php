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
        // Parse the DATABASE_URL
        $dbopts = parse_url($databaseUrl);

        $host = $dbopts["host"];
        $port = $dbopts["port"];
        $user = $dbopts["user"];
        $password = $dbopts["pass"];
        $dbname = ltrim($dbopts["path"], '/');

        // Create a new PDO instance
        $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
        
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
