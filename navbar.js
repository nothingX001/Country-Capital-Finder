let lastScrollTop = 0;
const navbar = document.querySelector('.navbar');
const navbarToggle = document.querySelector('.navbar-toggle');
const navbarList = document.querySelector('.navbar-list');
const threshold = 5; // Minimum amount of pixels to scroll before showing/hiding
let isHamburgerOpen = false;

// Function to handle hamburger menu state
function setHamburgerState(isOpen) {
    isHamburgerOpen = isOpen;
    document.body.classList.toggle('menu-open', isOpen);
    navbarToggle.classList.toggle('active', isOpen);
    navbarList.classList.toggle('open', isOpen);
}

// Handle hamburger click
navbarToggle.addEventListener('click', () => {
    setHamburgerState(!isHamburgerOpen);
});

// Handle scroll events
window.addEventListener('scroll', () => {
    if (isHamburgerOpen) {
        navbar.classList.remove('hidden');
        return;
    }

    const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    
    // Determine scroll direction and distance
    if (Math.abs(lastScrollTop - currentScroll) <= threshold) return;

    if (currentScroll > lastScrollTop && currentScroll > navbar.clientHeight) {
        // Scrolling down & past navbar height
        navbar.classList.add('hidden');
    } else {
        // Scrolling up or at the top
        navbar.classList.remove('hidden');
    }

    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
}, { passive: true });

// Close menu on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isHamburgerOpen) {
        setHamburgerState(false);
    }
});

// Expose the setHamburgerState function globally
window.setHamburgerState = setHamburgerState; 