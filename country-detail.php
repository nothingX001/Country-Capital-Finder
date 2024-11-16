<?php
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: country-profiles.php');
    exit;
}

$data = file_get_contents("http://localhost/fetch-country-data.php?type=detail&id=" . urlencode($id));
$country = json_decode($data, true);

if (!$country) {
    header('Location: country-profiles.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($country['country_name'] ?? 'Unknown Country'); ?> Profile</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="country-detail-styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section id="country-profile">
        <h1><?php echo htmlspecialchars($country['country_name'] ?? 'Unknown Country'); ?></h1>
        <p>Flag: <?php echo htmlspecialchars($country['flag_emoji'] ?? 'N/A'); ?></p>
        <p>Capital: <?php echo htmlspecialchars($country['capital_name'] ?? 'N/A'); ?></p>
        <p>Languages: <?php echo htmlspecialchars($country['language'] ?? 'N/A'); ?></p>
        <p>Alternate Names: <?php echo htmlspecialchars($country['alternate_names'] ?? 'N/A'); ?></p>
        <img src="<?php echo htmlspecialchars($country['map_image_url'] ?? 'placeholder.jpg'); ?>" alt="Map of <?php echo htmlspecialchars($country['country_name'] ?? 'Unknown Country'); ?>" style="width:100%; height:auto;">
    </section>
</body>
</html>
