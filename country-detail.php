<?php
// country-detail.php
include 'config.php';

$country_id = $_GET['id'] ?? null;
if (!$country_id) {
    die("Invalid country ID.");
}

// 1) Fetch the main country
$stmt = $conn->prepare("
    SELECT country_name, flag_emoji, language,
           entity_type, disclaimer, parent_id,
           flag_image_url
    FROM countries
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$country_id]);
$country = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$country) {
    die("Country not found.");
}

// 2) Fetch capitals
$stmt_capitals = $conn->prepare("
    SELECT capital_name, capital_type, latitude, longitude
    FROM capitals
    WHERE country_id = ?
");
$stmt_capitals->execute([$country_id]);
$capitals = $stmt_capitals->fetchAll(PDO::FETCH_ASSOC);

// 3) Fetch child territories
$stmt_child_terr = $conn->prepare("
    SELECT id, country_name, flag_emoji, disclaimer
    FROM countries
    WHERE parent_id = ?
      AND entity_type = 'territory'
    ORDER BY country_name ASC
");
$stmt_child_terr->execute([$country_id]);
$child_territories = $stmt_child_terr->fetchAll(PDO::FETCH_ASSOC);

// 4) Fetch child de_facto states
$stmt_child_defacto = $conn->prepare("
    SELECT id, country_name, flag_emoji, disclaimer
    FROM countries
    WHERE parent_id = ?
      AND entity_type = 'de_facto_state'
    ORDER BY country_name ASC
");
$stmt_child_defacto->execute([$country_id]);
$child_de_factos = $stmt_child_defacto->fetchAll(PDO::FETCH_ASSOC);

// 5) Fetch parent info (if territory or de_facto_state)
$parentInfo = null;
if (!empty($country['parent_id'])) {
    $stmt_parent = $conn->prepare("
        SELECT id, country_name
        FROM countries
        WHERE id = ?
    ");
    $stmt_parent->execute([$country['parent_id']]);
    $parentInfo = $stmt_parent->fetch(PDO::FETCH_ASSOC);
}

// 6) Fetch alternate names
$stmt_alt = $conn->prepare("
    SELECT alternate_name
    FROM country_alternate_names
    WHERE country_id = ?
    ORDER BY alternate_name
");
$stmt_alt->execute([$country_id]);
$alts = $stmt_alt->fetchAll(PDO::FETCH_COLUMN);
$alternate_names_list = $alts ? implode(', ', $alts) : 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($country['country_name']); ?> Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="country-detail-styles.css">

    <!-- Mapbox CSS/JS -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div id="country-detail-card">
        <div class="card-header">
            <h1>
                <?php echo htmlspecialchars($country['country_name']); ?>
                <?php if (!empty($country['flag_emoji'])) {
                    echo ' ' . htmlspecialchars($country['flag_emoji']);
                } ?>
            </h1>
        </div>

        <div class="card-content">
            <!-- Map -->
            <div id="map" style="width: 100%; height: 400px; margin-bottom: 20px;"></div>

            <div class="country-info">
                <?php
                // Combine multiple capitals if needed
                if ($capitals) {
                    $capItems = array_map(function($cap) {
                        $cName = htmlspecialchars($cap['capital_name'] ?? 'N/A');
                        $cType = htmlspecialchars($cap['capital_type'] ?? '');
                        return $cType ? "$cName ($cType)" : $cName;
                    }, $capitals);

                    $capital_list  = implode(' / ', $capItems);
                    $capital_count = count($capitals);
                    $capital_label = ($capital_count > 1) ? 'Capitals' : 'Capital';
                } else {
                    $capital_list  = 'N/A';
                    $capital_label = 'Capital';
                }
                ?>
                <p><strong><?php echo $capital_label; ?>:</strong> <?php echo $capital_list; ?></p>

                <?php if (!empty($country['flag_image_url'])): ?>
                    <p><strong>Flag Image:</strong><br>
                        <img src="<?php echo htmlspecialchars($country['flag_image_url']); ?>"
                             alt="Flag of <?php echo htmlspecialchars($country['country_name']); ?>"
                             style="max-width:150px;">
                    </p>
                <?php endif; ?>

                <p><strong>Languages:</strong> <?php echo htmlspecialchars($country['language'] ?? 'N/A'); ?></p>
                
                <!-- CHANGED from 'Alternate Names' to 'Also Referred As' -->
                <p><strong>Also Referred As:</strong> <?php echo htmlspecialchars($alternate_names_list); ?></p>

                <!-- If territory => "Territory of" -->
                <?php if ($country['entity_type'] === 'territory' && $parentInfo): ?>
                    <p>
                        <strong>Territory of:</strong>
                        <a href="country-detail.php?id=<?php echo htmlspecialchars($parentInfo['id']); ?>">
                            <?php echo htmlspecialchars($parentInfo['country_name']); ?>
                        </a>
                    </p>

                    <!-- Move disclaimers here, specifically for territories -->
                    <?php if (!empty($country['disclaimer'])): ?>
                        <p><em><?php echo nl2br(htmlspecialchars($country['disclaimer'])); ?></em></p>
                    <?php endif; ?>

                <!-- If de_facto_state => "Claimed by" -->
                <?php elseif ($country['entity_type'] === 'de_facto_state' && $parentInfo): ?>
                    <p>
                        <strong>Claimed by:</strong>
                        <a href="country-detail.php?id=<?php echo htmlspecialchars($parentInfo['id']); ?>">
                            <?php echo htmlspecialchars($parentInfo['country_name']); ?>
                        </a>
                    </p>

                    <!-- For de_facto_states, you can keep disclaimers in the normal place -->
                    <?php if (!empty($country['disclaimer'])): ?>
                        <p><em><?php echo nl2br(htmlspecialchars($country['disclaimer'])); ?></em></p>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- For normal countries or if no parent_id, disclaimers can appear as usual -->
                    <?php if (!empty($country['disclaimer'])): ?>
                        <p><em><?php echo nl2br(htmlspecialchars($country['disclaimer'])); ?></em></p>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- If main country => show child territories / child de_facto -->
                <?php if (in_array($country['entity_type'], ['member_state','observer_state'])): ?>
                    <?php if (!empty($child_territories)): ?>
                        <h3>Territories Administered by <?php echo htmlspecialchars($country['country_name']); ?></h3>
                        <ul>
                            <?php foreach ($child_territories as $ct): ?>
                                <li>
                                    <a href="country-detail.php?id=<?php echo htmlspecialchars($ct['id']); ?>">
                                        <?php echo htmlspecialchars($ct['country_name']); ?>
                                        <?php if (!empty($ct['flag_emoji'])) {
                                            echo ' ' . htmlspecialchars($ct['flag_emoji']);
                                        } ?>
                                    </a>
                                    <?php if (!empty($ct['disclaimer'])): ?>
                                        <br><em><?php echo htmlspecialchars($ct['disclaimer']); ?></em>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (!empty($child_de_factos)): ?>
                        <h3>De Facto States Claimed by <?php echo htmlspecialchars($country['country_name']); ?></h3>
                        <ul>
                            <?php foreach ($child_de_factos as $cdf): ?>
                                <li>
                                    <a href="country-detail.php?id=<?php echo htmlspecialchars($cdf['id']); ?>">
                                        <?php echo htmlspecialchars($cdf['country_name']); ?>
                                        <?php if (!empty($cdf['flag_emoji'])) {
                                            echo ' ' . htmlspecialchars($cdf['flag_emoji']);
                                        } ?>
                                    </a>
                                    <?php if (!empty($cdf['disclaimer'])): ?>
                                        <br><em><?php echo htmlspecialchars($cdf['disclaimer']); ?></em>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>

            </div> <!-- .country-info -->
        </div> <!-- .card-content -->
    </div> <!-- #country-detail-card -->

    <script>
    mapboxgl.accessToken = 'pk.eyJ1IjoiZGNobzIwMDEiLCJhIjoiY20yYW04bHdtMGl3YjJyb214YXB5dzBtbSJ9.Zs-Gl2JsEgUrU3qTi4gy4w';
    const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/streets-v12',
        center: [0, 0],
        zoom: 2
    });

    // Attempt geocoding
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
        if (!usedGeocode) {
          console.warn('Geocode failed. Using capital coords fallback.');
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

        // Add markers
        <?php foreach ($capitals as $cap) {
            if (!empty($cap['latitude']) && !empty($cap['longitude'])) {
                $safeName = htmlspecialchars($cap['capital_name'], ENT_QUOTES);
        ?>
        new mapboxgl.Marker()
          .setLngLat([<?php echo $cap['longitude']; ?>, <?php echo $cap['latitude']; ?>])
          .setPopup(new mapboxgl.Popup().setHTML('<h3><?php echo $safeName; ?></h3>'))
          .addTo(map);
        <?php } } ?>
      })
      .catch(err => {
        console.error('Mapbox error:', err);
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
