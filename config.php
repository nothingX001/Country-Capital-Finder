<?php
$hostname = getenv('DB_HOST') ?: 'mysql';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'root';
$database = getenv('DB_NAME') ?: 'capital_finder';

// Create connection
$mysqli = new mysqli('mysql', 'root', 'root', 'capital_finder');
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}
?>
