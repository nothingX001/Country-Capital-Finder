<?php
// index.php

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

// Optional helper to normalize user input
function normalize_country_input($input) {
    $input = strtolower(trim($input));
    // For example, handle hyphens, parentheses, apostrophes, etc.
    return ucwords($input, " \t\r\n\f\v-()/'");
}

// Handle the search form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $country_input = $_POST['country'] ?? '';
    $country = normalize_country_input($country_input);

    // 1) Look up the country by "Country Name"
    $stmt = $conn->prepare('
        SELECT
            id,
            "Country Name" AS country_name,
            "Flag Emoji"   AS flag_emoji,
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
            $capital_names = implode(', ', array_map(function($cap) {
                return '<strong>' . htmlspecialchars($cap) . '</strong>';
            }, $capitals));
            $capital_count = count($capitals);
            $capital_word  = ($capital_count > 1) ? 'capitals' : 'capital';
            $verb          = ($capital_count > 1) ? 'are' : 'is';
            // Build the message.
            $message = "The {$capital_word} of {$country_name} {$verb} {$capital_names}. <span class=\"flag-emoji\">{$flag}</span>";
        } else {
            $message = "No capitals found for {$country_name}.";
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ExploreCapitals - Find Any Country's Capital City</title>
    <link rel="icon" type="image/jpeg" href="images/explore-capitals-logo.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Find the capital city of any country or territory in the world. Search by country name to discover its capital(s).">
    <meta name="keywords" content="capital cities, world capitals, country capitals, geography quiz, world geography">
    <meta name="author" content="ExploreCapitals">
    <meta property="og:title" content="ExploreCapitals - Find Any Country's Capital City">
    <meta property="og:description" content="Find the capital city of any country or territory in the world. Search by country name to discover its capital(s).">
    <meta property="og:type" content="website">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="page-content home">
        <h1>ExploreCapitals</h1>
        <h3>Enter a country to find its capital:</h3>
        <form action="index.php" method="post">
            <input type="text" name="country" autocomplete="on" placeholder="Search..." required>
            <input type="submit" value="SUBMIT" class="button">
        </form>

        <?php if (isset($message)): ?>
            <!-- Output message as raw HTML so the <strong> tags take effect -->
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
    </div>

    <!-- Autocomplete script -->
    <script src="autocomplete.js" defer></script>
</body>
</html>
