document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('input[name="country"]');
    if (!input) return;

    // Only disable browser's default autocomplete but keep other mobile-friendly features
    input.setAttribute('autocomplete', 'off');

    const dropdown = document.createElement('ul');
    dropdown.className = 'autocomplete-dropdown';
    input.parentNode.appendChild(dropdown);

    let activeIndex = -1;

    // Position the dropdown below the input
    function positionDropdown() {
        const rect = input.getBoundingClientRect();
        const form = input.closest('form');
        const formRect = form.getBoundingClientRect();
        
        dropdown.style.top = `${rect.bottom + window.scrollY}px`;
        dropdown.style.left = `${formRect.left + (formRect.width - 300) / 2}px`;
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
                    input.value = items[activeIndex].textContent;
                    dropdown.style.display = 'none';
                    input.form.submit();
                } else if (input.value.trim()) {
                    // If no dropdown item is selected but input has value, submit the form
                    input.form.submit();
                }
                break;
            case 'Escape':
                dropdown.style.display = 'none';
                activeIndex = -1;
                break;
        }
    });

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
                    item.addEventListener('click', () => {
                        input.value = country;
                        dropdown.style.display = 'none';
                        input.form.submit();
                    });
                    dropdown.appendChild(item);
                });
                positionDropdown();
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

    // Improved touch event handling for mobile
    input.addEventListener('touchend', (e) => {
        e.preventDefault(); // Prevent double-tap zoom
        input.focus();
    }, { passive: false });
});
