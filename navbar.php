<!-- navbar.php -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="navbar-logo">Home</a>
        <button class="navbar-toggle" id="navbarToggle" aria-label="Toggle navigation">
            &#9776; <!-- Hamburger icon -->
        </button>
        <ul class="navbar-list" id="navbarList">
            <li><a href="index.php">Home</a></li>
            <li><a href="country-profiles.php">Country Profiles</a></li>
            <li><a href="quiz.php">Quiz</a></li>
            <li><a href="world-map.php">World Map</a></li>
        </ul>
    </div>
</nav>

<script>
// Toggle the visibility of the navbar list on mobile
document.getElementById('navbarToggle').addEventListener('click', function() {
    document.getElementById('navbarList').classList.toggle('open');
});
</script>
