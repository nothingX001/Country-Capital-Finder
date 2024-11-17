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

        // Update the site statistics
        try {
            $conn->beginTransaction();

            // Update most recent search
            $stmt = $conn->prepare("UPDATE site_statistics SET most_recent_search = ?");
            $stmt->execute([$country]);

            // Increment total searches
            $stmt = $conn->prepare("UPDATE site_statistics SET total_searches = total_searches + 1");
            $stmt->execute();

            // Increment searches_today
            $stmt = $conn->prepare("UPDATE site_statistics SET searches_today = searches_today + 1");
            $stmt->execute();

            // Update unique countries searched
            $stmt = $conn->query("SELECT unique_countries_searched FROM site_statistics LIMIT 1");
            $current_data = $stmt->fetch(PDO::FETCH_ASSOC);

            $unique_countries = $current_data['unique_countries_searched'] ?? '';
            $unique_countries_array = $unique_countries ? explode(',', $unique_countries) : [];

            if (!in_array($country, $unique_countries_array)) {
                $unique_countries_array[] = $country;
                $updated_unique_countries = implode(',', $unique_countries_array);

                $stmt = $conn->prepare("UPDATE site_statistics SET unique_countries_searched = ?");
                $stmt->execute([$updated_unique_countries]);
            }

            // Update most searched countries
            $stmt = $conn->query("SELECT country_name, COUNT(*) as search_count FROM searches GROUP BY country_name ORDER BY search_count DESC LIMIT 1");
            $most_searched_country = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($most_searched_country) {
                $stmt = $conn->prepare("UPDATE site_statistics SET most_searched_countries = ?");
                $stmt->execute([$most_searched_country['country_name']]);
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack(); // Log the error internally without showing it to the user
            error_log("Statistics update failed: " . $e->getMessage());
        }
    } else {
        $message = "Sorry, the country you entered is not in our database.";
    }
}
?>
