<?php
// Parse the ClearDB connection information from the environment variable
$cleardb_url = parse_url("mysql://bbf9f52b4bd161:1731f103@us-cluster-east-01.k8s.cleardb.net/heroku_bcd9c36422ff993?reconnect=true");

$host = $cleardb_url["host"]; // us-cluster-east-01.k8s.cleardb.net
$user = $cleardb_url["user"]; // bbf9f52b4bd161
$pass = $cleardb_url["pass"]; // 1731f103
$db = substr($cleardb_url["path"], 1); // heroku_bcd9c36422ff993

// Create a connection to the database
$conn = new mysqli($host, $user, $pass, $db);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
