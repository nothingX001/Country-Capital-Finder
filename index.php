<?php
// Enable error reporting (optional, for development purposes)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include 'config.php';

// Function to normalize country input
function normalize_country_input($input) {
    $input = strtolower(trim($input));
    // Include additional delimiters: hyphen (-), parentheses ( and ), apostrophe ('), slash (/)
    return ucwords($input, " \t\r\n\f\v-()/'");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $country_input = $_POST['country'];
    $country = normalize_country_input($country_input);

    // Prepare the search query to check both country_name and alternate_name
    $stmt = $conn->prepare("
        SELECT c.id, c.country_name, c.flag_emoji
        FROM countries c
        LEFT JOIN country_alternate_names can ON c.id = can.country_id
        WHERE LOWER(c.country_name) = LOWER(?) OR LOWER(can.alternate_name) = LOWER(?)
        LIMIT 1
    ");
    $stmt->execute([$country, $country]);
    $country_result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($country_result) {
        $country_id = $country_result['id'];
        $country_name = $country_result['country_name']; // Use the official country name
        $flag = $country_result['flag_emoji'] ?? '';

        // Fetch all capitals associated with the country
        $stmt = $conn->prepare("SELECT capital_name FROM capitals WHERE country_id = ?");
        $stmt->execute([$country_id]);
        $capitals = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if ($capitals) {
            $capital_names = implode(' / ', $capitals);
            $capital_count = count($capitals);
            $capital_word = $capital_count > 1 ? 'capitals' : 'capital';
            $verb = $capital_count > 1 ? 'are' : 'is';
            $message = "The {$capital_word} of {$country_name} {$verb} {$capital_names}. {$flag}";
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
            // Optionally log the error; do not display to users
            // error_log("Error updating site statistics: " . $e->getMessage());
        }
    } else {
        $message = "Sorry, the country you entered is not in our database.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta tags for SEO and responsiveness -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ExploreCapitals</title>
    <meta name="description" content="Discover capitals of countries around the world with our Country Capital Finder. Search over 195 capitals, explore fun facts, and learn geography with ease!">
    <meta name="keywords" content="country capital finder, find capitals, country capitals, capital search, world capitals, geography trivia, country capitals list">
    <meta name="author" content="Country Capital Finder">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Include Navbar -->
    <?php include 'navbar.php'; ?>

    <div class="main">
        <h1>ExploreCapitals.com</h1>
        <h3>Type in any country or territory to search for its capital</h3>
        <form action="index.php" method="post">
            <label><h3>Search:</h3></label>
            <input type="text" name="country" autocomplete="off" required>
            <input type="submit" value="SUBMIT">
        </form>

        <?php if (isset($message)) { ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>
    </div>

    <!-- Autocomplete Script -->
    <script src="autocomplete.js" defer></script>
</body>
</html>
