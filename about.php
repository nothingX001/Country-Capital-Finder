<?php
// Fetch site statistics
$data = file_get_contents('http://localhost/fetch-country-data.php?type=statistics');
$statistics = json_decode($data, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="The Country Capital Finder is a unique application where you can find any country’s capital. Take an interactive quiz and test your geography knowledge with ease. Perfect for geography bees and learners.">
    <meta name="keywords" content="find country's capital, country capital quiz, countries and capitals quiz, geography bee prep, interactive capital quiz, world capital learning game, easy geography quiz">
    <meta name="author" content="Country Capital Finder Team">
    <meta property="og:title" content="Country Capital Finder | Interactive Country Capital Quiz">
    <meta property="og:description" content="An interactive platform to find any country’s capital. Test your knowledge with a world capitals quiz and prep for geography bees.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.yoursite.com/about">
    <meta property="og:image" content="https://www.yoursite.com/images/country-capital-quiz.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Country Capital Finder">
    <meta name="twitter:description" content="Learn and memorize world capitals with our fun and easy geography quiz.">
    <meta name="twitter:image" content="https://www.yoursite.com/images/country-capital-quiz.png">
    <title>About | Country Capital Finder</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="about-styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <section class="about-section">
        <h1>ABOUT THE COUNTRY CAPITAL FINDER</h1>
        <p>Welcome to the <strong>Country Capital Finder</strong>—an intuitive application where you can <strong>find any country’s capital</strong> with ease. Created to support learners of all levels, from students to trivia enthusiasts, our platform offers an <strong>interactive capital quiz</strong> and an extensive <strong>capitals of the world quiz</strong> designed to make memorizing capitals both engaging and effective. Ideal for <strong>geography bee prep</strong>, this tool is perfect for anyone looking to improve their knowledge of world geography.</p>

        <h2>An Interactive Learning Tool</h2>
        <p>The Country Capital Finder isn’t just a <strong>country capital quiz</strong>; it’s a comprehensive <strong>study tool for capitals and countries</strong> that helps users <strong>memorize capitals easily</strong>. With options for <strong>memory-focused capital quizzes</strong> and <strong>easy quizzes for world capitals</strong>, learners can test their recall while exploring new ways to retain information. Our <strong>capitals by country finder</strong> and <strong>geography quiz app for learners</strong> also allow for quick lookups and enjoyable self-paced study sessions.</p>

        <h2>Geography Quiz Practice</h2>
        <p>For students and trivia fans alike, our <strong>country capital quiz based on search</strong> offers an easy-to-navigate platform to <strong>test your geography knowledge</strong>. With each use, you’ll encounter fresh challenges that cover every <strong>country and capital</strong> worldwide. Whether you're practicing for a <strong>geography bee</strong> or simply brushing up on capitals, our app’s <strong>interactive learning for capitals</strong> feature ensures a fun and thorough review of the world’s capitals.</p>

        <h2>Perfect for Students and Geography Enthusiasts</h2>
        <p>Designed to support geography students, teachers, and curious minds, the <strong>Country Capital Finder</strong> is ideal for building a strong foundation in world geography. With our <strong>quiz tool for geography students</strong>, users can prep for exams, quizzes, and trivia contests using real-world questions and reliable information. The <strong>geography bee prep tool</strong> is particularly useful for honing recall and reinforcing knowledge through timed challenges, <strong>geography trivia for students</strong>, and in-depth questions.</p>

        <h2>Fun and Easy Geography Practice</h2>
        <p>Offering an enjoyable, accessible approach to <strong>learning world capitals</strong>, the Country Capital Finder also includes <strong>world capital learning games</strong> and quizzes that transform traditional study methods into something engaging and memorable. Every question in the <strong>capitals of the world quiz</strong> is designed to enhance memory retention, making it perfect for a <strong>geography bee prep tool</strong> or just for <strong>learning capitals by memory</strong>.</p>

        <p>Whether you’re aiming to learn for fun, education, or competition, the Country Capital Finder brings the world’s capitals into easy reach, fostering learning and exploration across borders. Dive into our quizzes today and experience <strong>an application where you can find a country’s capital</strong> while testing your geography knowledge in new interactive ways.</p>

        <!-- Site Statistics Section -->
        <section class="site-statistics">
            <h2>SITE STATISTICS</h2>
            <p>Check out some key highlights from our platform:</p>
            <ul>
                <li><strong>Most searched country:</strong> <?php echo htmlspecialchars($statistics['most_searched_country'] ?? 'N/A'); ?></li>
                <li><strong>Most searched capital:</strong> <?php echo htmlspecialchars($statistics['most_searched_capital'] ?? 'N/A'); ?></li>
                <li><strong>Total quizzes completed:</strong> <?php echo htmlspecialchars($statistics['total_quizzes_completed'] ?? 'N/A'); ?></li>
                <li><strong>Last search:</strong> <?php echo htmlspecialchars($statistics['last_search'] ?? 'N/A'); ?></li>
            </ul>
        </section>
    </section>
</body>
</html>
