<?php
// country-detail.php

include 'config.php';

// Get the country from the URL parameter
$country_name = $_GET['country'] ?? null;

// Ensure the country name is valid
if ($country_name && array_key_exists($country_name, $country_map)) {
    // Query the database for country-specific information if available
    // For simplicity, we’ll just display the name and flag here
    $flag = $country_map[$country_name];
} else {
    // Redirect to the main profile page if no valid country is found
    header("Location: country-profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($country_name); ?> Profile</title>
</head>
<body>

<?php include 'navbar.php'; ?> <!-- Include your NavBar -->

<h1><?php echo htmlspecialchars($country_name); ?></h1>
<p>Flag: <?php echo $flag; ?></p>
<!-- Additional country information could go here, queried from the database -->

</body>
</html>
