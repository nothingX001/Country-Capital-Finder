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
        } else {
            document.body.classList.remove('menu-open');
            navbar.classList.remove('menu-active');
            navbarList.classList.remove('open');
            // Update accessibility attributes
            navbarToggle.setAttribute('aria-expanded', 'false');
            navbarList.setAttribute('aria-hidden', 'true');
        }
    }

    // Initialize state
    navbarToggle.setAttribute('aria-expanded', 'false');
    navbarList.setAttribute('aria-hidden', 'true');
    navbarList.classList.remove('open');

    // Hook up the toggle button - single direct event listener
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

// Add check to ensure navbar is visible and handle scroll behavior
document.addEventListener('DOMContentLoaded', function() {
    // Force the navbar to be visible
    const navbar = document.querySelector('.navbar');
    const navbarList = document.querySelector('.navbar-list');
    const pageContent = document.querySelector('.page-content, .quiz, .country-profiles, .about, .world-map');
    
    if (navbar) {
        navbar.style.display = 'block';
        navbar.style.visibility = 'visible';
        navbar.style.opacity = '1';
        navbar.style.transform = 'translateY(0)';
    }
    
    // Variables for scroll behavior
    let lastScrollTop = 0;
    let scrollDelta = 5;
    let navbarHeight = navbar ? navbar.offsetHeight : 80;
    let isNavbarVisible = true;
    
    // Function to handle navbar visibility
    function handleNavbarVisibility() {
        // Don't hide navbar if mobile menu is open
        if (document.body.classList.contains('menu-open')) {
            return;
        }
        
        let currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Make sure they scrolled more than delta
        if (Math.abs(lastScrollTop - currentScrollTop) <= scrollDelta) {
            return;
        }
        
        // If scrolled down past navbar height
        if (currentScrollTop > lastScrollTop && currentScrollTop > navbarHeight) {
            // Scrolling down - hide navbar
            navbar.style.transform = 'translateY(-100%)';
            isNavbarVisible = false;
        } else if (currentScrollTop < lastScrollTop || currentScrollTop <= navbarHeight) {
            // Scrolling up or at top - show navbar
            navbar.style.transform = 'translateY(0)';
            isNavbarVisible = true;
        }
        
        lastScrollTop = currentScrollTop;
    }
    
    // Add scroll event listener
    window.addEventListener('scroll', handleNavbarVisibility);
});
</script>
