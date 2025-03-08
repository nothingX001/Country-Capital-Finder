let lastScrollTop = 0;
const navbar = document.querySelector('.navbar');
const navbarToggle = document.querySelector('.navbar-toggle');
const navbarList = document.querySelector('.navbar-list');
const threshold = 5; // Minimum amount of pixels to scroll before showing/hiding
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
        navbar.classList.remove('hidden');
    } else {
        document.body.classList.remove('menu-open');
        navbarToggle.classList.remove('active');
        navbarList.classList.remove('open');
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
    
    // Determine scroll direction and distance
    if (Math.abs(lastScrollTop - currentScroll) <= threshold) return;

    if (currentScroll > lastScrollTop && currentScroll > navbar.clientHeight) {
        // Scrolling down & past navbar height
        navbar.classList.add('hidden');
    } else if (currentScroll < lastScrollTop) {
        // Only show navbar when explicitly scrolling up
        navbar.classList.remove('hidden');
    }

    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
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