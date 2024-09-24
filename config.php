<?php
// Parse ClearDB connection information from the environment variable
$cleardb_url = getenv('CLEARDB_DATABASE_URL');
if ($cleardb_url) {
    $cleardb_url_parts = parse_url($cleardb_url);

    $host = $cleardb_url_parts["host"];
    $user = $cleardb_url_parts["user"];
    $pass = $cleardb_url_parts["pass"];
    $db   = substr($cleardb_url_parts["path"], 1);

    // Create connection
    $conn = new mysqli($host, $user, $pass, $db);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} else {
    die("CLEARDB_DATABASE_URL is not set");
}
?>
