<?php
include 'config.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $country = $_POST['country'];

    // Search for the country in the database
    $stmt = $conn->prepare("SELECT id, capital_name FROM countries WHERE country_name = ?");
    $stmt->bind_param("s", $country);
    $stmt->execute();
    $stmt->bind_result($country_id, $capital);
    $stmt->fetch();
    $stmt->close();

    if ($capital) {
        // Display the capital
        $message = "The capital of {$country} is {$capital}.";

        // Check if the country already has search tracking, if not, insert into tracking
        $search_stmt = $conn->prepare("SELECT search_count FROM search_tracking WHERE country_id = ?");
        $search_stmt->bind_param("i", $country_id);
        $search_stmt->execute();
        $search_stmt->bind_result($search_count);
        $search_stmt->fetch();
        $search_stmt->close();

        if ($search_count) {
            // Update the search count if it exists
            $update_stmt = $conn->prepare("UPDATE search_tracking SET search_count = search_count + 1 WHERE country_id = ?");
            $update_stmt->bind_param("i", $country_id);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            // Insert the search count if it does not exist
            $insert_stmt = $conn->prepare("INSERT INTO search_tracking (country_id, search_count) VALUES (?, 1)");
            $insert_stmt->bind_param("i", $country_id);
            $insert_stmt->execute();
            $insert_stmt->close();
        }
    } else {
        $message = "Sorry, the country you entered is not in our list.";
    }
}

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Country Capital Finder</title>
</head>
<body>
    <h1>🇺🇸🇪🇺 Find a Country's Capital! 🇷🇺🇨🇳</h1>

    <form action="index.php" method="post">
        <label>Enter a country: </label>
        <input type="text" name="country" required>
        <input type="submit" value="Submit">
    </form>

    <?php if (isset($message)) { ?>
        <p><?php echo $message; ?></p>
    <?php } ?>

    <h3>What's the Most Searched Country on this app?</h3>
    <div id="most-searched">
        <?php if ($most_searched_country) { ?>
            <p>The most searched country on this app is <?php echo $most_searched_country; ?> with <?php echo $most_searches; ?> searches.</p>
        <?php } else { ?>
            <p>No searches have been recorded yet.</p>
        <?php } ?>
    </div>
</body>
</html>