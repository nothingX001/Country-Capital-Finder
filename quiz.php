<?php
$data = file_get_contents('http://localhost/fetch-country-data.php?type=random&limit=10');
$questions = json_decode($data, true);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Country Quiz</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <h1>Country Quiz</h1>
    <ul>
        <?php foreach ($questions as $question): ?>
            <li><?php echo htmlspecialchars($question['country_name']); ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
