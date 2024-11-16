<?php
$data = file_get_contents('http://localhost/fetch-country-data.php?type=random&limit=10');
$questions = json_decode($data, true);
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
        const questions = <?php echo json_encode($questions); ?>;
        let currentQuestionIndex = 0;
        let score = 0;
        let timer;
        let timeElapsed = 0;

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
                const question = questions[currentQuestionIndex];
                const isCountryQuestion = Math.random() > 0.5;
                const questionText = isCountryQuestion
                    ? `What is the capital of ${question.country_name}?`
                    : `${question.capital_name} is the capital of which country?`;

                document.getElementById('questionContainer').textContent = `Question ${currentQuestionIndex + 1}: ${questionText}`;
                document.getElementById('userAnswer').value = '';
            } else {
                endQuiz();
            }
        }

        function endQuiz() {
            clearInterval(timer);
            document.getElementById('quizContainer').style.display = 'none';
            document.getElementById('resultContainer').style.display = 'block';
            document.getElementById('score').textContent = `You scored ${score} out of ${questions.length}.`;
        }

        document.getElementById('answerForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const userAnswer = document.getElementById('userAnswer').value.trim().toLowerCase();
            const correctAnswer = questions[currentQuestionIndex].capital_name.toLowerCase();

            if (userAnswer === correctAnswer) {
                score++;
            }
            currentQuestionIndex++;
            showNextQuestion();
        });

        document.getElementById('redoQuizBtn').addEventListener('click', () => {
            location.reload();
        });

        document.getElementById('startQuizBtn').addEventListener('click', startQuiz);
    </script>
</body>
</html>
