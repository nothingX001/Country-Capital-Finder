<?php
// Enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Configuration Test</h1>";

// Test environment variables
echo "<h2>Environment Variables</h2>";
echo "DATABASE_URL set: " . (getenv('DATABASE_URL') ? "Yes" : "No") . "<br>";

// Test PHP extensions
echo "<h2>PHP Extensions</h2>";
echo "PDO installed: " . (extension_loaded('pdo') ? "Yes" : "No") . "<br>";
echo "PDO PostgreSQL installed: " . (extension_loaded('pdo_pgsql') ? "Yes" : "No") . "<br>";
echo "PostgreSQL installed: " . (extension_loaded('pgsql') ? "Yes" : "No") . "<br>";

// Test database connection
echo "<h2>Database Connection</h2>";
try {
    require_once 'config.php';
    if ($conn instanceof PDO) {
        echo "Database connection successful<br>";
        $result = $conn->query("SELECT version()")->fetch();
        echo "PostgreSQL version: " . htmlspecialchars($result[0]) . "<br>";
    } else {
        echo "Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
}

// Test file permissions
echo "<h2>File Permissions</h2>";
$files = ['.env', 'config.php', 'index.php'];
foreach ($files as $file) {
    echo htmlspecialchars($file) . " readable: " . (is_readable($file) ? "Yes" : "No") . "<br>";
}

// Show loaded PHP modules
echo "<h2>Loaded PHP Modules</h2>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>"; 