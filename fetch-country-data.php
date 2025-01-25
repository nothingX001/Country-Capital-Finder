<?php
// fetch-country-data.php
include 'config.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? null;
$response = [];

try {
    if ($type === 'all_main_only') {
        // Only fetch Member/Observer
        $stmt = $conn->query("
            SELECT id, country_name, flag_emoji
            FROM countries
            WHERE entity_type IN ('member_state','observer_state')
            ORDER BY country_name ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($type === 'random' && isset($_GET['limit'])) {
        // For the quiz: fetch random countries + aggregated capitals
        $limit = (int)$_GET['limit'];
        $stmt = $conn->query("
            SELECT c.id, c.country_name, 
                   array_agg(cap.capital_name) AS capitals
            FROM countries c
            JOIN capitals cap ON c.id = cap.country_id
            GROUP BY c.id
            ORDER BY RANDOM()
            LIMIT $limit
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($type === 'map') {
        // Return each country's name + capital info for world map
        // One row per capital
        $stmt = $conn->query("
            SELECT c.country_name, 
                   cap.capital_name, 
                   cap.latitude, 
                   cap.longitude,
                   c.flag_emoji
            FROM countries c
            LEFT JOIN capitals cap ON c.id = cap.country_id
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($type === 'detail' && isset($_GET['id'])) {
        // If you want an API detail endpoint
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("
            SELECT country_name, flag_emoji, language, flag_image_url
            FROM countries
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $response = $stmt->fetch(PDO::FETCH_ASSOC);

    } elseif ($type === 'statistics') {
        // Site statistics
        $response = [];

        // Most searched country
        $stmt = $conn->query("
            SELECT country_name 
            FROM site_statistics
            ORDER BY search_count DESC 
            LIMIT 1
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['most_searched_countries'] = $row['country_name'] ?? 'Data unavailable';

        // Total searches
        $stmt = $conn->query("
            SELECT SUM(search_count) AS total_searches
            FROM site_statistics
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['total_searches'] = $row['total_searches'] ?? 'Data unavailable';

        // Most recent search
        $stmt = $conn->query("
            SELECT country_name, last_searched_at
            FROM site_statistics
            ORDER BY last_searched_at DESC
            LIMIT 1
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $country_name    = $row['country_name'];
            $last_searched   = new DateTime($row['last_searched_at'], new DateTimeZone('UTC'));
            $user_tz         = new DateTimeZone('America/New_York');
            $last_searched->setTimezone($user_tz);
            $formatted_date  = $last_searched->format('F j, Y');
            $formatted_time  = $last_searched->format('g:i A');
            $response['most_recent_search'] = "$country_name, at $formatted_time on $formatted_date";
        } else {
            $response['most_recent_search'] = 'Data unavailable';
        }

        // Searches today
        $stmt = $conn->query("
            SELECT SUM(search_count) AS searches_today
            FROM site_statistics
            WHERE DATE(last_searched_at) = CURRENT_DATE
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['searches_today'] = $row['searches_today'] ?? 'Data unavailable';

        // Unique countries searched
        $stmt = $conn->query("
            SELECT COUNT(*) AS unique_countries_searched
            FROM site_statistics
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['unique_countries_searched'] = $row['unique_countries_searched'] ?? 'Data unavailable';

    } elseif ($type === 'autocomplete' && isset($_GET['query'])) {
        // Autocomplete
        $query = $_GET['query'];
        $stmt = $conn->prepare("
            SELECT country_name
            FROM countries
            WHERE LOWER(country_name) LIKE LOWER(?)
            ORDER BY country_name ASC
            LIMIT 10
        ");
        $stmt->execute([$query . '%']);
        $response = $stmt->fetchAll(PDO::FETCH_COLUMN);

    } else {
        http_response_code(400);
        $response = ['error' => 'Invalid type or missing parameters.'];
    }
} catch (Exception $e) {
    http_response_code(500);
    $response = ['error' => $e->getMessage()];
}

echo json_encode($response);
