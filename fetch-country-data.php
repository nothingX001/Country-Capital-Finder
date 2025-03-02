<?php
// fetch-country-data.php

include 'config.php';
header('Content-Type: application/json');

$type = $_GET['type'] ?? null;
$response = [];

try {
    // 1. Main List of Countries (Member/Observer States)
    if ($type === 'all_main_only') {
        $stmt = $conn->query("
            SELECT id, \"name\" AS country_name, \"flag\" AS flag_emoji
            FROM countries
            WHERE status IN ('UN member', 'UN observer')
            ORDER BY \"name\" ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // 2. Territories
    elseif ($type === 'all_territories') {
        $stmt = $conn->query("
            SELECT id, \"name\" AS country_name, \"flag\" AS flag_emoji
            FROM countries
            WHERE status = 'Territory'
            ORDER BY \"name\" ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // 3. De Facto States
    elseif ($type === 'all_de_facto_states') {
        $stmt = $conn->query("
            SELECT id, \"name\" AS country_name, \"flag\" AS flag_emoji
            FROM countries
            WHERE status = 'De Facto'
            ORDER BY \"name\" ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // 4. Random Member/Observer States for Quiz
    elseif ($type === 'random_main' && isset($_GET['limit'])) {
        $limit = (int)$_GET['limit'];
        $stmt = $conn->query("
            SELECT c.id, 
                   c.\"name\" AS country_name, 
                   array_agg(REPLACE(cap.name, ' / ', ', ')) AS capitals
            FROM countries c
            JOIN capitals cap ON c.id = cap.country_id
            WHERE c.status IN ('UN member', 'UN observer')
            GROUP BY c.id
            ORDER BY RANDOM()
            LIMIT $limit
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            if (!empty($row['capitals']) && is_string($row['capitals'])) {
                $row['capitals'] = array_map('trim', explode(',', trim($row['capitals'], '{}')));
                $row['capitals'] = array_map(function($capital) {
                    return str_replace('"', '', $capital);
                }, $row['capitals']);
            } else {
                $row['capitals'] = [];
            }
        }
        unset($row);
        $response = $rows;
    }
    // 5. Random Territories for Quiz
    elseif ($type === 'random_territories' && isset($_GET['limit'])) {
        $limit = (int)$_GET['limit'];
        $stmt = $conn->query("
            SELECT c.id, 
                   c.\"name\" AS country_name, 
                   array_agg(REPLACE(cap.name, ' / ', ', ')) AS capitals
            FROM countries c
            JOIN capitals cap ON c.id = cap.country_id
            WHERE c.status = 'Territory'
            GROUP BY c.id
            ORDER BY RANDOM()
            LIMIT $limit
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            if (!empty($row['capitals']) && is_string($row['capitals'])) {
                $row['capitals'] = array_map('trim', explode(',', trim($row['capitals'], '{}')));
                $row['capitals'] = array_map(function($capital) {
                    return str_replace('"', '', $capital);
                }, $row['capitals']);
            } else {
                $row['capitals'] = [];
            }
        }
        unset($row);
        $response = $rows;
    }
    // 6. Map Data
    elseif ($type === 'map') {
        // We use a UNION ALL to merge two sets:
        // - Countries with their own coordinates (capital_name is null)
        // - Capitals with their own coordinates
        // We add a sort_order so that rows from capitals (sort_order = 0) come before rows from countries (sort_order = 1).
        $query = "
            (
              SELECT
                  id,
                  \"Country Name\" AS country_name,
                  NULL::text AS capital_name,
                  \"Coordinates (Latitude)\"::text AS latitude,
                  \"Coordinates (Longitude)\"::text AS longitude,
                  \"ISO Alpha-2\" AS iso_code,
                  \"Flag\" AS flag_emoji,
                  1 AS sort_order
              FROM countries
              WHERE \"Coordinates (Latitude)\" IS NOT NULL
                AND \"Coordinates (Longitude)\" IS NOT NULL
            )
            UNION ALL
            (
              SELECT
                  cap.id,
                  c.\"Country Name\" AS country_name,
                  cap.capital_name,
                  cap.latitude::text AS latitude,
                  cap.longitude::text AS longitude,
                  c.\"ISO Alpha-2\" AS iso_code,
                  c.\"Flag\" AS flag_emoji,
                  0 AS sort_order
              FROM capitals cap
              JOIN countries c ON cap.country_id = c.id
              WHERE cap.latitude IS NOT NULL
                AND cap.longitude IS NOT NULL
            )
            ORDER BY sort_order, country_name
        ";
        $stmt = $conn->query($query);
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // 7. Country Detail by ID
    elseif ($type === 'detail' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $conn->prepare("
            SELECT \"name\" AS country_name, 
                   \"flag\" AS flag_emoji, 
                   language, 
                   flag_url AS flag_image_url,
                   status, 
                   disclaimer, 
                   parent_id
            FROM countries
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $response = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // 8. Autocomplete
    elseif ($type === 'autocomplete' && isset($_GET['query'])) {
        $query = $_GET['query'];
        $stmt = $conn->prepare("
            SELECT \"name\" AS country_name
            FROM countries
            WHERE LOWER(\"name\") LIKE LOWER(?)
            ORDER BY \"name\" ASC
            LIMIT 10
        ");
        $stmt->execute([$query . '%']);
        $response = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    // 9. Site Statistics
    elseif ($type === 'statistics') {
        $stmt = $conn->query("
            SELECT country_name
            FROM site_statistics
            ORDER BY search_count DESC
            LIMIT 1
        ");
        $most_searched = $stmt->fetch(PDO::FETCH_ASSOC);
        $most_searched_countries = $most_searched ? $most_searched['country_name'] : 'No data';

        $stmt = $conn->query("SELECT SUM(search_count) AS total_searches FROM site_statistics");
        $total_searches = $stmt->fetch(PDO::FETCH_ASSOC)['total_searches'] ?? 0;

        $stmt = $conn->query("SELECT country_name FROM site_statistics ORDER BY last_searched_at DESC LIMIT 1");
        $most_recent_search = $stmt->fetch(PDO::FETCH_ASSOC)['country_name'] ?? 'No data';

        $stmt = $conn->query("
            SELECT SUM(search_count) AS searches_today
            FROM site_statistics
            WHERE last_searched_at::date = CURRENT_DATE
        ");
        $searches_today = $stmt->fetch(PDO::FETCH_ASSOC)['searches_today'] ?? 0;

        $stmt = $conn->query("SELECT COUNT(DISTINCT country_name) AS unique_countries_searched FROM site_statistics");
        $unique_countries_searched = $stmt->fetch(PDO::FETCH_ASSOC)['unique_countries_searched'] ?? 0;

        $response = [
            'most_searched_countries' => $most_searched_countries,
            'total_searches' => $total_searches,
            'most_recent_search' => $most_recent_search,
            'searches_today' => $searches_today,
            'unique_countries_searched' => $unique_countries_searched
        ];
    }
    // 10. Invalid or Missing Type
    else {
        http_response_code(400);
        $response = ['error' => 'Invalid type or missing parameters.'];
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error in fetch-country-data.php: " . $e->getMessage());
    $response = ['error' => 'An internal server error occurred.'];
}

echo json_encode($response);
?>
