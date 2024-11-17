<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php'; // Database connection

// Fetch site statistics
try {
    $data = file_get_contents('http://localhost/fetch-country-data.php?type=statistics');
    if ($data === false) {
        throw new Exception("Failed to fetch statistics.");
    }

    $statistics = json_decode($data, true);

    if (!$statistics || isset($statistics['error'])) {
        throw new Exception("Statistics data unavailable.");
    }
} catch (Exception $e) {
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
    <meta name="description" content="The Country Capital Finder is a unique application where you can find any country’s capital. Take an interactive quiz and test your geography knowledge with ease. Perfect for geography bees and learners.">
    <meta name="keywords" content="find country's capital, country capital quiz, countries and capitals quiz, geography bee prep, interactive capital quiz, world capital learning game, easy geography quiz">
    <meta name="author" content="Country Capital Finder Team">
    <meta property="og:title" content="Country Capital Finder | Interactive Country Capital Quiz">
    <meta property="og:description" content="An interactive platform to find any country’s capital. Test your knowledge with a world capitals quiz and prep for geography bees.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.yoursite.com/about">
    <meta property="og:image" content="https://www.yoursite.com/images/country-capital-quiz.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Country Capital Finder">
    <meta name="twitter:description" content="Learn and memorize world capitals with our fun and easy geography quiz.">
    <meta name="twitter:image" content="https://www.yoursite.com/images/country-capital-quiz.png">
    <title>About | Country Capital Finder</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="about-styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="about-section">
        <h1>ABOUT THE COUNTRY CAPITAL FINDER</h1>
        <p>Welcome to the <strong>Country Capital Finder</strong>—an intuitive application where you can <strong>find any country’s capital</strong> with ease. Created to support learners of all levels, from students to trivia enthusiasts, our platform offers an <strong>interactive capital quiz</strong> and an extensive <strong>capitals of the world quiz</strong> designed to make memorizing capitals both engaging and effective.</p>

        <h2>Site Statistics</h2>
        <ul>
            <li><strong>Most Searched Countries:</strong> <?php echo htmlspecialchars($statistics['most_searched_countries']); ?></li>
            <li><strong>Total Searches:</strong> <?php echo htmlspecialchars($statistics['total_searches']); ?></li>
            <li><strong>Most Recent Search:</strong> <?php echo htmlspecialchars($statistics['most_recent_search']); ?></li>
            <li><strong>Searches Today:</strong> <?php echo htmlspecialchars($statistics['searches_today']); ?></li>
            <li><strong>Unique Countries Searched:</strong> <?php echo htmlspecialchars($statistics['unique_countries_searched']); ?></li>
        </ul>
    </section>
</body>
</html>
