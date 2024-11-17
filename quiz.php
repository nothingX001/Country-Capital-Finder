<?php
// Include the 'the-countries.php' file for normalization
include 'the-countries.php';

// Fetch quiz data from the `fetch-country-data.php` file
function fetchQuizQuestions() {
    $url = 'fetch-country-data.php?type=random&limit=10';
    $response = file_get_contents($url);
    return json_decode($response, true);
}

// Normalize function to handle "the" countries
function normalizeInput($input) {
    global $the_countries;
    $input = strtolower(trim($input));
    $input = preg_replace('/^the\s+/i', '', $input); // Remove "the" if present
    return in_array($input, array_map('strtolower', $the_countries)) ? "the $input" : $input;
}

// Fetch questions
$quizQuestions = fetchQuizQuestions();

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
    <button id="startQuizBtn">START QUIZ</button>

    <div id="quizContainer" style="display: none;">
        <div id="timer">Time: 0:00</div>
        <div id="questionContainer"></div>
        <form id="answerForm">
            <input type="text" id="userAnswer" placeholder="Type your answer here" required>
            <button type="submit">SUBMIT ANSWER</button>
        </form>
    </div>

    <div id="resultContainer" style="display: none;">
        <h2>Quiz Results</h2>
        <p id="score"></p>
        <div id="detailedResults"></div>
        <button id="redoQuizBtn">REDO QUIZ</button>
    </div>
</section>

<script>
// Fetch quiz data from PHP
const quizQuestions = <?php echo json_encode($quizQuestions); ?>;
let currentQuestionIndex = 0;
let score = 0;
let timer;
let timeElapsed = 0;
let userResponses = [];

// Normalize function for JavaScript
const theCountries = <?php echo json_encode(array_map('strtolower', $the_countries)); ?>;
function normalizeInput(input) {
    input = input.toLowerCase().trim();
    input = input.replace(/^the\s+/i, '');
    return theCountries.includes(input) ? `the ${input}` : input;
}

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
    if (currentQuestionIndex < quizQuestions.length) {
        const questionData = quizQuestions[currentQuestionIndex];
        const isCountryQuestion = Math.random() > 0.5;
        const questionText = isCountryQuestion 
            ? `What is the capital of ${questionData.country_name}?`
            : `${questionData.capital_name} is the capital of what country?`;

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
    return normalizedAnswer === normalizeInput(correctAnswer);
}

// End quiz function
function endQuiz() {
    clearInterval(timer);
    document.getElementById('quizContainer').style.display = 'none';
    document.getElementById('resultContainer').style.display = 'block';
    document.getElementById('score').textContent = `You scored ${score} out of ${quizQuestions.length}.`;

    let resultsHTML = '';
    userResponses.forEach((response, index) => {
        const resultText = response.isCorrect 
            ? `Correct. The answer was ${response.correctAnswer}.`
            : `Incorrect. The answer was ${response.correctAnswer}. You put "${response.userAnswer}".`;

        resultsHTML += `<p><strong>Question ${index + 1}: ${response.questionText}</strong><br>${resultText}</p>`;
    });
    document.getElementById('detailedResults').innerHTML = resultsHTML;
}

// Handle answer submission
document.getElementById('answerForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const userAnswer = document.getElementById('userAnswer').value.trim();
    const response = userResponses[currentQuestionIndex];

    const correctAnswer = response.isCountryQuestion 
        ? quizQuestions[currentQuestionIndex].capital_name
        : quizQuestions[currentQuestionIndex].country_name;

    const isCorrect = checkAnswer(userAnswer, correctAnswer);
    if (isCorrect) score++;

    response.userAnswer = userAnswer;
    response.isCorrect = isCorrect;

    currentQuestionIndex++;
    showNextQuestion();
});

// Redo quiz
document.getElementById('redoQuizBtn').addEventListener('click', () => location.reload());
document.getElementById('startQuizBtn').addEventListener('click', startQuiz);

</script>
</body>
</html>
