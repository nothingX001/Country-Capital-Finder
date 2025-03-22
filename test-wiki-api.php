<?php
// test-wiki-api.php
// A simple test script to verify Wikipedia API integration

// Set content type to plain text for better readability in browser
header('Content-Type: text/plain');

// Function to get Wikipedia summary for a country (copied from generate-description.php)
function getWikipediaSummary($countryName) {
    // URL encode the country name for the API request
    $encodedName = urlencode($countryName);
    
    // Make a request to the Wikipedia API
    $url = "https://en.wikipedia.org/api/rest_v1/page/summary/{$encodedName}";
    
    echo "Requesting: {$url}\n\n";
    
    // Initialize curl
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ExploreCapitals/1.0 (https://explorecapitals.com; info@explorecapitals.com)');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Status: {$status}\n\n";
    
    if ($status === 200) {
        $data = json_decode($response, true);
        if (isset($data['extract'])) {
            return $data['extract'];
        }
    }
    
    // Try a simpler query if the first one fails
    if (strpos($countryName, 'The ') === 0) {
        echo "Trying without 'The' prefix...\n\n";
        return getWikipediaSummary(substr($countryName, 4));
    }
    
    return 'No summary found.';
}

// Test countries to verify
$testCountries = [
    'France',
    'Japan',
    'Brazil',
    'Australia',
    'Egypt',
    'The Bahamas'
];

// Run tests for each country
foreach ($testCountries as $country) {
    echo "===== Testing: {$country} =====\n\n";
    $summary = getWikipediaSummary($country);
    echo "Summary:\n{$summary}\n\n";
    echo "===============================\n\n";
}
?> 