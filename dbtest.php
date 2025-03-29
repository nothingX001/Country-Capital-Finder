<?php
// Basic PHP test
echo "PHP is working\n";

// Test environment variable loading
$db_url = getenv('DATABASE_URL');
echo "Database URL is " . ($db_url ? "set" : "not set") . "\n";

// Test database connection
try {
    $dbopts = parse_url($db_url);
    
    if ($dbopts === false) {
        throw new Exception("Invalid DATABASE_URL format");
    }

    $host = $dbopts["host"];
    $port = $dbopts["port"];
    $user = $dbopts["user"];
    $password = $dbopts["pass"];
    $dbname = ltrim($dbopts["path"], '/');

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    
    $conn = new PDO(
        $dsn,
        $user,
        $password,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    
    echo "Database connection successful\n";
    $result = $conn->query("SELECT version()")->fetch();
    echo "PostgreSQL version: " . $result[0] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "DSN attempted: pgsql:host=$host;port=$port;dbname=$dbname\n";
} 