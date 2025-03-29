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
        'unique_countries_searched' => 'Data unavailable',
        'last_searched_at' => null
    ];
}

// Format the last searched timestamp if available
$last_searched_formatted = '';
if (!empty($statistics['last_searched_at'])) {
    $timestamp = strtotime($statistics['last_searched_at']);
    $last_searched_formatted = ', ' . date('g:i A', $timestamp) . ' on ' . date('F jS, Y', $timestamp);
}

// Prepare Windows flag URLs if ISO codes are available
$most_searched_flag_url = !empty($statistics['most_searched_iso']) ? "https://flagcdn.com/32x24/" . strtolower($statistics['most_searched_iso']) . ".png" : "";
$most_recent_flag_url = !empty($statistics['most_recent_iso']) ? "https://flagcdn.com/32x24/" . strtolower($statistics['most_recent_iso']) . ".png" : "";
?>
<!DOCTYPE html>
<html lang="en" style="overscroll-behavior-y: none; overflow-x: hidden;">
<head>
    <meta charset="UTF-8">
    <script id="Cookiebot" src="https://consent.cookiebot.com/uc.js" data-cbid="c7233634-6349-4f6d-8f04-54d9768b27b0" type="text/javascript" async></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>About | ExploreCapitals</title>
    <link rel="icon" type="image/jpeg" href="images/explore-capitals-logo.jpg">
    <meta name="description" content="ExploreCapitals is a unique application where you can find any country or territory's capital.">
    <meta name="author" content="ExploreCapitals">
    <link rel="stylesheet" href="styles.css"> <!-- Only the single stylesheet -->
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-94SRL3PBNE"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-94SRL3PBNE');
    </script>
    <style>
        html, body {
            overscroll-behavior-y: none !important;
            overflow-x: hidden !important;
        }
    </style>
</head>
<body style="overscroll-behavior-y: none; background: linear-gradient(180deg, #3B4B54, #DCCB9C);">
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
            <li><strong>Most Searched Country:</strong> <?php echo htmlspecialchars($statistics['most_searched_countries']); ?> <?php if (!empty($statistics['most_searched_flag'])): ?><span class="flag-emoji"><?php echo htmlspecialchars($statistics['most_searched_flag']); ?></span><?php endif; ?></li>
            <li><strong>Total Searches:</strong> <?php echo htmlspecialchars($statistics['total_searches']); ?></li>
            <li><strong>Most Recent Search:</strong> <?php echo htmlspecialchars($statistics['most_recent_search']); ?> <?php if (!empty($statistics['most_recent_flag'])): ?><span class="flag-emoji"><?php echo htmlspecialchars($statistics['most_recent_flag']); ?></span><?php endif; ?><?php echo $last_searched_formatted; ?></li>
            <li><strong>Searches Today:</strong> <?php echo htmlspecialchars($statistics['searches_today']); ?></li>
            <li><strong>Unique Countries Searched:</strong> <?php echo htmlspecialchars($statistics['unique_countries_searched']); ?></li>
        </ul>
    </section>
</body>
</html>