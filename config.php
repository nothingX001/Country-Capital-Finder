<?php
// Get the DATABASE_URL environment variable
$databaseUrl = getenv('DATABASE_URL');

if (!$databaseUrl) {
    die("DATABASE_URL environment variable not set.");
}

// Parse the DATABASE_URL
$dbopts = parse_url($databaseUrl);

$host = $dbopts["host"];
$port = $dbopts["port"];
$user = $dbopts["user"];
$password = $dbopts["pass"];
$dbname = ltrim($dbopts["path"], '/');

try {
    // Create a new PDO instance
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to the database successfully!";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit();
}
?>
