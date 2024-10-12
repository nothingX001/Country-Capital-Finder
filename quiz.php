<?php
// Include database connection
include 'config.php';

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

// Fetch the questions
$quizQuestions = getQuizQuestions($conn);
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

<script>
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
        const questionType = Math.random() > 0.5 ? 'country' : 'capital';
        const questionText = questionType === 'country' 
            ? `What is the capital of "${questionData.country_name}"?`
            : `Of which country is "${questionData.capital_name}" the capital?`;

        document.getElementById('questionContainer').textContent = `Question ${currentQuestionIndex + 1}: ${questionText}`;
        document.getElementById('userAnswer').value = '';
    } else {
        endQuiz();
    }
}

// Function to end the quiz
function endQuiz() {
    clearInterval(timer);
    document.getElementById('quizContainer').style.display = 'none';
    document.getElementById('resultContainer').style.display = 'block';
    document.getElementById('score').textContent = `You scored ${score} out of ${questions.length}`;

    // Generate detailed results
    let resultsHTML = '';
    userResponses.forEach((response, index) => {
        const resultText = response.isCorrect ? "Correct" : "Incorrect";
        resultsHTML += `<p>Question ${index + 1}: ${resultText}. The answer was "${response.correctAnswer}". You put "${response.userAnswer}".</p>`;
    });
    document.getElementById('detailedResults').innerHTML = resultsHTML;
}

// Function to handle answer submission
document.getElementById('answerForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const userAnswer = document.getElementById('userAnswer').value.trim().toLowerCase();
    const questionData = questions[currentQuestionIndex];
    const isCountryQuestion = Math.random() > 0.5;
    const correctAnswer = isCountryQuestion 
        ? questionData.capital_name.toLowerCase()
        : questionData.country_name.toLowerCase();

    // Check answer (case-insensitive)
    const isCorrect = userAnswer === correctAnswer;
    if (isCorrect) {
        score++;
    }

    // Record user response
    userResponses.push({
        isCorrect: isCorrect,
        correctAnswer: isCountryQuestion ? questionData.capital_name : questionData.country_name,
        userAnswer: userAnswer
    });

    currentQuestionIndex++;
    showNextQuestion();
});

// Event listener to start the quiz
document.getElementById('startQuizBtn').addEventListener('click', startQuiz);

// Event listener for "Redo Quiz" button
document.getElementById('redoQuizBtn').addEventListener('click', () => {
    document.getElementById('resultContainer').style.display = 'none';
    document.getElementById('startQuizBtn').style.display = 'block';
});

</script>

</body>
</html>
