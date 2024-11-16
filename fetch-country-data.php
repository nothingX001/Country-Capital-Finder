<?php
include 'config.php';

header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT country_name, capital_name AS capitals, coordinates, flag_emoji FROM countries");
    $stmt->execute();

    $countries = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $countries[] = [
            'country_name' => $row['country_name'],
            'capitals' => $row['capitals'],
            'coordinates' => $row['coordinates'],
            'flag_emoji' => $row['flag_emoji']
        ];
    }

    echo json_encode($countries);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
