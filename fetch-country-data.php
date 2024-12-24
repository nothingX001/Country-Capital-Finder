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
        // Initialize response array
        $response = [];

        // 1. Most Searched Country
        $stmt = $conn->query("SELECT country_name FROM site_statistics ORDER BY search_count DESC LIMIT 1");
        $most_searched_country = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['most_searched_countries'] = $most_searched_country['country_name'] ?? 'Data unavailable';

        // 2. Total Searches
        $stmt = $conn->query("SELECT SUM(search_count) AS total_searches FROM site_statistics");
        $total_searches = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['total_searches'] = $total_searches['total_searches'] ?? 'Data unavailable';

        // 3. Most Recent Search with Timestamp
        $stmt = $conn->query("SELECT country_name, last_searched_at FROM site_statistics ORDER BY last_searched_at DESC LIMIT 1");
        $most_recent_search = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($most_recent_search) {
            $country_name = $most_recent_search['country_name'];
            $last_searched_at = new DateTime($most_recent_search['last_searched_at'], new DateTimeZone('UTC'));

            // Convert to user's timezone (default example: Eastern Time)
            $user_timezone = new DateTimeZone('America/New_York'); // Replace dynamically if needed
            $last_searched_at->setTimezone($user_timezone);

            // Format the date and time
            $formatted_date = $last_searched_at->format('F j, Y');
            $formatted_time = $last_searched_at->format('g:i A');

            $response['most_recent_search'] = "$country_name: $formatted_time on $formatted_date.";
        } else {
            $response['most_recent_search'] = 'Data unavailable';
        }

        // 4. Searches Today
        $stmt = $conn->query("SELECT SUM(search_count) AS searches_today FROM site_statistics WHERE DATE(last_searched_at) = CURRENT_DATE");
        $searches_today = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['searches_today'] = $searches_today['searches_today'] ?? 'Data unavailable';

        // 5. Unique Countries Searched
        $stmt = $conn->query("SELECT COUNT(*) AS unique_countries_searched FROM site_statistics");
        $unique_countries_searched = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['unique_countries_searched'] = $unique_countries_searched['unique_countries_searched'] ?? 'Data unavailable';

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
