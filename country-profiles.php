<?php
// country-profiles.php

include 'config.php';
include 'the-countries.php'; // Include the list of "the" countries

try {
    // 1) Main Countries (UN member / observer)
    $stmt = $conn->prepare('
        SELECT
            id,
            "Country Name" AS country_name,
            "Flag Emoji"   AS flag_emoji,
            CASE 
                WHEN LOWER("Country Name") = ANY(?)
                THEN TRUE 
                ELSE FALSE 
            END AS needs_the
        FROM countries
        WHERE "Entity Type" IN (\'UN member\', \'UN observer\')
        ORDER BY "Country Name" ASC
    ');
    $stmt->execute(['{' . implode(',', $the_countries) . '}']);
    $mainCountries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2) Territories
    $stmt = $conn->prepare('
        SELECT
            id,
            "Country Name" AS country_name,
            "Flag Emoji"   AS flag_emoji,
            CASE 
                WHEN LOWER("Country Name") = ANY(?)
                THEN TRUE 
                ELSE FALSE 
            END AS needs_the
        FROM countries
        WHERE "Entity Type" = \'Territory\'
        ORDER BY "Country Name" ASC
    ');
    $stmt->execute(['{' . implode(',', $the_countries) . '}']);
    $territories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3) De Facto States
    $stmt = $conn->prepare('
        SELECT
            id,
            "Country Name" AS country_name,
            "Flag Emoji"   AS flag_emoji,
            CASE 
                WHEN LOWER("Country Name") = ANY(?)
                THEN TRUE 
                ELSE FALSE 
            END AS needs_the
        FROM countries
        WHERE "Entity Type" = \'De facto state\'
        ORDER BY "Country Name" ASC
    ');
    $stmt->execute(['{' . implode(',', $the_countries) . '}']);
    $deFactoStates = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error fetching country profiles: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Country Profiles | ExploreCapitals</title>
    <link rel="icon" type="image/jpeg" href="images/explore-capitals-logo.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="Browse our database of countries, territories, and more!">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="page-content country-profiles" id="main-country-profiles">
        <h1>Country Profiles</h1>
        <p>Browse our database of countries, territories, and de facto states.</p>

        <!-- 1) Main Countries -->
        <h2>Countries</h2>
        <?php if (!empty($mainCountries)): ?>
            <ul>
                <?php foreach ($mainCountries as $c): ?>
                    <?php
                        // Safely handle NULL values
                        $countryName = $c['country_name'] ?? '';
                        $flagEmoji   = $c['flag_emoji']   ?? '';
                        $displayName = $c['needs_the'] ? 'The ' . $countryName : $countryName;
                    ?>
                    <li>
                        <a href="country-detail.php?id=<?php echo htmlspecialchars($c['id']); ?>">
                            <?php echo htmlspecialchars($displayName); ?>
                            <?php if (!empty($flagEmoji)): ?>
                                <span class="flag-emoji"><?php echo htmlspecialchars($flagEmoji); ?></span>
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
                        $displayName = $t['needs_the'] ? 'The ' . $countryName : $countryName;
                    ?>
                    <li>
                        <a href="country-detail.php?id=<?php echo htmlspecialchars($t['id']); ?>">
                            <?php echo htmlspecialchars($displayName); ?>
                            <?php if (!empty($flagEmoji)): ?>
                                <span class="flag-emoji"><?php echo htmlspecialchars($flagEmoji); ?></span>
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
                        $displayName = $d['needs_the'] ? 'The ' . $countryName : $countryName;
                    ?>
                    <li>
                        <a href="country-detail.php?id=<?php echo htmlspecialchars($d['id']); ?>">
                            <?php echo htmlspecialchars($displayName); ?>
                            <?php if (!empty($flagEmoji)): ?>
                                <span class="flag-emoji"><?php echo htmlspecialchars($flagEmoji); ?></span>
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
