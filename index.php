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

    // Search for the country in the database
    $stmt = $conn->prepare("SELECT capital_name, flag_emoji FROM countries WHERE LOWER(country_name) = LOWER(?)");
    $stmt->execute([$country]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $capital = $result['capital_name'];
        $flag = $result['flag_emoji'] ?? '';
        $message = "The capital of {$country} is {$capital}. {$flag}";

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
            $stats_stmt->execute([$country]);
        } catch (Exception $e) {
            // Log error silently; do not show to users
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
