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

        // Update site statistics
        $updateStatsStmt = $conn->prepare("
            INSERT INTO site_statistics (country_name, search_count, last_searched_at)
            VALUES (?, 1, NOW())
            ON CONFLICT (country_name)
            DO UPDATE SET 
                search_count = site_statistics.search_count + 1,
                last_searched_at = NOW()
        ");
        $updateStatsStmt->execute([$country]);
    } else {
        $message = "Sorry, the country you entered is not in our database.";
    }
}

// Fetch site statistics
$data = file_get_contents('http://localhost/fetch-country-data.php?type=statistics');
$statistics = json_decode($data, true);

if (!$statistics || isset($statistics['error'])) {
    $statistics = [
        'most_searched_countries' => 'Data unavailable',
        'total_searches' => 'Data unavailable',
        'most_recent_search' => 'Data unavailable',
        'searches_today' => 'Data unavailable',
        'unique_countries_searched' => 'Data unavailable'
    ];
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

    <div class="site-statistics">
        <h2>SITE STATISTICS</h2>
        <ul>
            <li><strong>Most Searched Countries:</strong> <?php echo htmlspecialchars($statistics['most_searched_countries']); ?></li>
            <li><strong>Total Searches:</strong> <?php echo htmlspecialchars($statistics['total_searches']); ?></li>
            <li><strong>Most Recent Search:</strong> <?php echo htmlspecialchars($statistics['most_recent_search']); ?></li>
            <li><strong>Searches Today:</strong> <?php echo htmlspecialchars($statistics['searches_today']); ?></li>
            <li><strong>Unique Countries Searched:</strong> <?php echo htmlspecialchars($statistics['unique_countries_searched']); ?></li>
        </ul>
    </div>
</body>
</html>
