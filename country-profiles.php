<?php
// country-profiles.php

include 'config.php';

try {
    // 1) Fetch Main Countries (Member/Observer States)
    $stmtMain = $conn->query('
        SELECT id, "Official Name" AS country_name, "Flag Emoji" AS flag_emoji
        FROM countries
        WHERE "Entity Type" IN (\'Member State\', \'Observer State\')
        ORDER BY "Official Name" ASC
    ');
    $mainCountries = $stmtMain->fetchAll(PDO::FETCH_ASSOC);

    // 2) Fetch Territories
    $stmtTerr = $conn->query('
        SELECT id, "Official Name" AS country_name, "Flag Emoji" AS flag_emoji
        FROM countries
        WHERE "Entity Type" = \'Territory\'
        ORDER BY "Official Name" ASC
    ');
    $territories = $stmtTerr->fetchAll(PDO::FETCH_ASSOC);

    // 3) Fetch De Facto States
    $stmtDefacto = $conn->query('
        SELECT id, "Official Name" AS country_name, "Flag Emoji" AS flag_emoji
        FROM countries
        WHERE "Entity Type" = \'De Facto\'
        ORDER BY "Official Name" ASC
    ');
    $deFactoStates = $stmtDefacto->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // In case of error, you can log $e->getMessage()
    die("Error fetching country profiles: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Country Profiles | ExploreCapitals</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse profiles of countries, territories, and de facto states.">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="page-content country-profiles" id="main-country-profiles">
        <h1>Country Profiles</h1>
        <p>Browse our database of countries, territories, and de facto states.</p>

        <!-- Main Countries -->
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

        <!-- Territories -->
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

        <!-- De Facto States -->
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
