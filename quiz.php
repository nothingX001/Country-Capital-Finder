<?php
// Include database connection and alias files
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

// Merge and normalize alias arrays for easier case-insensitive use in JavaScript
$alias_map = array_merge(
    array_change_key_case($country_aliases, CASE_LOWER),
    array_change_key_case($capital_aliases, CASE_LOWER)
);
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
// Alias map passed from PHP to JavaScript for use in normalization
const aliasMap = <?php echo json_encode($alias_map); ?>;

// Function to normalize user input by converting to lowercase and checking aliases
function normalizeInput(input) {
    const lowerInput = input.toLowerCase().trim();
    return aliasMap[lowerInput] || lowerInput;
}

// Initialize variables for the quiz
let questions = <?php echo json_encode($quizQuestions); ?>;
let currentQuestionIndex = 0;
let score = 0;
let timer;
let timeElapsed = 0;
let userResponses = []; // Track each user’s response and correct answers

// Function to start the quiz
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

// Function to start the timer
function startTimer() {
    timer = setInterval(() => {
        timeElapsed++;
        const minutes = Math.floor(timeElapsed / 60);
        const seconds = timeElapsed % 60;
        document.getElementById('timer').textContent = `Time: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    }, 1000);
}

// Function to show the next question
function showNextQuestion() {
    if (currentQuestionIndex < questions.length) {
        const questionData = questions[currentQuestionIndex];
        const isCountryQuestion = Math.random() > 0.5;
        const questionText = isCountryQuestion 
            ? `What is the capital of ${questionData.country_name}?`
            : `Of which country is ${questionData.capital_name} the capital?`;

        // Store the question text and correct answer
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

// Function to check if answer matches any capital option
function checkAnswer(userAnswer, correctAnswer) {
    const normalizedAnswer = normalizeInput(userAnswer);
    const correctOptions = correctAnswer.toLowerCase().split('/').map(option => normalizeInput(option.trim()));
    return correctOptions.includes(normalizedAnswer);
}

// Function to end the quiz
function endQuiz() {
    clearInterval(timer);
    document.getElementById('quizContainer').style.display = 'none';
    document.getElementById('resultContainer').style.display = 'block';
    document.getElementById('score').textContent = `You scored ${score} out of ${questions.length}.`;

    // Generate detailed results
    let resultsHTML = '';
    userResponses.forEach((response, index) => {
        const resultText = response.isCorrect 
            ? `Correct. The answer was ${response.correctAnswer}.` 
            : `Incorrect. The answer was ${response.correctAnswer}. You put "${response.userAnswer}".`;

        resultsHTML += `<p><strong>Question ${index + 1}: ${response.questionText}</strong><br>${resultText}</p>`;
    });
    document.getElementById('detailedResults').innerHTML = resultsHTML;
}

// Function to handle answer submission
document.getElementById('answerForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const userAnswer = document.getElementById('userAnswer').value.trim();
    const questionData = questions[currentQuestionIndex];
    const response = userResponses[currentQuestionIndex]; // Access current question's response data

    // Get the correct answer based on question type
    const correctAnswer = response.isCountryQuestion 
        ? questionData.capital_name
        : questionData.country_name;

    // Check if user answer matches any correct option
    const isCorrect = checkAnswer(userAnswer, correctAnswer);
    if (isCorrect) {
        score++;
    }

    // Update the response with user answer and result
    response.userAnswer = userAnswer;
    response.isCorrect = isCorrect;

    currentQuestionIndex++;
    showNextQuestion();
});

// Function to reload the quiz with new questions
function reloadQuiz() {
    location.reload();  // Reload the page to fetch new questions and start fresh
}

// Event listener to start the quiz
document.getElementById('startQuizBtn').addEventListener('click', startQuiz);

// Event listener for "Redo Quiz" button
document.getElementById('redoQuizBtn').addEventListener('click', reloadQuiz);

</script>

</body>
</html>
