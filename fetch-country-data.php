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
            SELECT id, \"Country Name\" AS country_name, \"Flag Emoji\" AS flag_emoji
            FROM countries
            WHERE status IN ('UN member', 'UN observer')
            ORDER BY \"Country Name\" ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // 2. Territories
    elseif ($type === 'all_territories') {
        $stmt = $conn->query("
            SELECT id, \"Country Name\" AS country_name, \"Flag Emoji\" AS flag_emoji
            FROM countries
            WHERE status = 'Territory'
            ORDER BY \"Country Name\" ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // 3. De Facto States
    elseif ($type === 'all_de_facto_states') {
        $stmt = $conn->query("
            SELECT id, \"Country Name\" AS country_name, \"Flag Emoji\" AS flag_emoji
            FROM countries
            WHERE status = 'De facto state'
            ORDER BY \"Country Name\" ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // 4. Random Member/Observer States for Quiz
    elseif ($type === 'random_main' && isset($_GET['limit'])) {
        $limit = (int)$_GET['limit'];
        $stmt = $conn->query("
            WITH random_countries AS (
                SELECT c.id, 
                       c.\"Country Name\" AS country_name,
                       c.\"Flag Emoji\" AS flag_emoji,
                       array_agg(cap.capital_name) AS capitals
                FROM countries c
                JOIN capitals cap ON c.id = cap.country_id
                WHERE c.\"Entity Type\" IN ('UN member', 'UN observer')
                GROUP BY c.id, c.\"Country Name\", c.\"Flag Emoji\"
                ORDER BY RANDOM()
                LIMIT $limit
            )
            SELECT * FROM random_countries
            WHERE array_length(capitals, 1) > 0
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
            WITH random_countries AS (
                SELECT c.id,
                       c.\"Country Name\" AS country_name,
                       c.\"Flag Emoji\" AS flag_emoji,
                       array_agg(cap.capital_name) AS capitals
                FROM countries c
                JOIN capitals cap ON c.id = cap.country_id
                WHERE c.\"Entity Type\" = 'Territory'
                GROUP BY c.id, c.\"Country Name\", c.\"Flag Emoji\"
                ORDER BY RANDOM()
                LIMIT $limit
            )
            SELECT * FROM random_countries
            WHERE array_length(capitals, 1) > 0
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
        include 'the-countries.php'; // Include the list of "the" countries
        $query = trim($_GET['query']);
        
        // Special case: if query is just "the" (case insensitive), show all "the" countries
        if (preg_match('/^the$/i', $query)) {
            $placeholders = str_repeat('?,', count($the_countries) - 1) . '?';
            $stmt = $conn->prepare("
                SELECT \"Country Name\" AS country_name
                FROM countries 
                WHERE LOWER(\"Country Name\") IN ($placeholders)
                ORDER BY \"Country Name\" ASC
            ");
            $stmt->execute($the_countries);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = array_map(function($row) {
                return 'The ' . $row['country_name'];
            }, $results);
        } 
        // If query starts with "the " (case insensitive), only show matching "the" countries
        elseif (preg_match('/^the\s+(.+)/i', $query, $matches)) {
            $search_term = $matches[1]; // Get the part after "the "
            $placeholders = str_repeat('?,', count($the_countries) - 1) . '?';
            $stmt = $conn->prepare("
                SELECT \"Country Name\" AS country_name
                FROM countries 
                WHERE LOWER(\"Country Name\") IN ($placeholders)
                AND LOWER(\"Country Name\") LIKE LOWER(?)
                ORDER BY \"Country Name\" ASC
                LIMIT 10
            ");
            $params = array_merge($the_countries, [$search_term . '%']);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = array_map(function($row) {
                return 'The ' . $row['country_name'];
            }, $results);
        }
        else {
            // Regular search query
            $stmt = $conn->prepare("
                SELECT 
                    \"Country Name\" AS country_name,
                    CASE 
                        WHEN LOWER(\"Country Name\") = ANY($1)
                        THEN TRUE 
                        ELSE FALSE 
                    END AS needs_the
                FROM countries
                WHERE LOWER(\"Country Name\") LIKE LOWER($2)
                ORDER BY \"Country Name\" ASC
                LIMIT 10
            ");
            $stmt->execute(['{' . implode(',', $the_countries) . '}', $query . '%']);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = array_map(function($row) {
                return $row['needs_the'] ? 'The ' . $row['country_name'] : $row['country_name'];
            }, $results);
        }
    }
    // 9. Site Statistics
    elseif ($type === 'statistics') {
        $stmt = $conn->query("
            SELECT s.country_name, c.\"Flag Emoji\" AS flag_emoji
            FROM site_statistics s
            LEFT JOIN countries c ON s.country_name = c.\"Country Name\"
            ORDER BY s.search_count DESC
            LIMIT 1
        ");
        $most_searched = $stmt->fetch(PDO::FETCH_ASSOC);
        $most_searched_countries = $most_searched ? $most_searched['country_name'] : 'No data';
        $most_searched_flag = $most_searched ? $most_searched['flag_emoji'] : '';

        $stmt = $conn->query("SELECT SUM(search_count) AS total_searches FROM site_statistics");
        $total_searches = $stmt->fetch(PDO::FETCH_ASSOC)['total_searches'] ?? 0;

        $stmt = $conn->query("
            SELECT s.country_name, c.\"Flag Emoji\" AS flag_emoji
            FROM site_statistics s
            LEFT JOIN countries c ON s.country_name = c.\"Country Name\"
            ORDER BY s.last_searched_at DESC
            LIMIT 1
        ");
        $most_recent = $stmt->fetch(PDO::FETCH_ASSOC);
        $most_recent_search = $most_recent ? $most_recent['country_name'] : 'No data';
        $most_recent_flag = $most_recent ? $most_recent['flag_emoji'] : '';

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
            'most_searched_flag' => $most_searched_flag,
            'total_searches' => $total_searches,
            'most_recent_search' => $most_recent_search,
            'most_recent_flag' => $most_recent_flag,
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
