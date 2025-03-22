<!-- navbar.php -->
<nav class="navbar" style="position: fixed !important; top: 0 !important; transform: translateZ(0) !important; will-change: transform !important; -webkit-backface-visibility: visible !important; backface-visibility: visible !important;">
  <div class="navbar-container">
    <button class="navbar-toggle" id="navbarToggle" aria-label="Toggle navigation"></button>
    <!-- Logo -->
    <div class="navbar-logo">
      <a href="index.php">
        <img src="images/explore-capitals-logo.jpg" alt="ExploreCapitals Logo">
      </a>
    </div>

    <ul class="navbar-list" id="navbarList" style="--menu-item-spacing: 4px;">
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

    // Ensure navbar stays fixed during overscroll
    document.addEventListener('scroll', () => {
        if (window.scrollY < 0) {
            // During overscroll
            navbar.style.transform = 'translateY(0)';
            navbar.style.position = 'fixed';
            navbar.style.top = '0';
        }
    });

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
            
            // Ensure menu covers entire screen
            navbarList.style.minHeight = '100vh';
            navbarList.style.paddingTop = '100px';
            navbarList.style.paddingBottom = '40px';
            
            // Apply reduced text size for menu items
            navbarLinks.forEach(link => {
                link.style.fontSize = window.innerWidth <= 480 ? '20px' : '22px';
                link.style.padding = window.innerWidth <= 480 ? '5px 0' : '6px 0';
            });
            
            // Add hover and cursor styles immediately when menu opens
            applyMenuItemStyles();
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

    // Apply cursor and hover effects explicitly
    function applyMenuItemStyles() {
        navbarLinks.forEach(link => {
            // Ensure cursor is pointer
            link.style.cursor = 'pointer';
            
            // Clear existing listeners
            link.removeEventListener('mouseenter', handleMouseEnter);
            link.removeEventListener('mouseleave', handleMouseLeave);
            
            // Add them back
            link.addEventListener('mouseenter', handleMouseEnter);
            link.addEventListener('mouseleave', handleMouseLeave);
        });
    }

    // Event handler functions
    function handleMouseEnter() {
        this.style.color = '#DCCB9C';
    }

    function handleMouseLeave() {
        if (!this.classList.contains('active')) {
            this.style.color = '#ECECEC';
        }
    }

    // Handle link clicks with a delay to keep menu visible during navigation
    navbarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Only apply delay behavior in mobile view
            if (window.innerWidth <= 1200) {
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
        if (window.innerWidth > 1200 && isMenuOpen) {
            setMenuState(false);
        }
    });
    
    // Apply styles immediately
    applyMenuItemStyles();
})();

// Simplified handler to only focus on cursor and card visibility
document.addEventListener('DOMContentLoaded', function() {
    // Ensure navbar is visible
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        navbar.style.display = 'block';
        navbar.style.visibility = 'visible';
        navbar.style.opacity = '1';
    }
    
    // Apply cursor pointer to clickable elements
    const clickableElements = document.querySelectorAll('a, button, [role="button"], input[type="submit"], input[type="button"], input[type="reset"], .button, .navbar-list li a, .message a, .autocomplete-dropdown li');
    clickableElements.forEach(el => {
        el.style.cursor = 'pointer';
    });
    
    // Ensure country profile card is visible
    const countryProfileCard = document.getElementById('countryProfileCard');
    if (countryProfileCard) {
        countryProfileCard.style.display = 'block';
        countryProfileCard.style.visibility = 'visible';
        countryProfileCard.style.opacity = '1';
    }
    
    // Ensure message is visible
    const message = document.querySelector('.message');
    if (message) {
        message.style.display = 'block';
        message.style.visibility = 'visible';
        message.style.opacity = '1';
    }
});
</script>
