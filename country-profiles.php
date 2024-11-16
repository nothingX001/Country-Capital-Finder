<?php
include 'config.php'; // Include the database configuration

try {
    // Fetch all countries from the database
    $query = $conn->query("SELECT country_name, flag_emoji FROM countries ORDER BY country_name ASC");
    $countries = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching countries: " . $e->getMessage());
}
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

    <?php include 'navbar.php'; ?> <!-- Include your NavBar -->

    <section id="main-country-profiles">
        <h1>COUNTRY PROFILES</h1>
        <p>Select a country to view its profile.</p>
        <ul>
            <?php foreach ($countries as $country): ?>
                <li>
                    <a href="country-detail.php?country=<?php echo urlencode($country['country_name']); ?>">
                        <?php echo htmlspecialchars($country['country_name']) . " " . htmlspecialchars($country['flag_emoji']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

</body>
</html>
