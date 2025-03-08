let lastScrollTop = 0;
const navbar = document.querySelector('.navbar');
const navbarToggle = document.querySelector('.navbar-toggle');
const navbarList = document.querySelector('.navbar-list');
const scrollThreshold = 50; // Threshold for when to add background
let isHamburgerOpen = false;
let scrollPosition = 0;

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
    if (isHamburgerOpen) {
        return; // Don't process scroll events when menu is open
    }

    const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    
    // Add/remove scrolled class based on scroll position
    if (currentScroll > scrollThreshold) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
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