<?php
// country-profile.php

include 'config.php'; // Ensure the database connection is included

// Array or database query for country list (using your existing $country_map array)
$country_map = [ /* your existing array of countries */ ];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Country Profiles</title>
</head>
<body>

<?php include 'navbar.php'; ?> <!-- Include your NavBar -->

<h1>Country Profiles</h1>
<p>Select a country to view its profile.</p>
<ul>
    <?php foreach ($country_map as $country => $flag): ?>
        <li>
            <a href="country-profile.php?country=<?php echo urlencode($country); ?>">
                <?php echo $country . " " . $flag; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

</body>
</html>