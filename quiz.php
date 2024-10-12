<?php
// Include necessary files for aliases and configuration
include 'config.php';
include 'country_aliases.php';
include 'capital_aliases.php';

// Function to get 10 random country-capital pairs
function getQuizQuestions($conn) {
    $questions = [];
    $query = "SELECT country_name, capital_name FROM countries ORDER BY RAND() LIMIT 10";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
    return $questions;
}

// Fetch the initial questions
$quizQuestions = getQuizQuestions($conn);

// Merge alias arrays into one for easier JavaScript use
$alias_map = array_merge($country_aliases, $capital_aliases);

// Array of countries that should be preceded by "the"
$the_countries = [
    "United States", "United Kingdom", "Netherlands", "Philippines", "Bahamas", "Gambia", 
    "Czech Republic", "United Arab Emirates", "Central African Republic", "Republic of the Congo", 
    "Democratic Republic of the Congo", "Maldives", "Marshall Islands", "Seychelles", 
    "Solomon Islands", "Comoros"
];

// Function to handle "the" prefix for applicable countries
function addThe($country) {
    global $the_countries;
    return in_array($country, $the_countries) ? "the $country" : $country;
}

// Function to normalize the input, handle aliases, and ignore case differences
function normalizeInput($input) {
    global $alias_map;
    $input = strtolower(trim($input));
    return $alias_map[$input] ?? ucwords($input);
}

// Initialize quiz state and variables
$score = 0;
$currentQuestionIndex = 0;
$userResponses = [];
$quizResults = [];
$timeElapsed = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['answers'] as $index => $userAnswer) {
        $questionData = $quizQuestions[$index];
        $isCountryQuestion = rand(0, 1) > 0.5;
        
        // Determine the question format
        if ($isCountryQuestion) {
            $correctAnswer = normalizeInput($questionData['country_name']);
            $userAnswer = normalizeInput($userAnswer);
            $questionText = "Of which country is {$questionData['capital_name']} the capital?";
        } else {
            $correctAnswer = normalizeInput($questionData['capital_name']);
            $userAnswer = normalizeInput($userAnswer);
            $questionText = "What is the capital of " . addThe($questionData['country_name']) . "?";
        }

        $isCorrect = strcasecmp($userAnswer, $correctAnswer) === 0;
        if ($isCorrect) $score++;

        $quizResults[] = [
            'question' => $questionText,
            'userAnswer' => $userAnswer,
            'correctAnswer' => $correctAnswer,
            'isCorrect' => $isCorrect,
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Country Capital Quiz</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="quiz-styles.css">
</head>
<body>

<?php include 'navbar.php'; ?>
<section id="main-quiz">
    <h1>Country Capital Quiz</h1>
    <p>Test your knowledge of country capitals!</p>

    <?php if ($_SERVER["REQUEST_METHOD"] != "POST"): ?>
        <form action="quiz.php" method="POST" id="quizForm">
            <?php foreach ($quizQuestions as $index => $questionData): ?>
                <div class="quiz-question">
                    <?php 
                        $isCountryQuestion = rand(0, 1) > 0.5;
                        $questionText = $isCountryQuestion 
                            ? "Of which country is <strong>{$questionData['capital_name']}</strong> the capital?"
                            : "What is the capital of <strong>" . addThe($questionData['country_name']) . "</strong>?";
                    ?>
                    <p><strong>Question <?php echo $index + 1; ?>:</strong> <?php echo $questionText; ?></p>
                    <input type="text" name="answers[<?php echo $index; ?>]" required>
                </div>
            <?php endforeach; ?>
            <button type="submit">Submit Quiz</button>
        </form>
    <?php else: ?>
        <h2>Quiz Results</h2>
        <p>You scored <?php echo $score; ?> out of <?php echo count($quizQuestions); ?>.</p>
        <?php foreach ($quizResults as $index => $result): ?>
            <div class="quiz-result">
                <p><strong>Question <?php echo $index + 1; ?>:</strong> <?php echo $result['question']; ?></p>
                <p>
                    <?php if ($result['isCorrect']): ?>
                        <span style="color: green;">Correct. The answer was <?php echo $result['correctAnswer']; ?>.</span>
                    <?php else: ?>
                        <span style="color: red;">Incorrect. The answer was <?php echo $result['correctAnswer']; ?>. You put "<?php echo $result['userAnswer']; ?>".</span>
                    <?php endif; ?>
                </p>
            </div>
        <?php endforeach; ?>
        <button onclick="window.location.href='quiz.php'">Try Again</button>
    <?php endif; ?>
</section>

<script>
    // JavaScript to handle displaying of quiz elements
    const quizForm = document.getElementById("quizForm");
    const quizContainer = document.getElementById("main-quiz");
    const resultsContainer = document.getElementById("quiz-results");

    // Function to toggle the visibility of form sections as the quiz progresses
    function showResults() {
        quizForm.style.display = "none";
        resultsContainer.style.display = "block";
    }

    // Event listener for the form submission
    quizForm.addEventListener("submit", function(event) {
        event.preventDefault();
        showResults();
    });
</script>
</body>
</html>
