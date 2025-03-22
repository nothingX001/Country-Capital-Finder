document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('input[name="country"]');
    if (!input) return;

    // Only disable browser's default autocomplete but keep other mobile-friendly features
    input.setAttribute('autocomplete', 'off');

    const dropdown = document.createElement('ul');
    dropdown.className = 'autocomplete-dropdown';
    input.parentNode.appendChild(dropdown);

    let activeIndex = -1;

    // Helper function to normalize country name for submission
    function normalizeCountryName(countryName) {
        // Remove "The " prefix if present
        return countryName.replace(/^The\s+/i, '');
    }

    // Handle keyboard navigation
    input.addEventListener('keydown', (e) => {
        const items = dropdown.querySelectorAll('li');
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (items.length > 0) {
                    activeIndex = (activeIndex + 1) % items.length;
                    items.forEach((item, i) => {
                        item.classList.toggle('active', i === activeIndex);
                    });
                }
                break;
            case 'ArrowUp':
                e.preventDefault();
                if (items.length > 0) {
                    activeIndex = (activeIndex - 1 + items.length) % items.length;
                    items.forEach((item, i) => {
                        item.classList.toggle('active', i === activeIndex);
                    });
                }
                break;
            case 'Enter':
                e.preventDefault();
                if (items.length > 0 && activeIndex >= 0 && items[activeIndex]) {
                    input.value = normalizeCountryName(items[activeIndex].textContent);
                    dropdown.style.display = 'none';
                    submitFormWithAnimation();
                } else if (input.value.trim()) {
                    input.value = normalizeCountryName(input.value);
                    submitFormWithAnimation();
                }
                break;
            case 'Escape':
                dropdown.style.display = 'none';
                activeIndex = -1;
                break;
        }
    });

    // Function to submit form with loading animation
    function submitFormWithAnimation() {
        if (!input.form) return;
        
        // Create or get loading indicator
        let loadingIndicator = document.querySelector('.loading-indicator');
        
        if (!loadingIndicator) {
            loadingIndicator = document.createElement('div');
            loadingIndicator.className = 'loading-indicator';
            loadingIndicator.innerHTML = 'Searching...';
            loadingIndicator.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: rgba(0,0,0,0.7);
                color: white;
                padding: 15px 25px;
                border-radius: 5px;
                z-index: 10000;
                pointer-events: none;
            `;
            document.body.appendChild(loadingIndicator);
        }
        
        // Show loading indicator
        loadingIndicator.style.display = 'block';
        
        // Set a flag in session storage to check if we need to scroll to results
        sessionStorage.setItem('shouldScrollToResults', 'true');
        
        // Submit the form
        setTimeout(() => {
            input.form.submit();
        }, 50);
    }

    // Handle typing in the input field
    input.addEventListener('input', async (e) => {
        const query = e.target.value.trim();
        activeIndex = -1; // Reset the active index

        if (!query) {
            dropdown.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`fetch-country-data.php?type=autocomplete&query=${encodeURIComponent(query)}`);
            const countries = await response.json();

            // Populate the dropdown
            dropdown.innerHTML = '';
            if (countries.length > 0) {
                countries.forEach((country) => {
                    const item = document.createElement('li');
                    item.textContent = country;
                    item.style.cursor = 'pointer';
                    
                    item.addEventListener('click', () => {
                        input.value = normalizeCountryName(country);
                        dropdown.style.display = 'none';
                        submitFormWithAnimation();
                    });
                    
                    // Add hover effects
                    item.addEventListener('mouseenter', () => {
                        item.style.backgroundColor = '#404B50';
                        item.style.cursor = 'pointer';
                    });
                    
                    item.addEventListener('mouseleave', () => {
                        if (!item.classList.contains('active')) {
                            item.style.backgroundColor = '';
                        }
                    });
                    
                    dropdown.appendChild(item);
                });
                
                dropdown.style.display = 'block';
                input.classList.add('show-dropdown');
            } else {
                dropdown.style.display = 'none';
                input.classList.remove('show-dropdown');
            }
        } catch (error) {
            console.error("Autocomplete fetch error:", error);
            dropdown.style.display = 'none';
            input.classList.remove('show-dropdown');
        }
    });

    // Close dropdown when clicking outside, but not when clicking the input
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
            input.classList.remove('show-dropdown');
            activeIndex = -1;
        }
    });

    // Only focus on desktop devices
    if (!('ontouchstart' in window)) {
        input.focus();
    }

    // Form submit handler to normalize the input value and show loading indicator
    input.form.addEventListener('submit', (e) => {
        // Prevent default action for now
        e.preventDefault();
        
        // Normalize input value
        input.value = normalizeCountryName(input.value);
        
        // Submit with animation
        submitFormWithAnimation();
    });

    // Improved touch event handling for mobile
    let touchMoved = false;
    
    input.addEventListener('touchstart', () => {
        touchMoved = false;
    });

    input.addEventListener('touchmove', () => {
        touchMoved = true;
    });

    input.addEventListener('touchend', (e) => {
        if (!touchMoved) {
            e.preventDefault();
            input.focus();
        }
        touchMoved = false;
    });
    
    // If we just loaded the page after a form submission, check if we need to scroll to results
    if (sessionStorage.getItem('shouldScrollToResults') === 'true') {
        sessionStorage.removeItem('shouldScrollToResults');
        
        // Wait for the page to fully render
        setTimeout(() => {
            const message = document.querySelector('.message');
            const countryProfileCard = document.getElementById('countryProfileCard');
            
            // Scroll to either the message or the country profile card
            if (countryProfileCard) {
                countryProfileCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                
                // Add a highlight effect
                countryProfileCard.style.animation = 'highlight-pulse 2s ease';
                
                // Add this style to the document if it doesn't exist
                if (!document.querySelector('style#highlight-animation')) {
                    const style = document.createElement('style');
                    style.id = 'highlight-animation';
                    style.textContent = `
                        @keyframes highlight-pulse {
                            0% { box-shadow: 0 0 0 0 rgba(220, 203, 156, 0.5); }
                            70% { box-shadow: 0 0 0 10px rgba(220, 203, 156, 0); }
                            100% { box-shadow: 0 0 0 0 rgba(220, 203, 156, 0); }
                        }
                    `;
                    document.head.appendChild(style);
                }
            } else if (message) {
                message.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 500);
    }
});
