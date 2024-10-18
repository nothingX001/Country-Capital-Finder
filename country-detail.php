<?php
// country-detail.php

include 'config.php'; // Include database connection and configurations
include 'mapbox.php'; // Assuming there's a file to handle Mapbox API integration

// Get the country from the URL parameter
$country_name = $_GET['country'] ?? null;

// Validate the country and fetch relevant details from the database
if ($country_name && array_key_exists($country_name, $country_map)) {
    $flag = $country_map[$country_name];
    $query = $db->prepare("SELECT capital, language, founding_date, spoken_languages FROM countries WHERE name = ?");
    $query->execute([$country_name]);
    $country_info = $query->fetch(PDO::FETCH_ASSOC);
} else {
    header("Location: country-profiles.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($country_name); ?> Profile</title>
    <link rel="stylesheet" href="styles.css">
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.0.1/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.0.1/mapbox-gl.css' rel='stylesheet' />
</head>
<body>

<?php include 'navbar.php'; ?>

<section id="country-profile">
    <h1><?php echo htmlspecialchars($country_name); ?></h1>
    <p>Flag: <?php echo $flag; ?></p>
    <p>Capital: <?php echo htmlspecialchars($country_info['capital']); ?></p>
    <p>Language: <?php echo htmlspecialchars($country_info['language']); ?></p>
    <p>Founding Date: <?php echo htmlspecialchars($country_info['founding_date']); ?></p>
    <p>Spoken Languages: <?php echo htmlspecialchars($country_info['spoken_languages']); ?></p>

    <!-- Mapbox Integration -->
    <div id="map" style="width: 100%; height: 300px;"></div>
    <script>
        mapboxgl.accessToken = 'YOUR_MAPBOX_ACCESS_TOKEN';
        var map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [longitude, latitude], // Set to the country's central coordinates
            zoom: 5
        });
    </script>
</section>

</body>
</html>
