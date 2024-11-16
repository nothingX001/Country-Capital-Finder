<?php
// fetch-country-data.php
include 'config.php';

// Get the request type and additional parameters
$type = $_GET['type'] ?? '';
$limit = $_GET['limit'] ?? null;

header('Content-Type: application/json');

try {
    if ($type === 'all') {
        // Fetch all countries
        $query = $conn->query("SELECT country_name, capital_name, latitude, longitude, flag_emoji, map_image_url FROM countries ORDER BY country_name ASC");
        $countries = $query->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($countries);

    } elseif ($type === 'random') {
        // Fetch a random set of countries
        $limit = is_numeric($limit) ? (int)$limit : 10;
        $query = $conn->query("SELECT country_name, capital_name, latitude, longitude, flag_emoji FROM countries ORDER BY RANDOM() LIMIT $limit");
        $randomCountries = $query->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($randomCountries);

    } elseif ($type === 'map') {
        // Fetch data for map visualization
        $query = $conn->query("SELECT country_name, capital_name, latitude, longitude, flag_emoji FROM countries WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
        $mapData = $query->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($mapData);

    } else {
        // Invalid type
        echo json_encode(['error' => 'Invalid request type']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
