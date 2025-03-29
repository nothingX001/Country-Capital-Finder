<?php
// Force error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Basic output
echo "PHP is working\n";

// Test if we can write to error log
error_log("Test message from test.php");

// Show loaded extensions
echo "Loaded extensions:\n";
print_r(get_loaded_extensions());

// Check for specific required extensions
echo "\nRequired extensions:\n";
echo "PDO: " . (extension_loaded('pdo') ? 'Yes' : 'No') . "\n";
echo "PDO PostgreSQL: " . (extension_loaded('pdo_pgsql') ? 'Yes' : 'No') . "\n";
echo "PostgreSQL: " . (extension_loaded('pgsql') ? 'Yes' : 'No') . "\n"; 