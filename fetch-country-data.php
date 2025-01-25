<?php
// fetch-country-data.php

include 'config.php';
header('Content-Type: application/json');

// Read the 'type' parameter from the query string
$type = $_GET['type'] ?? null;

// Initialize an array for our response
$response = [];

try {
    // =========================================
    // 1) Lists of Countries / Territories / De Facto States
    // =========================================
    if ($type === 'all_main_only') {
        // Returns only member_state & observer_state
        $stmt = $conn->query("
            SELECT id, country_name, flag_emoji
            FROM countries
            WHERE entity_type IN ('member_state','observer_state')
            ORDER BY country_name ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    elseif ($type === 'all_territories') {
        // Returns only territories
        $stmt = $conn->query("
            SELECT id, country_name, flag_emoji
            FROM countries
            WHERE entity_type = 'territory'
            ORDER BY country_name ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    elseif ($type === 'all_de_facto_states') {
        // Returns only de_facto_state
        $stmt = $conn->query("
            SELECT id, country_name, flag_emoji
            FROM countries
            WHERE entity_type = 'de_facto_state'
            ORDER BY country_name ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================================
    // 2) Quiz Endpoints
    // =========================================
    elseif ($type === 'random_main' && isset($_GET['limit'])) {
        // Member/Observer states only
        $limit = (int)$_GET['limit'];
        $stmt = $conn->query("
            SELECT c.id,
                   c.country_name,
                   array_agg(cap.capital_name) AS capitals
            FROM countries c
            JOIN capitals cap ON c.id = cap.country_id
            WHERE c.entity_type IN ('member_state','observer_state')
            GROUP BY c.id
            ORDER BY RANDOM()
            LIMIT $limit
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    elseif ($type === 'random_territories' && isset($_GET['limit'])) {
        // Territories only
        $limit = (int)$_GET['limit'];
        $stmt = $conn->query("
            SELECT c.id,
                   c.country_name,
                   array_agg(cap.capital_name) AS capitals
            FROM countries c
            JOIN capitals cap ON c.id = cap.country_id
            WHERE c.entity_type = 'territory'
            GROUP BY c.id
            ORDER BY RANDOM()
            LIMIT $limit
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    elseif ($type === 'random' && isset($_GET['limit'])) {
        // ALL countries (if you still want a “full random” quiz)
        $limit = (int)$_GET['limit'];
        $stmt = $conn->query("
            SELECT c.id,
                   c.country_name,
                   array_agg(cap.capital_name) AS capitals
            FROM countries c
            JOIN capitals cap ON c.id = cap.country_id
            GROUP BY c.id
            ORDER BY RANDOM()
            LIMIT $limit
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================================
    // 3) Map Data
    // =========================================
    elseif ($type === 'map') {
        // Return each capital row for the map
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
    }

    // =========================================
    // 4) Detail Endpoint
    // =========================================
    elseif ($type === 'detail' && isset($_GET['id'])) {
        // Example usage if you want detail for a specific country
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("
            SELECT country_name,
                   flag_emoji,
                   language,
                   flag_image_url
            FROM countries
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $response = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // =========================================
    // 5) Statistics
    // =========================================
    elseif ($type === 'statistics') {
        $response = [];

        // 5a) Most searched country
        $stmt = $conn->query("
            SELECT country_name
            FROM site_statistics
            ORDER BY search_count DESC
            LIMIT 1
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['most_searched_countries'] = $row['country_name'] ?? 'Data unavailable';

        // 5b) Total searches
        $stmt = $conn->query("
            SELECT SUM(search_count) AS total_searches
            FROM site_statistics
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['total_searches'] = $row['total_searches'] ?? 'Data unavailable';

        // 5c) Most recent search
        $stmt = $conn->query("
            SELECT country_name, last_searched_at
            FROM site_statistics
            ORDER BY last_searched_at DESC
            LIMIT 1
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $country_name  = $row['country_name'];
            $last_searched = new DateTime($row['last_searched_at'], new DateTimeZone('UTC'));
            // Example: convert to US/Eastern
            $tz = new DateTimeZone('America/New_York');
            $last_searched->setTimezone($tz);
            $date_str = $last_searched->format('F j, Y');
            $time_str = $last_searched->format('g:i A');
            $response['most_recent_search'] = "$country_name, at $time_str on $date_str";
        } else {
            $response['most_recent_search'] = 'Data unavailable';
        }

        // 5d) Searches today
        $stmt = $conn->query("
            SELECT SUM(search_count) AS searches_today
            FROM site_statistics
            WHERE DATE(last_searched_at) = CURRENT_DATE
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['searches_today'] = $row['searches_today'] ?? 'Data unavailable';

        // 5e) Unique countries searched
        $stmt = $conn->query("
            SELECT COUNT(*) AS unique_countries_searched
            FROM site_statistics
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['unique_countries_searched'] = $row['unique_countries_searched'] ?? 'Data unavailable';
    }

    // =========================================
    // 6) Autocomplete
    // =========================================
    elseif ($type === 'autocomplete' && isset($_GET['query'])) {
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
    }

    // =========================================
    // If none of the above matched
    // =========================================
    else {
        http_response_code(400);
        $response = ['error' => 'Invalid type or missing parameters.'];
    }

} catch (Exception $e) {
    // If there's a server-side or DB error
    http_response_code(500);
    $response = ['error' => $e->getMessage()];
}

// Output as JSON
echo json_encode($response);
