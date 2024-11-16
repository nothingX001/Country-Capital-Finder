<?php
include 'config.php'; // Ensure the database connection is included

header('Content-Type: application/json');

// Check for the 'type' parameter in the URL
$type = $_GET['type'] ?? null;

// Handle the request based on the type
try {
    if ($type === 'all') {
        $stmt = $conn->query('SELECT id, country_name, capital_name, flag_emoji FROM countries');
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } elseif ($type === 'random') {
        $limit = (int)($_GET['limit'] ?? 10);
        $stmt = $conn->query("SELECT id, country_name, capital_name, flag_emoji FROM countries ORDER BY RANDOM() LIMIT $limit");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } elseif ($type === 'map') {
        $stmt = $conn->query('SELECT country_name, capital_name, latitude, longitude, flag_emoji FROM countries');
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        echo json_encode(['error' => 'Invalid request type']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
