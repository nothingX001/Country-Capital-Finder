<?php
include 'config.php';

$response = [];

try {
    $type = $_GET['type'] ?? null;

    if ($type === 'statistics') {
        // Fetch site statistics
        $stmt = $conn->query("
            SELECT 
                country_name AS most_searched_countries,
                search_count AS total_searches,
                MAX(last_searched_at) AS most_recent_search
            FROM site_statistics
            ORDER BY search_count DESC, last_searched_at DESC
            LIMIT 1
        ");
        $statistics = $stmt->fetch(PDO::FETCH_ASSOC);
        $response = $statistics;
    } else {
        http_response_code(400);
        $response = ['error' => 'Invalid type or missing parameters.'];
    }
} catch (Exception $e) {
    http_response_code(500);
    $response = ['error' => $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
