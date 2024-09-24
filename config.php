<?php
// Get the DATABASE_URL from Heroku environment variables
$db = parse_url(getenv("postgres://ua60mjac0k7a4b:pa526b469a3cc8a5d08dd59171c895c666d76320c1a9c61441195f57f7c909991@c67okggoj39697.cluster-czrs8kj4isg7.us-east-1.rds.amazonaws.com:5432/d364glsdk3i6cn"));

// Store the database connection details in variables
$host = $db["host"];
$user = $db["user"];
$password = $db["pass"];
$dbname = ltrim($db["path"], "/");
$port = $db["port"];

// Connect to the PostgreSQL database using the connection details
$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
?>
