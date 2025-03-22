<!-- navbar.php -->
<nav class="navbar">
  <div class="navbar-container">
    <button class="navbar-toggle" id="navbarToggle" aria-label="Toggle navigation"></button>
    <!-- Logo -->
    <div class="navbar-logo">
      <a href="index.php">
        <img src="images/explore-capitals-logo.jpg" alt="ExploreCapitals Logo">
      </a>
    </div>

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
// INLINED NAVBAR JS
(() => {
    const navbar       = document.querySelector('.navbar');
    const navbarToggle = document.getElementById('navbarToggle');
    const navbarList   = document.getElementById('navbarList');
    const navbarLinks  = document.querySelectorAll('.navbar-list li a');

    let isMenuOpen = false;

    // Toggle menu open/closed
    function setMenuState(open) {
        isMenuOpen = open;
        if (open) {
            document.body.classList.add('menu-open');
            navbar.classList.add('menu-active');
            navbarList.classList.add('open');
            // Add accessibility attributes
            navbarToggle.setAttribute('aria-expanded', 'true');
            navbarList.setAttribute('aria-hidden', 'false');
            console.log('Menu opened');
        } else {
            document.body.classList.remove('menu-open');
            navbar.classList.remove('menu-active');
            navbarList.classList.remove('open');
            // Update accessibility attributes
            navbarToggle.setAttribute('aria-expanded', 'false');
            navbarList.setAttribute('aria-hidden', 'true');
            console.log('Menu closed');
        }
    }

    // Initialize state
    navbarToggle.setAttribute('aria-expanded', 'false');
    navbarList.setAttribute('aria-hidden', 'true');
    navbarList.classList.remove('open');

    // Hook up the toggle button - use direct event listener
    navbarToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        setMenuState(!isMenuOpen);
    });

    // Close menu when a link is clicked
    navbarLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 1100) {
                setMenuState(false);
            }
        });
    });

    // Close menu on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isMenuOpen) {
            setMenuState(false);
        }
    });
    
    // Close menu if window is resized beyond mobile breakpoint
    window.addEventListener('resize', () => {
        if (window.innerWidth > 1100 && isMenuOpen) {
            setMenuState(false);
        }
    });
})();

// Add check to ensure navbar is visible
document.addEventListener('DOMContentLoaded', function() {
    // Force the navbar to be visible
    const navbar = document.querySelector('.navbar');
    const navbarList = document.querySelector('.navbar-list');
    const navbarToggle = document.getElementById('navbarToggle');
    
    if (navbar) {
        navbar.style.display = 'block';
        navbar.style.visibility = 'visible';
        navbar.style.opacity = '1';
    }
    
    // Double check toggle button functionality
    if (navbarToggle && navbarList) {
        navbarToggle.addEventListener('click', function() {
            navbarList.classList.toggle('open');
            const isOpen = navbarList.classList.contains('open');
            navbarToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }
});
</script>
