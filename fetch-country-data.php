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
        $stmtMostSearchedCountry = $conn->query("SELECT country_name FROM countries ORDER BY searches DESC LIMIT 1");
        $mostSearchedCountry = $stmtMostSearchedCountry->fetch(PDO::FETCH_ASSOC)['country_name'] ?? 'N/A';

        $stmtMostSearchedCapital = $conn->query("SELECT capital_name FROM countries ORDER BY searches DESC LIMIT 1");
        $mostSearchedCapital = $stmtMostSearchedCapital->fetch(PDO::FETCH_ASSOC)['capital_name'] ?? 'N/A';

        $stmtTotalQuizzes = $conn->query("SELECT COUNT(*) AS total FROM quizzes");
        $totalQuizzes = $stmtTotalQuizzes->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $stmtLastSearch = $conn->query("SELECT last_search FROM searches ORDER BY timestamp DESC LIMIT 1");
        $lastSearch = $stmtLastSearch->fetch(PDO::FETCH_ASSOC)['last_search'] ?? 'N/A';

        $response = [
            'most_searched_country' => $mostSearchedCountry,
            'most_searched_capital' => $mostSearchedCapital,
            'total_quizzes_completed' => $totalQuizzes,
            'last_search' => $lastSearch
        ];

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
