<?php
// Force error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Test basic PHP functionality
echo "PHP is working!<br>";

// Test error logging
$error_log_path = __DIR__ . '/error.log';
if (!file_exists($error_log_path)) {
    touch($error_log_path);
    chmod($error_log_path, 0666);
}
ini_set('log_errors', 1);
ini_set('error_log', $error_log_path);
error_log("Test error message");

// Test database connection
try {
    $databaseUrl = getenv('DATABASE_URL');
    echo "Database URL: " . ($databaseUrl ? "Set" : "Not set") . "<br>";
    
    if ($databaseUrl) {
        $dbopts = parse_url($databaseUrl);
        $host = $dbopts["host"];
        $port = $dbopts["port"];
        $user = $dbopts["user"];
        $password = $dbopts["pass"];
        $dbname = ltrim($dbopts["path"], '/');
        
        $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Database connection successful!<br>";
        
        $stmt = $conn->query("SELECT COUNT(*) FROM countries");
        $count = $stmt->fetchColumn();
        echo "Found $count countries in database.<br>";
    }
} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    error_log("Error: " . $e->getMessage());
}

// Test session functionality
session_start();
$_SESSION['test'] = 'Session is working';
echo $_SESSION['test'] . "<br>";

// Display PHP info
phpinfo(); 