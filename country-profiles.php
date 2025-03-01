<?php
// country-profiles.php

// Fetch data for the three groups using the updated endpoint
$mainData     = file_get_contents('http://localhost/fetch-country-data.php?type=all_main_only');
$mainCountries = json_decode($mainData, true) ?: [];

$territoryData = file_get_contents('http://localhost/fetch-country-data.php?type=all_territories');
$territories   = json_decode($territoryData, true) ?: [];

$deFactoData   = file_get_contents('http://localhost/fetch-country-data.php?type=all_de_facto_states');
$deFactoStates = json_decode($deFactoData, true) ?: [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Country Profiles | ExploreCapitals</title>
    <link rel="stylesheet" href="styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore profiles of countries, territories, and de facto states.">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="page-content country-profiles" id="main-country-profiles">
        <h1>Country Profiles</h1>
        <p>Browse our database of countries, territories, and de facto states.</p>

        <!-- 1) Main Countries (UN member/observer states) -->
        <h2>Countries</h2>
        <?php if (!empty($mainCountries)): ?>
            <ul>
                <?php foreach ($mainCountries as $c): ?>
                    <li>
                        <a href="country-detail.php?id=<?php echo htmlspecialchars($c['id']); ?>">
                            <?php echo htmlspecialchars($c['country_name']); ?>
                            <?php if (!empty($c['flag_emoji'])): ?>
                                <?php echo ' ' . htmlspecialchars($c['flag_emoji']); ?>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No countries found.</p>
        <?php endif; ?>

        <!-- 2) Territories -->
        <h2>Territories</h2>
        <?php if (!empty($territories)): ?>
            <ul>
                <?php foreach ($territories as $t): ?>
                    <li>
                        <a href="country-detail.php?id=<?php echo htmlspecialchars($t['id']); ?>">
                            <?php echo htmlspecialchars($t['country_name']); ?>
                            <?php if (!empty($t['flag_emoji'])): ?>
                                <?php echo ' ' . htmlspecialchars($t['flag_emoji']); ?>
                            <?php endif; ?>
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
                            <?php echo htmlspecialchars($d['country_name']); ?>
                            <?php if (!empty($d['flag_emoji'])): ?>
                                <?php echo ' ' . htmlspecialchars($d['flag_emoji']); ?>
                            <?php endif; ?>
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
