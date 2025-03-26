<?php
// Basic error checking
error_log("Starting index.php execution");

// Force error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if required files exist
$required_files = ['config.php', 'the-countries.php', 'navbar.php'];
foreach ($required_files as $file) {
    if (!file_exists($file)) {
        error_log("Required file missing: $file");
        die("Required file missing: $file");
    }
}

// Include required files
include 'config.php';
include 'the-countries.php';

// Test database connection
if (isset($conn) && !isset($conn->error)) {
    try {
        $test_stmt = $conn->query("SELECT COUNT(*) FROM countries");
        $count = $test_stmt->fetchColumn();
        error_log("Database test successful. Found $count countries.");
    } catch (PDOException $e) {
        error_log("Database test failed: " . $e->getMessage());
        echo "Database Error: " . htmlspecialchars($e->getMessage());
    }
} else {
    $error = isset($conn->error_message) ? $conn->error_message : "Unknown error";
    error_log("Database connection not available: " . $error);
    echo "Database Connection Error: " . htmlspecialchars($error);
}

// Security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' https:; connect-src 'self' https:;");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// Secure session settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Rate limiting
function checkRateLimit() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $time = time();
    $window = 60; // 1 minute window
    $max_requests = 30; // Maximum requests per window
    
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [
            'count' => 0,
            'window_start' => $time
        ];
    }
    
    // Reset if window has passed
    if ($time - $_SESSION['rate_limit']['window_start'] > $window) {
        $_SESSION['rate_limit'] = [
            'count' => 0,
            'window_start' => $time
        ];
    }
    
    // Check if limit exceeded
    if ($_SESSION['rate_limit']['count'] >= $max_requests) {
        return false;
    }
    
    $_SESSION['rate_limit']['count']++;
    return true;
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// index.php

// Optional helper to normalize user input
function normalize_country_input($input) {
    global $the_countries;
    // Sanitize input
    $input = filter_var(trim($input), FILTER_SANITIZE_STRING);
    if (empty($input)) {
        return '';
    }
    
    // Remove any potentially dangerous characters
    $input = preg_replace('/[^a-zA-Z\s\-\(\)\']/', '', $input);
    
    $input = strtolower($input);

    // Remove "the" prefix if present for comparison
    $input_without_the = preg_replace('/^the\s+/i', '', $input);

    // Check if this is a "the" country
    if (in_array($input_without_the, $the_countries)) {
        return ucwords($input_without_the, " \t\r\n\f\v-()/'");
    }

    // For non-"the" countries, just normalize the input
    return ucwords($input, " \t\r\n\f\v-()/'");
}

// Helper function to format country name in sentence with proper "the" prefix
function format_country_name_in_sentence($country_name, $the_countries) {
    $country_lower = strtolower($country_name);
    $needs_the = in_array($country_lower, $the_countries);
    return $needs_the ? "the " . $country_name : $country_name;
}

// Handle the search form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check rate limit
    if (!checkRateLimit()) {
        $message = "Too many requests. Please wait a moment before trying again.";
    } else {
        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $message = "Invalid request. Please try again.";
        } else {
            $country_input = $_POST['country'] ?? '';
            if (empty($country_input)) {
                $message = "Please enter a country name.";
            } else {
                $country = normalize_country_input($country_input);
                if (empty($country)) {
                    $message = "Invalid input. Please enter a valid country name.";
                } else {
                    // Check if database connection is available
                    if (isset($conn->error) && $conn->error) {
                        $message = "Database connection error. Please try again later.";
                    } else {
                        try {
                            // 1) Look up the country by "Country Name"
                            $stmt = $conn->prepare('
                                SELECT
                                    id,
                                    "Country Name" AS country_name,
                                    "Flag Emoji"   AS flag_emoji,
                                    "ISO Alpha-2"  AS iso_code,
                                    "Official Name" AS official_name
                                FROM countries
                                WHERE "Country Name" ILIKE ?
                                LIMIT 1
                            ');
                            $stmt->execute([$country]);
                            $country_result = $stmt->fetch(PDO::FETCH_ASSOC);

                            if ($country_result) {
                                $country_id   = $country_result['id'];
                                $country_name = htmlspecialchars($country_result['country_name']);
                                $flag         = htmlspecialchars($country_result['flag_emoji'] ?? '');
                                $iso_code     = htmlspecialchars($country_result['iso_code'] ?? '');
                                $official_name = htmlspecialchars($country_result['official_name'] ?? '');

                                // 2) Fetch matching capitals from the capitals table
                                $cap_stmt = $conn->prepare('
                                    SELECT capital_name
                                    FROM capitals
                                    WHERE country_id = ?
                                ');
                                $cap_stmt->execute([$country_id]);
                                $capitals = $cap_stmt->fetchAll(PDO::FETCH_COLUMN);

                                // 3) Build a message about the capital(s) with capital names in bold.
                                if ($capitals) {
                                    // Bold each capital using <strong> tags.
                                    $boldCapitals = array_map(function($cap) use ($country_id) {
                                        return '<a href="country-detail.php?id=' . urlencode($country_id) . '"><strong>' . htmlspecialchars($cap) . '</strong></a>';
                                    }, $capitals);

                                    // Format capitals based on count
                                    if (count($capitals) === 1) {
                                        $capital_names = $boldCapitals[0];
                                    } else if (count($capitals) === 2) {
                                        $capital_names = $boldCapitals[0] . ' or ' . $boldCapitals[1];
                                    } else {
                                        $lastCapital = array_pop($boldCapitals);
                                        $capital_names = implode(', ', $boldCapitals) . ' and ' . $lastCapital;
                                    }

                                    $capital_count = count($capitals);
                                    $capital_word  = ($capital_count > 1) ? 'capitals' : 'capital';
                                    $verb          = ($capital_count > 1) ? 'are' : 'is';
                                    // Format country name with "the" if needed
                                    $formatted_country_name = format_country_name_in_sentence($country_name, $the_countries);
                                    
                                    // Prepare the flag URL for Windows users
                                    $windows_flag_url = !empty($iso_code) ? "https://flagcdn.com/32x24/" . strtolower($iso_code) . ".png" : "";
                                    
                                    // Build the message with a clickable country name and flag
                                    $message = "The {$capital_word} of <a href='country-detail.php?id=" . urlencode($country_id) . "'>{$formatted_country_name}</a> {$verb} {$capital_names}. <span class=\"flag-emoji\">{$flag}</span>";
                                } else {
                                    // Format country name with "the" if needed
                                    $formatted_country_name = format_country_name_in_sentence($country_name, $the_countries);
                                    $message = "No capitals found for <a href='country-detail.php?id=" . urlencode($country_id) . "'>{$formatted_country_name}</a>.";
                                }

                                // 4) (Optional) Update site statistics if desired
                                try {
                                    $stats_stmt = $conn->prepare('
                                        INSERT INTO site_statistics (country_name, search_count, last_searched_at)
                                        VALUES (?, 1, NOW())
                                        ON CONFLICT (country_name)
                                        DO UPDATE SET
                                            search_count     = site_statistics.search_count + 1,
                                            last_searched_at = NOW()
                                    ');
                                    $stats_stmt->execute([$country_name]);
                                } catch (Exception $e) {
                                    // Optionally log or ignore the error.
                                }
                            } else {
                                $message = "Sorry, the country you entered is not in our database.";
                            }
                        } catch (PDOException $e) {
                            error_log("Database query error: " . $e->getMessage());
                            $message = "An error occurred while searching. Please try again later.";
                        }
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" style="overflow-x: hidden;">
<head>
    <meta charset="UTF-8">
    <title>ExploreCapitals | The World Capital Finder</title>
    <link rel="icon" type="image/jpeg" href="images/explore-capitals-logo.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="description" content="Find the capital city of any country or territory in the world. Search by country name to discover its capital(s).">
    <meta name="keywords" content="capital cities, world capitals, country capitals, geography quiz, world geography">
    <meta name="author" content="ExploreCapitals">
    <meta property="og:title" content="ExploreCapitals - Find Any Country's Capital City">
    <meta property="og:description" content="Find the capital city of any country or territory in the world. Search by country name to discover its capital(s).">
    <meta property="og:type" content="website">
    <link rel="stylesheet" href="styles.css">
    <style>
        html, body {
            overflow-x: hidden !important;
        }
        
        #countryProfileCard {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(220, 203, 156, 0.3);
        }
        
        /* Additional mobile-specific styles */
        @media (max-width: 480px) {
            .search-bar-container {
                width: 90% !important;
                max-width: 500px !important;
                margin: 0 auto 25px;
            }
            
            input[type="text"] {
                width: 100% !important;
                padding: 16px 15px !important;
                font-size: 16px !important;
                box-sizing: border-box !important;
            }
            
            #searchForm {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 100%;
                margin-bottom: 10px;
                padding: 0 !important;
            }
            
            /* Remove the padding adjustments for page-content */
        }
    </style>
</head>
<body style="background: transparent;">
    <?php include 'navbar.php'; ?>

    <div class="page-content home">
        <h1 style="white-space: nowrap; font-size: clamp(32px, 5vw, 38px); letter-spacing: -0.5px;">ExploreCapitals</h1>
        <h3 class="search-heading" style="color: #ECECEC;">Enter a country to find its capital:</h3>
        <form action="index.php" method="post" id="searchForm" style="width: 90%; max-width: 500px;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="search-bar-container" style="width: 90%; max-width: 500px;">
                <input type="text" name="country" placeholder="Search..." novalidate style="width: 100%; box-sizing: border-box;">
            </div>
            <input type="submit" value="SUBMIT" class="button">
        </form>

        <?php if (isset($message)): ?>
            <!-- Output message as raw HTML so the <strong> tags take effect -->
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
    </div>
    
    <?php if (isset($country_id)): ?>
        <!-- Country Profile Card - now separate from page-content -->
        <div id="countryProfileCard">
            <?php
            // Fetch detailed country data similar to country-detail.php
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
            $country_detail = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Fetch capitals
            $stmt_cap = $conn->prepare('
                SELECT capital_name, capital_type
                FROM capitals
                WHERE country_id = ?
            ');
            $stmt_cap->execute([$country_id]);
            $capitals = $stmt_cap->fetchAll(PDO::FETCH_ASSOC);
            
            // Format display name with "the" if needed
            $displayName = $country_detail['needs_the'] ? 'The ' . $country_detail['country_name'] : $country_detail['country_name'];
            ?>
            
            <div class="country-detail-header">
                <h1><?php echo htmlspecialchars($displayName); ?></h1>
                
                <!-- Entity Type -->
                <?php if (!empty($country_detail['entity_type'])): ?>
                    <div class="country-detail-entity">
                        <?php echo htmlspecialchars($country_detail['entity_type']); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Sovereign State (for territories) -->
                <?php if (!empty($country_detail['sovereign_state']) && strtolower(trim($country_detail['entity_type'])) === 'territory'): ?>
                    <div class="sovereign-state">
                        <strong>Sovereign State:</strong>
                        <a href="country-detail.php?id=<?php
                            // Fetch sovereign state ID
                            $sovereign_stmt = $conn->prepare('SELECT id FROM countries WHERE "Country Name" = ? LIMIT 1');
                            $sovereign_stmt->execute([$country_detail['sovereign_state']]);
                            echo urlencode($sovereign_stmt->fetchColumn());
                        ?>">
                            <?php echo htmlspecialchars($country_detail['sovereign_state']); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Flag Image -->
            <?php if (!empty($country_detail['flag_url'])): ?>
                <div class="flag-image">
                    <img 
                        src="<?php echo htmlspecialchars($country_detail['flag_url']); ?>"
                        alt="Flag of <?php echo htmlspecialchars($country_detail['country_name']); ?>"
                    >
                </div>
            <?php endif; ?>

            <!-- Official Name -->
            <?php if (!empty($country_detail['official_name'])): ?>
                <div class="country-detail-entity">
                    <em>officially <?php echo htmlspecialchars($country_detail['official_name']); ?></em>
                </div>
            <?php endif; ?>
            
            <!-- Country Attributes -->
            <div class="attributes">
                <!-- Capital(s) -->
                <?php if (!empty($capitals)): ?>
                    <p>
                        <strong><?php echo count($capitals) > 1 ? 'Capitals:' : 'Capital:'; ?></strong> 
                        <?php 
                        $capital_names = array_map(function($cap) {
                            return htmlspecialchars($cap['capital_name']) . 
                                  (!empty($cap['capital_type']) ? ' (' . htmlspecialchars($cap['capital_type']) . ')' : '');
                        }, $capitals);
                        echo implode(', ', $capital_names);
                        ?>
                    </p>
                <?php endif; ?>
                
                <!-- Common Attributes -->
                
                <?php if (!empty($country_detail['languages'])): ?>
                    <p><strong>Languages:</strong> <?php echo htmlspecialchars($country_detail['languages']); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($country_detail['currency'])): ?>
                    <p><strong>Currency:</strong> <?php echo htmlspecialchars($country_detail['currency']); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($country_detail['population'])): ?>
                    <p><strong>Population:</strong> <?php echo number_format($country_detail['population']); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($country_detail['calling_code'])): ?>
                    <p><strong>Calling Code:</strong> <?php 
                        $cc = trim($country_detail['calling_code']);
                        echo (strpos($cc, '+') === 0) ? $cc : '+' . $cc; 
                    ?></p>
                <?php endif; ?>
                
                <?php if (!empty($country_detail['internet_tld'])): ?>
                    <p><strong>Internet TLD:</strong> <?php echo htmlspecialchars($country_detail['internet_tld']); ?></p>
                <?php endif; ?>
             
            </div>
            
            <!-- View Full Profile Link -->
            <p style="margin-top: 20px;">
                <a href="country-detail.php?id=<?php echo urlencode($country_id); ?>" class="button" style="text-decoration: none;">
                    VIEW FULL PROFILE
                </a>
            </p>
        </div>
    <?php endif; ?>

    <!-- Autocomplete script -->
    <script src="autocomplete.js" defer></script>
    <script>
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        const input = this.querySelector('input[name="country"]');
        if (input && !input.value.trim()) {
            e.preventDefault();
            input.setCustomValidity('Please enter a country name');
            input.reportValidity();
        } else if (input) {
            input.setCustomValidity('');
            input.blur();
        }
    });

    // Clear validation message when user starts typing
    document.querySelector('input[name="country"]').addEventListener('input', function() {
        this.setCustomValidity('');
    });

    // Ensure country profile card appears after search
    document.addEventListener('DOMContentLoaded', function() {
        const countryProfileCard = document.getElementById('countryProfileCard');
        const message = document.querySelector('.message');
        
        if (countryProfileCard) {
            countryProfileCard.style.display = 'block';
            countryProfileCard.style.visibility = 'visible';
            countryProfileCard.style.opacity = '1';
            
            // Don't scroll to card
        } else if (message) {
            message.style.display = 'block';
            message.style.visibility = 'visible';
            message.style.opacity = '1';
        }
    });
    </script>
</body>
</html>
