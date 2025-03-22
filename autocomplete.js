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

    // Simple function to submit form with loading indicator
    function submitFormWithAnimation() {
        if (!input.form) return;
        
        // Don't set the flag to scroll to results
        
        // Submit the form
        input.form.submit();
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
            // Add a debug log
            console.log("Fetching autocomplete for:", query);
            
            // Force query to be at least one character
            const searchQuery = query.length > 0 ? query : '';
            
            const response = await fetch(`fetch-country-data.php?type=autocomplete&query=${encodeURIComponent(searchQuery)}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const responseText = await response.text();
            console.log("Response text:", responseText);
            
            let countries;
            try {
                countries = JSON.parse(responseText);
            } catch (parseError) {
                console.error("JSON Parse error:", parseError, "Response:", responseText);
                return;
            }

            // Populate the dropdown
            dropdown.innerHTML = '';
            if (countries && countries.length > 0) {
                countries.forEach((country) => {
                    const item = document.createElement('li');
                    item.textContent = country;
                    item.style.cursor = 'pointer';
                    
                    item.addEventListener('click', () => {
                        input.value = normalizeCountryName(country);
                        dropdown.style.display = 'none';
                        submitFormWithAnimation();
                    });
                    
                    dropdown.appendChild(item);
                });
                
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none';
            }
        } catch (error) {
            console.error("Autocomplete fetch error:", error);
            dropdown.style.display = 'none';
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
            activeIndex = -1;
        }
    });

    // Only focus on desktop devices
    if (!('ontouchstart' in window)) {
        input.focus();
    }

    // Form submit handler to normalize the input value
    input.form.addEventListener('submit', (e) => {
        // Prevent default action
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
    
    // Check if we need to scroll to results
    if (sessionStorage.getItem('shouldScrollToResults') === 'true') {
        // Clear the flag but don't scroll
        sessionStorage.removeItem('shouldScrollToResults');
    }
});
