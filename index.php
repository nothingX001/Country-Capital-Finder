<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php'; // Database connection

// Function to normalize country input
function normalize_country_input($input) {
    return ucwords(strtolower(trim($input))); // Capitalizes first letters
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $country_input = $_POST['country'];
    $country = normalize_country_input($country_input);

    // Search for the country, capital, and flag emoji in the database
    $stmt = $conn->prepare("SELECT capital_name, flag_emoji FROM countries WHERE LOWER(country_name) = LOWER(?)");
    $stmt->execute([$country]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $capital = $result['capital_name'];
        $flag = $result['flag_emoji'] ?? '';
        $message = "The capital of {$country} is {$capital}. {$flag}";

        // Log the search into the site_statistics table
        try {
            // Check if the country already exists in site_statistics
            $checkStmt = $conn->prepare("SELECT id FROM site_statistics WHERE LOWER(country_name) = LOWER(?)");
            $checkStmt->execute([$country]);
            $exists = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($exists) {
                // Update existing record
                $updateStmt = $conn->prepare("
                    UPDATE site_statistics 
                    SET search_count = search_count + 1, last_searched_at = NOW() 
                    WHERE LOWER(country_name) = LOWER(?)
                ");
                $updateStmt->execute([$country]);
            } else {
                // Insert new record
                $insertStmt = $conn->prepare("
                    INSERT INTO site_statistics (country_name, search_count, last_searched_at) 
                    VALUES (?, 1, NOW())
                ");
                $insertStmt->execute([$country]);
            }
        } catch (PDOException $e) {
            error_log("Error updating site statistics: " . $e->getMessage());
            // Do not show this message to users; fail gracefully
        }
    } else {
        $message = "Sorry, the country you entered is not in our database.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Country Capital Finder</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="main">
        <h1>CAPITAL FINDER</h1>
        <h3>🇺🇸🇪🇺 FIND THE CAPITAL OF YOUR COUNTRY 🇷🇺🇨🇳</h3>
        <form action="index.php" method="post">
            <label>ENTER A COUNTRY: </label>
            <input type="text" name="country" required>
            <input type="submit" value="SUBMIT">
        </form>

        <?php if (isset($message)) { ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php } ?>
    </div>
</body>
</html>
