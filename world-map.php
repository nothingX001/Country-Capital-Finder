<?php
// Fetch map data using the fetch-country-data.php API
$url = 'fetch-country-data.php?type=map';
$response = file_get_contents($url);
$map_data = json_decode($response, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>World Map with Capitals</title>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="world-map-styles.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<div id="main-world-map">
    <h1>WORLD MAP OF CAPITALS</h1>
    <p>Explore the capitals of countries around the world.</p>
    <div id="map" style="height: 500px; border-radius: 15px;"></div>
</div>

<script src="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.js"></script>
<script>
    mapboxgl.accessToken = 'YOUR_MAPBOX_ACCESS_TOKEN';
    const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/streets-v11',
        center: [0, 20],
        zoom: 1.5,
        projection: 'globe'
    });

    const mapData = <?php echo json_encode($map_data); ?>;
    mapData.forEach(country => {
        const marker = new mapboxgl.Marker()
            .setLngLat([country.longitude, country.latitude])
            .setPopup(new mapboxgl.Popup().setHTML(`
                <strong>${country.capital_name}</strong><br>
                ${country.country_name} ${country.flag_emoji}
            `))
            .addTo(map);
    });
</script>

</body>
</html>
