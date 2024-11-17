<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php'; // Database connection

// Function to normalize country input
function normalize_country_input($input) {
    return ucwords(strtolower(trim($input))); // Capitalizes first letters
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $country_input = $_POST['country'];
    $country = normalize_country_input($country_input);

    // Search for the country, capital, and flag emoji in the database
    $stmt = $conn->prepare("SELECT capital_name, flag_emoji FROM countries WHERE LOWER(country_name) = LOWER(?)");
    $stmt->execute([$country]); // Parameterized query for security
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $capital = $result['capital_name'];
        $flag = $result['flag_emoji'] ?? ''; // Use flag from the database
        $message = "The capital of {$country} is {$capital}. {$flag}";

        // Update the site statistics table
        $conn->beginTransaction();
        try {
            // Update most recent search
            $stmt = $conn->prepare("UPDATE site_statistics SET most_recent_search = ?");
            $stmt->execute([$country]);

            // Increment total searches
            $stmt = $conn->prepare("UPDATE site_statistics SET total_searches = total_searches + 1");
            $stmt->execute();

            // Increment searches_today
            $stmt = $conn->prepare("UPDATE site_statistics SET searches_today = searches_today + 1");
            $stmt->execute();

            // Update unique countries searched
            $stmt = $conn->prepare("SELECT unique_countries_searched FROM site_statistics");
            $stmt->execute();
            $current_data = $stmt->fetch(PDO::FETCH_ASSOC);

            $unique_countries = $current_data['unique_countries_searched'];
            $unique_countries_array = explode(',', $unique_countries);

            if (!in_array($country, $unique_countries_array)) {
                $unique_countries_array[] = $country;
                $updated_unique_countries = implode(',', $unique_countries_array);

                $stmt = $conn->prepare("UPDATE site_statistics SET unique_countries_searched = ?");
                $stmt->execute([$updated_unique_countries]);
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "An error occurred while updating statistics.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Discover capitals of countries around the world with our Country Capital Finder. Search over 195 capitals, explore fun facts, and learn geography with ease!">
    <meta name="keywords" content="country capital finder, find capitals, country capitals, capital search, world capitals, geography trivia, country capitals list">
    <meta name="author" content="Country Capital Finder">
    <title>Country Capital Finder</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your stylesheet -->
</head>
<body>
    <!-- Include Navbar -->
    <?php include 'navbar.php'; ?>

    <div class="main">
        <h1>CAPITAL FINDER</h1>
        <h3>🇺🇸🇪🇺 FIND THE CAPITAL OF YOUR COUNTRY 🇷🇺🇨🇳</h3>
        <form action="index.php" method="post">
            <label>ENTER A COUNTRY: </label>
            <input type="text" name="country" required>
            <input type="submit" value="SUBMIT">
        </form>

        <?php if (isset($message)) { ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>
    </div>
</body>
</html>
