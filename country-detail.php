<?php
// Include database connection
include 'config.php';

// Fetch country details using ID from query parameter
$country_id = $_GET['id'] ?? null;

if ($country_id) {
    $stmt = $conn->prepare("SELECT country_name, capital_name, flag_emoji, language, alternate_names, map_image_url FROM countries WHERE id = ?");
    $stmt->execute([$country_id]);
    $country = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$country) {
        die("Country not found.");
    }
} else {
    die("Invalid country ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($country['country_name']); ?> Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="country-detail-styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div id="country-detail-card">
        <div class="card-header">
            <h1><?php echo htmlspecialchars($country['country_name']); ?> <span><?php echo $country['flag_emoji']; ?></span></h1>
        </div>
        <div class="card-content">
            <div class="country-image">
                <img src="<?php echo htmlspecialchars($country['map_image_url']); ?>" alt="Map of <?php echo htmlspecialchars($country['country_name']); ?>">
            </div>
            <div class="country-info">
                <p><strong>Capital:</strong> <?php echo htmlspecialchars($country['capital_name']); ?></p>
                <p><strong>Languages:</strong> <?php echo htmlspecialchars($country['language'] ?? 'N/A'); ?></p>
                <p><strong>Alternate Names:</strong> <?php echo htmlspecialchars($country['alternate_names'] ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>
</body>
</html>
