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
    
    // Apply custom pointer cursor to ALL clickable elements immediately
    const allClickableElements = document.querySelectorAll('a, button, [role="button"], input[type="submit"], input[type="button"], input[type="reset"], .button, .navbar-list li a, input[type="text"], .search-bar-container *, .message a, .autocomplete-dropdown li');
    
    allClickableElements.forEach(el => {
        el.style.cursor = 'pointer';
    });
    
    // Ensure country profile card is visible
    const countryProfileCard = document.getElementById('countryProfileCard');
    if (countryProfileCard) {
        countryProfileCard.style.display = 'block';
        countryProfileCard.style.visibility = 'visible';
        countryProfileCard.style.opacity = '1';
        
        // Ensure all child elements are visible too
        const cardElements = countryProfileCard.querySelectorAll('*');
        cardElements.forEach(el => {
            el.style.display = el.tagName === 'DIV' ? 'block' : '';
            el.style.visibility = 'visible';
            el.style.opacity = '1';
        });
    }
    
    // Ensure message is visible
    const message = document.querySelector('.message');
    if (message) {
        message.style.display = 'block';
        message.style.visibility = 'visible';
        message.style.opacity = '1';
    }
    
    // Fix hover behavior by applying event listeners to all links
    const allLinks = document.querySelectorAll('a, .navbar-list li a, .button');
    allLinks.forEach(link => {
        // Force apply the cursor pointer style
        link.style.cursor = 'pointer';
        
        // Ensure links are properly styled by adding event listeners
        link.addEventListener('mouseenter', function() {
            this.style.color = '#DCCB9C';
            this.style.cursor = 'pointer';
        });
        
        link.addEventListener('mouseleave', function() {
            // Don't override text color if it's a button with its own background
            if (!this.classList.contains('button')) {
                this.style.color = '';
            }
        });
        
        link.addEventListener('focus', function() {
            this.style.outline = '2px solid #DCCB9C';
            this.style.outlineOffset = '2px';
        });
        
        link.addEventListener('blur', function() {
            this.style.outline = '';
            this.style.outlineOffset = '';
        });
    });
    
    // Fix cursor issues when interacting with inputs
    const allInputs = document.querySelectorAll('input[type="text"], input[type="submit"]');
    allInputs.forEach(input => {
        input.style.cursor = 'pointer';
    });
    
    // Make the entire document interactive immediately
    document.body.style.pointerEvents = 'auto';
    
    // Apply to dynamically added elements - using MutationObserver
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Apply cursor to clickable elements
                        const clickables = node.querySelectorAll ? 
                            node.querySelectorAll('a, button, [role="button"], input[type="submit"], input[type="button"], input[type="reset"], .button') : [];
                        
                        Array.from(clickables).forEach(el => {
                            el.style.cursor = 'pointer';
                        });
                        
                        // Check for countryProfileCard
                        if (node.id === 'countryProfileCard' || node.querySelector('#countryProfileCard')) {
                            const card = node.id === 'countryProfileCard' ? node : node.querySelector('#countryProfileCard');
                            if (card) {
                                card.style.display = 'block';
                                card.style.visibility = 'visible';
                                card.style.opacity = '1';
                            }
                        }
                    }
                });
            }
        });
    });
    
    observer.observe(document.body, { childList: true, subtree: true });
});
</script>
