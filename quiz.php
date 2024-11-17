<?php
include 'config.php';
include 'fetch-country-data.php';
include 'the-countries.php';

// Fetch 10 random country-capital pairs
$data = json_decode(file_get_contents('fetch-country-data.php?type=random&limit=10'), true);

if (!$data) {
    echo "Error fetching quiz data.";
    exit;
}

// Normalize function to handle case-insensitive matches and "the" countries
function normalizeInput($input) {
    global $the_countries;
    $input = strtolower(trim($input));
    $input = preg_replace('/^the\s+/i', '', $input); // Remove "the" if present
    return $input;
}

// Prepare quiz questions
$quizQuestions = [];
foreach ($data as $row) {
    $quizQuestions[] = [
        'country' => $row['country_name'],
        'capital' => $row['capital_name'],
    ];
}
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
        const questions = <?php echo json_encode($quizQuestions); ?>;
        const theCountries = <?php echo json_encode(array_map('strtolower', $the_countries)); ?>;

        let currentQuestionIndex = 0;
        let score = 0;
        let timeElapsed = 0;
        let timer;

        // Function to add "the" for countries that require it
        function addThe(country) {
            return theCountries.includes(country.toLowerCase()) ? `the ${country}` : country;
        }

        // Normalize function to handle case-insensitive matching
        function normalizeInput(input) {
            let normalized = input.toLowerCase().trim();
            normalized = normalized.replace(/^the\s+/i, ''); // Remove "the" if present
            return normalized;
        }

        function startQuiz() {
            document.getElementById('startQuizBtn').style.display = 'none';
            document.getElementById('resultContainer').style.display = 'none';
            document.getElementById('quizContainer').style.display = 'block';
            score = 0;
            timeElapsed = 0;
            currentQuestionIndex = 0;
            startTimer();
            showNextQuestion();
        }

        function startTimer() {
            timer = setInterval(() => {
                timeElapsed++;
                const minutes = Math.floor(timeElapsed / 60);
                const seconds = timeElapsed % 60;
                document.getElementById('timer').textContent = `Time: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            }, 1000);
        }

        function showNextQuestion() {
            if (currentQuestionIndex < questions.length) {
                const questionData = questions[currentQuestionIndex];
                const isCountryQuestion = Math.random() > 0.5;
                const questionText = isCountryQuestion 
                    ? `What is the capital of ${addThe(questionData.country)}?`
                    : `${addThe(questionData.capital)} is the capital of which country?`;

                document.getElementById('questionContainer').textContent = `Question ${currentQuestionIndex + 1}: ${questionText}`;
                document.getElementById('userAnswer').value = '';
            } else {
                endQuiz();
            }
        }

        function checkAnswer(userAnswer, correctAnswer) {
            const normalizedAnswer = normalizeInput(userAnswer);
            const correctOptions = correctAnswer.toLowerCase().split('/').map(option => normalizeInput(option.trim()));
            return correctOptions.includes(normalizedAnswer);
        }

        function endQuiz() {
            clearInterval(timer);
            document.getElementById('quizContainer').style.display = 'none';
            document.getElementById('resultContainer').style.display = 'block';
            document.getElementById('score').textContent = `You scored ${score} out of ${questions.length}.`;
        }

        document.getElementById('answerForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const userAnswer = document.getElementById('userAnswer').value.trim();
            const questionData = questions[currentQuestionIndex];
            const isCorrect = currentQuestionIndex % 2 === 0 
                ? checkAnswer(userAnswer, questionData.capital) 
                : checkAnswer(userAnswer, questionData.country);

            if (isCorrect) {
                score++;
            }

            currentQuestionIndex++;
            showNextQuestion();
        });

        document.getElementById('startQuizBtn').addEventListener('click', startQuiz);
        document.getElementById('redoQuizBtn').addEventListener('click', () => location.reload());
    </script>
</body>
</html>
