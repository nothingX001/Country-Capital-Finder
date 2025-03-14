<?php
// country-detail.php

include 'config.php';
include 'the-countries.php'; // Include the list of "the" countries

// Get the country ID from the query string
$country_id = $_GET['id'] ?? null;
if (!$country_id) {
    die("Invalid country ID.");
}

try {
    // 1) Fetch the country row from the countries table.
    $stmt = $conn->prepare('
        SELECT
            "Country Name" AS country_name,
            "Flag Emoji"   AS flag_emoji,
            "Flag"         AS flag_url,
            "Entity Type"  AS entity_type,
            "Sovereign State" AS sovereign_state,
            "Coordinates (Latitude)"::text  AS lat,
            "Coordinates (Longitude)"::text AS lon,
            "Languages"    AS languages,
            "Currency"     AS currency,
            "Region"       AS region,
            "Subregion"    AS subregion,
            "Population"   AS population,
            "Area (km2)"   AS area_km2,
            "Calling Code" AS calling_code,
            "Internet TLD" AS internet_tld,
            "Official Name" AS official_name,
            CASE 
                WHEN LOWER("Country Name") = ANY(?)
                THEN TRUE 
                ELSE FALSE 
            END AS needs_the
        FROM countries
        WHERE id = ?
        LIMIT 1
    ');
    $stmt->execute(['{' . implode(',', $the_countries) . '}', $country_id]);
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

    // Format coordinates: convert to float and add degree symbol and direction.
    $latVal = floatval($country['lat']);
    $lonVal = floatval($country['lon']);
    $latDir = ($latVal >= 0) ? 'N' : 'S';
    $lonDir = ($lonVal >= 0) ? 'E' : 'W';
    $latFormatted = number_format(abs($latVal), 4) . "° " . $latDir;
    $lonFormatted = number_format(abs($lonVal), 4) . "° " . $lonDir;

    // Format population with commas.
    $popFormatted = !empty($country['population']) ? number_format($country['population']) : '';

    // Format area with commas.
    $areaFormatted = !empty($country['area_km2']) ? number_format($country['area_km2']) : '';

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
    <link rel="icon" type="image/jpeg" href="images/explore-capitals-logo.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="Browse our database of countries, territories, and more!">
    <link rel="stylesheet" href="styles.css">
    <style>
      /* Additional styling for the country detail page */
      .country-detail-header {
          margin-bottom: 20px;
          text-align: center;
          font-family: "Courier New", Courier, monospace;
      }
      .country-detail-header h1 {
          font-family: "Courier New", Courier, monospace;
          font-size: 2.5rem;
          margin-bottom: 10px;
          font-weight: 600;
          letter-spacing: 0.5px;
      }
      .country-detail-entity {
          font-family: "Courier New", Courier, monospace;
          font-size: 1.2rem;
          color: #DCCB9C;
          margin-bottom: 10px;
      }
      .sovereign-state {
          font-family: "Courier New", Courier, monospace;
          font-size: 1.2rem;
          color: #DCCB9C;
          margin-bottom: 20px;
          text-align: center;
      }
      .flag-image {
          text-align: center;
          margin-bottom: 30px;
      }
      .flag-image img {
          height: 150px;  /* Fixed height that works well on all devices without overflow */
          width: auto;    /* Width will adjust proportionally */
      }

      .attributes {
          max-width: 500px;
          margin: 0 auto;
          text-align: left;
          font-size: 1.1rem;
          color: #DCCB9C;
          font-family: "Courier New", Courier, monospace;
      }
      .attributes p {
          margin: 10px 0;
          line-height: 1.5;
          font-family: "Courier New", Courier, monospace;
      }
      .attributes strong {
          font-weight: bold;
          color: #DCCB9C;
          font-family: "Courier New", Courier, monospace;
      }
      .constituent-countries {
          font-family: "Courier New", Courier, monospace;
      }
      .header-emoji {
          font-size: 3rem;
          margin-top: 10px;
      }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="page-content country-detail">
        <!-- Header: Country Name and Entity Type -->
        <div class="country-detail-header">
            <h1><?php 
                $displayName = $country['needs_the'] ? 'The ' . $country['country_name'] : $country['country_name'];
                echo htmlspecialchars($displayName); 
            ?></h1>
            <?php if (!empty($country['entity_type'])): ?>
                <?php if ($country['country_name'] === 'United Kingdom'): ?>
                    <div class="constituent-countries">
                        Comprises of 
                        <?php
                        // Fetch the actual IDs of the constituent countries
                        $constituent_stmt = $conn->prepare('
                            SELECT id, "Country Name"
                            FROM countries
                            WHERE "Country Name" IN (\'England\', \'Scotland\', \'Northern Ireland\', \'Wales\')
                            ORDER BY "Country Name" ASC
                        ');
                        $constituent_stmt->execute();
                        $constituents = $constituent_stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        $links = [];
                        foreach ($constituents as $constituent) {
                            $links[] = '<a href="country-detail.php?id=' . urlencode($constituent['id']) . '">' . htmlspecialchars($constituent['Country Name']) . '</a>';
                        }
                        echo implode(', ', $links);
                        ?>
                    </div>
                <?php endif; ?>
                <div class="country-detail-entity">
                    <?php 
                    if (strpos(strtolower($country['entity_type']), 'part of the united kingdom') !== false) {
                        // Fetch the United Kingdom's ID
                        $uk_stmt = $conn->prepare('
                            SELECT id
                            FROM countries
                            WHERE "Country Name" = \'United Kingdom\'
                            LIMIT 1
                        ');
                        $uk_stmt->execute();
                        $uk_id = $uk_stmt->fetchColumn();
                        
                        if ($uk_id) {
                            echo 'Part of the <a href="country-detail.php?id=' . urlencode($uk_id) . '">United Kingdom</a>';
                        } else {
                            echo 'Part of the United Kingdom';
                        }
                    } else {
                        echo htmlspecialchars($country['entity_type']);
                    }
                    ?>
                </div>
            <?php endif; ?>
            <?php
            // If this is a territory, display the sovereign state in one line, centered.
            if (!empty($country['sovereign_state']) && strtolower(trim($country['entity_type'])) === 'territory') {
                // Fetch the sovereign state's ID
                $sovereign_stmt = $conn->prepare('
                    SELECT id
                    FROM countries
                    WHERE "Country Name" = ?
                    LIMIT 1
                ');
                $sovereign_stmt->execute([$country['sovereign_state']]);
                $sovereign_id = $sovereign_stmt->fetchColumn();
                
                if ($sovereign_id) {
                    echo '<div class="sovereign-state"><strong>Sovereign State:</strong> <a href="country-detail.php?id=' . urlencode($sovereign_id) . '">' . htmlspecialchars($country['sovereign_state']) . '</a></div>';
                } else {
                    echo '<div class="sovereign-state"><strong>Sovereign State:</strong> ' . htmlspecialchars($country['sovereign_state']) . '</div>';
                }
            }
            ?>
        </div>

        <!-- Flag Image (below entity type and sovereign state) -->
        <?php if (!empty($country['flag_url'])): ?>
            <div class="flag-image">
                <img 
                    src="<?php echo htmlspecialchars($country['flag_url']); ?>" 
                    alt="Flag of <?php echo htmlspecialchars($country['country_name']); ?>"
                >
            </div>
        <?php endif; ?>

        <!-- Official Name -->
        <?php if (!empty($country['official_name'])): ?>
            <div class="country-detail-entity"><em>officially <?php echo htmlspecialchars($country['official_name']);?></em></div>
        <?php endif; ?>

        <!-- Attributes Section -->
        <div class="attributes">
            <?php
            // Capitals
            if (!empty($capitals)) {
                $capList = [];
                foreach ($capitals as $cap) {
                    $capString = htmlspecialchars($cap['capital_name']);
                    if (!empty($cap['capital_type'])) {
                        $capString .= ' (' . htmlspecialchars($cap['capital_type']) . ')';
                    }
                    $capList[] = $capString;
                }
                $capString = implode(', ', $capList);
                echo '<p><strong>Capital(s):</strong> ' . $capString . '</p>';
            }
            
            // Coordinates
            if (!empty($country['lat']) && !empty($country['lon'])) {
                echo '<p><strong>Coordinates:</strong> ' . $latFormatted . ', ' . $lonFormatted . '</p>';
            }
            
            // Languages
            if (!empty($country['languages'])) {
                echo '<p><strong>Languages:</strong> ' . htmlspecialchars($country['languages']) . '</p>';
            }
            
            // Currency
            if (!empty($country['currency'])) {
                echo '<p><strong>Currency:</strong> ' . htmlspecialchars($country['currency']) . '</p>';
            }
            
            // Region
            if (!empty($country['region'])) {
                echo '<p><strong>Region:</strong> ' . htmlspecialchars($country['region']) . '</p>';
            }
            
            // Subregion
            if (!empty($country['subregion'])) {
                echo '<p><strong>Subregion:</strong> ' . htmlspecialchars($country['subregion']) . '</p>';
            }
            
            // Population
            if (!empty($popFormatted)) {
                echo '<p><strong>Population:</strong> ' . $popFormatted . '</p>';
            }
            
            // Area (km²)
            if (!empty($areaFormatted)) {
                echo '<p><strong>Area (km²):</strong> ' . $areaFormatted . '</p>';
            }
            
            // Calling Code
            if (!empty($callingCode)) {
                echo '<p><strong>Calling Code:</strong> ' . htmlspecialchars($callingCode) . '</p>';
            }
            
            // Internet TLD
            if (!empty($country['internet_tld'])) {
                echo '<p><strong>Internet TLD:</strong> ' . htmlspecialchars($country['internet_tld']) . '</p>';
            }
            ?>
        </div>
    </section>
</body>
</html>
