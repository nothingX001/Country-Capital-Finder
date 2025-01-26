<?php
// country-profiles.php

// 1) Fetch Member/Observer
$mainData = file_get_contents('http://localhost/fetch-country-data.php?type=all_main_only');
$mainCountries = json_decode($mainData, true) ?: [];

// 2) Fetch Territories
$terrData = file_get_contents('http://localhost/fetch-country-data.php?type=all_territories');
$territories = json_decode($terrData, true) ?: [];

// 3) Fetch De Facto States
$deFactoData = file_get_contents('http://localhost/fetch-country-data.php?type=all_de_facto_states'); // Corrected type
$deFactoStates = json_decode($deFactoData, true) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Country Profiles</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="country-profiles-styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section id="main-country-profiles">
        <h1>COUNTRY PROFILES</h1>
        <p>Explore member/observer states, territories, and de facto states in our database.</p>

        <!-- 1) Member/Observer States -->
        <h2>Member/Observer States</h2>
        <?php if (!empty($mainCountries)): ?>
            <ul>
                <?php foreach ($mainCountries as $c): ?>
                <li>
                    <a href="country-detail.php?id=<?php echo htmlspecialchars($c['id']); ?>">
                        <?php echo htmlspecialchars($c['country_name']) . " " . htmlspecialchars($c['flag_emoji']); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No member/observer states found.</p>
        <?php endif; ?>

        <!-- 2) Territories -->
        <h2>Territories</h2>
        <?php if (!empty($territories)): ?>
            <ul>
                <?php foreach ($territories as $t): ?>
                <li>
                    <a href="country-detail.php?id=<?php echo htmlspecialchars($t['id']); ?>">
                        <?php echo htmlspecialchars($t['country_name']) . " " . htmlspecialchars($t['flag_emoji']); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No territories found.</p>
        <?php endif; ?>

        <!-- 3) De Facto States -->
        <h2>De Facto States</h2>
        <?php if (!empty($deFactoStates)): ?>
            <ul>
                <?php foreach ($deFactoStates as $d): ?>
                <li>
                    <a href="country-detail.php?id=<?php echo htmlspecialchars($d['id']); ?>">
                        <?php echo htmlspecialchars($d['country_name']) . " " . htmlspecialchars($d['flag_emoji']); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No de facto states found.</p>
        <?php endif; ?>

    </section>
</body>
</html>