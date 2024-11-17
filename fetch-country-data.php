<?php
include 'config.php';

$response = [];

try {
    $type = $_GET['type'] ?? null;

    if ($type === 'statistics') {
        // Ensure the query matches the structure of your database
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

        // Check if the query returned valid statistics
        if ($statistics) {
            $response = [
                'most_searched_countries' => $statistics['most_searched_countries'],
                'total_searches' => $statistics['total_searches'],
                'most_recent_search' => $statistics['most_recent_search']
            ];
        } else {
            // If no data exists in the statistics table
            $response = [
                'most_searched_countries' => 'No data',
                'total_searches' => 0,
                'most_recent_search' => 'Never'
            ];
        }
    } else {
        // Invalid or missing type parameter
        http_response_code(400);
        $response = ['error' => 'Invalid type or missing parameters.'];
    }
} catch (Exception $e) {
    // Catch any errors and return a 500 error
    http_response_code(500);
    $response = ['error' => $e->getMessage()];
}

// Output response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
