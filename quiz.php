<?php
// quiz.php
include 'config.php';
include 'the-countries.php'; // Contains $the_countries array + normalizeInput() function
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
        <p>Select a quiz type to begin.</p>

        <!-- TWO QUIZ CHOICES: MEMBER/OBSERVER vs. TERRITORIES -->
        <button id="startMainQuizBtn">Start Member/Observer States Quiz</button>
        <button id="startTerritoriesQuizBtn">Start Territories Quiz</button>

        <!-- QUIZ UI Hidden Initially -->
        <div id="quizContainer" style="display: none;">
            <div id="timer">Time: 0:00</div>
            <div id="questionContainer"></div>
            <form id="answerForm">
                <input type="text" id="userAnswer" placeholder="Type your answer here" required>
                <button type="submit">SUBMIT ANSWER</button>
            </form>
        </div>

        <!-- RESULTS UI Hidden Initially -->
        <div id="resultContainer" style="display: none;">
            <h2>Quiz Results</h2>
            <p id="score"></p>
            <div id="detailedResults"></div>
            <button id="redoQuizBtn">REDO QUIZ</button>
        </div>
    </section>

    <script>
    // The same quiz logic, but data is loaded dynamically upon user choice
    let questions = [];
    const theCountries = <?php echo json_encode(array_map('strtolower', $the_countries)); ?>;
    let currentQuestionIndex = 0;
    let score = 0;
    let timeElapsed = 0;
    let timer;
    let userResponses = [];

    // Utility: Some countries require "the" prefix (United States -> "the United States")
    function addThe(country) {
        return theCountries.includes(country.toLowerCase()) ? `the ${country}` : country;
    }

    function normalizeInput(input) {
        let norm = input.toLowerCase().trim();
        norm = norm.replace(/^the\s+/i, '');
        return norm;
    }

    // Called by "Start Member/Observer" or "Start Territories"
    function startQuiz(quizType) {
        // Fetch 10 random from either random_main or random_territories
        fetch(`fetch-country-data.php?type=${quizType}&limit=10`)
            .then(r => r.json())
            .then(data => {
                if (!data || data.error) {
                    alert('Error fetching quiz data.');
                    return;
                }
                prepareQuestions(data);
            })
            .catch(err => {
                console.error('Quiz fetch error:', err);
                alert('Unable to load quiz data.');
            });
    }

    // Once we fetch the data, build the question set
    function prepareQuestions(data) {
        questions = [];
        data.forEach(row => {
            if (row.capitals && row.capitals.length > 0) {
                questions.push({
                    country: row.country_name,
                    capitals: row.capitals
                });
            }
        });

        if (questions.length === 0) {
            alert('No quiz data available. Possibly no capitals found.');
            return;
        }

        // Show the quiz container
        document.getElementById('quizContainer').style.display = 'block';
        document.getElementById('resultContainer').style.display = 'none';
        document.getElementById('startMainQuizBtn').style.display = 'none';
        document.getElementById('startTerritoriesQuizBtn').style.display = 'none';

        // Reset
        score = 0;
        timeElapsed = 0;
        currentQuestionIndex = 0;
        userResponses = [];

        startTimer();
        showNextQuestion();
    }

    function startTimer() {
        clearInterval(timer);
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
                questionText = `What is the capital of ${addThe(qData.country)}?`;
                userResponses.push({
                    questionText,
                    correctAnswers: qData.capitals, 
                    userAnswer: '',
                    isCorrect: false,
                    correctAnswerText: qData.capitals.join(' / ')
                });
            } else {
                const cCount = qData.capitals.length;
                const capitalNames = qData.capitals.join(' / ');
                const verb = cCount > 1 ? 'are' : 'is';
                questionText = `${capitalNames} ${verb} the capital${cCount>1 ? 's' : ''} of which country?`;
                userResponses.push({
                    questionText,
                    correctAnswers: [qData.country],
                    userAnswer: '',
                    isCorrect: false,
                    correctAnswerText: qData.country
                });
            }

            document.getElementById('questionContainer').textContent =
                `Question ${currentQuestionIndex + 1}: ${questionText}`;
            document.getElementById('userAnswer').value = '';
        } else {
            endQuiz();
        }
    }

    function checkAnswer(userAnswer, correctAnswers) {
        const userNorm = normalizeInput(userAnswer);
        return correctAnswers.some(cAnswer => {
            // Each correct answer could be multiple capitals separated by slash
            const variants = cAnswer.toLowerCase().split('/').map(x => normalizeInput(x.trim()));
            return variants.includes(userNorm);
        });
    }

    function endQuiz() {
        clearInterval(timer);
        document.getElementById('quizContainer').style.display = 'none';
        document.getElementById('resultContainer').style.display = 'block';

        document.getElementById('score').textContent = 
            `You scored ${score} out of ${questions.length}.`;

        let resultsHTML = '';
        userResponses.forEach((resp, idx) => {
            const resultText = resp.isCorrect
              ? `Correct. The answer was ${resp.correctAnswerText}.`
              : `Incorrect. The answer was ${resp.correctAnswerText}. You answered "${resp.userAnswer}".`;
            resultsHTML += `
                <p><strong>Question ${idx+1}:</strong> ${resp.questionText}<br>${resultText}</p>
            `;
        });
        document.getElementById('detailedResults').innerHTML = resultsHTML;
    }

    // On Submit, check the user's typed answer
    document.getElementById('answerForm').addEventListener('submit', e => {
        e.preventDefault();
        const userAnswer = document.getElementById('userAnswer').value.trim();
        const resp = userResponses[currentQuestionIndex];
        const isCorrect = checkAnswer(userAnswer, resp.correctAnswers);

        resp.userAnswer = userAnswer;
        resp.isCorrect  = isCorrect;
        if (isCorrect) {
            score++;
        }
        currentQuestionIndex++;
        showNextQuestion();
    });

    // For redoing the quiz, just reload the page
    document.getElementById('redoQuizBtn').addEventListener('click', () => {
        location.reload();
    });

    // Hooks for the 2 quiz choice buttons
    document.getElementById('startMainQuizBtn').addEventListener('click', () => {
        startQuiz('random_main');
    });
    document.getElementById('startTerritoriesQuizBtn').addEventListener('click', () => {
        startQuiz('random_territories');
    });
    </script>
</body>
</html>
