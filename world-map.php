<?php
// world-map.php

// Fetch location data from your API endpoint.
$data = file_get_contents('http://localhost/fetch-country-data.php?type=map');
$locations = json_decode($data, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Explore capitals of countries, territories, and more with our world map!">
  <title>World Map | ExploreCapitals</title>
  <link rel="stylesheet" href="styles.css">
  <link href="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css" rel="stylesheet">
  <style>
    /* Revert to your original styling */
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
    mapboxgl.accessToken = 'pk.eyJ1IjoiZGNobzIwMDEiLCJhIjoiY20yYW04bHdtMGl3YjJyb214YXB5dzBtbSJ9.Zs-Gl2JsEgUrU3qTi4gy4w';

    const map = new mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/mapbox/streets-v12',
      center: [0, 20],
      zoom: 1.5,
      projection: 'globe'
    });

    map.on('style.load', () => {
      map.setFog({
        range: [0.5, 10],
        color: 'rgba(135, 206, 235, 0.15)',
        "high-color": 'rgba(255, 255, 255, 0.1)',
        "space-color": 'rgba(0, 0, 0, 1)',
        "horizon-blend": 0.1,
        "star-intensity": 0.1
      });

      map.addSource('country-borders', {
        type: 'vector',
        url: 'mapbox://mapbox.country-boundaries-v1'
      });

      map.addLayer({
        id: 'country-borders-highlight',
        type: 'line',
        source: 'country-borders',
        'source-layer': 'country_boundaries',
        paint: {
          'line-color': '#FF0000',
          'line-width': 2
        },
        filter: ['==', 'iso_3166_1', '']
      });
    });

    map.on('error', (e) => {
      console.error('Map error:', e.error);
      alert('Failed to load the map. Please check the console for details.');
    });

    // Expected keys: country_name, capital_name, latitude, longitude, iso_code, flag_emoji
    const locations = <?php echo json_encode($locations); ?>;
    
    const searchBar = document.getElementById('search-bar');
    searchBar.addEventListener('input', function() {
      const query = this.value.toLowerCase().trim();
      if (!query) return;
      
      // First, check for an exact capital match.
      const matchCapital = locations.find(loc => 
        loc.capital_name && loc.capital_name.toLowerCase() === query
      );
      
      if (matchCapital && matchCapital.latitude && matchCapital.longitude) {
        const lng = parseFloat(matchCapital.longitude);
        const lat = parseFloat(matchCapital.latitude);
        // For capitals, zoom in close so that the capital point is centered.
        map.flyTo({ center: [lng, lat], zoom: 8 });
        if (matchCapital.iso_code) {
          map.setFilter('country-borders-highlight', ['==', 'iso_3166_1', matchCapital.iso_code]);
        }
        return;
      }
      
      // Next, check for an exact country match.
      const matchCountry = locations.find(loc => 
        loc.country_name && loc.country_name.toLowerCase() === query
      );
      
      if (matchCountry && matchCountry.latitude && matchCountry.longitude) {
        const lng = parseFloat(matchCountry.longitude);
        const lat = parseFloat(matchCountry.latitude);
        // For countries, use a zoom level that shows the entire country.
        map.flyTo({ center: [lng, lat], zoom: 3 });
        if (matchCountry.iso_code) {
          map.setFilter('country-borders-highlight', ['==', 'iso_3166_1', matchCountry.iso_code]);
        }
        return;
      }
      
      // If no match, clear any border highlighting.
      map.setFilter('country-borders-highlight', ['==', 'iso_3166_1', '']);
    });
  </script>
</body>
</html>
