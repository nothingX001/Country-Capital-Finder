<?php
// Enable error reporting
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
    $stmt->execute([$country]); // Parameterized query for security
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $capital = $result['capital_name'];
        $flag = $result['flag_emoji'] ?? ''; // Use flag from the database
        $message = "The capital of {$country} is {$capital}. {$flag}";

        // Update the site statistics table
        $conn->beginTransaction();
        try {
            // Update most recent search
            $stmt = $conn->prepare("UPDATE site_statistics SET most_recent_search = ?");
            $stmt->execute([$country]);

            // Increment total searches
            $stmt = $conn->prepare("UPDATE site_statistics SET total_searches = total_searches + 1");
            $stmt->execute();

            // Increment searches_today
            $stmt = $conn->prepare("UPDATE site_statistics SET searches_today = searches_today + 1");
            $stmt->execute();

            // Add country to unique countries searched if not already present
            $stmt = $conn->prepare("SELECT most_searched_countries FROM site_statistics");
            $stmt->execute();
            $current_data = $stmt->fetch(PDO::FETCH_ASSOC);

            $most_searched = $current_data['most_searched_countries'];
            $most_searched_array = explode(',', $most_searched);
            $country = strtolower($country);

            if (!in_array($country, $most_searched_array)) {
                $most_searched_array[] = $country;
                $updated_most_searched = implode(',', $most_searched_array);

                $stmt = $conn->prepare("UPDATE site_statistics SET most_searched_countries = ?");
                $stmt->execute([$updated_most_searched]);
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            $message = "An error occurred while updating statistics.";
        }
    } else {
        $message = "Sorry, the country you entered is not in our database.";
    }
}
?>
