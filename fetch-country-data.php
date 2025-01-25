<?php
// fetch-country-data.php

include 'config.php';
header('Content-Type: application/json');

// Get the requested type from the query string
$type = $_GET['type'] ?? null;
$response = [];

try {
    // ============================
    // 1. Main List of Countries (Member/Observer States)
    // ============================
    if ($type === 'all_main_only') {
        $stmt = $conn->query("
            SELECT id, country_name, flag_emoji
            FROM countries
            WHERE entity_type IN ('member_state', 'observer_state')
            ORDER BY country_name ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================
    // 2. Territories
    // ============================
    elseif ($type === 'all_territories') {
        $stmt = $conn->query("
            SELECT id, country_name, flag_emoji
            FROM countries
            WHERE entity_type = 'territory'
            ORDER BY country_name ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================
    // 3. De Facto States
    // ============================
    elseif ($type === 'all_de_facto_states') {
        $stmt = $conn->query("
            SELECT id, country_name, flag_emoji
            FROM countries
            WHERE entity_type = 'de_facto_state'
            ORDER BY country_name ASC
        ");
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================
    // 4. Random Member/Observer States for Quiz
    // ============================
    elseif ($type === 'random_main' && isset($_GET['limit'])) {
        $limit = (int)$_GET['limit'];
        $stmt = $conn->query("
            SELECT c.id, 
                   c.country_name, 
                   array_agg(cap.capital_name) AS capitals
            FROM countries c
            JOIN capitals cap ON c.id = cap.country_id
            WHERE c.entity_type IN ('member_state', 'observer_state')
            GROUP BY c.id
            ORDER BY RANDOM()
            LIMIT $limit
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            if (!empty($row['capitals']) && is_string($row['capitals'])) {
                $row['capitals'] = array_map('trim', explode(',', trim($row['capitals'], '{}')));
            } else {
                $row['capitals'] = [];
            }
        }
        unset($row);
        $response = $rows;
    }

    // ============================
    // 5. Random Territories for Quiz
    // ============================
    elseif ($type === 'random_territories' && isset($_GET['limit'])) {
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
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            if (!empty($row['capitals']) && is_string($row['capitals'])) {
                $row['capitals'] = array_map('trim', explode(',', trim($row['capitals'], '{}')));
            } else {
                $row['capitals'] = [];
            }
        }
        unset($row);
        $response = $rows;
    }

    // ============================
    // 6. Map Data
    // ============================
    elseif ($type === 'map') {
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

    // ============================
    // 7. Country Detail by ID
    // ============================
    elseif ($type === 'detail' && isset($_GET['id'])) {
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

    // ============================
    // 8. Autocomplete
    // ============================
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

    // ============================
    // 9. Invalid or Missing Type
    // ============================
    else {
        http_response_code(400);
        $response = ['error' => 'Invalid type or missing parameters.'];
    }

} catch (Exception $e) {
    http_response_code(500);
    $response = ['error' => $e->getMessage()];
}

echo json_encode($response);
