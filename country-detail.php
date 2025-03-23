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
            "ISO Alpha-2"  AS iso_code,
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

// Prepare Windows flag URL if ISO code is available
$windowsFlagUrl = !empty($country['iso_code']) ? "https://flagcdn.com/32x24/" . strtolower($country['iso_code']) . ".png" : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($country['country_name'] ?? 'Country Detail'); ?> - ExploreCapitals</title>
    <link rel="icon" type="image/jpeg" href="images/explore-capitals-logo.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="Browse our database of countries, territories, and more!">
    <meta property="og:title" content="<?php echo htmlspecialchars($country['country_name'] ?? 'Country Detail'); ?> - Country Detail | ExploreCapitals">
    <meta property="og:description" content="Learn about <?php echo htmlspecialchars($country['country_name'] ?? 'Country Detail'); ?> and its capital<?php echo (count($capitals) > 1) ? 's' : ''; ?> with ExploreCapitals.">
    <meta property="og:type" content="website">
    <link rel="stylesheet" href="styles.css">
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
                            $links[] = '<a href="country-detail.php?id=' . urlencode($constituent['id']) . '" class="sovereign-link">' . htmlspecialchars($constituent['Country Name']) . '</a>';
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
                            echo 'Part of the <a href="country-detail.php?id=' . urlencode($uk_id) . '" class="sovereign-link">United Kingdom</a>';
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

        <!-- AI Generated Section -->
        <div class="ai-description">
            <h3>About <?php echo htmlspecialchars($displayName); ?></h3>
            <div class="ai-loading" style="display: none;">
                <div class="ai-loading-spinner"></div>
                <span>Researching information...</span>
            </div>
            <div id="aiDescription" class="ai-description-content" style="line-height: 1.8; font-size: 1.1em;"></div>
        </div>
    </section>

    <!-- JavaScript for AI description typing effect -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Store country data for API call
            const countryData = {
                name: "<?php echo addslashes($displayName); ?>",
                capitals: <?php 
                    $caps = [];
                    foreach ($capitals as $cap) {
                        $caps[] = $cap['capital_name'];
                    }
                    echo json_encode($caps); 
                ?>,
                region: "<?php echo addslashes($country['region'] ?? ''); ?>",
                subregion: "<?php echo addslashes($country['subregion'] ?? ''); ?>",
                population: "<?php echo addslashes($popFormatted ?? ''); ?>",
                languages: "<?php echo addslashes($country['languages'] ?? ''); ?>",
                entityType: "<?php echo addslashes($country['entity_type'] ?? ''); ?>",
                area: "<?php echo addslashes($areaFormatted ?? ''); ?>",
                currency: "<?php echo addslashes($country['currency'] ?? ''); ?>",
                isTerritory: <?php echo (strtolower(trim($country['entity_type'] ?? '')) === 'territory') ? 'true' : 'false'; ?>,
                sovereignState: "<?php echo addslashes($country['sovereign_state'] ?? ''); ?>"
            };

            // Add educational loading messages that cycle during generation
            const loadingMessages = [
                "Searching Wikipedia for information...",
                "Gathering historical facts...",
                "Exploring cultural traditions...",
                "Researching geographic features...",
                "Compiling trusted sources...",
                "Finding interesting landmarks...",
                "Discovering notable people...",
                "Formatting educational content..."
            ];
            
            let messageIndex = 0;
            let loadingInterval;
            
            function cycleLoadingMessages() {
                const loadingSpan = document.querySelector('.ai-loading span');
                loadingSpan.textContent = loadingMessages[messageIndex];
                messageIndex = (messageIndex + 1) % loadingMessages.length;
            }

            // Function to fetch AI description
            function getAIDescription() {
                // Show the loading animation
                document.querySelector('.ai-loading').style.display = 'flex';
                
                // Start cycling through loading messages
                loadingInterval = setInterval(cycleLoadingMessages, 2000);
                cycleLoadingMessages(); // Set first message immediately
                
                // Prepare API request to your backend
                fetch('generate-description.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(countryData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.description) {
                        // Clear the loading interval
                        clearInterval(loadingInterval);
                        
                        // Hide loading animation
                        document.querySelector('.ai-loading').style.display = 'none';
                        
                        // Store debug info for troubleshooting
                        window.descriptionDebug = data.debug;
                        
                        // Type out the description with a typing effect
                        typeDescription(data.description, data.source);
                        
                        // Add debug info for admins (only visible in console)
                        console.log('Description source:', data.source);
                        console.log('Debug info:', data.debug);
                    } else {
                        showError("Could not generate description: No content returned");
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError("Network or server error while generating description");
                });
            }

            // Function to show error message if AI generation fails
            function showError(errorMessage) {
                clearInterval(loadingInterval);
                document.querySelector('.ai-loading').style.display = 'none';
                document.getElementById('aiDescription').innerHTML = 
                    "Sorry, we couldn't generate a description at this time. Please try again later." + 
                    "<div style='font-size: 0.8em; margin-top: 10px; color: #999;'>Error: " + errorMessage + "</div>";
            }

            // Function to simulate typing effect
            function typeDescription(text, source) {
                const descriptionEl = document.getElementById('aiDescription');
                descriptionEl.innerHTML = ''; // Clear any existing content
                let i = 0;
                const typingSpeed = 8; // Faster typing speed (was 10ms)
                
                // Create cursor element
                const cursor = document.createElement('span');
                cursor.className = 'ai-typing-cursor';
                cursor.innerHTML = '|';
                cursor.style.animation = 'blink 1s step-start infinite';
                descriptionEl.appendChild(cursor);
                
                const typing = setInterval(() => {
                    if (i < text.length) {
                        // Check if we need to insert a paragraph break
                        if (text.substring(i, i+2) === "\n\n") {
                            descriptionEl.insertBefore(document.createElement('br'), cursor);
                            descriptionEl.insertBefore(document.createElement('br'), cursor);
                            i += 2;
                        } else if (text[i] === "\n") {
                            descriptionEl.insertBefore(document.createElement('br'), cursor);
                            i++;
                        } else {
                            // Insert the next character
                            descriptionEl.insertBefore(document.createTextNode(text[i]), cursor);
                            i++;
                        }
                        
                        // Scroll to keep up with the text if necessary
                        if (descriptionEl.offsetHeight > window.innerHeight) {
                            descriptionEl.scrollIntoView({ behavior: 'smooth', block: 'end' });
                        }
                    } else {
                        // Remove the cursor when finished
                        cursor.remove();
                        clearInterval(typing);
                        
                        // Debug info is still kept in console for troubleshooting
                        if (window.descriptionDebug) {
                            console.log('Description debug info:', window.descriptionDebug);
                        }
                    }
                }, typingSpeed);
            }

            // Start the process when page is loaded
            setTimeout(getAIDescription, 1000); // Small delay for better UX
        });
    </script>
</body>
</html>
