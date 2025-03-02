<?php
// country-detail.php

include 'config.php';

$country_id = $_GET['id'] ?? null;
if (!$country_id) {
    die("Invalid country ID.");
}

try {
    // Fetch the country row using the exact column names
    $stmt = $conn->prepare('
        SELECT
            "Country Name"            AS country_name,
            "Sovereign State"         AS sovereign_state,
            "Official Name"           AS official_name,
            "Flag Emoji"              AS flag_emoji,
            "Flag"                    AS flag_url,
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

    // Fetch capitals (only from the capitals table)
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
    <title><?php echo htmlspecialchars($country['country_name']); ?> - Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="page-content country-detail">
        <!-- Header: Country Name with Flag Emoji -->
        <h1>
            <?php echo htmlspecialchars($country['country_name']); ?>
            <?php if (!empty($country['flag_emoji'])): ?>
                <?php echo ' ' . htmlspecialchars($country['flag_emoji']); ?>
            <?php endif; ?>
        </h1>

        <!-- Display Entity Type as plain text (e.g. UN member, Territory, etc.) -->
        <?php if (!empty($country['entity_type'])): ?>
            <p><?php echo htmlspecialchars($country['entity_type']); ?></p>
        <?php endif; ?>

        <!-- 1. Capitals (from capitals table) -->
        <?php if (!empty($capitals)): ?>
            <h2>Capitals</h2>
            <ul>
                <?php foreach ($capitals as $cap): ?>
                    <li>
                        <?php echo htmlspecialchars($cap['capital_name']); ?>
                        <?php if (!empty($cap['capital_type'])): ?>
                            (<?php echo htmlspecialchars($cap['capital_type']); ?>)
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- 2. Coordinates (from countries table) -->
        <?php if (!empty($country['lat']) && !empty($country['lon'])): ?>
            <p><strong>Coordinates:</strong> <?php echo htmlspecialchars($country['lat']); ?>, <?php echo htmlspecialchars($country['lon']); ?></p>
        <?php endif; ?>

        <!-- 3. Languages -->
        <?php if (!empty($country['languages'])): ?>
            <p><strong>Languages:</strong> <?php echo htmlspecialchars($country['languages']); ?></p>
        <?php endif; ?>

        <!-- 4. Currency -->
        <?php if (!empty($country['currency'])): ?>
            <p><strong>Currency:</strong> <?php echo htmlspecialchars($country['currency']); ?></p>
        <?php endif; ?>

        <!-- 5. Region -->
        <?php if (!empty($country['region'])): ?>
            <p><strong>Region:</strong> <?php echo htmlspecialchars($country['region']); ?></p>
        <?php endif; ?>

        <!-- 6. Subregion -->
        <?php if (!empty($country['subregion'])): ?>
            <p><strong>Subregion:</strong> <?php echo htmlspecialchars($country['subregion']); ?></p>
        <?php endif; ?>

        <!-- 7. Population -->
        <?php if (!empty($country['population'])): ?>
            <p><strong>Population:</strong> <?php echo htmlspecialchars($country['population']); ?></p>
        <?php endif; ?>

        <!-- 8. Area (km²) -->
        <?php if (!empty($country['area_km2'])): ?>
            <p><strong>Area (km²):</strong> <?php echo htmlspecialchars($country['area_km2']); ?></p>
        <?php endif; ?>

        <!-- 9. Calling Code -->
        <?php if (!empty($country['calling_code'])): ?>
            <p><strong>Calling Code:</strong> <?php echo htmlspecialchars($country['calling_code']); ?></p>
        <?php endif; ?>

        <!-- 10. Internet TLD -->
        <?php if (!empty($country['internet_tld'])): ?>
            <p><strong>Internet TLD:</strong> <?php echo htmlspecialchars($country['internet_tld']); ?></p>
        <?php endif; ?>

        <!-- Flag image at the bottom -->
        <?php if (!empty($country['flag_url'])): ?>
            <div class="flag-image">
                <img src="<?php echo htmlspecialchars($country['flag_url']); ?>"
                     alt="Flag of <?php echo htmlspecialchars($country['country_name']); ?>">
            </div>
        <?php endif; ?>
    </section>
</body>
</html>
