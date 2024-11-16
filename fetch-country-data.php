<?php
include 'config.php';

// Get query parameters
$type = $_GET['type'] ?? '';
$limit = intval($_GET['limit'] ?? 0);
$country = $_GET['country'] ?? '';

// Initialize response
$response = [];

try {
    if ($type === 'all') {
        // Fetch all countries
        $stmt = $conn->query("SELECT country_name, flag_emoji FROM countries");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($type === 'detail' && $country) {
        // Fetch details for a specific country
        $stmt = $conn->prepare("SELECT * FROM countries WHERE country_name = :country");
        $stmt->execute([':country' => $country]);
        $response = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($type === 'random' && $limit > 0) {
        // Fetch random countries
        $stmt = $conn->query("SELECT country_name, capital_name FROM countries ORDER BY RANDOM() LIMIT $limit");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($type === 'map') {
        // Fetch countries with coordinates for the map
        $stmt = $conn->query("SELECT country_name, capital_name, latitude, longitude, flag_emoji FROM countries");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Invalid type or missing parameters
        http_response_code(400);
        $response = ['error' => 'Invalid type or missing parameters'];
    }
} catch (Exception $e) {
    http_response_code(500);
    $response = ['error' => 'Server error: ' . $e->getMessage()];
}

// Output as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
