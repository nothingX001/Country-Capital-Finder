<?php
// quiz.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';

// Optional: If you have a file listing countries that need a "the" prefix, include it.
// Otherwise, remove or ignore this line.
// Example: $the_countries = ["bahamas","gambia","philippines"];
include 'the-countries.php';

/**
 * A helper function to fetch up to $limit random countries by certain entity types,
 * then for each country, fetch capitals from the capitals table and attach them.
 */
function fetchQuizData(PDO $conn, array $entityTypes, int $limit = 10): array {
    // 1) Get up to $limit random countries with matching "Entity Type"
    //    (Only selecting id + "Country Name")
    $inList = "'" . implode("','", $entityTypes) . "'";
    $sql = "
        SELECT id, \"Country Name\" AS country_name
        FROM countries
        WHERE \"Entity Type\" IN ($inList)
        ORDER BY RANDOM()
        LIMIT $limit
    ";
    $rows = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // 2) For each country, fetch capitals from the capitals table
    foreach ($rows as &$row) {
        $capStmt = $conn->prepare('
            SELECT capital_name
            FROM capitals
            WHERE country_id = ?
        ');
        $capStmt->execute([$row['id']]);
        $capList = $capStmt->fetchAll(PDO::FETCH_COLUMN);
        // Attach the array of capital names to this row
        $row['capitals'] = $capList;
    }
    unset($row);

    return $rows;
}

try {
    // 1) Fetch up to 10 random "main" countries (UN member / observer)
    //    Adjust these strings if your CSV uses something else like "UN Member" or "Member State"
    $randomMain = fetchQuizData($conn, ['UN member', 'UN observer'], 10);

    // 2) Fetch up to 10 random "territories"
    $randomTerritories = fetchQuizData($conn, ['Territory'], 10);

} catch (Exception $e) {
    die("Error fetching quiz data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz | ExploreCapitals</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse our database of countries, territories, and more!">
    <link rel="stylesheet" href="styles.css">
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

        <div id="resultContainer" style="display:none;">
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

    // 2) "theCountries" array for adding "the" prefix if you want it
    const theCountries = <?php
        if (isset($the_countries) && is_array($the_countries)) {
            echo json_encode(array_map('strtolower', $the_countries));
        } else {
            echo '[]';
        }
    ?>;

    function addThe(country) {
        return theCountries.includes(country.toLowerCase()) ? `the ${country}` : country;
    }

    let questions = [];
    let currentQuestionIndex = 0;
    let score = 0;
    let timeElapsed = 0;
    let timer;
    let userResponses = [];

    // Called to start the quiz with the given data set
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

        document.querySelector('#main-quiz p').style.display = 'none';
        document.getElementById('quizContainer').style.display = 'block';
        document.getElementById('resultContainer').style.display = 'none';
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

    function showNextQuestion() {
        if (currentQuestionIndex < questions.length) {
            const qData = questions[currentQuestionIndex];
            // Randomly decide question type
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

    function normalizeInput(str) {
        let norm = str.toLowerCase().trim();
        norm = norm.replace(/^the\s+/, '');
        norm = norm.replace(/[^\w\s]/g, '');
        norm = norm.replace(/\s+/g, ' ');
        norm = norm.replace(/\bst\.?\b/gi, 'saint');
        return norm;
    }

    function checkAnswer(userAnswer, correctAnswers) {
        const userNorm = normalizeInput(userAnswer);
        return correctAnswers.some(ca => {
            const caNorm = normalizeInput(ca);
            return userNorm === caNorm;
        });
    }

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

    document.getElementById('answerForm').addEventListener('submit', e => {
        e.preventDefault();
        const userAnswer = document.getElementById('userAnswer').value.trim();
        const currentResp = userResponses[currentQuestionIndex];
        const isCorrect = checkAnswer(userAnswer, currentResp.correctAnswers);
        currentResp.userAnswer = userAnswer;
        currentResp.isCorrect  = isCorrect;
        if (isCorrect) score++;
        currentQuestionIndex++;
        showNextQuestion();
    });

    document.getElementById('redoQuizBtn').addEventListener('click', () => {
        location.reload();
    });

    // Start quiz buttons
    document.getElementById('startMainQuizBtn').addEventListener('click', () => {
        startQuiz(randomMain);
    });
    document.getElementById('startTerritoriesQuizBtn').addEventListener('click', () => {
        startQuiz(randomTerritories);
    });
    </script>
</body>
</html>
