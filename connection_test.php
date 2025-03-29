<?php
// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Loaded Extensions: " . implode(", ", get_loaded_extensions()) . "\n\n";

// Check if PostgreSQL extensions are available
echo "PDO installed: " . (extension_loaded('pdo') ? "Yes" : "No") . "\n";
echo "PDO PostgreSQL installed: " . (extension_loaded('pdo_pgsql') ? "Yes" : "No") . "\n";
echo "PostgreSQL installed: " . (extension_loaded('pgsql') ? "Yes" : "No") . "\n\n";

// Test database connection
try {
    echo "Attempting database connection...\n";
    
    $dsn = "pgsql:host=dpg-csrani5umphs738ege3g-a.ohio-postgres.render.com;port=5432;dbname=country_capital_finder_postgresql;sslmode=require";
    $user = "country_capital_finder_postgresql_user";
    $password = "XqDlRUPvPZYEzasbiy9B5nR8m96sY3KP";
    
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connection successful!\n";
    
    // Test query
    $result = $conn->query("SELECT version()")->fetch(PDO::FETCH_ASSOC);
    echo "PostgreSQL Version: " . $result['version'] . "\n";
    
} catch (PDOException $e) {
    echo "Connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
}
echo "</pre>"; 