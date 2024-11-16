<?php
// Get the country from the URL parameter
$country_name = $_GET['country'] ?? '';
if (!$country_name) {
    header("Location: country-profiles.php");
    exit;
}

// Fetch the country details using the fetch-country-data.php API
$url = 'fetch-country-data.php?type=detail&country=' . urlencode($country_name);
$response = file_get_contents($url);
$country_detail = json_decode($response, true);

// Redirect to profiles if country is not found
if (!$country_detail) {
    header("Location: country-profiles.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($country_name); ?> Profile</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="country-detail-styles.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<section id="country-profile">
    <h1><?php echo htmlspecialchars($country_detail['country_name']); ?></h1>
    <p>Flag: <?php echo $country_detail['flag_emoji']; ?></p>
    <p>Capital: <?php echo htmlspecialchars($country_detail['capital_name']); ?></p>
    <p>Language: <?php echo htmlspecialchars($country_detail['language']); ?></p>
    <p>Alternate Names: <?php echo htmlspecialchars($country_detail['alternate_names']); ?></p>
    <img src="<?php echo htmlspecialchars($country_detail['map_image_url']); ?>" alt="Map of <?php echo htmlspecialchars($country_detail['country_name']); ?>" style="width: 100%; max-width: 600px; border-radius: 10px;">
</section>

</body>
</html>
