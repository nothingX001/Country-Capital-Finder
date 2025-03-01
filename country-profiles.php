<?php
// country-profiles.php

include 'config.php';

try {
    // 1) Fetch UN member/observer states
    //    Use the actual strings in your "Entity Type" column, e.g. 'UN member', 'UN observer'
    $stmtMain = $conn->query('
        SELECT
            id,
            "Official Name" AS country_name,
            "Flag Emoji"    AS flag_emoji
        FROM countries
        WHERE "Entity Type" IN (\'UN member\', \'UN observer\')
        ORDER BY "Official Name" ASC
    ');
    $mainCountries = $stmtMain->fetchAll(PDO::FETCH_ASSOC);

    // 2) Fetch Territories
    //    If your CSV has 'Territory' in "Entity Type", filter by that
    $stmtTerr = $conn->query('
        SELECT
            id,
            "Official Name" AS country_name,
            "Flag Emoji"    AS flag_emoji
        FROM countries
        WHERE "Entity Type" = \'Territory\'
        ORDER BY "Official Name" ASC
    ');
    $territories = $stmtTerr->fetchAll(PDO::FETCH_ASSOC);

    // 3) Fetch De Facto States
    //    If your CSV uses 'De Facto' in "Entity Type", filter by that
    $stmtDefacto = $conn->query('
        SELECT
            id,
            "Official Name" AS country_name,
            "Flag Emoji"    AS flag_emoji
        FROM countries
        WHERE "Entity Type" = \'De Facto\'
        ORDER BY "Official Name" ASC
    ');
    $deFactoStates = $stmtDefacto->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Log or display an error message
    die("Error fetching country profiles: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Country Profiles | ExploreCapitals</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse our database of countries, territories, and de facto states.">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="page-content country-profiles" id="main-country-profiles">
        <h1>Country Profiles</h1>
        <p>Browse our database of countries, territories, and de facto states.</p>

        <!-- 1) Main Countries (UN member / observer) -->
        <h2>Countries</h2>
        <?php if (!empty($mainCountries)): ?>
            <ul>
                <?php foreach ($mainCountries as $c): ?>
                    <?php
                        // Handle NULL values to avoid deprecation warnings
                        $countryName = $c['country_name'] ?? '';
                        $flagEmoji   = $c['flag_emoji']   ?? '';
                    ?>
                    <li>
                        <a href="country-detail.php?id=<?php echo htmlspecialchars($c['id']); ?>">
                            <?php echo htmlspecialchars($countryName); ?>
                            <?php if (!empty($flagEmoji)): ?>
                                <?php echo ' ' . htmlspecialchars($flagEmoji); ?>
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
                    <?php
                        $countryName = $t['country_name'] ?? '';
                        $flagEmoji   = $t['flag_emoji']   ?? '';
                    ?>
                    <li>
                        <a href="country-detail.php?id=<?php echo htmlspecialchars($t['id']); ?>">
                            <?php echo htmlspecialchars($countryName); ?>
                            <?php if (!empty($flagEmoji)): ?>
                                <?php echo ' ' . htmlspecialchars($flagEmoji); ?>
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
                    <?php
                        $countryName = $d['country_name'] ?? '';
                        $flagEmoji   = $d['flag_emoji']   ?? '';
                    ?>
                    <li>
                        <a href="country-detail.php?id=<?php echo htmlspecialchars($d['id']); ?>">
                            <?php echo htmlspecialchars($countryName); ?>
                            <?php if (!empty($flagEmoji)): ?>
                                <?php echo ' ' . htmlspecialchars($flagEmoji); ?>
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
