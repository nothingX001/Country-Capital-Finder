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

    // Handle link clicks with a delay to keep menu visible during navigation
    navbarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Only apply delay behavior in mobile view
            if (window.innerWidth <= 1100) {
                // Get the href to navigate to
                const href = this.getAttribute('href');
                
                // Don't delay for same-page links or # links
                if (href && href !== '#' && !href.startsWith('#')) {
                    // Prevent the default link behavior
                    e.preventDefault();
                    
                    // Add a visual indicator that the link was clicked
                    this.style.color = '#DCCB9C';
                    
                    // Keep the menu visible during navigation
                    // Navigate after a slight delay for visual feedback
                    setTimeout(() => {
                        window.location.href = href;
                    }, 150); // Slightly longer delay for better user feedback
                }
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

// Add check to ensure navbar is visible and links are styled properly
document.addEventListener('DOMContentLoaded', function() {
    // Force the navbar to be visible
    const navbar = document.querySelector('.navbar');
    
    if (navbar) {
        navbar.style.display = 'block';
        navbar.style.visibility = 'visible';
        navbar.style.opacity = '1';
    }
    
    // Ensure all navbar links have the correct styles
    const navLinks = document.querySelectorAll('.navbar-list li a');
    navLinks.forEach(link => {
        // Force apply the cursor pointer style
        link.style.cursor = 'pointer';
        
        // Ensure hover effects work by manually adding event listeners
        link.addEventListener('mouseenter', function() {
            this.style.color = '#DCCB9C';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.color = '#ECECEC';
        });
    });
    
    // Force document to be interactive immediately
    document.body.addEventListener('click', function() {
        // This is a dummy event listener to ensure the page becomes interactive
        // without needing the user to click anywhere specific
    }, { once: true });
    
    // Simulate a click on the document body to make it interactive immediately
    document.body.click();
});
</script>
