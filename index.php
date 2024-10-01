<?php
// Enable error reporting to debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php'; // Database connection

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
        $message = "The capital of {$country} is {$capital}.";

        // Update search tracking
        $search_stmt = $conn->prepare("SELECT search_count FROM search_tracking WHERE country_id = ?");
        $search_stmt->bind_param("i", $country_id);
        $search_stmt->execute();
        $search_stmt->bind_result($search_count);
        $search_stmt->fetch();
        $search_stmt->close();

        if ($search_count) {
            // Update the search count
            $update_stmt = $conn->prepare("UPDATE search_tracking SET search_count = search_count + 1 WHERE country_id = ?");
            $update_stmt->bind_param("i", $country_id);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            // Insert the search count if it doesn't exist
            $insert_stmt = $conn->prepare("INSERT INTO search_tracking (country_id, search_count) VALUES (?, 1)");
            $insert_stmt->bind_param("i", $country_id);
            $insert_stmt->execute();
            $insert_stmt->close();
        }

        // Update most recent search
        $recent_stmt = $conn->prepare("INSERT INTO recent_searches (country_id, search_time) VALUES (?, NOW())");
        $recent_stmt->bind_param("i", $country_id);
        $recent_stmt->execute();
        $recent_stmt->close();
    } else {
        $message = "Sorry, the country you entered is not in our list.";
    }
}

// Fetch the most searched country
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

// Fetch the most recent search
$recent_search_stmt = $conn->prepare("
    SELECT c.country_name, r.search_time
    FROM recent_searches r
    JOIN countries c ON r.country_id = c.id
    ORDER BY r.search_time DESC
    LIMIT 1
");
$recent_search_stmt->execute();
$recent_search_stmt->bind_result($most_recent_search, $search_time);
$recent_search_stmt->fetch();
$recent_search_stmt->close();

// Convert the search time into a more readable format in ISO format for JS
if ($search_time) {
    $formatted_search_time = date("Y-m-d\TH:i:s\Z", strtotime($search_time));
} else {
    $formatted_search_time = "N/A";
}

// Get total number of searches
$total_searches_stmt = $conn->prepare("SELECT SUM(search_count) FROM search_tracking");
$total_searches_stmt->execute();
$total_searches_stmt->bind_result($total_searches);
$total_searches_stmt->fetch();
$total_searches_stmt->close();

// Get searches today
$searches_today_stmt = $conn->prepare("
    SELECT COUNT(*) FROM recent_searches 
    WHERE DATE(search_time) = CURDATE()
");
$searches_today_stmt->execute();
$searches_today_stmt->bind_result($searches_today);
$searches_today_stmt->fetch();
$searches_today_stmt->close();

// Get number of unique countries searched
$unique_countries_stmt = $conn->prepare("SELECT COUNT(DISTINCT country_id) FROM search_tracking");
$unique_countries_stmt->execute();
$unique_countries_stmt->bind_result($unique_countries_searched);
$unique_countries_stmt->fetch();
$unique_countries_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Discover capitals of countries around the world with our Country Capital Finder. Search over 195 capitals, explore fun facts, and learn geography with ease!">
    <meta name="keywords" content="country capital finder, find capitals, country capitals, capital search, world capitals, geography trivia, country capitals list">
    <meta name="author" content="country capital finder">
    <title>Country Capital Finder</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="main">
        <h1>🇺🇸🇪🇺 Find a Country's Capital 🇷🇺🇨🇳</h1>

        <form action="index.php" method="post">
            <label>Enter a country: </label>
            <input type="text" name="country" required>
            <input type="submit" value="Submit">
        </form>

        <?php if (isset($message)) { ?>
            <p class="message"><?php echo $message; ?></p>
        <?php } ?>
    </div>

    <div class="seo-content">
        <section id="why-use">
            <h2>Why Use the Country Capital Finder?</h2>
            <ul>
                <li>You can instantly <strong>find capitals</strong> of any country.</li>
                <li>You have access to <strong>up-to-date information</strong> on over 195 countries.</li>
                <li>You can explore real-time data with our <strong>site statistics</strong>.</li>
            </ul>
        </section>

        <section id="faq">
            <h2>Frequently Asked Questions (FAQ)</h2>
            <ul>
                <li>
                    <strong>What is the country capital finder?</strong>
                    <p>The country capital finder is an online tool that helps users quickly locate the capital city of any country worldwide.</p>
                </li>
                <li>
                    <strong>How accurate is the capital information provided?</strong>
                    <p>The information is sourced from reliable databases and updated regularly to ensure accuracy.</p>
                </li>
                <li>
                    <strong>How do I find the capital of a country?</strong>
                    <p>Simply enter the name of the country into the search box, and our tool will return its capital.</p>
                </li>
            </ul>
        </section>

    <!-- Site Statistics Section inside SEO Content -->
        <section id="site-stats">
            <h2>📊 Site Statistics</h2>
            <p><strong>🔝 Most Searched Country:</strong> <?php echo $most_searched_country ?? "No data yet"; ?> with <?php echo $most_searches ?? 0; ?> searches.</p>
            <p><strong>🕒 Most Recent Search:</strong> 
                <span id="recent-search-time" data-country="<?php echo $most_recent_search ?? 'N/A'; ?>" data-utc="<?php echo $formatted_search_time; ?>">
                    <?php echo $formatted_search_time; ?>
                </span>
            </p>
            <p><strong>🔢 Total Searches:</strong> <?php echo $total_searches ?? 0; ?></p>
            <p><strong>📅 Searches Today:</strong> <?php echo $searches_today ?? 0; ?></p>
            <p><strong>🌍 Unique Countries Searched:</strong> <?php echo $unique_countries_searched ?? 0; ?></p>
        </section>
        <!-- List of 195 Countries Section -->
        <section id="country-list">
            <h2>Complete List of Countries in Our Database</h2>
            <p>Here's the complete list of countries in our database. Try to guess their capital before searching in the finder!</p>
            <ul>
                <li>Afghanistan</li>
                <li>Albania</li>
                <li>Algeria</li>
                <li>Andorra</li>
                <li>Angola</li>
                <li>Antigua and Barbuda</li>
                <li>Argentina</li>
                <li>Armenia</li>
                <li>Australia</li>
                <li>Austria</li>
                <li>Azerbaijan</li>
                <li>Bahamas</li>
                <li>Bahrain</li>
                <li>Bangladesh</li>
                <li>Barbados</li>
                <li>Belarus</li>
                <li>Belgium</li>
                <li>Belize</li>
                <li>Benin</li>
                <li>Bhutan</li>
                <li>Bolivia</li>
                <li>Bosnia and Herzegovina</li>
                <li>Botswana</li>
                <li>Brazil</li>
                <li>Brunei</li>
                <li>Bulgaria</li>
                <li>Burkina Faso</li>
                <li>Burundi</li>
                <li>Cabo Verde</li>
                <li>Cambodia</li>
                <li>Cameroon</li>
                <li>Canada</li>
                <li>Central African Republic</li>
                <li>Chad</li>
                <li>Chile</li>
                <li>China</li>
                <li>Colombia</li>
                <li>Comoros</li>
                <li>Democratic Republic of the Congo</li>
                <li>Republic of the Congo</li>
                <li>Costa Rica</li>
                <li>Croatia</li>
                <li>Cuba</li>
                <li>Cyprus</li>
                <li>Czech Republic</li>
                <li>Denmark</li>
                <li>Djibouti</li>
                <li>Dominica</li>
                <li>Dominican Republic</li>
                <li>East Timor (Timor-Leste)</li>
                <li>Ecuador</li>
                <li>Egypt</li>
                <li>El Salvador</li>
                <li>Equatorial Guinea</li>
                <li>Eritrea</li>
                <li>Estonia</li>
                <li>Eswatini</li>
                <li>Ethiopia</li>
                <li>Fiji</li>
                <li>Finland</li>
                <li>France</li>
                <li>Gabon</li>
                <li>Gambia</li>
                <li>Georgia</li>
                <li>Germany</li>
                <li>Ghana</li>
                <li>Greece</li>
                <li>Grenada</li>
                <li>Guatemala</li>
                <li>Guinea</li>
                <li>Guinea-Bissau</li>
                <li>Guyana</li>
                <li>Haiti</li>
                <li>Honduras</li>
                <li>Hungary</li>
                <li>Iceland</li>
                <li>India</li>
                <li>Indonesia</li>
                <li>Iran</li>
                <li>Iraq</li>
                <li>Ireland</li>
                <li>Israel</li>
                <li>Italy</li>
                <li>Jamaica</li>
                <li>Japan</li>
                <li>Jordan</li>
                <li>Kazakhstan</li>
                <li>Kenya</li>
                <li>Kiribati</li>
                <li>North Korea</li>
                <li>South Korea</li>
                <li>Kosovo</li>
                <li>Kuwait</li>
                <li>Kyrgyzstan</li>
                <li>Laos</li>
                <li>Latvia</li>
                <li>Lebanon</li>
                <li>Lesotho</li>
                <li>Liberia</li>
                <li>Libya</li>
                <li>Liechtenstein</li>
                <li>Lithuania</li>
                <li>Luxembourg</li>
                <li>Madagascar</li>
                <li>Malawi</li>
                <li>Malaysia</li>
                <li>Maldives</li>
                <li>Mali</li>
                <li>Malta</li>
                <li>Marshall Islands</li>
                <li>Mauritania</li>
                <li>Mauritius</li>
                <li>Mexico</li>
                <li>Micronesia</li>
                <li>Moldova</li>
                <li>Monaco</li>
                <li>Mongolia</li>
                <li>Montenegro</li>
                <li>Morocco</li>
                <li>Mozambique</li>
                <li>Myanmar</li>
                <li>Namibia</li>
                <li>Nauru</li>
                <li>Nepal</li>
                <li>Netherlands</li>
                <li>New Zealand</li>
                <li>Nicaragua</li>
                <li>Niger</li>
                <li>Nigeria</li>
                <li>North Macedonia</li>
                <li>Norway</li>
                <li>Oman</li>
                <li>Pakistan</li>
                <li>Palau</li>
                <li>Panama</li>
                <li>Papua New Guinea</li>
                <li>Paraguay</li>
                <li>Peru</li>
                <li>Philippines</li>
                <li>Poland</li>
                <li>Portugal</li>
                <li>Qatar</li>
                <li>Romania</li>
                <li>Russia</li>
                <li>Rwanda</li>
                <li>Saint Kitts and Nevis</li>
                <li>Saint Lucia</li>
                <li>Saint Vincent and the Grenadines</li>
                <li>Samoa</li>
                <li>San Marino</li>
                <li>Sao Tome and Principe</li>
                <li>Saudi Arabia</li>
                <li>Senegal</li>
                <li>Serbia</li>
                <li>Seychelles</li>
                <li>Sierra Leone</li>
                <li>Singapore</li>
                <li>Slovakia</li>
                <li>Slovenia</li>
                <li>Solomon Islands</li>
                <li>Somalia</li>
                <li>South Africa</li>
                <li>South Sudan</li>
                <li>Spain</li>
                <li>Sri Lanka</li>
                <li>Sudan</li>
                <li>Suriname</li>
                <li>Sweden</li>
                <li>Switzerland</li>
                <li>Syria</li>
                <li>Taiwan</li>
                <li>Tajikistan</li>
                <li>Tanzania</li>
                <li>Thailand</li>
                <li>Togo</li>
                <li>Tonga</li>
                <li>Trinidad and Tobago</li>
                <li>Tunisia</li>
                <li>Turkey</li>
                <li>Turkmenistan</li>
                <li>Tuvalu</li>
                <li>Uganda</li>
                <li>Ukraine</li>
                <li>United Arab Emirates</li>
                <li>United Kingdom</li>
                <li>United States</li>
                <li>Uruguay</li>
                <li>Uzbekistan</li>
                <li>Vanuatu</li>
                <li>Vatican City</li>
                <li>Venezuela</li>
                <li>Vietnam</li>
                <li>Yemen</li>
                <li>Zambia</li>
                <li>Zimbabwe</li>
            </ul>
            <p>More countries to be added in the future.</p>
        </section>
    </div>
    </div>

    <script>
// Function to get the ordinal suffix for a day
function getOrdinalSuffix(day) {
    if (day > 3 && day < 21) return 'th'; // Covers 11th-13th
    switch (day % 10) {
        case 1: return 'st';
        case 2: return 'nd';
        case 3: return 'rd';
        default: return 'th';
    }
}

// Function to convert UTC time to user's local timezone with formatted date
function convertUTCtoLocal() {
    const recentSearchElement = document.getElementById('recent-search-time');
    const utcTimeString = recentSearchElement.getAttribute('data-utc');
    const countrySearched = recentSearchElement.getAttribute('data-country');

    if (utcTimeString && utcTimeString !== "N/A" && countrySearched && countrySearched !== "N/A") {
        // Parse the UTC date string into a Date object
        const utcDate = new Date(utcTimeString);

        // Check if the date is valid
        if (!isNaN(utcDate)) {
            // Extract the day, month, year, and time components
            const day = utcDate.getDate();
            const month = utcDate.toLocaleString('default', { month: 'long' });
            const weekday = utcDate.toLocaleString('default', { weekday: 'long' });
            const year = utcDate.getFullYear();

            // Format the time in 12-hour AM/PM format
            const timeOptions = {
                hour: 'numeric',
                minute: 'numeric',
                hour12: true,  // Ensure it's 12-hour format with AM/PM
                timeZoneName: 'short'
            };
            const timeString = utcDate.toLocaleTimeString('default', timeOptions);

            // Add ordinal suffix to the day
            const ordinalDay = day + getOrdinalSuffix(day);

            // Construct the formatted date string
            const formattedDateString = `${weekday}, ${month} ${ordinalDay}, ${year} at ${timeString}`;

            // Update the content of the element with the country and formatted local time
            recentSearchElement.innerText = `Someone searched for ${countrySearched} on ${formattedDateString}`;
        } else {
            recentSearchElement.innerText = "N/A";  // If invalid, show N/A
        }
    } else {
        recentSearchElement.innerText = "No recent searches.";
    }
}

// Run the conversion function after the page loads
window.onload = convertUTCtoLocal;
</script>

</body>
</html>
