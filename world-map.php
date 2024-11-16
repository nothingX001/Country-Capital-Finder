<?php
$data = file_get_contents('http://localhost/fetch-country-data.php?type=map');
$countries = json_decode($data, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>World Map</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="world-map-styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div id="main-world-map">
        <h1>WORLD MAP OF CAPITALS</h1>
        <p>Explore the capitals of countries around the world.</p>
        <div class="search-bar-container">
            <input type="text" id="search-bar" placeholder="Search for a country or capital...">
        </div>
        <div id="map" style="height: 500px; border-radius: 15px;"></div>
    </div>

    <script>
        const countries = <?php echo json_encode($countries); ?>;
        // The rest of your JavaScript code remains unchanged...
    </script>
</body>
</html>
