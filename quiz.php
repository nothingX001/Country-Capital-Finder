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

// Merge alias arrays into one for easier JavaScript use
$alias_map = array_merge($country_aliases, $capital_aliases);

// List of countries that typically start with "the"
$the_countries = ["United States", "United Kingdom", "Netherlands", "Philippines", "Bahamas", "Gambia", "Czech Republic", "United Arab Emirates", "Central African Republic", "Republic of the Congo", "Democratic Republic of the Congo", "Maldives", "Marshall Islands", "Seychelles", "Solomon Islands", "Comoros"];

// Normalize user input to account for "the" and aliases
function normalizeInput($input) {
    global $alias_map;
    $lowerInput = strtolower(trim($input));

    // Remove "the" at the start of the input if it exists
    if (strpos($lowerInput, "the ") === 0) {
        $lowerInput = substr($lowerInput, 4);
    }
    return $alias_map[$lowerInput] ?? $lowerInput;  // Use alias if available, else return input
}

// Format country names with "the" for specific countries in questions and results
function formatCountryName($country) {
    global $the_countries;
    return in_array($country, $the_countries) ? "the $country" : $country;
}

// Function to generate question text with formatted country name
function getQuestionText($country, $capital, $isCountryQuestion) {
    $formattedCountry = formatCountryName($country);
    if ($isCountryQuestion) {
        return "What is the capital of $formattedCountry?";
    } else {
        return "Of which country is $capital the capital?";
    }
}

// Handle form submission and check answers
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $country_input = $_POST['country'];
    $country = normalizeInput($country_input);

    $stmt = $conn->prepare("SELECT id, capital_name FROM countries WHERE LOWER(country_name) = LOWER(?)");
    $stmt->bind_param("s", $country);
    $stmt->execute();
    $stmt->bind_result($country_id, $capital);
    $stmt->fetch();
    $stmt->close();

    if ($capital) {
        $flag_emoji = get_flag_emoji($country);
        $country_name_with_the = formatCountryName($country);
        $message = "The capital of $country_name_with_the is $capital. $flag_emoji";

        // Update search tracking (omitted here for brevity)
    } else {
        $message = "Sorry, the country you entered is not in our list.";
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
const aliasMap = <?php echo json_encode(array_change_key_case($alias_map, CASE_LOWER)); ?>;
const theCountries = <?php echo json_encode($the_countries); ?>;

// Normalize user input by handling "the" and checking aliases
function normalizeInput(input) {
    let lowerInput = input.toLowerCase().trim();
    if (lowerInput.startsWith("the ")) {
        lowerInput = lowerInput.slice(4);
    }
    return aliasMap[lowerInput] || lowerInput;
}

// Add "the" in the question if needed
function formatCountryName(country) {
    return theCountries.includes(country) ? `the ${country}` : country;
}

// Initialize variables for the quiz
let questions = <?php echo json_encode($quizQuestions); ?>;
let currentQuestionIndex = 0;
let score = 0;
let timer;
let timeElapsed = 0;
let userResponses = [];

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
            ? `What is the capital of ${formatCountryName(questionData.country_name)}?`
            : `Of which country is ${questionData.capital_name} the capital?`;

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

// Check the user's answer
function checkAnswer(userAnswer, correctAnswer) {
    const normalizedAnswer = normalizeInput(userAnswer);
    const correctOptions = correctAnswer.toLowerCase().split('/').map(option => normalizeInput(option.trim()));
    return correctOptions.includes(normalizedAnswer);
}

// End the quiz
function endQuiz() {
    clearInterval(timer);
    document.getElementById('quizContainer').style.display = 'none';
    document.getElementById('resultContainer').style.display = 'block';
    document.getElementById('score').textContent = `You scored ${score} out of ${questions.length}.`;

    let resultsHTML = '';
    userResponses.forEach((response, index) => {
        const resultText = response.isCorrect 
            ? `Correct. The answer was ${response.correctAnswer}.` 
            : `Incorrect. The answer was ${response.correctAnswer}. You put "${response.userAnswer}".`;

        resultsHTML += `<p><strong>Question ${index + 1}: ${response.questionText}</strong><br>${resultText}</p>`;
    });
    document.getElementById('detailedResults').innerHTML = resultsHTML;
}

// Submit answer
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

// Reload the quiz with new questions
function reloadQuiz() {
    location.reload();
}

// Start quiz event
document.getElementById('startQuizBtn').addEventListener('click', startQuiz);

// Redo quiz event
document.getElementById('redoQuizBtn').addEventListener('click', reloadQuiz);

</script>

</body>
</html>
