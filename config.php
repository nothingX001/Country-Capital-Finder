<?php
// ClearDB connection details
$url = parse_url(getenv("mysql://bbf9f52b4bd161:1731f103@us-cluster-east-01.k8s.cleardb.net/heroku_bcd9c36422ff993?reconnect=true"));

$host = $url["host"];
$user = $url["user"];
$password = $url["pass"];
$dbname = substr($url["path"], 1); // Remove the leading '/' from the path

// Connect to ClearDB MySQL
$conn = new mysqli($host, $user, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
