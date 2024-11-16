<?php
include 'config.php'; // Include the database configuration

// Get the country name from the URL parameter
$country_name = $_GET['country'] ?? null;

if (!$country_name) {
    header("Location: country-profiles.php");
    exit;
}

try {
    // Fetch country details from the database
    $query = $conn->prepare("SELECT * FROM countries WHERE country_name = :country_name");
    $query->bindParam(':country_name', $country_name, PDO::PARAM_STR);
    $query->execute();
    $country_info = $query->fetch(PDO::FETCH_ASSOC);

    if (!$country_info) {
        header("Location: country-profiles.php");
        exit;
    }
} catch (PDOException $e) {
    die("Error fetching country details: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($country_info['country_name']); ?> Profile</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="country-detail-styles.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<section id="country-profile">
    <div class="profile-card">
        <h1><?php echo htmlspecialchars($country_info['country_name']); ?></h1>
        <p class="flag"><?php echo htmlspecialchars($country_info['flag_emoji']); ?></p>
        <p><strong>Capital:</strong> <?php echo htmlspecialchars($country_info['capital_name']); ?></p>
        <p><strong>Language:</strong> <?php echo htmlspecialchars($country_info['language']); ?></p>
        <p><strong>Alternate Names:</strong> <?php echo htmlspecialchars($country_info['alternate_names']); ?></p>

        <!-- Display Country Image -->
        <div class="country-image">
            <img src="<?php echo htmlspecialchars($country_info['map_image_url']); ?>" alt="Map of <?php echo htmlspecialchars($country_info['country_name']); ?>">
        </div>
    </div>
</section>

</body>
</html>
