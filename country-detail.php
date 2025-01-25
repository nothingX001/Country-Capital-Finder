<?php
// country-detail.php

include 'config.php';

$country_id = $_GET['id'] ?? null;
if (!$country_id) {
    die("Invalid country ID.");
}

// Fetch main country info
$stmt = $conn->prepare("
    SELECT country_name, flag_emoji, language,
           entity_type, disclaimer, parent_id, flag_image_url
    FROM countries
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$country_id]);
$country = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$country) {
    die("Country not found.");
}

// Fetch capitals
$stmt_capitals = $conn->prepare("
    SELECT capital_name, capital_type, latitude, longitude
    FROM capitals
    WHERE country_id = ?
");
$stmt_capitals->execute([$country_id]);
$capitals = $stmt_capitals->fetchAll(PDO::FETCH_ASSOC);

// Fetch alternate names
$stmt_alt = $conn->prepare("
    SELECT alternate_name
    FROM country_alternate_names
    WHERE country_id = ?
    ORDER BY alternate_name
");
$stmt_alt->execute([$country_id]);
$alts = $stmt_alt->fetchAll(PDO::FETCH_COLUMN);
$alternate_names_list = $alts ? implode(', ', $alts) : 'N/A';

// (Optional) Fetch any child entities
$stmt_children = $conn->prepare("
    SELECT id, country_name, flag_emoji, entity_type, disclaimer
    FROM countries
    WHERE parent_id = ?
    ORDER BY country_name ASC
");
$stmt_children->execute([$country_id]);
$child_entities = $stmt_children->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($country['country_name'] ?? 'Unknown'); ?> Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="country-detail-styles.css">

    <!-- Mapbox CSS & JS -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js"></script>
</head>
<body>
<?php include 'navbar.php'; ?>

<div id="country-detail-card">
    <div class="card-header">
        <h1>
            <?php echo htmlspecialchars($country['country_name']); ?>
            <?php if (!empty($country['flag_emoji'])): ?>
                <?php echo ' ' . htmlspecialchars($country['flag_emoji']); ?>
            <?php endif; ?>
        </h1>
    </div>

    <div class="card-content">
        <!-- Map container -->
        <div id="map" style="width: 100%; height: 400px; margin-bottom: 20px;"></div>

        <div class="country-info">
            <?php
            // Combine multiple capitals if needed
            if ($capitals) {
                $capital_names = array_map(function($cap) {
                    $cName = htmlspecialchars($cap['capital_name']);
                    $cType = htmlspecialchars($cap['capital_type'] ?? '');
                    return $cType ? "$cName ($cType)" : $cName;
                }, $capitals);

                $capital_list  = implode(' / ', $capital_names);
                $capital_count = count($capitals);
                $capital_label = ($capital_count > 1) ? 'Capitals' : 'Capital';
            } else {
                $capital_list  = 'N/A';
                $capital_label = 'Capital';
            }
            ?>
            <p><strong><?php echo $capital_label; ?>:</strong> <?php echo $capital_list; ?></p>

            <?php if (!empty($country['flag_image_url'])): ?>
                <p>
                    <strong>Flag Image:</strong><br>
                    <img src="<?php echo htmlspecialchars($country['flag_image_url']); ?>"
                         alt="Flag of <?php echo htmlspecialchars($country['country_name']); ?>"
                         style="max-width: 150px;">
                </p>
            <?php endif; ?>

            <p><strong>Languages:</strong>
               <?php echo htmlspecialchars($country['language'] ?? 'N/A'); ?>
            </p>
            <p><strong>Alternate Names:</strong>
               <?php echo htmlspecialchars($alternate_names_list); ?>
            </p>

            <!-- If territory or de_facto_state -->
            <?php if (!empty($country['entity_type']) && in_array($country['entity_type'], ['territory','de_facto_state'])): ?>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($country['entity_type']); ?></p>
            <?php endif; ?>

            <?php if (!empty($country['disclaimer'])): ?>
                <p><em><?php echo nl2br(htmlspecialchars($country['disclaimer'])); ?></em></p>
            <?php endif; ?>

            <!-- If this is a territory or de_facto_state => show parent -->
            <?php
            if (!empty($country['entity_type'])
                && in_array($country['entity_type'], ['territory','de_facto_state'])
                && !empty($country['parent_id'])) {
                // fetch parent
                $stmt_parent = $conn->prepare("SELECT id, country_name FROM countries WHERE id = ?");
                $stmt_parent->execute([$country['parent_id']]);
                $parent = $stmt_parent->fetch(PDO::FETCH_ASSOC);

                if ($parent) {
                    $parentNameEsc = htmlspecialchars($parent['country_name']);
                    $parentLink    = "country-detail.php?id=".htmlspecialchars($parent['id']);

                    if ($country['entity_type'] === 'territory') {
                        echo "<p><strong>Administered by:</strong> 
                              <a href='$parentLink'>$parentNameEsc</a></p>";
                    } else {
                        echo "<p><strong>Claimed by:</strong> 
                              <a href='$parentLink'>$parentNameEsc</a></p>";
                    }
                }
            }
            ?>

            <!-- Child Territories / De Facto States -->
            <?php if (!empty($child_entities)): ?>
                <h3>Child Territories / De Facto States</h3>
                <ul>
                <?php foreach ($child_entities as $child): ?>
                    <li>
                        <a href="country-detail.php?id=<?php echo htmlspecialchars($child['id']); ?>">
                            <?php echo htmlspecialchars($child['country_name']); ?>
                            <?php if (!empty($child['flag_emoji'])) {
                                echo ' ' . htmlspecialchars($child['flag_emoji']);
                            } ?>
                        </a>
                        <?php if (!empty($child['disclaimer'])): ?>
                            <br><em><?php echo htmlspecialchars($child['disclaimer']); ?></em>
                        <?php endif; ?>
                        <br><strong>Type:</strong>
                            <?php echo htmlspecialchars($child['entity_type']); ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        </div><!-- .country-info -->
    </div><!-- .card-content -->
</div><!-- #country-detail-card -->

<script>
    // Replace token with your own
    mapboxgl.accessToken = 'pk.eyJ1IjoiZGNobzIwMDEiLCJhIjoiY20yYW04bHdtMGl3YjJyb214YXB5dzBtbSJ9.Zs-Gl2JsEgUrU3qTi4gy4w';

    const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [0, 0],
        zoom: 2
    });

    // Attempt geocoding of country_name
    fetch(`https://api.mapbox.com/geocoding/v5/mapbox.places/<?php echo urlencode($country['country_name']); ?>.json?access_token=${mapboxgl.accessToken}&limit=1`)
        .then(r => r.json())
        .then(data => {
            let usedGeocode = false;
            if (data.features && data.features.length > 0) {
                const feat = data.features[0];
                if (feat.bbox) {
                    map.fitBounds(feat.bbox, { padding: 20 });
                    usedGeocode = true;
                } else if (feat.center && feat.center.length === 2) {
                    map.setCenter(feat.center);
                    map.setZoom(4);
                    usedGeocode = true;
                }
            }
            // Fallback if geocode fails
            if (!usedGeocode) {
                console.warn('No geocode found; using capital fallback.');
                <?php if (!empty($capitals)):
                    $first = $capitals[0];
                    if (!empty($first['latitude']) && !empty($first['longitude'])) {
                        $lat = $first['latitude'];
                        $lng = $first['longitude'];
                ?>
                map.setCenter([<?php echo $lng; ?>, <?php echo $lat; ?>]);
                map.setZoom(6);
                <?php } else { ?>
                map.setCenter([0, 0]);
                map.setZoom(2);
                <?php } endif; ?>
            }

            // Add capital markers
            <?php foreach ($capitals as $cap) {
                if (!empty($cap['latitude']) && !empty($cap['longitude'])) {
                    $capNameJS = htmlspecialchars($cap['capital_name'], ENT_QUOTES);
            ?>
            new mapboxgl.Marker()
                .setLngLat([<?php echo $cap['longitude']; ?>, <?php echo $cap['latitude']; ?>])
                .setPopup(new mapboxgl.Popup().setHTML('<h3><?php echo $capNameJS; ?></h3>'))
                .addTo(map);
            <?php } } ?>
        })
        .catch(err => {
            console.error('Mapbox fetch failed:', err);
            // Hard fallback
            <?php if (!empty($capitals)):
                $first = $capitals[0];
                if (!empty($first['latitude']) && !empty($first['longitude'])) {
                    $lat = $first['latitude'];
                    $lng = $first['longitude'];
            ?>
            map.setCenter([<?php echo $lng; ?>, <?php echo $lat; ?>]);
            map.setZoom(6);
            <?php } else { ?>
            map.setCenter([0, 0]);
            map.setZoom(2);
            <?php } endif; ?>
        });
</script>
</body>
</html>
