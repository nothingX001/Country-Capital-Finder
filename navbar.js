let lastScrollTop = 0;
const navbar = document.querySelector('.navbar');
const navbarToggle = document.querySelector('.navbar-toggle');
const navbarList = document.querySelector('.navbar-list');
const threshold = 5;
let isMenuOpen = false;

function setHamburgerState(open) {
    isMenuOpen = open;
    const body = document.body;
    
    if (open) {
        navbarToggle.classList.add('active');
        navbarList.classList.add('open');
        body.classList.add('menu-open');
        navbar.classList.remove('hidden');
    } else {
        navbarToggle.classList.remove('active');
        navbarList.classList.remove('open');
        body.classList.remove('menu-open');
    }
}

// Handle hamburger menu click
navbarToggle.addEventListener('click', () => {
    setHamburgerState(!isMenuOpen);
});

// Handle scroll events
window.addEventListener('scroll', () => {
    if (isMenuOpen) return; // Don't hide navbar if menu is open
    
    const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    
    if (Math.abs(currentScroll - lastScrollTop) <= threshold) return;
    
    if (currentScroll > lastScrollTop && currentScroll > 80) {
        // Scrolling down & past navbar height
        navbar.classList.add('hidden');
    } else {
        // Scrolling up
        navbar.classList.remove('hidden');
    }
    
    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
}, { passive: true });

// Close menu on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isMenuOpen) {
        setHamburgerState(false);
    }
});

// Prevent touchmove when menu is open
document.body.addEventListener('touchmove', (e) => {
    if (isMenuOpen) {
        e.preventDefault();
    }
}, { passive: false });

// Handle resize events
window.addEventListener('resize', () => {
    if (window.innerWidth > 1100 && isMenuOpen) {
        setHamburgerState(false);
    }
}, { passive: true }); 