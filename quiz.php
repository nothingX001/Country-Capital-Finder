<?php
include 'config.php';
include 'the-countries.php'; // Contains normalizeInput()

// Fetch 10 random country IDs
$data = json_decode(file_get_contents('http://localhost/fetch-country-data.php?type=random&limit=10'), true);

if (!$data || isset($data['error'])) {
    echo "Error fetching quiz data.";
    exit;
}

// Prepare quiz questions
$quizQuestions = [];
foreach ($data as $row) {
    $country_id = $row['id'];
    $country_name = $row['country_name'];

    // Fetch all capitals for the country
    $stmt = $conn->prepare("SELECT capital_name FROM capitals WHERE country_id = ?");
    $stmt->execute([$country_id]);
    $capitals = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($capitals) {
        $quizQuestions[] = [
            'country' => $country_name,
            'capitals' => $capitals,
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- [Meta tags and stylesheets as before] -->
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
        let userResponses = [];

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
            userResponses = [];
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
                const capitalNames = questionData.capitals.join(' / ');
                const capitalCount = questionData.capitals.length;
                const capitalWord = capitalCount > 1 ? 'capitals' : 'capital';
                const verb = capitalCount > 1 ? 'are' : 'is';

                const questionText = isCountryQuestion 
                    ? `What ${capitalWord} ${verb} of ${addThe(questionData.country)}?`
                    : `The ${capitalWord} of ${addThe(questionData.country)} ${verb} ${capitalNames}. What is the country?`;

                userResponses.push({
                    questionText,
                    correctAnswers: isCountryQuestion ? questionData.capitals : [questionData.country],
                    userAnswer: "",
                    isCorrect: false
                });

                document.getElementById('questionContainer').textContent = `Question ${currentQuestionIndex + 1}: ${questionText}`;
                document.getElementById('userAnswer').value = '';
            } else {
                endQuiz();
            }
        }

        function checkAnswer(userAnswer, correctAnswers) {
            const normalizedAnswer = normalizeInput(userAnswer);
            return correctAnswers.some(correctAnswer => {
                const correctOptions = correctAnswer.toLowerCase().split('/').map(option => normalizeInput(option.trim()));
                return correctOptions.includes(normalizedAnswer);
            });
        }

        function endQuiz() {
            clearInterval(timer);
            document.getElementById('quizContainer').style.display = 'none';
            document.getElementById('resultContainer').style.display = 'block';
            document.getElementById('score').textContent = `You scored ${score} out of ${questions.length}.`;

            // Display detailed results
            let resultsHTML = '';
            userResponses.forEach((response, index) => {
                const correctAnswerText = response.correctAnswers.join(' / ');
                const resultText = response.isCorrect 
                    ? `Correct. The answer was ${addThe(correctAnswerText)}.`
                    : `Incorrect. The answer was ${addThe(correctAnswerText)}. You answered "${response.userAnswer}".`;

                resultsHTML += `
                    <p><strong>Question ${index + 1}: ${response.questionText}</strong><br>${resultText}</p>
                `;
            });
            document.getElementById('detailedResults').innerHTML = resultsHTML;
        }

        document.getElementById('answerForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const userAnswer = document.getElementById('userAnswer').value.trim();
            const response = userResponses[currentQuestionIndex];
            const isCorrect = checkAnswer(userAnswer, response.correctAnswers);

            response.userAnswer = userAnswer;
            response.isCorrect = isCorrect;

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
