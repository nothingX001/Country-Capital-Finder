<?php
include 'config.php';

// Fetch all countries using fetch-country-data.php API
function fetchData($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        die("cURL error: " . curl_error($ch));
    }
    curl_close($ch);
    return $result;
}

$url = './fetch-country-data.php?type=all';
$response = fetchData($url);
$countries = json_decode($response, true);
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
                <a href="country-detail.php?country=<?php echo urlencode($country['country_name']); ?>">
                    <?php echo htmlspecialchars($country['country_name']) . " " . $country['flag_emoji']; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

</body>
</html>
