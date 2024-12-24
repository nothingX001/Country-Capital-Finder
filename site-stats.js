// Fetch the most recent search and display it in local time
function fetchMostRecentSearch() {
    fetch('/fetch-country-data.php?type=statistics')
        .then(response => response.json())
        .then(data => {
            // Extract data for the most recent search
            const mostRecentSearch = data.most_recent_search || 'No recent searches available.';

            // Update the "Most Recent Search" section
            document.getElementById('most-recent-search').textContent = mostRecentSearch;

            // Optional: You can handle additional statistics data if needed
            console.log('Statistics:', data);
        })
        .catch(error => console.error('Error fetching recent search:', error));
}

// Initialize function on page load
window.onload = fetchMostRecentSearch;
