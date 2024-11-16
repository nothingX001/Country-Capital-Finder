<?php
$data = file_get_contents('http://localhost/fetch-country-data.php?type=all');
$countries = json_decode($data, true);
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
        <p>This is a complete list of countries in our database. Select a country to view its profile.</p>
        <ul>
            <?php foreach ($countries as $country): ?>
                <li>
                    <a href="country-detail.php?id=<?php echo htmlspecialchars($country['id']); ?>">
                        <?php echo htmlspecialchars($country['country_name']) . " " . htmlspecialchars($country['flag_emoji']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</body>
</html>
