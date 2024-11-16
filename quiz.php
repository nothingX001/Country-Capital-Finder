<?php
// Fetch random quiz data using the fetch-country-data.php API
$url = 'fetch-country-data.php?type=random&limit=10';
$response = file_get_contents($url);
$quiz_data = json_decode($response, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Country Capital Quiz</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="quiz-styles.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<section id="main-quiz">
    <h1>COUNTRY CAPITAL QUIZ</h1>
    <p>Test your knowledge of country capitals.</p>
    <form id="quizForm">
        <?php foreach ($quiz_data as $index => $question): ?>
            <div>
                <label for="question-<?php echo $index; ?>">
                    <?php echo "What is the capital of " . htmlspecialchars($question['country_name']) . "?"; ?>
                </label>
                <input type="text" id="question-<?php echo $index; ?>" name="question-<?php echo $index; ?>" required>
            </div>
        <?php endforeach; ?>
        <button type="submit">Submit Answers</button>
    </form>
</section>

</body>
</html>
