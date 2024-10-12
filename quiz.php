<?php
// Include necessary files
include 'config.php';
include 'country_aliases.php';
include 'capital_aliases.php';

// Array of countries that should include "the"
$the_countries = [
    "United States", "United Kingdom", "Netherlands", "Philippines", "Bahamas",
    "Gambia", "Czech Republic", "United Arab Emirates", "Central African Republic",
    "Republic of the Congo", "Democratic Republic of the Congo", "Maldives", 
    "Marshall Islands", "Seychelles", "Solomon Islands", "Comoros"
];

// Function to prefix "the" where needed
function addThe($country) {
    global $the_countries;
    return in_array($country, $the_countries) ? "the $country" : $country;
}

// Fetch quiz questions
function getQuizQuestions($conn) {
    $query = "SELECT country_name, capital_name FROM countries ORDER BY RAND() LIMIT 10";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

// Normalize and standardize for alias matching
function normalize($string) {
    return strtolower(preg_replace('/[^a-z0-9]/', '', $string));
}

// Combine aliases into a case-insensitive map
$alias_map = array_change_key_case(array_merge($country_aliases, $capital_aliases), CASE_LOWER);
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
    <button id="startQuizBtn">Start Quiz</button>
    <div id="quizContainer" style="display: none;">
        <div id="timer">Time: 0:00</div>
        <div id="questionContainer"></div>
        <form id="answerForm">
            <input type="text" id="userAnswer" placeholder="Type your answer" required>
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
const aliasMap = <?php echo json_encode($alias_map); ?>;
const theCountries = <?php echo json_encode(array_map('strtolower', $the_countries)); ?>;

// Helper to add "the" prefix
function addThe(country) {
    return theCountries.includes(country.toLowerCase()) ? `the ${country}` : country;
}

// Normalize function to account for capitalization and aliases
function normalizeInput(input) {
    input = input.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    input = input.replace(/[^a-z0-9]/g, '');
    return aliasMap[input] || input;
}

let questions = <?php echo json_encode(getQuizQuestions($conn)); ?>;
let currentQuestionIndex = 0;
let score = 0;
let timer;
let timeElapsed = 0;
let userResponses = [];

// Start Quiz
function startQuiz() {
    document.getElementById('startQuizBtn').style.display = 'none';
    document.getElementById('resultContainer').style.display = 'none';
    document.getElementById('quizContainer').style.display = 'block';
    score = 0;
    userResponses = [];
    currentQuestionIndex = 0;
    startTimer();
    showNextQuestion();
}

// Start Timer
function startTimer() {
    timer = setInterval(() => {
        timeElapsed++;
        document.getElementById('timer').textContent = `Time: ${Math.floor(timeElapsed / 60)}:${timeElapsed % 60}`;
    }, 1000);
}

// Show Next Question
function showNextQuestion() {
    if (currentQuestionIndex < questions.length) {
        const questionData = questions[currentQuestionIndex];
        const isCountryQuestion = Math.random() > 0.5;
        const questionText = isCountryQuestion
            ? `What is the capital of ${addThe(questionData.country_name)}?`
            : `Of which country is ${addThe(questionData.capital_name)} the capital?`;

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

// Check if the answer is correct using aliases
function checkAnswer(userAnswer, correctAnswer) {
    const normalizedAnswer = normalizeInput(userAnswer);
    const correctOptions = correctAnswer.toLowerCase().split('/').map(opt => normalizeInput(opt.trim()));
    return correctOptions.includes(normalizedAnswer);
}

// End Quiz
function endQuiz() {
    clearInterval(timer);
    document.getElementById('quizContainer').style.display = 'none';
    document.getElementById('resultContainer').style.display = 'block';
    document.getElementById('score').textContent = `You scored ${score} out of ${questions.length}.`;

    let resultsHTML = '';
    userResponses.forEach((response, index) => {
        const resultText = response.isCorrect 
            ? `Correct. The answer was ${addThe(response.correctAnswer)}.`
            : `Incorrect. The answer was ${addThe(response.correctAnswer)}. You put "${response.userAnswer}".`;
        resultsHTML += `<p><strong>Question ${index + 1}: ${response.questionText}</strong><br>${resultText}</p>`;
    });
    document.getElementById('detailedResults').innerHTML = resultsHTML;
}

// Handle answer submission
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

document.getElementById('startQuizBtn').addEventListener('click', startQuiz);
document.getElementById('redoQuizBtn').addEventListener('click', () => location.reload());
</script>

</body>
</html>
