<?php
include 'config.php';

// Get the most searched country
$most_searched_stmt = $conn->prepare("
    SELECT c.country_name, MAX(st.search_count) as max_searches
    FROM search_tracking st
    JOIN countries c ON st.country_id = c.id
    GROUP BY c.country_name
    ORDER BY max_searches DESC
    LIMIT 1
");
$most_searched_stmt->execute();
$most_searched_stmt->bind_result($most_searched_country, $most_searches);
$most_searched_stmt->fetch();
$most_searched_stmt->close();

if ($most_searched_country) {
    echo "The most searched country is $most_searched_country with $most_searches searches.";
} else {
    echo "No searches have been recorded yet.";
}
?>

