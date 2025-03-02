<?php
// country-detail.php

include 'config.php';

$country_id = $_GET['id'] ?? null;
if (!$country_id) {
    die("Invalid country ID.");
}

try {
    // 1) Fetch the country row from countries
    $stmt = $conn->prepare('
        SELECT
            "Country Name"            AS country_name,
            "Sovereign State"         AS sovereign_state,
            "Official Name"           AS official_name,
            "Flag Emoji"              AS flag_emoji,
            "Flag"                    AS flag_url,
            "Capital"                 AS capital_in_countries,  -- if you store a capital here
            "Coordinates (Latitude)"  AS lat,
            "Coordinates (Longitude)" AS lon,
            "Languages"               AS languages,
            "Currency"                AS currency,
            "Region"                  AS region,
            "Subregion"               AS subregion,
            "Population"              AS population,
            "Area (km2)"              AS area_km2,
            "Calling Code"            AS calling_code,
            "Internet TLD"            AS internet_tld,
            "Entity Type"             AS entity_type
        FROM countries
        WHERE id = ?
        LIMIT 1
    ');
    $stmt->execute([$country_id]);
    $country = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$country) {
        die("Country not found.");
    }

    // 2) Fetch any matching capitals from the capitals table
    //    (Remove if you only store the capital in countries)
    $stmt_cap = $conn->prepare('
        SELECT capital_name, capital_type, latitude, longitude
        FROM capitals
        WHERE country_id = ?
    ');
    $stmt_cap->execute([$country_id]);
    $capitals = $stmt_cap->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error fetching country details: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>
        <?php echo htmlspecialchars($country['country_name'] ?? 'Country Detail'); ?> - ExploreCapitals
    </title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="page-content country-detail">
        <h1>
            <?php echo htmlspecialchars($country['country_name'] ?? ''); ?>
            <?php if (!empty($country['flag_emoji'])): ?>
                <?php echo ' ' . htmlspecialchars($country['flag_emoji']); ?>
            <?php endif; ?>
        </h1>

        <!-- Show "Sovereign State" and "Official Name" if desired -->
        <p><strong>Sovereign State:</strong> 
            <?php echo htmlspecialchars($country['sovereign_state'] ?? ''); ?>
        </p>
        <p><strong>Official Name:</strong> 
            <?php echo htmlspecialchars($country['official_name'] ?? ''); ?>
        </p>

        <!-- 1) Capitals from the capitals table -->
        <?php if ($capitals): ?>
            <h2>Capitals (from the capitals table):</h2>
            <ul>
                <?php foreach ($capitals as $cap): ?>
                    <li>
                        <?php echo htmlspecialchars($cap['capital_name']); ?>
                        <?php if (!empty($cap['capital_type'])): ?>
                            <?php echo ' (' . htmlspecialchars($cap['capital_type']) . ')'; ?>
                        <?php endif; ?>
                        <?php if (!empty($cap['latitude']) && !empty($cap['longitude'])): ?>
                            <?php echo ' — ' . htmlspecialchars($cap['latitude']) . ', ' . htmlspecialchars($cap['longitude']); ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No matching capital(s) found in the capitals table.</p>
        <?php endif; ?>

        <!-- 2) If you also store a capital in the countries table -->
        <?php if (!empty($country['capital_in_countries'])): ?>
            <p><strong>Capital (in countries table):</strong>
                <?php echo htmlspecialchars($country['capital_in_countries']); ?>
            </p>
        <?php endif; ?>

        <!-- Flag image (if "Flag" column has a URL) -->
        <?php if (!empty($country['flag_url'])): ?>
            <p><strong>Flag Image:</strong><br>
                <img src="<?php echo htmlspecialchars($country['flag_url']); ?>"
                     alt="Flag of <?php echo htmlspecialchars($country['country_name'] ?? ''); ?>"
                     style="max-width:200px;">
            </p>
        <?php endif; ?>

        <!-- Coordinates from the countries table -->
        <p><strong>Coordinates:</strong>
            <?php
            $lat = $country['lat'] ?? '';
            $lon = $country['lon'] ?? '';
            if ($lat && $lon) {
                echo htmlspecialchars($lat) . ', ' . htmlspecialchars($lon);
            } else {
                echo 'N/A';
            }
            ?>
        </p>

        <!-- Misc. other fields -->
        <p><strong>Languages:</strong> <?php echo htmlspecialchars($country['languages'] ?? ''); ?></p>
        <p><strong>Currency:</strong> <?php echo htmlspecialchars($country['currency'] ?? ''); ?></p>
        <p><strong>Region:</strong> <?php echo htmlspecialchars($country['region'] ?? ''); ?></p>
        <p><strong>Subregion:</strong> <?php echo htmlspecialchars($country['subregion'] ?? ''); ?></p>
        <p><strong>Population:</strong> <?php echo htmlspecialchars($country['population'] ?? ''); ?></p>
        <p><strong>Area (km²):</strong> <?php echo htmlspecialchars($country['area_km2'] ?? ''); ?></p>
        <p><strong>Calling Code:</strong> <?php echo htmlspecialchars($country['calling_code'] ?? ''); ?></p>
        <p><strong>Internet TLD:</strong> <?php echo htmlspecialchars($country['internet_tld'] ?? ''); ?></p>
        <p><strong>Entity Type:</strong> <?php echo htmlspecialchars($country['entity_type'] ?? ''); ?></p>
    </section>
</body>
</html>
