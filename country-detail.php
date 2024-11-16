<?php
include 'config.php'; // Include the database configuration

// Get the country name from the URL parameter
$country_name = $_GET['country'] ?? null;

if (!$country_name) {
    header("Location: country-profiles.php");
    exit;
}

try {
    // Fetch country details from the database
    $query = $conn->prepare("SELECT * FROM countries WHERE country_name = :country_name");
    $query->bindParam(':country_name', $country_name, PDO::PARAM_STR);
    $query->execute();
    $country_info = $query->fetch(PDO::FETCH_ASSOC);

    if (!$country_info) {
        header("Location: country-profiles.php");
        exit;
    }
} catch (PDOException $e) {
    die("Error fetching country details: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($country_info['country_name']); ?> Profile</title>
    <link rel="stylesheet" href="styles.css">
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.0.1/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.0.1/mapbox-gl.css' rel='stylesheet' />
</head>
<body>

<?php include 'navbar.php'; ?>

<section id="country-profile">
    <h1><?php echo htmlspecialchars($country_info['country_name']); ?></h1>
    <p>Flag: <?php echo htmlspecialchars($country_info['flag_emoji']); ?></p>
    <p>Capital: <?php echo htmlspecialchars($country_info['capital_name']); ?></p>
    <p>Language: <?php echo htmlspecialchars($country_info['language']); ?></p>
    <p>Alternate Names: <?php echo htmlspecialchars($country_info['alternate_names']); ?></p>

    <!-- Mapbox Integration -->
    <div id="map" style="width: 100%; height: 300px;"></div>
    <script>
        mapboxgl.accessToken = 'YOUR_MAPBOX_ACCESS_TOKEN';
        var map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [<?php echo htmlspecialchars($country_info['longitude']); ?>, <?php echo htmlspecialchars($country_info['latitude']); ?>],
            zoom: 5
        });
    </script>
</section>

</body>
</html>
