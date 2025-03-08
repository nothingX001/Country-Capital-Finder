let lastScrollTop = 0;
const navbar = document.querySelector('.navbar');
const navbarToggle = document.querySelector('.navbar-toggle');
const navbarList = document.querySelector('.navbar-list');
const navbarLogo = document.querySelector('.navbar-logo');
const scrollThreshold = 50; // Threshold for when to add background
let isHamburgerOpen = false;
let scrollPosition = 0;
let lastScrollY = 0;
const scrollThresholdForHide = 5; // Minimum scroll amount to trigger hide/show

// Function to handle hamburger menu state
function setHamburgerState(isOpen) {
    isHamburgerOpen = isOpen;
    
    if (isOpen) {
        // Store current scroll position
        scrollPosition = window.pageYOffset;
        document.body.classList.add('menu-open');
        navbarToggle.classList.add('active');
        navbarList.classList.add('open');
        navbar.classList.add('scrolled'); // Always show background when menu is open
        // Reset the navbar visibility when opening menu
        navbar.classList.remove('navbar-hidden');
        navbarLogo.classList.remove('hidden');
        navbarToggle.classList.remove('hidden');
    } else {
        document.body.classList.remove('menu-open');
        navbarToggle.classList.remove('active');
        navbarList.classList.remove('open');
        // Check if we should remove the scrolled class
        if (window.pageYOffset < scrollThreshold) {
            navbar.classList.remove('scrolled');
        }
        // Restore scroll position
        window.scrollTo(0, scrollPosition);
    }
}

// Handle hamburger click
navbarToggle.addEventListener('click', () => {
    setHamburgerState(!isHamburgerOpen);
});

// Handle scroll events
window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    
    if (isHamburgerOpen) {
        // When menu is open, handle scroll-based visibility of header elements
        const scrollDiff = currentScroll - lastScrollY;
        
        if (Math.abs(scrollDiff) > scrollThresholdForHide) {
            if (scrollDiff > 0 && currentScroll > 50) {
                // Scrolling down - hide the logo and hamburger
                navbarLogo.classList.add('hidden');
                navbarToggle.classList.add('hidden');
            } else {
                // Scrolling up - show the logo and hamburger
                navbarLogo.classList.remove('hidden');
                navbarToggle.classList.remove('hidden');
            }
        }
    } else {
        // Normal scroll behavior when menu is closed
        if (currentScroll > scrollThreshold) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
    
    lastScrollY = currentScroll;
}, { passive: true });

// Prevent touchmove events when menu is open
document.addEventListener('touchmove', (e) => {
    if (isHamburgerOpen) {
        e.preventDefault();
    }
}, { passive: false });

// Close menu on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isHamburgerOpen) {
        setHamburgerState(false);
    }
});

// Expose the setHamburgerState function globally
window.setHamburgerState = setHamburgerState; 