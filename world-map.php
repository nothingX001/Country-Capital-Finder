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
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css" rel="stylesheet">
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

    <script src="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js"></script>
    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoiZGNobzIwMDEiLCJhIjoiY20yYW04bHdtMGl3YjJyb214YXB5dzBtbSJ9.Zs-Gl2JsEgUrU3qTi4gy4w';

        // Initialize Mapbox map
        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [0, 20], // Initial center (longitude, latitude)
            zoom: 1.5,
            projection: 'globe'
        });

        // Add fog for aesthetic effect
        map.on('style.load', () => {
            map.setFog({
                range: [0.5, 10],
                color: 'rgba(135, 206, 235, 0.15)',
                "high-color": 'rgba(255, 255, 255, 0.1)',
                "horizon-blend": 0.1,
                "space-color": 'rgba(0, 0, 0, 1)',
                "star-intensity": 0.1
            });
        });

        // Add markers for countries and capitals
        const countries = <?php echo json_encode($countries); ?>;
        if (countries) {
            countries.forEach(country => {
                if (country.latitude && country.longitude) {
                    const marker = new mapboxgl.Marker()
                        .setLngLat([country.longitude, country.latitude])
                        .setPopup(new mapboxgl.Popup().setHTML(
                            `<strong>${country.capital_name}</strong> - ${country.country_name} ${country.flag_emoji}`
                        ))
                        .addTo(map);
                }
            });
        }

        // Search functionality
        document.getElementById('search-bar').addEventListener('input', function () {
            const query = this.value.toLowerCase();
            const match = countries.find(country => 
                country.country_name.toLowerCase() === query || 
                country.capital_name.toLowerCase() === query
            );

            if (match && match.latitude && match.longitude) {
                map.flyTo({ center: [match.longitude, match.latitude], zoom: 5 });
            }
        });
    </script>
</body>
</html>
