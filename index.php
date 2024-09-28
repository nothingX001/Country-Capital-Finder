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

    <!-- SEO Optimized Content -->
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
            <dl>
                <dt><strong>What is a country capital finder?</strong></dt>
                <dd>A <strong>country capital finder</strong> is an online tool that helps users quickly locate the capital city of any country worldwide.</dd>
                <dt><strong>How accurate is the capital information provided?</strong></dt>
                <dd>The information is sourced from reliable databases and updated regularly to ensure accuracy.</dd>
                <dt><strong>How do I find the capital of a country?</strong></dt>
                <dd>Simply enter the name of the country into the search box, and our tool will return its capital.</dd>
            </dl>
        </section>

        <section id="fun-facts">
            <h2>Fun Facts About Capitals</h2>
            <ul>
                <li><strong>Did you know</strong> that the capital of <strong>Turkey</strong>, <strong>Ankara</strong>, is often mistaken for Istanbul?</li>
                <li><strong>Fun fact</strong>: <strong>Canberra</strong> was chosen as the capital of <strong>Australia</strong> to settle a rivalry between Sydney and Melbourne.</li>
                <li><strong>Trivia</strong>: The capital of <strong>Bolivia</strong>, <strong>Sucre</strong>, shares governmental duties with <strong>La Paz</strong>.</li>
            </ul>
        </section>

        <section id="quiz">
            <h2>Test Your Knowledge: Country Capitals Quiz</h2>
            <p>Think you know your capitals? Take our quiz and see how well you do!</p>
            <button onclick="startQuiz()">Start Quiz</button>
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
    </div>

</body>
</html>
