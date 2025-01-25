<?php
// quiz.php
include 'config.php';
include 'the-countries.php'; // For the "the" countries array

// Fetch random countries + aggregated capitals
$data = json_decode(file_get_contents('http://localhost/fetch-country-data.php?type=random&limit=10'), true);
if (!$data || isset($data['error'])) {
    echo "Error fetching quiz data.";
    exit;
}

// Prepare quiz questions
$quizQuestions = [];
foreach ($data as $row) {
    $country_id    = $row['id'];
    $country_name  = $row['country_name'];
    $capitalsArray = $row['capitals'] ?? []; // array of capital_name strings

    // Only if there's at least one capital
    if (!empty($capitalsArray)) {
        $quizQuestions[] = [
            'country'  => $country_name,
            'capitals' => $capitalsArray,
        ];
    }
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

        <div id="quizContainer" style="display:none;">
            <div id="timer">Time: 0:00</div>
            <div id="questionContainer"></div>
            <form id="answerForm">
                <input type="text" id="userAnswer" placeholder="Type your answer here" required>
                <button type="submit">SUBMIT ANSWER</button>
            </form>
        </div>

        <div id="resultContainer" style="display:none;">
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

    function addThe(country) {
        return theCountries.includes(country.toLowerCase()) ? `the ${country}` : country;
    }

    function normalizeInput(input) {
        let norm = input.toLowerCase().trim();
        norm = norm.replace(/^the\s+/i, '');
        return norm;
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
            const q = questions[currentQuestionIndex];
            const isCountryQuestion = Math.random() > 0.5;

            let questionText;
            if (isCountryQuestion) {
                // "What is the capital of X?"
                questionText = `What is the capital of ${addThe(q.country)}?`;
                userResponses.push({
                    questionText,
                    correctAnswers: q.capitals,
                    userAnswer: "",
                    isCorrect: false,
                    correctAnswerText: q.capitals.join(' / ')
                });
            } else {
                // "X is the capital of which country?"
                const capCount = q.capitals.length;
                const capitalNames = q.capitals.join(' / ');
                const verb = capCount > 1 ? 'are' : 'is';
                questionText = `${capitalNames} ${verb} the capital${capCount>1 ? 's' : ''} of which country?`;
                userResponses.push({
                    questionText,
                    // The correct country is array, but we only have one real "country" name
                    correctAnswers: [q.country],
                    userAnswer: "",
                    isCorrect: false,
                    correctAnswerText: q.country
                });
            }

            document.getElementById('questionContainer').textContent = `Question ${currentQuestionIndex + 1}: ${questionText}`;
            document.getElementById('userAnswer').value = '';
        } else {
            endQuiz();
        }
    }

    function checkAnswer(userAnswer, correctAnswers) {
        const userNorm = normalizeInput(userAnswer);
        return correctAnswers.some(ca => {
            const variants = ca.split('/').map(s => normalizeInput(s.trim()));
            return variants.includes(userNorm);
        });
    }

    function endQuiz() {
        clearInterval(timer);
        document.getElementById('quizContainer').style.display = 'none';
        document.getElementById('resultContainer').style.display = 'block';
        document.getElementById('score').textContent = `You scored ${score} out of ${questions.length}.`;

        let resultsHTML = '';
        userResponses.forEach((resp, idx) => {
            const resultText = resp.isCorrect
              ? `Correct. The answer was ${resp.correctAnswerText}.`
              : `Incorrect. The answer was ${resp.correctAnswerText}. You answered "${resp.userAnswer}".`;
            resultsHTML += `
                <p>
                    <strong>Question ${idx+1}: ${resp.questionText}</strong><br>
                    ${resultText}
                </p>
            `;
        });
        document.getElementById('detailedResults').innerHTML = resultsHTML;
    }

    document.getElementById('answerForm').addEventListener('submit', e => {
        e.preventDefault();
        const userAnswer = document.getElementById('userAnswer').value.trim();
        const resp = userResponses[currentQuestionIndex];
        const isCorrect = checkAnswer(userAnswer, resp.correctAnswers);

        resp.userAnswer = userAnswer;
        resp.isCorrect = isCorrect;

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
