<?php
// country-detail.php

include 'config.php';

// Get the country ID from the query string
$country_id = $_GET['id'] ?? null;
if (!$country_id) {
    die("Invalid country ID.");
}

try {
    // 1) Fetch the country row
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

    // Format coordinates
    $latVal = floatval($country['lat']);
    $lonVal = floatval($country['lon']);
    $latDir = ($latVal >= 0) ? 'N' : 'S';
    $lonDir = ($lonVal >= 0) ? 'E' : 'W';
    $latFormatted = number_format(abs($latVal), 4) . "° $latDir";
    $lonFormatted = number_format(abs($lonVal), 4) . "° $lonDir";

    // Format population with commas
    $popFormatted = !empty($country['population']) ? number_format($country['population']) : '';

    // Format calling code with plus sign
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
      /* Additional styling for country detail */
      .country-detail-header {
          margin-bottom: 20px;
          text-align: center;
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

        <!-- Flag Image (below entity type) -->
        <?php if (!empty($country['flag_url'])): ?>
            <div class="flag-image">
                <img src="<?php echo htmlspecialchars($country['flag_url']); ?>"
                     alt="Flag of <?php echo htmlspecialchars($country['country_name'] ?? ''); ?>">
            </div>
        <?php endif; ?>

        <!-- Attributes Section -->
        <div class="attributes">
            <?php
            // 1) Capitals
            if (!empty($capitals)) {
                // Combine all capitals in a single string
                $capList = [];
                foreach ($capitals as $cap) {
                    $capString = htmlspecialchars($cap['capital_name']);
                    if (!empty($cap['capital_type'])) {
                        $capString .= ' (' . htmlspecialchars($cap['capital_type']) . ')';
                    }
                    $capList[] = $capString;
                }
                $capString = implode(' / ', $capList);
                echo '<p><strong>Capitals:</strong> ' . $capString . '</p>';
            }

            // 2) Coordinates
            if (!empty($country['lat']) && !empty($country['lon'])) {
                echo '<p><strong>Coordinates:</strong> ' . $latFormatted . ', ' . $lonFormatted . '</p>';
            }

            // 3) Languages
            if (!empty($country['languages'])) {
                echo '<p><strong>Languages:</strong> ' . htmlspecialchars($country['languages']) . '</p>';
            }

            // 4) Currency
            if (!empty($country['currency'])) {
                echo '<p><strong>Currency:</strong> ' . htmlspecialchars($country['currency']) . '</p>';
            }

            // 5) Region
            if (!empty($country['region'])) {
                echo '<p><strong>Region:</strong> ' . htmlspecialchars($country['region']) . '</p>';
            }

            // 6) Subregion
            if (!empty($country['subregion'])) {
                echo '<p><strong>Subregion:</strong> ' . htmlspecialchars($country['subregion']) . '</p>';
            }

            // 7) Population
            if (!empty($popFormatted)) {
                echo '<p><strong>Population:</strong> ' . $popFormatted . '</p>';
            }

            // 8) Area (km²)
            if (!empty($country['area_km2'])) {
                echo '<p><strong>Area (km²):</strong> ' . htmlspecialchars($country['area_km2']) . '</p>';
            }

            // 9) Calling Code
            if (!empty($callingCode)) {
                echo '<p><strong>Calling Code:</strong> ' . htmlspecialchars($callingCode) . '</p>';
            }

            // 10) Internet TLD
            if (!empty($country['internet_tld'])) {
                echo '<p><strong>Internet TLD:</strong> ' . htmlspecialchars($country['internet_tld']) . '</p>';
            }
            ?>
        </div>
    </section>
</body>
</html>
