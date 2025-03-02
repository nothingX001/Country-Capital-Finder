<?php
// world-map.php

// Fetch location data from your API endpoint.
$data = file_get_contents('http://localhost/fetch-country-data.php?type=map');
$countries = json_decode($data, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Explore capitals of countries, territories, and more with our world map!">
  <title>World Map | ExploreCapitals</title>
  <link rel="stylesheet" href="styles.css"> <!-- Only the single stylesheet -->
  <link href="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css" rel="stylesheet">
  <style>
    /* Original styling from your initial map page */
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

  <section class="page-content world-map" id="main-world-map">
    <h1>World Map</h1>
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

    // Initialize the map with the original styling.
    const map = new mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/mapbox/streets-v12', // Default Mapbox style
      center: [0, 20], // Initial map center
      zoom: 1.5, // Initial zoom level
      projection: 'globe' // Enable globe projection
    });

    // Add the fog and country borders as in your original code.
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

    map.on('error', (e) => {
      console.error('Map error:', e.error);
      alert('Failed to load the map. Please check the console for details.');
    });

    // Use the location data from PHP (keys: country_name, capital_name, latitude, longitude, iso_code, flag_emoji)
    const countries = <?php echo json_encode($countries); ?>;

    // Search bar functionality: require an exact full-match (case-insensitive)
    const searchBar = document.getElementById('search-bar');
    searchBar.addEventListener('input', function() {
      const query = this.value.toLowerCase().trim();
      // Find a location whose country_name or capital_name exactly matches the query.
      const match = countries.find(loc => {
        return (loc.country_name && loc.country_name.toLowerCase() === query) ||
               (loc.capital_name && loc.capital_name.toLowerCase() === query);
      });
      if (match && match.latitude && match.longitude) {
        // Determine zoom level: if capital_name is null, assume it's a country and zoom out more.
        const zoomLevel = (match.capital_name === null) ? 4 : 5;
        const lng = parseFloat(match.longitude);
        const lat = parseFloat(match.latitude);
        map.flyTo({ center: [lng, lat], zoom: zoomLevel });
        // Optionally, if iso_code is available, highlight the country borders.
        if (match.iso_code) {
          map.setFilter('country-borders-highlight', ['==', 'iso_3166_1', match.iso_code]);
        }
      } else {
        // If no match is found, clear any border highlighting.
        map.setFilter('country-borders-highlight', ['==', 'iso_3166_1', '']);
      }
    });
  </script>
</body>
</html>
