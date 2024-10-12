<?php
// Include database connection and alias files
include 'config.php';
include 'country_aliases.php';
include 'capital_aliases.php';

// Array of countries that should be preceded by "the"
$the_countries = [
    "United States",
    "United Kingdom",
    "Netherlands",
    "Philippines",
    "Bahamas",
    "Gambia",
    "Czech Republic",
    "United Arab Emirates",
    "Central African Republic",
    "Republic of the Congo",
    "Democratic Republic of the Congo",
    "Maldives",
    "Marshall Islands",
    "Seychelles",
    "Solomon Islands",
    "Comoros"
];

// Function to add "the" to countries that require it
function addThe($country) {
    global $the_countries;
    return in_array($country, $the_countries) ? "the $country" : $country;
}

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

// Normalize function to handle case-insensitivity, remove "the" prefix, and normalize special characters
function normalize($string) {
    $string = mb_strtolower($string); // Convert to lowercase
    $string = preg_replace('/^the\s+/', '', $string); // Remove "the" prefix if present
    $string = str_replace(['ü', 'é', 'á', 'ö', 'ç', 'ñ', 'ã', 'í'], ['u', 'e', 'a', 'o', 'c', 'n', 'a', 'i'], $string);
    $string = preg_replace('/[^a-z0-9]/', '', $string); // Remove non-alphanumeric characters
    return $string;
}

// Fetch initial questions
$quizQuestions = getQuizQuestions($conn);

// Lowercase alias maps for consistent normalization
$country_aliases = array_change_key_case($country_aliases, CASE_LOWER);
$capital_aliases = array_change_key_case($capital_aliases, CASE_LOWER);

// Combined alias map
$alias_map = array_merge($country_aliases, $capital_aliases);
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
    <h1>Country Capital Quiz</h1>
    <p>Test your knowledge of country capitals!</p>
    <button id="startQuizBtn">Start Quiz</button>

    <div id="quizContainer" style="display: none;">
        <div id="timer">Time: 0:00</div>
        <div id="questionContainer"></div>
        <form id="answerForm">
            <input type="text" id="userAnswer" placeholder="Type your answer here" required>
            <button type="submit">Submit Answer</button>
        </form>
    </div>

    <div id="resultContainer" style="display: none;">
        <h2>Quiz Results</h2>
        <p id="score"></p>
        <div id="detailedResults"></div>
        <button id="redoQuizBtn">Redo Quiz</button>
    </div>
</section>

<script>
// Alias map from PHP to JavaScript
const aliasMap = <?php echo json_encode($alias_map); ?>;

// Array of countries requiring "the"
const theCountries = <?php echo json_encode(array_map('strtolower', $the_countries)); ?>;

// Function to add "the" for countries that require it
function addThe(country) {
    return theCountries.includes(country.toLowerCase()) ? `the ${country}` : country;
}

// Normalize function to handle case-insensitive matching
function normalizeInput(input) {
    let normalized = input.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    normalized = normalized.replace(/^the\s+/, ''); // Remove "the" if present
    normalized = normalized.replace(/[^a-z0-9]/g, ''); // Remove non-alphanumeric chars
    return aliasMap[normalized] || normalized;
}

// Initialize quiz variables
let questions = <?php echo json_encode($quizQuestions); ?>;
let currentQuestionIndex = 0;
let score = 0;
let timer;
let timeElapsed = 0;
let userResponses = [];

// Start quiz function
function startQuiz() {
    document.getElementById('startQuizBtn').style.display = 'none';
    document.getElementById('resultContainer').style.display = 'none';
    document.getElementById('quizContainer').style.display = 'block';
    score = 0;
    timeElapsed = 0;
    userResponses = [];
    currentQuestionIndex = 0;
    startTimer();
    showNextQuestion();
}

// Start timer function
function startTimer() {
    timer = setInterval(() => {
        timeElapsed++;
        const minutes = Math.floor(timeElapsed / 60);
        const seconds = timeElapsed % 60;
        document.getElementById('timer').textContent = `Time: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    }, 1000);
}

// Show the next question
function showNextQuestion() {
    if (currentQuestionIndex < questions.length) {
        const questionData = questions[currentQuestionIndex];
        const isCountryQuestion = Math.random() > 0.5;
        const questionText = isCountryQuestion 
            ? `What is the capital of ${addThe(questionData.country_name)}?`
            : `${addThe(questionData.capital_name)} is the capital of what country?`;

        userResponses.push({
            questionText: questionText,
            correctAnswer: isCountryQuestion ? questionData.capital_name : questionData.country_name,
            isCountryQuestion: isCountryQuestion
        });

        document.getElementById('questionContainer').textContent = `Question ${currentQuestionIndex + 1}: ${questionText}`;
        document.getElementById('userAnswer').value = '';
    } else {
        endQuiz();
    }
}

// Check if the answer is correct
function checkAnswer(userAnswer, correctAnswer) {
    const normalizedAnswer = normalizeInput(userAnswer);
    const correctOptions = correctAnswer.toLowerCase().split('/').map(option => normalizeInput(option.trim()));
    return correctOptions.includes(normalizedAnswer);
}

// End quiz function
function endQuiz() {
    clearInterval(timer);
    document.getElementById('quizContainer').style.display = 'none';
    document.getElementById('resultContainer').style.display = 'block';
    document.getElementById('score').textContent = `You scored ${score} out of ${questions.length}.`;

    // Display detailed results
    let resultsHTML = '';
    userResponses.forEach((response, index) => {
        const resultText = response.isCorrect 
            ? `Correct. The answer was ${addThe(response.correctAnswer)}.`
            : `Incorrect. The answer was ${addThe(response.correctAnswer)}. You put "${response.userAnswer}".`;

        resultsHTML += `<p><strong>Question ${index + 1}: ${response.questionText}</strong><br>${resultText}</p>`;
    });
    document.getElementById('detailedResults').innerHTML = resultsHTML;
}

// Answer submission handler
document.getElementById('answerForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const userAnswer = document.getElementById('userAnswer').value.trim();
    const questionData = questions[currentQuestionIndex];
    const response = userResponses[currentQuestionIndex];

    const correctAnswer = response.isCountryQuestion 
        ? questionData.capital_name
        : questionData.country_name;

    const isCorrect = checkAnswer(userAnswer, correctAnswer);
    if (isCorrect) {
        score++;
    }

    response.userAnswer = userAnswer;
    response.isCorrect = isCorrect;

    currentQuestionIndex++;
    showNextQuestion();
});

// Redo quiz function
function reloadQuiz() {
    location.reload();
}

// Start button event listener
document.getElementById('startQuizBtn').addEventListener('click', startQuiz);

// Redo button event listener
document.getElementById('redoQuizBtn').addEventListener('click', reloadQuiz);

</script>

</body>
</html>
