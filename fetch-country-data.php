<?php
// Include database connection
include 'config.php';

// Initialize response array
$response = [];

try {
    // Get the type of data requested
    $type = $_GET['type'] ?? null;

    if ($type === 'all') {
        // Fetch all countries (alphabetically ordered)
        $stmt = $conn->query("SELECT id, country_name, flag_emoji FROM countries ORDER BY country_name ASC");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($type === 'random' && isset($_GET['limit'])) {
        // Fetch a limited number of random countries for the quiz
        $limit = (int)$_GET['limit'];
        $stmt = $conn->query("SELECT id, country_name, capital_name FROM countries ORDER BY RANDOM() LIMIT $limit");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($type === 'map') {
        // Fetch data for the world map
        $stmt = $conn->query("SELECT country_name, capital_name, latitude, longitude, flag_emoji FROM countries");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($type === 'detail' && isset($_GET['id'])) {
        // Fetch detailed information for a specific country
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("SELECT country_name, capital_name, flag_emoji, language, alternate_names, map_image_url FROM countries WHERE id = ?");
        $stmt->execute([$id]);
        $response = $stmt->fetch(PDO::FETCH_ASSOC);

    } elseif ($type === 'statistics') {
        // Fetch site statistics
        $stmt = $conn->query("
            SELECT 
                country_name AS most_searched_countries,
                MAX(search_count) AS total_searches,
                MAX(last_searched_at) AS most_recent_search,
                COUNT(*) AS unique_countries_searched,
                SUM(search_count) AS searches_today
            FROM site_statistics
        ");
        $statistics = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ensure all statistics are retrieved
        if ($statistics) {
            $response = [
                'most_searched_countries' => $statistics['most_searched_countries'] ?? 'N/A',
                'total_searches' => $statistics['total_searches'] ?? 0,
                'most_recent_search' => $statistics['most_recent_search'] ?? 'N/A',
                'searches_today' => $statistics['searches_today'] ?? 0,
                'unique_countries_searched' => $statistics['unique_countries_searched'] ?? 0
            ];
        } else {
            $response = ['error' => 'No statistics found.'];
        }
    } else {
        // Invalid or missing type parameter
        http_response_code(400);
        $response = ['error' => 'Invalid type or missing parameters.'];
    }
} catch (Exception $e) {
    // Catch any errors
    http_response_code(500);
    $response = ['error' => $e->getMessage()];
}

// Output response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
