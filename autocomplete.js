document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('input[name="country"]');
    const dropdown = document.createElement('ul');
    dropdown.classList.add('autocomplete-dropdown');
    document.body.appendChild(dropdown);

    // Position the dropdown
    function positionDropdown() {
        const rect = input.getBoundingClientRect();
        dropdown.style.position = 'absolute';
        dropdown.style.top = `${rect.bottom + window.scrollY}px`;
        dropdown.style.left = `${rect.left + window.scrollX}px`;
        dropdown.style.width = `${rect.width}px`;
    }

    // Event listener for typing
    input.addEventListener('input', async (e) => {
        const query = e.target.value.trim();

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
            countries.forEach(country => {
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

    // Close the dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
});
