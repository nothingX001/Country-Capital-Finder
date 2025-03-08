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
    // 1) Get up to $limit random countries with matching "Entity Type" that have capitals
    //    (Only selecting id + "Country Name" + "Flag Emoji")
    $inList = "'" . implode("','", $entityTypes) . "'";
    $sql = "
        SELECT DISTINCT c.id, c.\"Country Name\" AS country_name, c.\"Flag Emoji\" AS flag_emoji, RANDOM() as rand
        FROM countries c
        INNER JOIN capitals cap ON c.id = cap.country_id
        WHERE c.\"Entity Type\" IN ($inList)
        ORDER BY rand
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

// Helper function to format country name in sentence with proper "the" prefix
function format_country_name_in_sentence($country_name, $the_countries) {
    $country_lower = strtolower($country_name);
    $needs_the = in_array($country_lower, $the_countries);
    return $needs_the ? "the " . $country_name : $country_name;
}

try {
    // 1) Fetch up to 10 random "main" countries (UN member / observer)
    //    Adjust these strings if your CSV uses something else like "UN Member" or "Member State"
    $randomMain = fetchQuizData($conn, ['UN member', 'UN observer'], 10);

    // 2) Fetch up to 10 random "territories"
    $randomTerritories = fetchQuizData($conn, ['Territory'], 10);

    // Get a random country and its capital
    $stmt = $conn->query('
        SELECT 
            c.id,
            c."Country Name" AS country_name,
            cap.capital_name
        FROM countries c
        JOIN capitals cap ON c.id = cap.country_id
        WHERE c."Entity Type" IN (\'UN member\', \'UN observer\')
        ORDER BY RANDOM()
        LIMIT 1
    ');
    $country = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get 3 random incorrect capitals
    $stmt = $conn->prepare('
        WITH RandomCapitals AS (
            SELECT DISTINCT capital_name, RANDOM() as rand
            FROM capitals
            WHERE country_id != ?
        )
        SELECT capital_name
        FROM RandomCapitals
        ORDER BY rand
        LIMIT 3
    ');
    $stmt->execute([$country['id']]);
    $incorrect_capitals = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Combine correct and incorrect answers
    $options = array_merge([$country['capital_name']], $incorrect_capitals);
    // Shuffle the options
    shuffle($options);

    // Format the country name with "the" if needed
    $formatted_country_name = format_country_name_in_sentence($country['country_name'], $the_countries);

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
        <h1 id="quizTitle">ExploreCapitals Quiz</h1>
        <p>Select a quiz type to begin.</p>

        <button id="startMainQuizBtn" class="button">COUNTRIES QUIZ</button>
        <button id="startTerritoriesQuizBtn" class="button">TERRITORIES QUIZ</button>

        <div id="quizContainer" style="display: none;">
            <div id="timer">Time: 0:00</div>
            <div id="questionContainer">
                <p>What is the capital of <?php echo htmlspecialchars($formatted_country_name); ?>?</p>
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

        <div id="resultContainer" style="display: none;">
            <h2>Quiz Results</h2>
            <p id="score"></p>
            <div id="detailedResults"></div>
            <button id="redoQuizBtn" class="button">TAKE QUIZ AGAIN</button>
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
            return `<strong>${capitals[0]}</strong>`;
        } else if (capitals.length === 2) {
            return `<strong>${capitals[0]}</strong> or <strong>${capitals[1]}</strong>`;
        } else {
            const lastCapital = capitals[capitals.length - 1];
            const otherCapitals = capitals.slice(0, -1).map(cap => `<strong>${cap}</strong>`);
            return `${otherCapitals.join(', ')} or <strong>${lastCapital}</strong>`;
        }
    }

    // 6) Helper function to format country name with flag
    function formatCountryName(country) {
        return country.country_name;
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
            if (Array.isArray(row.capitals) && row.capitals.length > 0) {
                questions.push({
                    country_name: row.country_name,
                    flag_emoji: row.flag_emoji,
                    capitals: row.capitals,
                    id: row.id
                });
            }
        });

        // If no valid entries, show an alert
        if (questions.length === 0) {
            alert('No valid quiz data found.');
            return;
        }

        // Update the quiz title based on quiz type
        document.getElementById('quizTitle').textContent = 
            quizType === 'main' ? 'Countries Quiz' : 'Territories Quiz';

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
                questionText = `What is the capital of <strong>${qData.country_name}</strong>?`;
                userResponses.push({
                    questionText,
                    correctAnswers: qData.capitals,
                    userAnswer: "",
                    isCorrect: false,
                    correctAnswerText: formatCapitals(qData.capitals),
                    countryName: qData.country_name,
                    flagEmoji: qData.flag_emoji,
                    id: qData.id
                });
            } else {
                const capitalStr = `<strong>${qData.capitals[0]}</strong>`;
                let placeLabel = (quizType === 'territory') ? 'territory' : 'country';
                questionText = `${capitalStr} is the capital of which ${placeLabel}?`;
                userResponses.push({
                    questionText,
                    correctAnswers: [qData.country_name],
                    userAnswer: "",
                    isCorrect: false,
                    correctAnswerText: `<strong>${qData.country_name}</strong>`,
                    countryName: qData.country_name,
                    flagEmoji: qData.flag_emoji,
                    id: qData.id
                });
            }

            document.getElementById('questionContainer').innerHTML =
                `<p>Question ${currentQuestionIndex + 1}:<br>${questionText}</p>`;
            document.getElementById('userAnswer').value = '';
        } else {
            endQuiz();
        }
    }

    function endQuiz() {
        clearInterval(timer);
        document.getElementById('quizContainer').style.display = 'none';
        document.getElementById('resultContainer').style.display = 'block';
        document.getElementById('score').textContent =
            `You scored ${score} out of ${questions.length}.`;

        let detailHTML = '';
        userResponses.forEach((resp, idx) => {
            // Create country link with normal text styling
            const countryLink = `<a href="country-detail.php?id=${resp.id}" class="quiz-link"><strong>${resp.countryName}</strong></a>`;
            
            // Create properly formatted capital links
            const capitalLinks = resp.correctAnswers.map(capital => 
                `<a href="country-detail.php?id=${resp.id}" class="quiz-link"><strong>${capital}</strong></a>`
            );
            
            // Format multiple capitals properly
            const correctAnswerText = capitalLinks.length === 1 
                ? capitalLinks[0]
                : capitalLinks.slice(0, -1).join(', ') + ' or ' + capitalLinks[capitalLinks.length - 1];
            
            const userAnswerText = resp.userAnswer ? `<strong>${resp.userAnswer}</strong>` : '""';
            const resultText = resp.isCorrect
                ? `Correct. The answer was ${correctAnswerText}. <span class="flag-emoji">${resp.flagEmoji}</span>`
                : `Incorrect. The answer was ${correctAnswerText}. <span class="flag-emoji">${resp.flagEmoji}</span> You answered ${userAnswerText}.`;

            // Replace the country name in the question with a styled link
            const questionTextWithLink = resp.questionText.replace(
                new RegExp(`<strong>${resp.countryName}</strong>`),
                `<a href="country-detail.php?id=${resp.id}" class="quiz-link"><strong>${resp.countryName}</strong></a>`
            );

            detailHTML += `
                <p class="${resp.isCorrect ? 'correct' : 'incorrect'}">
                    <strong>Question ${idx + 1}:</strong> ${questionTextWithLink}<br>
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

    // Add this function to handle scrolling to top when restarting quiz
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Add click handlers to quiz restart buttons
    document.addEventListener('DOMContentLoaded', function() {
        const restartButtons = document.querySelectorAll('.restart-quiz-btn');
        restartButtons.forEach(button => {
            button.addEventListener('click', function() {
                scrollToTop();
            });
        });
    });
    </script>
</body>
</html>
