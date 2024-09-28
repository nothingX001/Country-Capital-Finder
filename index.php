<?php
include 'config.php';

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
    } else {
        $message = "Sorry, the country you entered is not in our list.";
    }
}

// Get the most searched country
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Country Capital Finder</title>
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

    <div class="most-searched-section">
        <h4>What's the most searched country on this app?</h4>
        <div id="most-searched">
            <?php if ($most_searched_country) { ?>
                <p>The most searched country on this app is <?php echo $most_searched_country; ?> with <?php echo $most_searches; ?> searches.</p>
            <?php } else { ?>
                <p>No searches have been recorded yet.</p>
            <?php } ?>
        </div>
    </div>

    <!-- SEO Optimized Content Starts Here -->

    <!-- FAQ Section -->
    <section class="faq">
        <h2>Frequently Asked Questions</h2>
        <div class="faq-item">
            <h3>What is a country capital finder?</h3>
            <p>A country capital finder is a tool that helps users quickly locate the capital city of any country around the world.</p>
        </div>
        <div class="faq-item">
            <h3>How accurate is the information?</h3>
            <p>Our country capital information is regularly updated and sourced from trusted databases to ensure accuracy.</p>
        </div>
        <div class="faq-item">
            <h3>How do I use this tool?</h3>
            <p>Simply type the name of any country in the search bar above and click "Submit" to find its capital.</p>
        </div>
    </section>

    <!-- List of Countries and Capitals -->
    <section class="country-capitals">
        <h2>Complete List of Countries and Capitals</h2>
        <p>Below is a list of all countries and their capital cities:</p>
        <ul>
            <li>United States - Washington, D.C.</li>
            <li>France - Paris</li>
            <li>Germany - Berlin</li>
            <li>Japan - Tokyo</li>
            <li>Australia - Canberra</li>
            <!-- Add more countries as needed -->
        </ul>
    </section>

    <!-- Travel Section -->
    <section class="travel-tips">
        <h2>Top Travel Tips for Visiting Capital Cities</h2>
        <p>Traveling to a capital city soon? Here are some tips:</p>
        <ul>
            <li>Plan ahead and make sure to visit famous landmarks.</li>
            <li>Always carry local currency when visiting markets or small shops.</li>
            <li>Try local foods – every capital city has its own unique cuisine!</li>
            <!-- Add more tips as necessary -->
        </ul>
    </section>

    <!-- Educational Content -->
    <section class="learning-capitals">
        <h2>Why Learn About World Capitals?</h2>
        <p>Knowing the capitals of countries can improve your geographical knowledge, help in travel, and even prepare you for trivia games. Use our tool to test and expand your knowledge of world capitals!</p>
    </section>

    <!-- SEO Optimized Content Ends Here -->

</body>
</html>
