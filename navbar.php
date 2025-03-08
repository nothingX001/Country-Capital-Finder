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
    const navbarLogo   = document.querySelector('.navbar-logo');

    let isMenuOpen           = false;
    let lastWindowScrollY    = 0;
    let lastMenuScrollY      = 0;
    const SCROLL_THRESHOLD   = 50; // if page scrolled beyond 50px, we call it "scrolled"
    const HIDE_THRESHOLD     = 5;  // minimal scroll inside the menu to hide the logo/toggle

    // 1) NORMAL PAGE SCROLL (when menu is closed):
    function handleWindowScroll() {
        if (!isMenuOpen) {
            const currentScroll = window.pageYOffset;
            if (currentScroll > SCROLL_THRESHOLD) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            lastWindowScrollY = currentScroll;
        }
    }

    // 2) If we wanted to do “hide logo on menu scroll,” we’d need to
    //    attach a scroll listener to the menu itself. BUT in this
    //    approach, the menu is in normal flow – it’s not an overlay
    //    with its own scroll container. The entire page just scrolls.
    //    So we no longer need a separate "menu scroll" listener.

    // 3) Toggle menu open/closed in normal flow
    function setMenuState(open) {
        isMenuOpen = open;
        if (open) {
            // Instead of locking the page, let it scroll normally.
            // We'll just expand the navbar-list below the hamburger.
            navbarList.classList.add('open');
            // Force background on the navbar if user was near top
            navbar.classList.add('scrolled');

        } else {
            navbarList.classList.remove('open');
            // If the page is near top, remove .scrolled
            if (window.pageYOffset < SCROLL_THRESHOLD) {
                navbar.classList.remove('scrolled');
            }
        }
    }

    // 4) Hook up the toggle button
    navbarToggle.addEventListener('click', () => {
        setMenuState(!isMenuOpen);
    });

    // 5) Listen for normal page scrolling
    window.addEventListener('scroll', handleWindowScroll, { passive: true });

    // 6) Optional: close menu on Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isMenuOpen) {
            setMenuState(false);
        }
    });
})();
</script>
