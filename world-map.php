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
        <h1>WORLD MAP OF COUNTRIES</h1>
        <p>Explore countries and their capitals around the world.</p>
        <div class="search-bar-container">
            <input type="text" id="search-bar" placeholder="Search for a country or capital...">
        </div>
        <div id="map"></div>
    </section>

    <script src="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js"></script>
    <script>
        // Replace with your actual Mapbox access token
        mapboxgl.accessToken = 'pk.eyJ1IjoiZGNobzIwMDEiLCJhIjoiY20yYW04bHdtMGl3YjJyb214YXB5dzBtbSJ9.Zs-Gl2JsEgUrU3qTi4gy4w';

        // Initialize the map with a default Mapbox style
        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v12', // Default Mapbox style
            center: [0, 20], // Initial map center
            zoom: 1.5, // Initial zoom level
            projection: 'globe' // Enable globe projection
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

            // Add a source for country borders
            map.addSource('country-borders', {
                type: 'vector',
                url: 'mapbox://mapbox.country-boundaries-v1'
            });

            // Add a layer to highlight country borders
            map.addLayer({
                id: 'country-borders-highlight',
                type: 'line',
                source: 'country-borders',
                'source-layer': 'country_boundaries',
                paint: {
                    'line-color': '#FF0000', // Red border color
                    'line-width': 2 // Border width
                },
                filter: ['==', 'iso_3166_1', ''] // Initially no country selected
            });
        });

        // Handle map errors
        map.on('error', (e) => {
            console.error('Map error:', e.error);
            alert('Failed to load the map. Please check the console for details.');
        });

        // Load country data from PHP
        const countries = <?php echo json_encode($countries); ?>;

        // Search bar functionality
        const searchBar = document.getElementById('search-bar');
        searchBar.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            // Find match among countries
            const match = countries.find(row =>
                (row.country_name && row.country_name.toLowerCase() === query) ||
                (row.capital_name && row.capital_name.toLowerCase() === query)
            );
            if (match) {
                // Fly to the country's center
                if (match.latitude && match.longitude) {
                    map.flyTo({ center: [match.longitude, match.latitude], zoom: 5 });
                }

                // Highlight the country borders
                if (match.iso_code) { // Ensure the country has an ISO code
                    map.setFilter('country-borders-highlight', ['==', 'iso_3166_1', match.iso_code]);
                }
            } else {
                // Clear the border highlight if no match is found
                map.setFilter('country-borders-highlight', ['==', 'iso_3166_1', '']);
            }
        });
    </script>
</body>
</html>