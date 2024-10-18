<!-- navbar.php -->
<nav class="navbar">
    <div class="navbar-container">
        <button class="navbar-toggle" id="navbarToggle" aria-label="Toggle navigation">
            &#9776; <!-- Hamburger icon -->
        </button>
        <ul class="navbar-list" id="navbarList">
            <li><a href="index.php">HOME</a></li>
            <li><a href="country-profiles.php">COUNTRY PROFILES</a></li>
            <li><a href="quiz.php">QUIZ</a></li>
            <li><a href="world-map.php">WORLD MAP</a></li>
            <li><a href="about.php">ABOUT</a></li>
        </ul>
    </div>
</nav>

<script>
document.getElementById('navbarToggle').addEventListener('click', function() {
    document.getElementById('navbarList').classList.toggle('open');
});
</script>
