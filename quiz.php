<?php
// quiz.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

// Optional: Include "the-countries.php" if you still use that logic for "the" prefix
// If not needed, you can remove or comment this out.
// Example: $the_countries = ["bahamas", "gambia", "philippines", ...];
include 'the-countries.php';

// 1) Fetch random main countries (UN member/observer)
try {
    $stmtMain = $conn->query('
        SELECT c.id,
               c."Country Name" AS country_name,
               array_agg(cap.capital_name ORDER BY cap.capital_name) AS capitals
        FROM countries c
        JOIN capitals cap ON c.id = cap.country_id
        WHERE c."Entity Type" IN (\'UN member\', \'UN observer\')
        GROUP BY c.id
        ORDER BY RANDOM()
        LIMIT 10
    ');
    $randomMain = $stmtMain->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching main quiz data: " . $e->getMessage());
}

// 2) Fetch random territories
try {
    $stmtTerr = $conn->query('
        SELECT c.id,
               c."Country Name" AS country_name,
               array_agg(cap.capital_name ORDER BY cap.capital_name) AS capitals
        FROM countries c
        JOIN capitals cap ON c.id = cap.country_id
        WHERE c."Entity Type" = \'Territory\'
        GROUP BY c.id
        ORDER BY RANDOM()
        LIMIT 10
    ');
    $randomTerritories = $stmtTerr->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching territories quiz data: " . $e->getMessage());
}

// Convert to arrays of [ "country_name" => ..., "capitals" => [...], ... ]
// Already done via array_agg(...) above. Each row has country_name and capitals array.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz | ExploreCapitals</title>
    <link rel="stylesheet" href="styles.css"> <!-- Adjust if needed -->
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="page-content quiz" id="main-quiz">
        <h1>ExploreCapitals Quiz</h1>
        <p>Select a quiz type to begin.</p>

        <button id="startMainQuizBtn" class="button">COUNTRIES QUIZ</button>
        <button id="startTerritoriesQuizBtn" class="button">TERRITORIES QUIZ</button>

        <div id="quizContainer" style="display: none;">
            <div id="timer">Time: 0:00</div>
            <div id="questionContainer"></div>
            <form id="answerForm">
                <input type="text" id="userAnswer" placeholder="Type your answer here" required>
                <button type="submit" class="button">SUBMIT ANSWER</button>
            </form>
        </div>

        <div id="resultContainer" style="display: none;">
            <h2>Quiz Results</h2>
            <p id="score"></p>
            <div id="detailedResults"></div>
            <button id="redoQuizBtn" class="button">REDO QUIZ</button>
        </div>
    </section>

    <script>
    // 1) Convert the random data from PHP to JavaScript arrays
    //    Each element has { id, country_name, capitals: [ ... ] }
    const randomMain = <?php echo json_encode($randomMain); ?>;
    const randomTerritories = <?php echo json_encode($randomTerritories); ?>;

    // 2) If you still use a "the-countries.php" array to prepend "the" for certain countries
    //    define it here; otherwise, you can remove this entire logic
    const theCountries = <?php
        // If you do not use "the-countries.php", you can just echo "[]"
        if (isset($the_countries) && is_array($the_countries)) {
            echo json_encode(array_map('strtolower', $the_countries));
        } else {
            echo '[]';
        }
    ?>;

    function addThe(country) {
        // If "the-countries.php" is in use
        return theCountries.includes(country.toLowerCase()) ? `the ${country}` : country;
    }

    // 3) We'll store the current quiz data in a global variable
    let questions = [];
    let currentQuestionIndex = 0;
    let score = 0;
    let timeElapsed = 0;
    let timer;
    let userResponses = [];

    // Called to start the quiz with the given data set (randomMain or randomTerritories)
    function startQuiz(dataArray) {
        // Prepare the data
        questions = [];
        dataArray.forEach(row => {
            // row.country_name, row.capitals (array)
            if (Array.isArray(row.capitals) && row.capitals.length > 0) {
                // Some countries may have multiple capitals
                questions.push({
                    country: row.country_name,
                    capitals: row.capitals
                });
            }
        });

        // If no valid entries, show an alert
        if (questions.length === 0) {
            alert('No valid quiz data found.');
            return;
        }

        // Hide the initial "Select a quiz type" text
        document.querySelector('#main-quiz p').style.display = 'none';
        // Show the quiz container
        document.getElementById('quizContainer').style.display = 'block';
        // Hide the result container
        document.getElementById('resultContainer').style.display = 'none';
        // Hide the start buttons
        document.getElementById('startMainQuizBtn').style.display = 'none';
        document.getElementById('startTerritoriesQuizBtn').style.display = 'none';

        // Reset quiz state
        score = 0;
        timeElapsed = 0;
        currentQuestionIndex = 0;
        userResponses = [];

        startTimer();
        showNextQuestion();
    }

    // Start the timer
    function startTimer() {
        clearInterval(timer);
        timeElapsed = 0;
        document.getElementById('timer').textContent = 'Time: 0:00';
        timer = setInterval(() => {
            timeElapsed++;
            const mins = Math.floor(timeElapsed / 60);
            const secs = timeElapsed % 60;
            document.getElementById('timer').textContent =
                `Time: ${mins}:${secs < 10 ? '0' : ''}${secs}`;
        }, 1000);
    }

    // Show the next question
    function showNextQuestion() {
        if (currentQuestionIndex < questions.length) {
            const qData = questions[currentQuestionIndex];
            // Randomly decide if we ask "What is the capital of X?" or "X is the capital of which country?"
            const isCountryQuestion = Math.random() > 0.5;

            let questionText;
            if (isCountryQuestion) {
                questionText = `What is the capital of <strong>${addThe(qData.country)}</strong>?`;
                userResponses.push({
                    questionText,
                    correctAnswers: qData.capitals,
                    userAnswer: "",
                    isCorrect: false,
                    correctAnswerText: qData.capitals.join(' / ')
                });
            } else {
                const capCount = qData.capitals.length;
                const capitalStr = qData.capitals.map(c => `<strong>${c}</strong>`).join(' / ');
                const verb = capCount > 1 ? 'are' : 'is';
                questionText = `${capitalStr} ${verb} the capital${capCount > 1 ? 's' : ''} of which country?`;
                userResponses.push({
                    questionText,
                    // We'll store just one correct answer if you want
                    // but if multiple synonyms exist, you could store them
                    correctAnswers: [qData.country],
                    userAnswer: "",
                    isCorrect: false,
                    correctAnswerText: qData.country
                });
            }

            document.getElementById('questionContainer').innerHTML =
                `<p>Question ${currentQuestionIndex + 1}:<br>${questionText}</p>`;
            document.getElementById('userAnswer').value = '';
        } else {
            endQuiz();
        }
    }

    // Check if user input matches one of the correct answers
    function checkAnswer(userAnswer, correctAnswers) {
        const userNorm = normalizeInput(userAnswer);
        // For each correct answer, we can check multiple variants
        return correctAnswers.some(ca => {
            // If you have synonyms, you can do more logic here
            const caNorm = normalizeInput(ca);
            return userNorm === caNorm;
        });
    }

    // Simple normalization: lowercase, remove punctuation, etc.
    function normalizeInput(str) {
        let norm = str.toLowerCase().trim();
        norm = norm.replace(/^the\s+/i, '');         // remove leading "the"
        norm = norm.replace(/[^\w\s]/g, '');        // remove punctuation
        norm = norm.replace(/\s+/g, ' ');           // collapse extra spaces
        norm = norm.replace(/\bst\.?\b/gi, 'saint');// handle "St." => "saint"
        return norm;
    }

    // Called when we run out of questions
    function endQuiz() {
        clearInterval(timer);
        document.getElementById('quizContainer').style.display = 'none';
        document.getElementById('resultContainer').style.display = 'block';
        document.getElementById('score').textContent =
            `You scored ${score} out of ${questions.length}.`;

        let detailHTML = '';
        userResponses.forEach((resp, idx) => {
            const correctAnswerText = `<strong>${resp.correctAnswerText}</strong>`;
            const userAnswerText = resp.userAnswer ? `<strong>${resp.userAnswer}</strong>` : '""';
            const resultText = resp.isCorrect
                ? `Correct. The answer was ${correctAnswerText}.`
                : `Incorrect. The answer was ${correctAnswerText}. You answered ${userAnswerText}.`;
            detailHTML += `
                <p class="${resp.isCorrect ? 'correct' : 'incorrect'}">
                    <strong>Question ${idx + 1}:</strong> ${resp.questionText}<br>
                    ${resultText}
                </p>
            `;
        });
        document.getElementById('detailedResults').innerHTML = detailHTML;
    }

    // Event listeners
    document.getElementById('answerForm').addEventListener('submit', e => {
        e.preventDefault();
        const userAnswer = document.getElementById('userAnswer').value.trim();
        const currentResp = userResponses[currentQuestionIndex];
        const isCorrect = checkAnswer(userAnswer, currentResp.correctAnswers);
        currentResp.userAnswer = userAnswer;
        currentResp.isCorrect = isCorrect;
        if (isCorrect) score++;
        currentQuestionIndex++;
        showNextQuestion();
    });

    document.getElementById('redoQuizBtn').addEventListener('click', () => {
        location.reload();
    });

    document.getElementById('startMainQuizBtn').addEventListener('click', () => {
        startQuiz(randomMain);
    });
    document.getElementById('startTerritoriesQuizBtn').addEventListener('click', () => {
        startQuiz(randomTerritories);
    });
    </script>
</body>
</html>
