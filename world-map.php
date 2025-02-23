<?php
// world-map.php
$data = file_get_contents('http://localhost/fetch-country-data.php?type=map');
$countries = json_decode($data, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>World Map</title>
    <link rel="stylesheet" href="styles.css"> <!-- Only the single stylesheet -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css" rel="stylesheet">
    <style>
        /* Ensure the map container has a defined height */
        #map {
            height: 500px;
            width: 100%;
            border-radius: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Common container .page-content + .world-map, keep the ID if you like -->
    <section class="page-content world-map" id="main-world-map">
        <h1>WORLD MAP OF CAPITALS</h1>
        <p>Explore capitals of countries, territories, and more around the world.</p>
        <div class="search-bar-container">
            <input type="text" id="search-bar" placeholder="Search for a country or capital...">
        </div>
        <div id="map"></div>
    </section>

    <script src="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js"></script>
    <script>
        // Replace with your actual Mapbox access token
        mapboxgl.accessToken = 'pk.eyJ1IjoiZGNobzIwMDEiLCJhIjoiY20yYW04bHdtMGl3YjJyb214YXB5dzBtbSJ9.Zs-Gl2JsEgUrU3qTi4gy4w';

        // Initialize the map with your custom style
        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/dcho2001/cm2amde1g001b01qqhve88jlo', // Your custom Mapbox style
            center: [0, 20],
            zoom: 1.5,
            projection: 'globe'
        });

        // Add fog effect for a globe-like appearance
        map.on('style.load', () => {
            map.setFog({
                range: [0.5, 10], // Valid range for fog
                color: 'rgba(135, 206, 235, 0.15)', // Fog color
                "high-color": 'rgba(255, 255, 255, 0.1)', // High-altitude color
                "space-color": 'rgba(0, 0, 0, 1)', // Space color
                "horizon-blend": 0.1, // Horizon blend
                "star-intensity": 0.1 // Star intensity
            });
        });

        // Handle map errors
        map.on('error', (e) => {
            console.error('Map error:', e.error);
            alert('Failed to load the map. Please check the console for details.');
        });

        // Load country data from PHP
        const countries = <?php echo json_encode($countries); ?>;

        // Place markers for each capital record (without popups)
        if (countries) {
            countries.forEach(row => {
                if (row.latitude && row.longitude) {
                    new mapboxgl.Marker()
                        .setLngLat([row.longitude, row.latitude])
                        .addTo(map); // No popup
                }
            });
        }

        // Search bar functionality
        const searchBar = document.getElementById('search-bar');
        searchBar.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            // Find match among countries
            const match = countries.find(row =>
                (row.country_name && row.country_name.toLowerCase() === query) ||
                (row.capital_name && row.capital_name.toLowerCase() === query)
            );
            if (match && match.latitude && match.longitude) {
                map.flyTo({ center: [match.longitude, match.latitude], zoom: 5 });
            }
        });
    </script>
</body>
</html>