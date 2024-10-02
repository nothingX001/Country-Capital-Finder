<?php
// Enable error reporting to debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php'; // Database connection

// Country flag emoji map (hard-coded for now)
$country_map = [
    "Afghanistan" => "🇦🇫",
    "Albania" => "🇦🇱",
    "Algeria" => "🇩🇿",
    "Andorra" => "🇦🇩",
    "Angola" => "🇦🇴",
    "Antigua and Barbuda" => "🇦🇬",
    "Argentina" => "🇦🇷",
    "Armenia" => "🇦🇲",
    "Australia" => "🇦🇺",
    "Austria" => "🇦🇹",
    "Azerbaijan" => "🇦🇿",
    "Bahamas" => "🇧🇸",
    "Bahrain" => "🇧🇭",
    "Bangladesh" => "🇧🇩",
    "Barbados" => "🇧🇧",
    "Belarus" => "🇧🇾",
    "Belgium" => "🇧🇪",
    "Belize" => "🇧🇿",
    "Benin" => "🇧🇯",
    "Bhutan" => "🇧🇹",
    "Bolivia" => "🇧🇴",
    "Bosnia and Herzegovina" => "🇧🇦",
    "Botswana" => "🇧🇼",
    "Brazil" => "🇧🇷",
    "Brunei" => "🇧🇳",
    "Bulgaria" => "🇧🇬",
    "Burkina Faso" => "🇧🇫",
    "Burundi" => "🇧🇮",
    "Cabo Verde" => "🇨🇻",
    "Cambodia" => "🇰🇭",
    "Cameroon" => "🇨🇲",
    "Canada" => "🇨🇦",
    "Central African Republic" => "🇨🇫",
    "Chad" => "🇹🇩",
    "Chile" => "🇨🇱",
    "China" => "🇨🇳",
    "Colombia" => "🇨🇴",
    "Comoros" => "🇰🇲",
    "Republic of the Congo" => "🇨🇬",
    "Democratic Republic of the Congo" => "🇨🇩",
    "Costa Rica" => "🇨🇷",
    "Côte d'Ivoire"=> "🇨🇮",
    "Croatia" => "🇭🇷",
    "Cuba" => "🇨🇺",
    "Cyprus" => "🇨🇾",
    "Czech Republic" => "🇨🇿",
    "Denmark" => "🇩🇰",
    "Djibouti" => "🇩🇯",
    "Dominica" => "🇩🇲",
    "Dominican Republic" => "🇩🇴",
    "East Timor" => "🇹🇱",
    "Ecuador" => "🇪🇨",
    "Egypt" => "🇪🇬",
    "El Salvador" => "🇸🇻",
    "Equatorial Guinea" => "🇬🇶",
    "Eritrea" => "🇪🇷",
    "Estonia" => "🇪🇪",
    "Eswatini" => "🇸🇿",
    "Ethiopia" => "🇪🇹",
    "Fiji" => "🇫🇯",
    "Finland" => "🇫🇮",
    "France" => "🇫🇷",
    "Gabon" => "🇬🇦",
    "Gambia" => "🇬🇲",
    "Georgia" => "🇬🇪",
    "Germany" => "🇩🇪",
    "Ghana" => "🇬🇭",
    "Greece" => "🇬🇷",
    "Grenada" => "🇬🇩",
    "Guatemala" => "🇬🇹",
    "Guinea" => "🇬🇳",
    "Guinea-Bissau" => "🇬🇼",
    "Guyana" => "🇬🇾",
    "Haiti" => "🇭🇹",
    "Honduras" => "🇭🇳",
    "Hungary" => "🇭🇺",
    "Iceland" => "🇮🇸",
    "India" => "🇮🇳",
    "Indonesia" => "🇮🇩",
    "Iran" => "🇮🇷",
    "Iraq" => "🇮🇶",
    "Ireland" => "🇮🇪",
    "Israel" => "🇮🇱",
    "Italy" => "🇮🇹",
    "Jamaica" => "🇯🇲",
    "Japan" => "🇯🇵",
    "Jordan" => "🇯🇴",
    "Kazakhstan" => "🇰🇿",
    "Kenya" => "🇰🇪",
    "Kiribati" => "🇰🇮",
    "North Korea" => "🇰🇵",
    "South Korea" => "🇰🇷",
    "Kosovo" => "🇽🇰",
    "Kuwait" => "🇰🇼",
    "Kyrgyzstan" => "🇰🇬",
    "Laos" => "🇱🇦",
    "Latvia" => "🇱🇻",
    "Lebanon" => "🇱🇧",
    "Lesotho" => "🇱🇸",
    "Liberia" => "🇱🇷",
    "Libya" => "🇱🇾",
    "Liechtenstein" => "🇱🇮",
    "Lithuania" => "🇱🇹",
    "Luxembourg" => "🇱🇺",
    "Madagascar" => "🇲🇬",
    "Malawi" => "🇲🇼",
    "Malaysia" => "🇲🇾",
    "Maldives" => "🇲🇻",
    "Mali" => "🇲🇱",
    "Malta" => "🇲🇹",
    "Marshall Islands" => "🇲🇭",
    "Mauritania" => "🇲🇷",
    "Mauritius" => "🇲🇺",
    "Mexico" => "🇲🇽",
    "Micronesia" => "🇫🇲",
    "Moldova" => "🇲🇩",
    "Monaco" => "🇲🇨",
    "Mongolia" => "🇲🇳",
    "Montenegro" => "🇲🇪",
    "Morocco" => "🇲🇦",
    "Mozambique" => "🇲🇿",
    "Myanmar" => "🇲🇲",
    "Namibia" => "🇳🇦",
    "Nauru" => "🇳🇷",
    "Nepal" => "🇳🇵",
    "Netherlands" => "🇳🇱",
    "New Zealand" => "🇳🇿",
    "Nicaragua" => "🇳🇮",
    "Niger" => "🇳🇪",
    "Nigeria" => "🇳🇬",
    "North Macedonia" => "🇲🇰",
    "Norway" => "🇳🇴",
    "Oman" => "🇴🇲",
    "Pakistan" => "🇵🇰",
    "Palau" => "🇵🇼",
    "Panama" => "🇵🇦",
    "Papua New Guinea" => "🇵🇬",
    "Palestine"=> "🇵🇸",
    "Paraguay" => "🇵🇾",
    "Peru" => "🇵🇪",
    "Philippines" => "🇵🇭",
    "Poland" => "🇵🇱",
    "Portugal" => "🇵🇹",
    "Qatar" => "🇶🇦",
    "Romania" => "🇷🇴",
    "Russia" => "🇷🇺",
    "Rwanda" => "🇷🇼",
    "Saint Kitts and Nevis" => "🇰🇳",
    "Saint Lucia" => "🇱🇨",
    "Saint Vincent and the Grenadines" => "🇻🇨",
    "Samoa" => "🇼🇸",
    "San Marino" => "🇸🇲",
    "Sao Tome and Principe" => "🇸🇹",
    "Saudi Arabia" => "🇸🇦",
    "Senegal" => "🇸🇳",
    "Serbia" => "🇷🇸",
    "Seychelles" => "🇸🇨",
    "Sierra Leone" => "🇸🇱",
    "Singapore" => "🇸🇬",
    "Slovakia" => "🇸🇰",
    "Slovenia" => "🇸🇮",
    "Solomon Islands" => "🇸🇧",
    "Somalia" => "🇸🇴",
    "South Africa" => "🇿🇦",
    "South Sudan" => "🇸🇸",
    "Spain" => "🇪🇸",
    "Sri Lanka" => "🇱🇰",
    "Sudan" => "🇸🇩",
    "Suriname" => "🇸🇷",
    "Sweden" => "🇸🇪",
    "Switzerland" => "🇨🇭",
    "Syria" => "🇸🇾",
    "Taiwan" => "🇹🇼",
    "Tajikistan" => "🇹🇯",
    "Tanzania" => "🇹🇿",
    "Thailand" => "🇹🇭",
    "Togo" => "🇹🇬",
    "Tonga" => "🇹🇴",
    "Trinidad and Tobago" => "🇹🇹",
    "Tunisia" => "🇹🇳",
    "Turkey" => "🇹🇷",
    "Turkmenistan" => "🇹🇲",
    "Tuvalu" => "🇹🇻",
    "Uganda" => "🇺🇬",
    "Ukraine" => "🇺🇦",
    "United Arab Emirates" => "🇦🇪",
    "United Kingdom" => "🇬🇧",
    "United States" => "🇺🇸",
    "Uruguay" => "🇺🇾",
    "Uzbekistan" => "🇺🇿",
    "Vanuatu" => "🇻🇺",
    "Vatican City" => "🇻🇦",
    "Venezuela" => "🇻🇪",
    "Vietnam" => "🇻🇳",
    "Yemen" => "🇾🇪",
    "Zambia" => "🇿🇲",
    "Zimbabwe" => "🇿🇼"
];

// Alias map for alternate country names
$alias_map = [
    // United States
    "USA" => "United States",
    "US" => "United States",
    "America" => "United States",
    "U.S.A." => "United States",

    // United Kingdom
    "UK" => "United Kingdom",
    "Britain" => "United Kingdom",
    "England" => "United Kingdom",
    "Great Britain" => "United Kingdom",
    "Scotland" => "United Kingdom",
    "Wales" => "United Kingdom",
    "Northern Ireland" => "United Kingdom",

    // Democratic Republic of the Congo
    "DRC" => "Democratic Republic of the Congo",
    "Congo (Kinshasa)" => "Democratic Republic of the Congo",
    "Congo-Kinshasa" => "Democratic Republic of the Congo",
    "DR Congo" => "Democratic Republic of the Congo",

    // Republic of the Congo
    "Congo" => "Republic of the Congo",
    "Congo (Brazzaville)" => "Republic of the Congo",
    "Congo-Brazzaville" => "Republic of the Congo",

    // South Korea
    "Korea (South)" => "South Korea",
    "ROK" => "South Korea",
    "Republic of Korea" => "South Korea",
    "S. Korea" => "South Korea",

    // North Korea
    "Korea (North)" => "North Korea",
    "DPRK" => "North Korea",
    "Democratic People's Republic of Korea" => "North Korea",
    "N. Korea" => "North Korea",

    // Russia
    "Russian Federation" => "Russia",
    "Rossiya" => "Russia",

    // Netherlands
    "Holland" => "Netherlands",
    "Dutchland" => "Netherlands",
    "Nederland" => "Netherlands",

    // Ivory Coast
    "Ivory Coast" => "Côte d'Ivoire",
    "Cote d'Ivoire" => "Côte d'Ivoire",

    // Myanmar
    "Burma" => "Myanmar",

    // Bolivia
    "Bolivia (Plurinational State of)" => "Bolivia",

    // Venezuela
    "Bolivarian Republic of Venezuela" => "Venezuela",

    // Syria
    "Syrian Arab Republic" => "Syria",

    // Laos
    "Lao People's Democratic Republic" => "Laos",
    "Laos PDR" => "Laos",

    // Vietnam
    "Viet Nam" => "Vietnam",
    "Socialist Republic of Vietnam" => "Vietnam",

    // Tanzania
    "United Republic of Tanzania" => "Tanzania",

    // Gambia
    "The Gambia" => "Gambia",

    // Libya
    "Libyan Arab Jamahiriya" => "Libya",

    // Moldova
    "Republic of Moldova" => "Moldova",

    // Macedonia
    "FYROM" => "North Macedonia",
    "Macedonia" => "North Macedonia",
    "Former Yugoslav Republic of Macedonia" => "North Macedonia",

    // UAE
    "UAE" => "United Arab Emirates",
    "Emirates" => "United Arab Emirates",

    // Czech Republic
    "Czechia" => "Czech Republic",

    // Swaziland
    "Eswatini" => "Swaziland",

    // Micronesia
    "Federated States of Micronesia" => "Micronesia",

    // East Timor
    "Timor-Leste" => "East Timor",
    "Democratic Republic of Timor-Leste" => "East Timor",

    // Palestine
    "State of Palestine" => "Palestine",
    "Palestinian Territories" => "Palestine",

    // Taiwan
    "Republic of China" => "Taiwan",
    "ROC" => "Taiwan",

    // Iran
    "Islamic Republic of Iran" => "Iran",

    // Egypt
    "Arab Republic of Egypt" => "Egypt",

    // Sudan
    "Republic of the Sudan" => "Sudan",

    // South Sudan
    "Republic of South Sudan" => "South Sudan",

    // Saudi Arabia
    "Kingdom of Saudi Arabia" => "Saudi Arabia",

    // Brunei
    "Brunei Darussalam" => "Brunei",

    // Bahamas
    "The Bahamas" => "Bahamas",

    // Venezuela
    "Bolivarian Republic of Venezuela" => "Venezuela",

    // Timor-Leste
    "East Timor" => "Timor-Leste",

    // Papua New Guinea
    "PNG" => "Papua New Guinea",

    // Kazakhstan
    "Republic of Kazakhstan" => "Kazakhstan",

    // Kyrgyzstan
    "Kyrgyz Republic" => "Kyrgyzstan",

    // Finland
    "Suomi" => "Finland",

    // Japan
    "Nippon" => "Japan",

    // Greece
    "Hellas" => "Greece",

    // Germany
    "Deutschland" => "Germany",

    // Spain
    "España" => "Spain",

    // Afghanistan
    "Islamic Republic of Afghanistan" => "Afghanistan",

    // Armenia
    "Hayastan" => "Armenia",

    // Azerbaijan
    "Republic of Azerbaijan" => "Azerbaijan",

    // Bangladesh
    "People's Republic of Bangladesh" => "Bangladesh",

    // Belgium
    "Belgique" => "Belgium",
    "België" => "Belgium",

    // Bosnia and Herzegovina
    "Bosnia" => "Bosnia and Herzegovina",
    "BiH" => "Bosnia and Herzegovina",

    // Botswana
    "Republic of Botswana" => "Botswana",

    // China
    "People's Republic of China" => "China",

    // Croatia
    "Hrvatska" => "Croatia",

    // Cyprus
    "Republic of Cyprus" => "Cyprus",

    // Denmark
    "Kingdom of Denmark" => "Denmark",

    // Fiji
    "Republic of Fiji" => "Fiji",

    // France
    "République française" => "France",

    // Ghana
    "Republic of Ghana" => "Ghana",

    // Iceland
    "Republic of Iceland" => "Iceland",

    // India
    "Republic of India" => "India",

    // Indonesia
    "Republic of Indonesia" => "Indonesia",

    // Ireland
    "Republic of Ireland" => "Ireland",
    "Éire" => "Ireland",

    // Italy
    "Repubblica Italiana" => "Italy",

    // Jamaica
    "Commonwealth of Jamaica" => "Jamaica",

    // Lebanon
    "Lebanese Republic" => "Lebanon",

    // Mexico
    "Mexican United States" => "Mexico",
    "Estados Unidos Mexicanos" => "Mexico",

    // Montenegro
    "Crna Gora" => "Montenegro",

    // Nepal
    "Federal Democratic Republic of Nepal" => "Nepal",

    // Philippines
    "Republic of the Philippines" => "Philippines",

    // Portugal
    "Portuguese Republic" => "Portugal",

    // Romania
    "Republic of Romania" => "Romania",

    // Rwanda
    "Republic of Rwanda" => "Rwanda",

    // Senegal
    "Republic of Senegal" => "Senegal",

    // Seychelles
    "Republic of Seychelles" => "Seychelles",

    // Singapore
    "Republic of Singapore" => "Singapore",

    // Solomon Islands
    "Solomons" => "Solomon Islands",

    // Switzerland
    "Swiss Confederation" => "Switzerland",

    // Turkey
    "Republic of Türkiye" => "Turkey",

    // Uganda
    "Republic of Uganda" => "Uganda",

    // Ukraine
    "Ukrayina" => "Ukraine",

    // Uruguay
    "Eastern Republic of Uruguay" => "Uruguay",

    // Zambia
    "Republic of Zambia" => "Zambia",

    // Zimbabwe
    "Republic of Zimbabwe" => "Zimbabwe"
];

// Function to get the flag emoji for a country
function get_flag_emoji($country) {
    global $country_map;
    return isset($country_map[$country]) ? $country_map[$country] : '';
}

// Function to normalize the country input (case-insensitive + alias support)
function normalize_country_input($input) {
    global $alias_map;

    // Convert input to lowercase
    $input = strtolower($input);

    // Check if the input matches any alias (also in lowercase)
    foreach ($alias_map as $alias => $canonical) {
        if (strtolower($alias) == $input) {
            return $canonical; // Return the canonical country name
        }
    }

    // If no alias match, capitalize the first letter of each word
    return ucwords($input);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $country_input = $_POST['country'];

    // Normalize the country input (case-insensitive and alias resolution)
    $country = normalize_country_input($country_input);

    // Search for the country in the database
    $stmt = $conn->prepare("SELECT id, capital_name FROM countries WHERE LOWER(country_name) = LOWER(?)");
    $stmt->bind_param("s", $country);
    $stmt->execute();
    $stmt->bind_result($country_id, $capital);
    $stmt->fetch();
    $stmt->close();

    if ($capital) {
        $flag_emoji = get_flag_emoji($country);
        $message = "The capital of {$country} is {$capital}. {$flag_emoji}";

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
                    <p>The country capital finder is an application that helps users quickly locate international capital cities.</p>
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

        <!-- List of countries in the database -->
        <section id="complete-list">
            <h2>Complete List of Countries in Our Database</h2>
            <p>Here's the complete list of countries in our database. Try to guess its capital before searching in the finder!</p>
            <ul>
                <?php foreach ($country_map as $country => $flag): ?>
                    <li><?php echo $country . " " . $flag; ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
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
            recentSearchElement.innerText = `Someone searched for ${countrySearched} on ${formattedDateString}.`;
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