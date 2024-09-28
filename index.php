<?php
// Enable error reporting to debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php'; // Database connection

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $country = $_POST['country'];

    // Search for the country in the database
    $stmt = $conn->prepare("SELECT id, capital_name FROM countries WHERE country_name = ?");
    $stmt->bind_param("s", $country);
    $stmt->execute();
    $stmt->bind_result($country_id, $capital);
    $stmt->fetch();
    $stmt->close();

    if ($capital) {
        $message = "The capital of {$country} is {$capital}.";

        // Update search tracking
        $search_stmt = $conn->prepare("SELECT search_count FROM search_tracking WHERE country_id = ?");
        $search_stmt->bind_param("i", $country_id);
        $search_stmt->execute();
        $search_stmt->bind_result($search_count);
        $search_stmt->fetch();
        $search_stmt->close();

        if ($search_count) {
            // Update the search count
            $update_stmt = $conn->prepare("UPDATE search_tracking SET search_count = search_count + 1 WHERE country_id = ?");
            $update_stmt->bind_param("i", $country_id);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            // Insert the search count if it doesn't exist
            $insert_stmt = $conn->prepare("INSERT INTO search_tracking (country_id, search_count) VALUES (?, 1)");
            $insert_stmt->bind_param("i", $country_id);
            $insert_stmt->execute();
            $insert_stmt->close();
        }

        // Update most recent search
        $recent_stmt = $conn->prepare("INSERT INTO recent_searches (country_id, search_time) VALUES (?, NOW())");
        $recent_stmt->bind_param("i", $country_id);
        $recent_stmt->execute();
        $recent_stmt->close();
    } else {
        $message = "Sorry, the country you entered is not in our list.";
    }
}

// Fetch the most searched country
$most_searched_stmt = $conn->prepare("
    SELECT c.country_name, MAX(st.search_count) as max_searches
    FROM search_tracking st
    JOIN countries c ON st.country_id = c.id
    GROUP BY c.country_name
    ORDER BY max_searches DESC
    LIMIT 1
");
$most_searched_stmt->execute();
$most_searched_stmt->bind_result($most_searched_country, $most_searches);
$most_searched_stmt->fetch();
$most_searched_stmt->close();

// Fetch the most recent search
$recent_search_stmt = $conn->prepare("
    SELECT c.country_name, r.search_time
    FROM recent_searches r
    JOIN countries c ON r.country_id = c.id
    ORDER BY r.search_time DESC
    LIMIT 1
");
$recent_search_stmt->execute();
$recent_search_stmt->bind_result($most_recent_search, $search_time);
$recent_search_stmt->fetch();
$recent_search_stmt->close();

// Get total number of searches
$total_searches_stmt = $conn->prepare("SELECT SUM(search_count) FROM search_tracking");
$total_searches_stmt->execute();
$total_searches_stmt->bind_result($total_searches);
$total_searches_stmt->fetch();
$total_searches_stmt->close();

// Get searches today
$searches_today_stmt = $conn->prepare("
    SELECT COUNT(*) FROM recent_searches 
    WHERE DATE(search_time) = CURDATE()
");
$searches_today_stmt->execute();
$searches_today_stmt->bind_result($searches_today);
$searches_today_stmt->fetch();
$searches_today_stmt->close();

// Get number of unique countries searched
$unique_countries_stmt = $conn->prepare("SELECT COUNT(DISTINCT country_id) FROM search_tracking");
$unique_countries_stmt->execute();
$unique_countries_stmt->bind_result($unique_countries_searched);
$unique_countries_stmt->fetch();
$unique_countries_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Discover capitals of countries around the world with our Country Capital Finder. Search over 195 capitals, explore fun facts, and learn geography with ease!">
    <meta name="keywords" content="country capital finder, find capitals, country capitals, capital search, world capitals, geography trivia, country capitals list">
    <meta name="author" content="Cher">
    <title>Country Capital Finder</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="main">
        <h1>🇺🇸🇪🇺 Find a Country's Capital 🇷🇺🇨🇳</h1>

        <form action="index.php" method="post">
            <label>Enter a country: </label>
            <input type="text" name="country" required>
            <input type="submit" value="Submit">
        </form>

        <?php if (isset($message)) { ?>
            <p class="message"><?php echo $message; ?></p>
        <?php } ?>
    </div>

    <div class="seo-content">
        <section id="why-use">
            <h2>Why Use the Country Capital Finder?</h2>
            <ul>
                <li>Instantly <strong>find capitals</strong> of any country.</li>
                <li>Access <strong>up-to-date information</strong> on over 195 countries.</li>
                <li>Get fun facts about famous and lesser-known capitals.</li>
                <li>Explore real-time data with our <strong>site statistics</strong>.</li>
            </ul>
        </section>

        <section id="faq">
            <h2>Frequently Asked Questions (FAQ)</h2>
                <ul>
                    <li>
                        <p><strong>What is a country capital finder?</strong></p>
                        <p>A <strong>country capital finder</strong> is an online tool that helps users quickly locate the capital city of any country worldwide.</p>
                    </li>
                    <li>
                        <p><strong>How accurate is the capital information provided?</strong></p>
                        <p>The information is sourced from reliable databases and updated regularly to ensure accuracy.</p>
                    </li>
                    <li>
                        <p><strong>How do I find the capital of a country?</strong></p>
                        <p>Simply enter the name of the country into the search box, and our tool will return its capital.</p>
                    </li>
                </ul>
        </section>

        <section id="fun-facts">
            <h2>Fun Facts About Capitals</h2>
            <ul>
                <li><strong>Did you know</strong> that the capital of <strong>Turkey</strong>, <strong>Ankara</strong>, is often mistaken for Istanbul?</li>
                <li><strong>Fun fact</strong>: <strong>Canberra</strong> was chosen as the capital of <strong>Australia</strong> to settle a rivalry between Sydney and Melbourne.</li>
                <li><strong>Trivia</strong>: The capital of <strong>Bolivia</strong>, <strong>Sucre</strong>, shares governmental duties with <strong>La Paz</strong>.</li>
            </ul>
        </section>

        <section id="travel-tips">
            <h2>Travel Information</h2>
            <p>Planning a trip to a capital city? Here are our top travel tips for visiting the world’s capitals:</p>
            <ul>
                <li><strong>Best time to visit Paris</strong>: Spring and fall offer mild weather and fewer crowds.</li>
                <li><strong>Top attractions in Tokyo</strong>: Visit the ancient temples of Asakusa and the bustling Shibuya Crossing.</li>
                <li><strong>Must-see landmarks in Washington, D.C.</strong>: The White House, Lincoln Memorial, and Smithsonian Museums.</li>
            </ul>
        </section>

        <section id="quiz">
            <h2>Test Your Knowledge: Country Capitals Quiz</h2>
            <p>Think you know your capitals? Take our quiz and see how well you do!</p>
            <button onclick="startQuiz()">Start Quiz</button>
        </section>
    </div>

        <!-- Site Statistics Section -->
        <div class="most-searched-section">
        <h4>📊 Site Statistics</h4>
        <div id="site-stats">
            <!-- Most Searched Country -->
            <p>🔝 Most Searched Country: <?php echo $most_searched_country ?? "No data yet"; ?> with <?php echo $most_searches ?? 0; ?> searches.</p>

            <!-- Most Recent Search -->
            <p>🕒 Most Recent Search: <?php echo $most_recent_search ?? "No searches yet"; ?> at <?php echo $search_time ?? "N/A"; ?></p>

            <!-- Total Number of Searches -->
            <p>🔢 Total Searches: <?php echo $total_searches ?? 0; ?></p>

            <!-- Searches Today -->
            <p>📅 Searches Today: <?php echo $searches_today ?? 0; ?></p>

            <!-- Number of Unique Countries Searched -->
            <p>🌍 Unique Countries Searched: <?php echo $unique_countries_searched ?? 0; ?></p>
        </div>
    </div>

</body>
</html>
