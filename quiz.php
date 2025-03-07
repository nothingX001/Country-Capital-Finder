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
    //    (Only selecting id + "Country Name" + "Flag Emoji")
    $inList = "'" . implode("','", $entityTypes) . "'";
    $sql = "
        SELECT id, \"Country Name\" AS country_name, \"Flag Emoji\" AS flag_emoji
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
    <link rel="icon" type="image/jpeg" href="images/explore-capitals-logo.jpg">
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
            <div id="questionContainer">
                <p>What is the capital of <?php echo htmlspecialchars($country['country_name']); ?>?</p>
                <p>Choose from the following options:</p>
                <div class="options">
                    <?php foreach ($options as $option): ?>
                        <button class="option-button" onclick="checkAnswer('<?php echo htmlspecialchars($option); ?>')">
                            <?php echo htmlspecialchars($option); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <form id="answerForm">
                <input type="text" id="userAnswer" placeholder="Type your answer here" required autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
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
    // Declare a global variable for the quiz type.
    let quizType = 'main';

    // 1) Convert the random data from PHP to JavaScript arrays
    //    Each element has { id, country_name, capitals: [ ... ] }
    const randomMain = <?php echo json_encode($randomMain); ?>;
    const randomTerritories = <?php echo json_encode($randomTerritories); ?>;

    // 2) "theCountries" array for adding "the" prefix if you want it
    const theCountries = <?php echo json_encode($the_countries); ?>;

    // 3) Helper function to normalize input (remove accents, etc.)
    function normalizeInput(str) {
        let norm = str.toLowerCase().trim();
        // Remove "the" from beginning of string
        norm = norm.replace(/^the\s+/, '');
        // Remove special characters except letters and spaces
        norm = norm.replace(/[^\w\s]/g, '');
        // Normalize whitespace
        norm = norm.replace(/\s+/g, ' ');
        // Replace "st." or "st" with "saint"
        norm = norm.replace(/\bst\.?\b/gi, 'saint');
        // Remove all diacritics/accents
        norm = norm.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        return norm;
    }

    // 4) Helper function to check if answer matches any of the capitals
    function checkAnswer(userAnswer, capitals) {
        const normalizedUserAnswer = normalizeInput(userAnswer);
        return capitals.some(capital => 
            normalizeInput(capital) === normalizedUserAnswer
        );
    }

    // 5) Helper function to format capitals for display
    function formatCapitals(capitals) {
        if (capitals.length === 1) {
            return capitals[0];
        } else if (capitals.length === 2) {
            return `${capitals[0]} or ${capitals[1]}`;
        } else {
            const lastCapital = capitals[capitals.length - 1];
            const otherCapitals = capitals.slice(0, -1);
            return `${otherCapitals.join(', ')} or ${lastCapital}`;
        }
    }

    // 6) Helper function to format country name with flag
    function formatCountryName(country) {
        return `${country.country_name} <span class="flag-emoji">${country.flag_emoji}</span>`;
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
                    country_name: row.country_name,
                    flag_emoji: row.flag_emoji,
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
                // For country questions, randomly select one capital if there are multiple
                const randomCapital = qData.capitals[Math.floor(Math.random() * qData.capitals.length)];
                questionText = `What is the capital of <strong>${qData.country_name}</strong> <span class="flag-emoji">${qData.flag_emoji}</span>?`;
                userResponses.push({
                    questionText,
                    correctAnswers: qData.capitals, // Keep all capitals as correct answers
                    userAnswer: "",
                    isCorrect: false,
                    correctAnswerText: formatCapitals(qData.capitals), // Use formatCapitals instead of join
                    countryName: qData.country_name,
                    flagEmoji: qData.flag_emoji
                });
            } else {
                const capitalStr = `<strong>${qData.capitals[0]}</strong>`; // Use only first capital for this question type
                // Use "territory" if the quiz type is set to territory, otherwise "country"
                let placeLabel = (quizType === 'territory') ? 'territory' : 'country';
                questionText = `${capitalStr} is the capital of which ${placeLabel}?`;
                userResponses.push({
                    questionText,
                    correctAnswers: [qData.country_name],
                    userAnswer: "",
                    isCorrect: false,
                    correctAnswerText: qData.country_name,
                    countryName: qData.country_name,
                    flagEmoji: qData.flag_emoji
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
        // Remove "the" from beginning of string
        norm = norm.replace(/^the\s+/, '');
        // Remove special characters except letters and spaces
        norm = norm.replace(/[^\w\s]/g, '');
        // Normalize whitespace
        norm = norm.replace(/\s+/g, ' ');
        // Replace "st." or "st" with "saint"
        norm = norm.replace(/\bst\.?\b/gi, 'saint');
        // Remove all diacritics/accents
        norm = norm.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
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
            const correctAnswerText = `<strong>${resp.correctAnswerText}</strong> <span class="flag-emoji">${resp.flagEmoji}</span>`;
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
        quizType = 'main';
        startQuiz(randomMain);
    });
    document.getElementById('startTerritoriesQuizBtn').addEventListener('click', () => {
        quizType = 'territory';
        startQuiz(randomTerritories);
    });
    </script>
</body>
</html>
