document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('input[name="country"]');
    const dropdown = document.createElement('ul');
    dropdown.classList.add('autocomplete-dropdown');
    
    // Add some basic inline styling so the dropdown is visible.
    dropdown.style.backgroundColor = '#fff';
    dropdown.style.border = '1px solid #ccc';
    dropdown.style.zIndex = '1000';
    dropdown.style.position = 'absolute';
    dropdown.style.listStyleType = 'none';
    dropdown.style.padding = '0';
    dropdown.style.margin = '0';
    dropdown.style.maxHeight = '200px';
    dropdown.style.overflowY = 'auto';

    document.body.appendChild(dropdown);

    let activeIndex = -1; // Tracks the currently highlighted dropdown item

    // Position the dropdown relative to the input field.
    function positionDropdown() {
        const rect = input.getBoundingClientRect();
        dropdown.style.top = `${rect.bottom + window.scrollY}px`;
        dropdown.style.left = `${rect.left + window.scrollX}px`;
        dropdown.style.width = `${rect.width}px`;
    }

    // Highlight a dropdown item.
    function highlightItem(index) {
        const items = dropdown.querySelectorAll('li');
        items.forEach((item, i) => {
            item.classList.toggle('active', i === index);
        });
        if (index >= 0 && index < items.length) {
            items[index].scrollIntoView({ block: 'nearest', inline: 'nearest' });
        }
    }

    // Handle typing in the input field.
    input.addEventListener('input', async (e) => {
        const query = e.target.value.trim();
        activeIndex = -1; // Reset the active index

        if (!query) {
            dropdown.style.display = 'none';
            return;
        }

        try {
            // Use a relative URL (without a leading slash) so that it points to the correct file.
            const response = await fetch(`fetch-country-data.php?type=autocomplete&query=${encodeURIComponent(query)}`);
            const countries = await response.json();
            console.log("Autocomplete results:", countries); // Debug: check results in console

            // Populate the dropdown.
            dropdown.innerHTML = '';
            if (countries.length > 0) {
                countries.forEach((country) => {
                    const item = document.createElement('li');
                    item.textContent = country;
                    item.style.padding = '5px 10px';
                    item.style.cursor = 'pointer';
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

    // Handle keyboard navigation.
    input.addEventListener('keydown', (e) => {
        const items = dropdown.querySelectorAll('li');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            activeIndex = (activeIndex + 1) % items.length;
            highlightItem(activeIndex);
            e.preventDefault();
        } else if (e.key === 'ArrowUp') {
            activeIndex = (activeIndex - 1 + items.length) % items.length;
            highlightItem(activeIndex);
            e.preventDefault();
        } else if (e.key === 'Enter') {
            if (activeIndex >= 0 && activeIndex < items.length) {
                e.preventDefault();
                items[activeIndex].click();
            } else {
                e.preventDefault();
                input.form.submit();
            }
        }
    });

    // Close the dropdown when clicking outside.
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
});
