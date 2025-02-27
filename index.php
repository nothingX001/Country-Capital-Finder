<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include DB connection
include 'config.php';

// Function to normalize country input
function normalize_country_input($input) {
    $input = strtolower(trim($input));
    // Include delimiters: hyphen(-), parentheses(), apostrophe('), slash(/)
    return ucwords($input, " \t\r\n\f\v-()/'");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $country_input = $_POST['country'] ?? '';
    $country = normalize_country_input($country_input);

    // Check both country_name and alternate_name
    $stmt = $conn->prepare("
        SELECT c.id, c.country_name, c.flag_emoji
        FROM countries c
        LEFT JOIN country_alternate_names can ON c.id = can.country_id
        WHERE c.country_name ILIKE ?
           OR can.alternate_name ILIKE ?
        LIMIT 1
    ");
    $stmt->execute([$country, $country]);
    $country_result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($country_result) {
        $country_id   = $country_result['id'];
        $country_name = $country_result['country_name'];
        $flag         = $country_result['flag_emoji'] ?? '';

        // Fetch capitals from the separate capitals table
        $stmt = $conn->prepare("
            SELECT capital_name
            FROM capitals
            WHERE country_id = ?
        ");
        $stmt->execute([$country_id]);
        $capitals = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if ($capitals) {
            $capital_names  = implode(' / ', $capitals);
            $capital_count  = count($capitals);
            $capital_word   = $capital_count > 1 ? 'capitals' : 'capital';
            $verb           = $capital_count > 1 ? 'are' : 'is';
            $message        = "The {$capital_word} of {$country_name} {$verb} {$capital_names}. {$flag}";
        } else {
            $message = "No capitals found for {$country_name}.";
        }

        // Update site statistics
        try {
            $stats_stmt = $conn->prepare("
                INSERT INTO site_statistics (country_name, search_count, last_searched_at)
                VALUES (?, 1, NOW())
                ON CONFLICT (country_name)
                DO UPDATE SET
                    search_count = site_statistics.search_count + 1,
                    last_searched_at = NOW()
            ");
            $stats_stmt->execute([$country_name]);
        } catch (Exception $e) {
            // Log the error if desired
        }
    } else {
        $message = "Sorry, the country or territory you entered is not in our database.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Discover capitals of countries, territories, and more!">
    <meta name="keywords" content="explore capitals, find capitals, countries and capitals">
    <meta name="author" content="ExploreCapitals">
    <title>ExploreCapitals</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="page-content home">
        <h1>Explore Capitals</h1>
        <h3>Enter a country to find its capital:</h3>
        <form action="index.php" method="post">
            <input type="text" name="country" autocomplete="off" placeholder="Search..." required>
            <input type="submit" value="SUBMIT" class="button">
        </form>

        <?php if (isset($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>

    <script src="autocomplete.js" defer></script>
</body>
</html>