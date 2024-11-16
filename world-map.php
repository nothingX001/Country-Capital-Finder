<?php
$data = file_get_contents('http://localhost/fetch-country-data.php?type=map');
$countries = json_decode($data, true);
?>
<!DOCTYPE html>
<html>
<head>
    <title>World Map</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <h1>World Map</h1>
    <div id="map" style="height: 500px;"></div>
    <script>
        const countries = <?php echo json_encode($countries); ?>;
        countries.forEach(country => {
            console.log(country.country_name, country.latitude, country.longitude);
        });
    </script>
</body>
</html>
