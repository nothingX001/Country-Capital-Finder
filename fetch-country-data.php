<?php
// fetch-country-data.php

include 'config.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? null;
$response = [];

try {
    // 1) All main only (member_state, observer_state)
    if ($type === 'all_main_only') {
        $stmt = $conn->query("
            SELECT id, country_name, flag_emoji
            FROM countries
            WHERE entity_type IN ('member_state','observer_state')
            ORDER BY country_name ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2) All territories
    } elseif ($type === 'all_territories') {
        $stmt = $conn->query("
            SELECT id, country_name, flag_emoji
            FROM countries
            WHERE entity_type = 'territory'
            ORDER BY country_name ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3) All de facto states
    } elseif ($type === 'all_de_facto') {
        $stmt = $conn->query("
            SELECT id, country_name, flag_emoji
            FROM countries
            WHERE entity_type = 'de_facto_state'
            ORDER BY country_name ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4) Quiz: random_main (member/observer)
    } elseif ($type === 'random_main' && isset($_GET['limit'])) {
        $limit = (int)$_GET['limit'];
        $stmt = $conn->query("
            SELECT c.id, c.country_name,
                   array_agg(cap.capital_name) AS capitals
            FROM countries c
            JOIN capitals cap ON c.id = cap.country_id
            WHERE c.entity_type IN ('member_state','observer_state')
            GROUP BY c.id
            ORDER BY RANDOM()
            LIMIT $limit
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5) Quiz: random_territories
    } elseif ($type === 'random_territories' && isset($_GET['limit'])) {
        $limit = (int)$_GET['limit'];
        $stmt = $conn->query("
            SELECT c.id, c.country_name,
                   array_agg(cap.capital_name) AS capitals
            FROM countries c
            JOIN capitals cap ON c.id = cap.country_id
            WHERE c.entity_type = 'territory'
            GROUP BY c.id
            ORDER BY RANDOM()
            LIMIT $limit
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // (Optional) Legacy random
    } elseif ($type === 'random' && isset($_GET['limit'])) {
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

    // 6) Map data
    } elseif ($type === 'map') {
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

    // 7) Detail
    } elseif ($type === 'detail' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("
            SELECT country_name, flag_emoji, language, flag_image_url
            FROM countries
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $response = $stmt->fetch(PDO::FETCH_ASSOC);

    // 8) Statistics
    } elseif ($type === 'statistics') {
        $response = [];

        // Most searched
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
            $country_name   = $row['country_name'];
            $dt             = new DateTime($row['last_searched_at'], new DateTimeZone('UTC'));
            $user_tz        = new DateTimeZone('America/New_York');
            $dt->setTimezone($user_tz);
            $response['most_recent_search'] =
                $country_name . ', at ' . $dt->format('g:i A') . ' on ' . $dt->format('F j, Y');
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

        // Unique
        $stmt = $conn->query("
            SELECT COUNT(*) AS unique_countries_searched
            FROM site_statistics
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['unique_countries_searched'] = $row['unique_countries_searched'] ?? 'Data unavailable';

    // 9) Autocomplete
    } elseif ($type === 'autocomplete' && isset($_GET['query'])) {
        $query = $_GET['query'] ?? '';
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
