<?php
// about.php
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
        'searches_today' => 'Data unavailable',
        'unique_countries_searched' => 'Data unavailable'
    ];
}

// Prepare Windows flag URLs if ISO codes are available
$most_searched_flag_url = !empty($statistics['most_searched_iso']) ? "https://flagcdn.com/32x24/" . strtolower($statistics['most_searched_iso']) . ".png" : "";
$most_recent_flag_url = !empty($statistics['most_recent_iso']) ? "https://flagcdn.com/32x24/" . strtolower($statistics['most_recent_iso']) . ".png" : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>About | ExploreCapitals</title>
    <link rel="icon" type="image/jpeg" href="images/explore-capitals-logo.jpg">
    <meta name="description" content="ExploreCapitals is a unique application where you can find any country or territory's capital.">
    <meta name="author" content="ExploreCapitals">
    <link rel="stylesheet" href="styles.css"> <!-- Only the single stylesheet -->
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Common container class plus .about -->
    <section class="page-content about">
        <h1>About ExploreCapitals</h1>
        <p>
            ExploreCapitals</strong> is an intuitive application where you can
            <strong>find any capital</strong> with ease. Created to support learners of all levels, 
            our platform offers an interactive <strong>capital quiz</strong>,<strong> a world map</strong>, and an extensive database of<strong> country profiles</strong>.
        </p>
        <h2>Connect with Us!</h2>
        <ul>
            <li><strong>Instagram:</strong> <a href="https://www.instagram.com/explorecapitals" target="_blank">@explorecapitals</a></li>
            <li><strong>Facebook:</strong> <a href="https://www.facebook.com/me/" target="_blank">Anaximander Miletus</a></li>
            <li><strong>Twitter/X:</strong> <a href="https://twitter.com/explorecapitals" target="_blank">@explorecapitals</a></li>
            <li><strong>Email:</strong> <a href="mailto:anaximanderomiletus@gmail.com">anaximanderomiletus@gmail.com</a></li>
        </ul>

        <h2>Site Statistics</h2>
        <ul>
            <li><strong>Most Searched Country:</strong> <?php echo htmlspecialchars($statistics['most_searched_countries']); ?> <?php if (!empty($statistics['most_searched_flag'])): ?><span class="flag-emoji" data-windows-flag-url="<?php echo htmlspecialchars($most_searched_flag_url); ?>"><?php echo htmlspecialchars($statistics['most_searched_flag']); ?></span><?php endif; ?></li>
            <li><strong>Total Searches:</strong> <?php echo htmlspecialchars($statistics['total_searches']); ?></li>
            <li><strong>Most Recent Search:</strong> <?php echo htmlspecialchars($statistics['most_recent_search']); ?> <?php if (!empty($statistics['most_recent_flag'])): ?><span class="flag-emoji" data-windows-flag-url="<?php echo htmlspecialchars($most_recent_flag_url); ?>"><?php echo htmlspecialchars($statistics['most_recent_flag']); ?></span><?php endif; ?></li>
            <li><strong>Searches Today:</strong> <?php echo htmlspecialchars($statistics['searches_today']); ?></li>
            <li><strong>Unique Countries Searched:</strong> <?php echo htmlspecialchars($statistics['unique_countries_searched']); ?></li>
        </ul>
    </section>
    
    <!-- Flag emoji handler for Windows devices -->
    <script src="flag-emoji-handler.js" defer></script>
</body>
</html>