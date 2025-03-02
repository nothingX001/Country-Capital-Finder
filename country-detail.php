<?php
// country-detail.php

include 'config.php';

// Get the country ID from the query string
$country_id = $_GET['id'] ?? null;
if (!$country_id) {
    die("Invalid country ID.");
}

try {
    // 1) Fetch the country row from the countries table
    //    Cast coordinates to text so they appear exactly as stored (no float rounding).
    $stmt = $conn->prepare('
        SELECT
            "Country Name" AS country_name,
            "Flag Emoji"   AS flag_emoji,
            "Flag"         AS flag_url,
            "Entity Type"  AS entity_type,
            "Coordinates (Latitude)"::text  AS lat,
            "Coordinates (Longitude)"::text AS lon,
            "Languages"    AS languages,
            "Currency"     AS currency,
            "Region"       AS region,
            "Subregion"    AS subregion,
            "Population"   AS population,
            "Area (km2)"   AS area_km2,
            "Calling Code" AS calling_code,
            "Internet TLD" AS internet_tld
        FROM countries
        WHERE id = ?
        LIMIT 1
    ');
    $stmt->execute([$country_id]);
    $country = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$country) {
        die("Country not found.");
    }

    // 2) Fetch capitals exclusively from the capitals table
    $stmt_cap = $conn->prepare('
        SELECT capital_name, capital_type
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
        <!-- 1) Country Name (required) and Flag Emoji (optional) -->
        <h1>
            <?php echo htmlspecialchars($country['country_name'] ?? ''); ?>
            <?php if (!empty($country['flag_emoji'])): ?>
                <?php echo ' ' . htmlspecialchars($country['flag_emoji']); ?>
            <?php endif; ?>
        </h1>

        <!-- 2) Entity Type (e.g. "UN member", "Territory", "De Facto"), if not empty -->
        <?php if (!empty($country['entity_type'])): ?>
            <p><?php echo htmlspecialchars($country['entity_type']); ?></p>
        <?php endif; ?>

        <!-- 3) Capitals (from capitals table) -->
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

        <!-- 4) Coordinates (from countries table) -->
        <?php if (!empty($country['lat']) && !empty($country['lon'])): ?>
            <p>
                <strong>Coordinates:</strong>
                <?php echo htmlspecialchars($country['lat']) . ', ' . htmlspecialchars($country['lon']); ?>
            </p>
        <?php endif; ?>

        <!-- 5) Languages -->
        <?php if (!empty($country['languages'])): ?>
            <p><strong>Languages:</strong> <?php echo htmlspecialchars($country['languages']); ?></p>
        <?php endif; ?>

        <!-- 6) Currency -->
        <?php if (!empty($country['currency'])): ?>
            <p><strong>Currency:</strong> <?php echo htmlspecialchars($country['currency']); ?></p>
        <?php endif; ?>

        <!-- 7) Region -->
        <?php if (!empty($country['region'])): ?>
            <p><strong>Region:</strong> <?php echo htmlspecialchars($country['region']); ?></p>
        <?php endif; ?>

        <!-- 8) Subregion -->
        <?php if (!empty($country['subregion'])): ?>
            <p><strong>Subregion:</strong> <?php echo htmlspecialchars($country['subregion']); ?></p>
        <?php endif; ?>

        <!-- 9) Population -->
        <?php if (!empty($country['population'])): ?>
            <p><strong>Population:</strong> <?php echo htmlspecialchars($country['population']); ?></p>
        <?php endif; ?>

        <!-- 10) Area (km²) -->
        <?php if (!empty($country['area_km2'])): ?>
            <p><strong>Area (km²):</strong> <?php echo htmlspecialchars($country['area_km2']); ?></p>
        <?php endif; ?>

        <!-- 11) Calling Code -->
        <?php if (!empty($country['calling_code'])): ?>
            <p><strong>Calling Code:</strong> <?php echo htmlspecialchars($country['calling_code']); ?></p>
        <?php endif; ?>

        <!-- 12) Internet TLD -->
        <?php if (!empty($country['internet_tld'])): ?>
            <p><strong>Internet TLD:</strong> <?php echo htmlspecialchars($country['internet_tld']); ?></p>
        <?php endif; ?>

        <!-- Flag image at the bottom -->
        <?php if (!empty($country['flag_url'])): ?>
            <div class="flag-image">
                <img src="<?php echo htmlspecialchars($country['flag_url']); ?>"
                     alt="Flag of <?php echo htmlspecialchars($country['country_name'] ?? ''); ?>">
            </div>
        <?php endif; ?>
    </section>
</body>
</html>
