<?php
$servername = "localhost";
$username = "root";
$password = "";  // Use your own MySQL password, or leave blank if default
$dbname = "country_searches";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>