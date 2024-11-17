<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

// Fetch site statistics
$data = file_get_contents('http://localhost/fetch-country-data.php?type=statistics');
$statistics = json_decode($data, true);

if (!$statistics || isset($statistics['error'])) {
    $statistics = [
        'most_searched_countries' => 'Data unavailable',
        'total_searches' => 'Data unavailable',
        'most_recent_search' => 'Data unavailable',
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | Country Capital Finder</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <section class="about-section">
        <h1>About Country Capital Finder</h1>
        <h2>Site Statistics</h2>
        <ul>
            <li><strong>Most Searched Country:</strong> <?php echo htmlspecialchars($statistics['most_searched_countries'] ?? 'N/A'); ?></li>
            <li><strong>Total Searches:</strong> <?php echo htmlspecialchars($statistics['total_searches'] ?? 'N/A'); ?></li>
            <li><strong>Most Recent Search:</strong> <?php echo htmlspecialchars($statistics['most_recent_search'] ?? 'N/A'); ?></li>
        </ul>
    </section>
</body>
</html>
