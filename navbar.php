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
// ----------------------------------------------------------
// INLINED NAVBAR.JS
// ----------------------------------------------------------
(() => {
    const navbar        = document.querySelector('.navbar');
    const navbarToggle  = document.getElementById('navbarToggle');
    const navbarList    = document.getElementById('navbarList');
    const navbarLogo    = document.querySelector('.navbar-logo');

    let isHamburgerOpen = false;
    let lastScrollY     = 0;
    const scrollThreshold       = 50;  // If user scrolls beyond 50px, we "scrolled"
    const scrollThresholdForHide= 5;   // Minimal scroll to hide the logo/toggle inside menu

    // Normal page scrolling behavior when menu is closed
    function handleWindowScroll() {
        if (!isHamburgerOpen) {
            const currentScroll = window.pageYOffset;
            if (currentScroll > scrollThreshold) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
    }

    // Scroll inside the open hamburger menu
    function handleMenuScroll() {
        if (!isHamburgerOpen) return;
        const currentScroll = navbarList.scrollTop;
        const diff          = currentScroll - lastScrollY;

        if (Math.abs(diff) > scrollThresholdForHide) {
            if (diff > 0 && currentScroll > 50) {
                // Scrolling down => hide logo + toggle
                navbarLogo.classList.add('hidden');
                navbarToggle.classList.add('hidden');
            } else {
                // Scrolling up => show them again
                navbarLogo.classList.remove('hidden');
                navbarToggle.classList.remove('hidden');
            }
        }
        lastScrollY = currentScroll;
    }

    // Toggle open/closed
    function setHamburgerState(isOpen) {
        isHamburgerOpen = isOpen;
        if (isOpen) {
            // Lock body scrolling behind overlay
            document.body.classList.add('menu-open');

            // Show the overlay
            navbarList.classList.add('open');
            // Force a background on the navbar
            navbar.classList.add('scrolled');

            // Reset hidden states
            navbarLogo.classList.remove('hidden');
            navbarToggle.classList.remove('hidden');

            // Reset scroll inside the menu
            navbarList.scrollTop = 0;
            lastScrollY = 0;
        } else {
            document.body.classList.remove('menu-open');
            navbarList.classList.remove('open');

            // If the main page hasn't scrolled beyond threshold, remove .scrolled
            if (window.pageYOffset < scrollThreshold) {
                navbar.classList.remove('scrolled');
            }
        }
    }

    // Handle hamburger toggle
    navbarToggle.addEventListener('click', () => {
        setHamburgerState(!isHamburgerOpen);
    });

    // Listen for normal page scroll
    window.addEventListener('scroll', handleWindowScroll, { passive: true });

    // Listen for scroll within the open menu
    navbarList.addEventListener('scroll', handleMenuScroll, { passive: true });

    // Allow closing with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isHamburgerOpen) {
            setHamburgerState(false);
        }
    });
})();
</script>
