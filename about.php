<?php
include 'config.php'; // Include database connection

// Fetch site statistics
try {
    $stmt = $conn->query("SELECT * FROM site_statistics LIMIT 1");
    $statistics = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$statistics) {
        $statistics = [
            'most_searched_countries' => 'Data unavailable',
            'total_searches' => 'Data unavailable',
            'most_recent_search' => 'Data unavailable',
            'searches_today' => 'Data unavailable',
            'unique_countries_searched' => 'Data unavailable'
        ];
    }
} catch (Exception $e) {
    error_log("Failed to fetch statistics: " . $e->getMessage());
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
    <meta name="description" content="Learn about the Country Capital Finder and explore fun features like site statistics, quizzes, and geography tools.">
    <meta name="keywords" content="about country capital finder, site statistics, country capitals, geography quiz tools">
    <meta name="author" content="Country Capital Finder">
    <title>About | Country Capital Finder</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="about-styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="about-section">
        <h1>ABOUT THE COUNTRY CAPITAL FINDER</h1>
        <p>Welcome to the Country Capital Finder...</p>

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
