<?php
// world-map.php

// Fetch location data from your API endpoint.
$data = file_get_contents('http://localhost/fetch-country-data.php?type=map');
$locations = json_decode($data, true);
?>
<!DOCTYPE html>
<html lang="en" style="overscroll-behavior-y: none; overflow-x: hidden;">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="description" content="Explore capitals of countries, territories, and more with our interactive world map! Find country capitals, learn about different nations, and test your geography knowledge.">
  <meta name="keywords" content="world map, country capitals, geography, interactive map, world geography, country information">
  <meta name="author" content="ExploreCapitals">
  <meta property="og:title" content="World Map | ExploreCapitals">
  <meta property="og:description" content="Explore capitals of countries, territories, and more with our interactive world map!">
  <meta property="og:type" content="website">
  <meta property="og:image" content="images/explore-capitals-logo.jpg">
  <title>World Map | ExploreCapitals</title>
  <link rel="icon" type="image/jpeg" href="images/explore-capitals-logo.jpg">
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  <style>
    #map {
      height: 500px;
      width: 100%;
      border-radius: 15px;
      margin-top: 20px;
    }
    html, body {
        overscroll-behavior-y: none !important;
        overflow-x: hidden !important;
    }
    .leaflet-popup-content {
      margin: 10px;
      text-align: center;
    }
    .leaflet-popup-content h3 {
      margin: 0 0 5px 0;
      color: #2A363B;
      font-size: 16px;
    }
    .leaflet-popup-content p {
      margin: 0;
      color: #2A363B;
      font-size: 14px;
    }
  </style>
</head>
<body style="overscroll-behavior-y: none; background: linear-gradient(180deg, #3B4B54, #DCCB9C);">
  <?php include 'navbar.php'; ?>

  <section class="page-content world-map" id="main-world-map">
    <h1>World Map</h1>
    <p>Explore countries and their capitals around the world.</p>
    <div class="search-bar-container">
      <input type="text" id="search-bar" name="country" placeholder="Search for a country..." autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
    </div>
    <div id="map"></div>
  </section>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script src="map-autocomplete.js"></script>
  <script>
    // Initialize the map
    const map = L.map('map', {
      center: [20, 0],
      zoom: 2,
      minZoom: 2,
      maxZoom: 18
    });

    // Add CARTO Voyager tiles (a more modern and clearer style)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>',
      subdomains: 'abcd',
      maxZoom: 20
    }).addTo(map);

    // Expected keys: country_name, capital_name, latitude, longitude, iso_code, flag_emoji
    const locations = <?php echo json_encode($locations); ?>;
    
    const searchBar = document.getElementById('search-bar');
    searchBar.addEventListener('input', function() {
      const query = this.value.toLowerCase().trim();
      if (!query) return;
      
      // Only check for an exact match on country_name
      const matchCountry = locations.find(loc =>
        loc.country_name && loc.country_name.toLowerCase() === query
      );
      
      if (matchCountry && matchCountry.latitude && matchCountry.longitude) {
        const lng = parseFloat(matchCountry.longitude);
        const lat = parseFloat(matchCountry.latitude);
        
        // Calculate zoom level based on country size
        let zoomLevel = 5; // Default zoom level increased from 4 to 5
        
        // If we have bounding box coordinates, use them to calculate zoom
        if (matchCountry.min_lat && matchCountry.max_lat && 
            matchCountry.min_lng && matchCountry.max_lng) {
          const latDiff = Math.abs(matchCountry.max_lat - matchCountry.min_lat);
          const lngDiff = Math.abs(matchCountry.max_lng - matchCountry.min_lng);
          
          // Calculate the larger difference to determine zoom level
          const maxDiff = Math.max(latDiff, lngDiff);
          
          // Adjust zoom level based on the size
          if (maxDiff < 0.5) { // Very small countries (like Vatican City, Singapore)
            zoomLevel = 12;    // Increased from 10
          } else if (maxDiff < 1) { // Small countries
            zoomLevel = 11;    // Increased from 9
          } else if (maxDiff < 2) { // Medium-small countries
            zoomLevel = 10;    // Increased from 8
          } else if (maxDiff < 5) { // Medium countries
            zoomLevel = 8;     // Increased from 7
          } else if (maxDiff < 10) { // Medium-large countries
            zoomLevel = 7;     // Increased from 6
          } else if (maxDiff < 20) { // Large countries
            zoomLevel = 6;     // Increased from 5
          } else { // Very large countries (like Russia, Canada, China)
            zoomLevel = 5;     // Increased from 4
          }
        }
        
        map.flyTo([lat, lng], zoomLevel, {
          duration: 2,
          easeLinearity: 0.25
        });
      }
    });

    // Add markers for each location
    locations.forEach(location => {
      if (location.latitude && location.longitude && location.capital_name) {  // Only add markers for capitals
        const lat = parseFloat(location.latitude);
        const lng = parseFloat(location.longitude);
        
        if (!isNaN(lat) && !isNaN(lng)) {
          const marker = L.marker([lat, lng]).addTo(map);
          
          // Create popup content with capital name
          const popupContent = `<h3>${location.capital_name}</h3>`;
          marker.bindPopup(popupContent);
        }
      }
    });
  </script>
</body>
</html>
