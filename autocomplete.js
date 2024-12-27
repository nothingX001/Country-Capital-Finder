document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('input[name="country"]');
    const dropdown = document.createElement('ul');
    dropdown.classList.add('autocomplete-dropdown');
    document.body.appendChild(dropdown);

    let activeIndex = -1; // Tracks the currently highlighted dropdown item

    // Position the dropdown
    function positionDropdown() {
        const rect = input.getBoundingClientRect();
        dropdown.style.position = 'absolute';
        dropdown.style.top = `${rect.bottom + window.scrollY}px`;
        dropdown.style.left = `${rect.left + window.scrollX}px`;
        dropdown.style.width = `${rect.width}px`;
    }

    // Highlight a dropdown item
    function highlightItem(index) {
        const items = dropdown.querySelectorAll('li');
        items.forEach((item, i) => {
            item.classList.toggle('active', i === index);
        });
    }

    // Handle typing in the input field
    input.addEventListener('input', async (e) => {
        const query = e.target.value.trim();
        activeIndex = -1; // Reset the active index

        // Hide dropdown if input is empty
        if (!query) {
            dropdown.style.display = 'none';
            return;
        }

        // Fetch matching countries
        const response = await fetch(`/fetch-country-data.php?type=autocomplete&query=${query}`);
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
                });
                dropdown.appendChild(item);
            });
            positionDropdown();
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
        }
    });

    // Handle keyboard navigation
    input.addEventListener('keydown', (e) => {
        const items = dropdown.querySelectorAll('li');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            activeIndex = (activeIndex + 1) % items.length; // Move down
            highlightItem(activeIndex);
        } else if (e.key === 'ArrowUp') {
            activeIndex = (activeIndex - 1 + items.length) % items.length; // Move up
            highlightItem(activeIndex);
        } else if (e.key === 'Enter') {
            if (activeIndex >= 0 && activeIndex < items.length) {
                e.preventDefault(); // Prevent form submission
                items[activeIndex].click(); // Trigger click on the active item
            }
        }
    });

    // Close the dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
});
