<?php
$data = file_get_contents('http://localhost/fetch-country-data.php?type=all');
$countries = json_decode($data, true);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Country Profiles</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <h1>Country Profiles</h1>
    <ul>
        <?php foreach ($countries as $country): ?>
            <li>
                <a href="country-detail.php?id=<?php echo $country['id']; ?>">
                    <?php echo htmlspecialchars($country['country_name'] . ' ' . $country['flag_emoji']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
