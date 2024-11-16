<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>World Map with Capitals</title>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.css" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Montserrat' rel='stylesheet'>
    <link href="styles.css" rel="stylesheet">
    <link href="world-map-styles.css" rel="stylesheet">
</head>
<body>

<?php include 'navbar.php'; ?>

<div id="main-world-map">
    <h1>WORLD MAP OF CAPITALS</h1>
    <p>Explore the capitals of countries around the world.</p>

    <!-- Search Bar for Finding Capitals -->
    <div class="search-bar-container">
        <input type="text" id="search-bar" placeholder="Search for a country or capital...">
    </div>

    <div id="map" style="height: 500px; border-radius: 15px;"></div>

    <!-- Reset View Button -->
    <div class="reset-button-container">
        <button id="reset-button">RESET VIEW</button>
    </div>
</div>

<script src="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.js"></script>
<script>
    mapboxgl.accessToken = 'pk.eyJ1IjoiZGNobzIwMDEiLCJhIjoiY20yYW04bHdtMGl3YjJyb214YXB5dzBtbSJ9.Zs-Gl2JsEgUrU3qTi4gy4w';

    // Initial map settings
    const initialCenter = [0, 20];
    const initialZoom = 1.5;

    const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/satellite-streets-v11',
        center: initialCenter,
        zoom: initialZoom,
        projection: 'globe'
    });

    map.on('style.load', () => {
        map.setFog({
            color: 'rgba(135, 206, 235, 0.15)',
            "high-color": 'rgba(255, 255, 255, 0.1)',
            "horizon-blend": 0.1,
            "space-color": 'rgba(0, 0, 0, 1)',
            "star-intensity": 0.2
        });
    });

    // Pass PHP country data directly to JavaScript
    const countries = <?php
        include 'config.php';
        $stmt = $conn->prepare("SELECT country_name, capital_name AS capitals, coordinates, flag_emoji FROM countries");
        $stmt->execute();
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = [
                'country_name' => $row['country_name'],
                'capitals' => $row['capitals'],
                'coordinates' => $row['coordinates'],
                'flag_emoji' => $row['flag_emoji']
            ];
        }
        echo json_encode($data);
    ?>;

    const markers = {};
    countries.forEach(country => {
        const capitals = country.capitals.split(',');
        const coordinates = JSON.parse(country.coordinates);
        capitals.forEach((capital, index) => {
            const [lng, lat] = coordinates[index];
            const marker = new mapboxgl.Marker({ color: "blue" })
                .setLngLat([lng, lat])
                .setPopup(new mapboxgl.Popup().setText(`${capital} - ${country.country_name} ${country.flag_emoji}`));
            markers[`${country.country_name.toLowerCase()} - ${capital.toLowerCase()}`] = marker;
        });
    });

    document.getElementById('search-bar').addEventListener('input', function() {
        const searchQuery = this.value.toLowerCase();
        const match = countries.find(country =>
            country.country_name.toLowerCase() === searchQuery ||
            country.capitals.toLowerCase().includes(searchQuery)
        );

        if (match) {
            const coordinates = JSON.parse(match.coordinates);
            const [lng, lat] = coordinates[0];
            map.flyTo({ center: [lng, lat], zoom: 5 });
            Object.values(markers).forEach(marker => marker.remove());
            match.capitals.split(',').forEach(capital => {
                const key = `${match.country_name.toLowerCase()} - ${capital.toLowerCase()}`;
                if (markers[key]) {
                    markers[key].addTo(map);
                }
            });
        }
    });

    // Reset view to initial center and zoom when Reset View button is clicked
    document.getElementById('reset-button').addEventListener('click', () => {
        map.flyTo({ center: initialCenter, zoom: initialZoom });
        Object.values(markers).forEach(marker => marker.remove());
    });
</script>

</body>
</html>
