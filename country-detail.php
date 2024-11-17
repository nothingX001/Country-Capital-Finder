<?php
// Include database connection
include 'config.php';

// Fetch country details using ID from query parameter
$country_id = $_GET['id'] ?? null;

if ($country_id) {
    // Fetch country info
    $stmt = $conn->prepare("SELECT country_name, flag_emoji, language, alternate_names FROM countries WHERE id = ?");
    $stmt->execute([$country_id]);
    $country = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$country) {
        die("Country not found.");
    }

    // Fetch all capitals and their coordinates
    $stmt = $conn->prepare("SELECT capital_name, capital_type, latitude, longitude FROM capitals WHERE country_id = ?");
    $stmt->execute([$country_id]);
    $capitals = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    die("Invalid country ID.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags and stylesheets -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($country['country_name'] ?? 'Unknown Country'); ?> Details</title>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="country-detail-styles.css">

    <!-- Mapbox GL JS -->
    <link href='https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css' rel='stylesheet' />
    <script src='https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js'></script>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div id="country-detail-card">
        <div class="card-header">
            <h1><?php echo htmlspecialchars($country['country_name'] ?? 'Unknown Country'); ?></h1>
        </div>
        <div class="card-content">
            <!-- Map Container -->
            <div id="map" style="width: 100%; height: 400px;"></div>

            <div class="country-info">
                <?php
                if ($capitals) {
                    $capital_names = array_map(function($capital) {
                        $capital_name = htmlspecialchars($capital['capital_name'] ?? 'N/A');
                        $capital_type = htmlspecialchars($capital['capital_type'] ?? '');
                        return $capital_type ? "{$capital_name} ({$capital_type})" : $capital_name;
                    }, $capitals);
                    $capital_list = implode(' / ', $capital_names);
                    $capital_count = count($capitals);
                    $capital_label = $capital_count > 1 ? 'Capitals' : 'Capital';
                } else {
                    $capital_list = 'N/A';
                    $capital_label = 'Capital';
                }
                ?>
                <p><strong><?php echo $capital_label; ?>:</strong> <?php echo $capital_list; ?></p>
                <p><strong>Flag:</strong> <?php echo htmlspecialchars($country['flag_emoji'] ?? 'N/A'); ?></p>
                <p><strong>Languages:</strong> <?php echo htmlspecialchars($country['language'] ?? 'N/A'); ?></p>
                <p><strong>Alternate Names:</strong> <?php echo htmlspecialchars($country['alternate_names'] ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>

    <!-- Mapbox Initialization Script -->
    <script>
        // Replace 'YOUR_MAPBOX_ACCESS_TOKEN' with your actual Mapbox access token
        mapboxgl.accessToken = 'YOUR_MAPBOX_ACCESS_TOKEN';

        // Create a new map instance
        const map = new mapboxgl.Map({
            container: 'map', // Container ID
            style: 'mapbox://styles/mapbox/streets-v12', // Map style
            center: [0, 0], // Initial map center [lng, lat]
            zoom: 2 // Initial zoom level
        });

        // Fetch the country's bounding box using Mapbox Geocoding API
        fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/<?php echo urlencode($country['country_name']); ?>.json?access_token=${mapboxgl.accessToken}&limit=1`)
            .then(response => response.json())
            .then(data => {
                if (data.features.length > 0) {
                    const countryFeature = data.features[0];
                    const bbox = countryFeature.bbox;
                    if (bbox) {
                        // Fit the map to the country's bounding box
                        map.fitBounds(bbox, { padding: 20 });
                    } else {
                        // Center the map on the country's center
                        map.setCenter(countryFeature.center);
                        map.setZoom(4);
                    }

                    // Add markers for the capitals
                    <?php foreach ($capitals as $capital) {
                        if (!empty($capital['latitude']) && !empty($capital['longitude'])) {
                            $capitalName = htmlspecialchars($capital['capital_name']);
                            $latitude = $capital['latitude'];
                            $longitude = $capital['longitude'];
                    ?>
                    new mapboxgl.Marker()
                        .setLngLat([<?php echo $longitude; ?>, <?php echo $latitude; ?>])
                        .setPopup(new mapboxgl.Popup().setHTML('<h3><?php echo $capitalName; ?></h3>'))
                        .addTo(map);
                    <?php } } ?>
                } else {
                    console.error('Country not found in Geocoding API.');
                }
            })
            .catch(error => console.error('Error fetching country data:', error));
    </script>
</body>
</html>
