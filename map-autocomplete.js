document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('#search-bar');
    if (!input) return;

    const dropdown = document.createElement('ul');
    dropdown.className = 'autocomplete-dropdown';
    input.parentNode.appendChild(dropdown);

    let activeIndex = -1;

    // Helper function to normalize country name for searching
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
                    // Trigger the map's search functionality
                    input.dispatchEvent(new Event('input'));
                }
                break;
            case 'Escape':
                dropdown.style.display = 'none';
                activeIndex = -1;
                break;
        }
    });

    // Handle typing in the input field
    let debounceTimer;
    input.addEventListener('input', async (e) => {
        const query = e.target.value.trim();
        activeIndex = -1;

        if (!query) {
            dropdown.style.display = 'none';
            return;
        }

        // Clear previous timer
        clearTimeout(debounceTimer);

        // Set new timer
        debounceTimer = setTimeout(async () => {
            try {
                const response = await fetch(`fetch-country-data.php?type=autocomplete&query=${encodeURIComponent(query)}`);
                const countries = await response.json();

                // Populate the dropdown
                dropdown.innerHTML = '';
                if (countries.length > 0) {
                    countries.forEach((country) => {
                        const item = document.createElement('li');
                        item.textContent = country;
                        item.addEventListener('click', () => {
                            input.value = normalizeCountryName(country);
                            dropdown.style.display = 'none';
                            // Trigger the map's search functionality
                            input.dispatchEvent(new Event('input'));
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
        }, 300); // 300ms debounce delay
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
            activeIndex = -1;
        }
    });
}); 