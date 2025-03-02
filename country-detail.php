<?php
// country-detail.php

include 'config.php';

// Get the country ID from the query string
$country_id = $_GET['id'] ?? null;
if (!$country_id) {
    die("Invalid country ID.");
}

try {
    // 1) Fetch the country row from the countries table.
    //    Coordinates are cast to text to preserve full precision.
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

    // 2) Fetch capitals exclusively from the capitals table.
    $stmt_cap = $conn->prepare('
        SELECT capital_name, capital_type
        FROM capitals
        WHERE country_id = ?
    ');
    $stmt_cap->execute([$country_id]);
    $capitals = $stmt_cap->fetchAll(PDO::FETCH_ASSOC);

    // Format coordinates: convert to float and add degrees and direction.
    $lat = floatval($country['lat']);
    $lon = floatval($country['lon']);
    $latDir = ($lat >= 0) ? 'N' : 'S';
    $lonDir = ($lon >= 0) ? 'E' : 'W';
    // Adjust decimals as needed (here 4 decimals)
    $latFormatted = number_format(abs($lat), 4) . "° " . $latDir;
    $lonFormatted = number_format(abs($lon), 4) . "° " . $lonDir;

    // Format population with commas.
    $popFormatted = !empty($country['population']) ? number_format($country['population']) : '';

    // Format calling code with plus sign.
    $callingCode = '';
    if (!empty($country['calling_code'])) {
        $cc = trim($country['calling_code']);
        $callingCode = (strpos($cc, '+') === 0) ? $cc : '+' . $cc;
    }
} catch (Exception $e) {
    die("Error fetching country details: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($country['country_name'] ?? 'Country Detail'); ?> - ExploreCapitals</title>
    <link rel="stylesheet" href="styles.css">
    <style>
      /* Additional styles specific to the country detail page */
      .country-detail-header {
          margin-bottom: 20px;
      }
      .country-detail-header h1 {
          font-size: 2.5rem;
          margin-bottom: 10px;
      }
      .country-detail-entity {
          font-size: 1.2rem;
          color: #666;
          margin-bottom: 20px;
      }
      .flag-image {
          text-align: center;
          margin-bottom: 30px;
      }
      .flag-image img {
          max-width: 300px;
          border: 1px solid #ccc;
          border-radius: 4px;
      }
      .attributes {
          display: flex;
          flex-direction: column;
          align-items: flex-start;
          gap: 15px;
          max-width: 500px;
          margin: 0 auto;
          text-align: left;
          font-size: 1.1rem;
          color: #333;
      }
      .attributes p {
          margin: 0;
      }
      .attributes strong {
          display: inline-block;
          width: 140px;
      }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="page-content country-detail">
        <!-- Header: Country Name and Entity Type -->
        <div class="country-detail-header">
            <h1>
                <?php echo htmlspecialchars($country['country_name']); ?>
                <?php if (!empty($country['flag_emoji'])): ?>
                    <?php echo ' ' . htmlspecialchars($country['flag_emoji']); ?>
                <?php endif; ?>
            </h1>
            <?php if (!empty($country['entity_type'])): ?>
                <div class="country-detail-entity"><?php echo htmlspecialchars($country['entity_type']); ?></div>
            <?php endif; ?>
        </div>

        <!-- Flag Image (placed directly below entity type) -->
        <?php if (!empty($country['flag_url'])): ?>
            <div class="flag-image">
                <img src="<?php echo htmlspecialchars($country['flag_url']); ?>"
                     alt="Flag of <?php echo htmlspecialchars($country['country_name'] ?? ''); ?>">
            </div>
        <?php endif; ?>

        <!-- Capitals Section -->
        <?php if (!empty($capitals)): ?>
            <h2>Capitals</h2>
            <ul style="list-style: none; padding: 0; margin-bottom: 30px; text-align: left;">
                <?php foreach ($capitals as $cap): ?>
                    <li style="margin-bottom: 5px;">
                        <?php echo '<strong>' . htmlspecialchars($cap['capital_name']) . '</strong>'; ?>
                        <?php if (!empty($cap['capital_type'])): ?>
                            (<?php echo htmlspecialchars($cap['capital_type']); ?>)
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- Attributes Section -->
        <div class="attributes">
            <?php if (!empty($latFormatted) && !empty($lonFormatted)): ?>
                <p><strong>Coordinates:</strong> <?php echo $latFormatted . ', ' . $lonFormatted; ?></p>
            <?php endif; ?>
            <?php if (!empty($country['languages'])): ?>
                <p><strong>Languages:</strong> <?php echo htmlspecialchars($country['languages']); ?></p>
            <?php endif; ?>
            <?php if (!empty($country['currency'])): ?>
                <p><strong>Currency:</strong> <?php echo htmlspecialchars($country['currency']); ?></p>
            <?php endif; ?>
            <?php if (!empty($country['region'])): ?>
                <p><strong>Region:</strong> <?php echo htmlspecialchars($country['region']); ?></p>
            <?php endif; ?>
            <?php if (!empty($country['subregion'])): ?>
                <p><strong>Subregion:</strong> <?php echo htmlspecialchars($country['subregion']); ?></p>
            <?php endif; ?>
            <?php if (!empty($popFormatted)): ?>
                <p><strong>Population:</strong> <?php echo $popFormatted; ?></p>
            <?php endif; ?>
            <?php if (!empty($country['area_km2'])): ?>
                <p><strong>Area (km²):</strong> <?php echo htmlspecialchars($country['area_km2']); ?></p>
            <?php endif; ?>
            <?php if (!empty($callingCode)): ?>
                <p><strong>Calling Code:</strong> <?php echo htmlspecialchars($callingCode); ?></p>
            <?php endif; ?>
            <?php if (!empty($country['internet_tld'])): ?>
                <p><strong>Internet TLD:</strong> <?php echo htmlspecialchars($country['internet_tld']); ?></p>
            <?php endif; ?>
        </div>
    </section>
</body>
</html>
