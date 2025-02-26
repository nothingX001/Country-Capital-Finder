<?php
// quiz.php
include 'config.php';
include 'the-countries.php'; // For "the" prefix logic
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Country Capital Quiz</title>
    <link rel="stylesheet" href="styles.css"> <!-- Only the single stylesheet -->
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- .page-content + .quiz -->
    <section class="page-content quiz" id="main-quiz">
        <h1>EXPLORE CAPITALS QUIZ</h1>
        <p>Select a quiz type to begin.</p>

        <button id="startMainQuizBtn" class="button">Countries Quiz</button>
        <button id="startTerritoriesQuizBtn" class="button">Territories Quiz</button>

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
    // We'll store quiz data here
    let questions = [];
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

        // Remove leading/trailing whitespace, punctuation, etc.
        norm = norm.replace(/^the\s+/i, ''); // Remove leading "the"
        norm = norm.replace(/[^\w\s]/g, ''); // Remove punctuation (including quotes)
        norm = norm.replace(/\s+/g, ' ');    // Normalize spaces

        // Handle abbreviations (e.g., "St. George" -> "saint george")
        norm = norm.replace(/\bst\.?\b/gi, 'saint');

        return norm;
    }

    // Called when user clicks a quiz start button
    function startQuiz(quizType) {
        fetch(`fetch-country-data.php?type=${quizType}&limit=10`)
            .then(async response => {
                if (!response.ok) {
                    let text = await response.text();
                    console.error('Response not OK:', response.status, text);
                    alert('Unable to load quiz data.');
                    return;
                }
                let data = await response.json();
                if (!data || data.error) {
                    alert('Unable to load quiz data.');
                    return;
                }
                if (!Array.isArray(data) || data.length === 0) {
                    alert('No quiz data found.');
                    return;
                }
                prepareQuestions(data);
            })
            .catch(err => {
                console.error('Fetch error:', err);
                alert('Unable to load quiz data.');
            });
    }

    function prepareQuestions(data) {
        questions = [];
        data.forEach(row => {
            if (row.capitals && row.capitals.length > 0) {
                // Ensure "Washington, D.C." is treated as one capital
                const capitals = row.capitals.map(capital => capital.replace(/\s*\/\s*/, ', '));
                questions.push({
                    country: row.country_name,
                    capitals: capitals
                });
            }
        });

        if (questions.length === 0) {
            alert('No valid quiz entries (no capitals).');
            return;
        }

        // Hide the "Select a quiz type to begin." text
        document.querySelector('#main-quiz p').style.display = 'none';

        // Show quiz container
        document.getElementById('quizContainer').style.display = 'block';
        document.getElementById('resultContainer').style.display = 'none';
        document.getElementById('startMainQuizBtn').style.display = 'none';
        document.getElementById('startTerritoriesQuizBtn').style.display = 'none';

        // Reset everything
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
            const isCountryQuestion = Math.random() > 0.5;

            let questionText;
            if (isCountryQuestion) {
                questionText = `What is the capital of <strong>${addThe(qData.country)}</strong>?`;
                userResponses.push({
                    questionText,
                    correctAnswers: qData.capitals,
                    userAnswer: "",
                    isCorrect: false,
                    correctAnswerText: qData.capitals.join(' / ') // No quotes here
                });
            } else {
                const capCount = qData.capitals.length;
                const capStr = qData.capitals.map(cap => `<strong>${cap}</strong>`).join(' / ');
                const verb = capCount > 1 ? 'are' : 'is';
                questionText = `${capStr} ${verb} the capital${capCount > 1 ? 's' : ''} of which country?`;
                userResponses.push({
                    questionText,
                    correctAnswers: [qData.country],
                    userAnswer: "",
                    isCorrect: false,
                    correctAnswerText: qData.country // No quotes here
                });
            }

            document.getElementById('questionContainer').innerHTML =
                `<p>Question ${currentQuestionIndex + 1}:<br>${questionText}</p>`;
            document.getElementById('userAnswer').value = '';
        } else {
            endQuiz();
        }
    }

    function checkAnswer(userAnswer, correctAnswers) {
        const userNorm = normalizeInput(userAnswer);
        return correctAnswers.some(ca => {
            const variants = ca.toLowerCase().split('/').map(v => normalizeInput(v.trim()));
            return variants.includes(userNorm);
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
            const correctAnswerText = `<strong>${resp.correctAnswerText.replace(/"/g, '')}</strong>`; // Remove quotes
            const userAnswerText = resp.userAnswer ? `<strong>${resp.userAnswer.replace(/"/g, '')}</strong>` : '""'; // Remove quotes

            const resultText = resp.isCorrect
                ? `Correct. The answer was ${correctAnswerText}.`
                : `Incorrect. The answer was ${correctAnswerText}. You answered ${userAnswerText}.`;

            detailHTML += `
                <div class="result-item ${resp.isCorrect ? 'correct' : 'incorrect'}">
                    <p>
                        <strong>Question ${idx + 1}:</strong> ${resp.questionText}<br>
                        ${resultText}
                    </p>
                </div>
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
        startQuiz('random_main');
    });
    document.getElementById('startTerritoriesQuizBtn').addEventListener('click', () => {
        startQuiz('random_territories');
    });
    </script>
</body>
</html>