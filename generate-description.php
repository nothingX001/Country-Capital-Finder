<?php
// generate-description.php
header('Content-Type: application/json');

// Try to include config but continue if it fails (for testing purposes)
try {
    include 'config.php';
} catch (Exception $e) {
    // Continue without database connection
    // This allows us to test the API functionality without DB
}

// Get JSON data from the request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// For direct testing (when not called via AJAX)
if (empty($data) && php_sapi_name() === 'cli') {
    // Sample data for testing via command line
    $data = [
        'name' => 'Sample Country',
        'capitals' => ['Sample Capital'],
        'region' => 'Europe',
        'entityType' => 'Country'
    ];
    // Skip the JSON header when in CLI mode
    header_remove('Content-Type');
}

// Verify we have country data
if (!$data || !isset($data['name'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request data'
    ]);
    exit;
}

// Function to get Wikipedia summary for a country
function getWikipediaSummary($countryName) {
    // Sometimes Wikipedia pages use different formats
    $possibleNames = [
        $countryName,
        str_replace(' ', '_', $countryName),
        str_replace(['The ', 'the '], '', $countryName)
    ];
    
    // Some countries have special Wikipedia page names
    $specialCases = [
        'United States' => 'United_States_of_America',
        'United Kingdom' => 'United_Kingdom',
        'Russia' => 'Russia',
        'China' => 'China',
        'North Korea' => 'North_Korea',
        'South Korea' => 'South_Korea',
        'Democratic Republic of the Congo' => 'Democratic_Republic_of_the_Congo',
        'Republic of the Congo' => 'Republic_of_the_Congo'
    ];
    
    if (isset($specialCases[$countryName])) {
        $possibleNames[] = $specialCases[$countryName];
    }
    
    $wikipediaSummary = '';
    $errorDetails = [];
    
    // Try each possible name format
    foreach ($possibleNames as $name) {
        // URL encode the country name for the API request
        $encodedName = urlencode($name);
        
        // Make a request to the Wikipedia API
        $url = "https://en.wikipedia.org/api/rest_v1/page/summary/{$encodedName}";
        
        // Initialize curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ExploreCapitals/1.1 (https://explorecapitals.com; info@explorecapitals.com)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 20); // Increase timeout to 20 seconds
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verify SSL
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5); // Maximum redirects
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Store error details for debugging
        $errorDetails[] = [
            'name' => $name,
            'url' => $url,
            'status' => $status,
            'error' => $error
        ];
        
        if ($status === 200 && !empty($response)) {
            $data = json_decode($response, true);
            if (isset($data['extract']) && strlen($data['extract']) > 100) {
                // Success! We found a good summary
                return $data['extract'];
            } elseif (isset($data['extract'])) {
                // We got something, but it's too short - save it just in case
                $wikipediaSummary = $data['extract'];
            }
        }
    }
    
    // If we get here, all attempts failed or returned insufficient content
    // Return whatever we might have found, or empty string
    return $wikipediaSummary;
}

// Format Wikipedia data into a well-structured description
function formatWikipediaDescription($countryData, $wikipediaSummary) {
    $name = $countryData['name'];
    $capitals = !empty($countryData['capitals']) ? implode(', ', $countryData['capitals']) : 'its capital';
    $region = $countryData['region'] ?? 'its region';
    $languages = $countryData['languages'] ?? 'various languages';
    $entityType = strtolower($countryData['entityType'] ?? 'country');
    $population = $countryData['population'] ?? '';
    $area = $countryData['area'] ?? '';
    $currency = $countryData['currency'] ?? '';
    
    // Format Wikipedia summary into sentences
    $sentences = explode('. ', $wikipediaSummary);
    
    // Take the first 4-6 sentences from Wikipedia for the core description
    $coreSentences = array_slice($sentences, 0, min(6, max(4, ceil(count($sentences) / 2))));
    $coreDescription = implode('. ', $coreSentences) . '.';
    
    // Build additional information based on available data
    $additionalInfo = [];
    
    // Add population and area if available
    if (!empty($population) && !empty($area)) {
        $additionalInfo[] = "with a population of {$population} people spread across {$area} km²";
    } elseif (!empty($population)) {
        $additionalInfo[] = "with a population of {$population} people";
    }
    
    // Add capital information
    if (count($countryData['capitals']) > 1) {
        $additionalInfo[] = "its administrative centers include {$capitals}";
    } else {
        $additionalInfo[] = "its capital is {$capitals}";
    }
    
    // Add language information
    if (strpos($languages, ',') !== false) {
        $additionalInfo[] = "where {$languages} are spoken";
    } else {
        $additionalInfo[] = "where {$languages} is the primary language";
    }
    
    // Add currency information if available
    if (!empty($currency)) {
        $additionalInfo[] = "using {$currency} as its currency";
    }
    
    // Add region-specific unique facts
    if (strpos(strtolower($region), 'europe') !== false) {
        $additionalInfo[] = "boasting a rich heritage of medieval architecture, classical music traditions, and world-renowned cuisine";
    } 
    elseif (strpos(strtolower($region), 'asia') !== false) {
        $additionalInfo[] = "featuring ancient temples, traditional arts, and distinctive culinary traditions";
    }
    elseif (strpos(strtolower($region), 'america') !== false) {
        $additionalInfo[] = "characterized by diverse landscapes, indigenous heritage, and vibrant cultural expressions";
    }
    elseif (strpos(strtolower($region), 'africa') !== false) {
        $additionalInfo[] = "home to diverse wildlife, rich cultural traditions, and significant archaeological sites";
    }
    elseif (strpos(strtolower($region), 'oceania') !== false) {
        $additionalInfo[] = "known for its unique biodiversity, indigenous cultures, and stunning natural landscapes";
    }
    else {
        $additionalInfo[] = "distinguished by its unique cultural heritage and geographical features";
    }
    
    // Combine all information into a single comprehensive paragraph
    $additionalText = implode(', ', $additionalInfo);
    
    // Create a more natural flow by connecting the Wikipedia content with the additional information
    $finalDescription = $coreDescription . " " . $name . ", " . $additionalText . ".";
    
    // Clean up any double spaces or awkward punctuation
    $finalDescription = preg_replace('/\s+/', ' ', $finalDescription);
    $finalDescription = preg_replace('/,\./', '.', $finalDescription);
    
    return $finalDescription;
}

// Process the country data to generate a description
if (!empty($data)) {
    $countryName = $data['name'];
    $description = '';
    $debug = []; // For debugging
    
    // Get Wikipedia summary - our primary and only source
    $wikipediaSummary = getWikipediaSummary($countryName);
    $debug['wikipedia_length'] = strlen($wikipediaSummary);
    
    // If we have Wikipedia content, format it
    if (!empty($wikipediaSummary)) {
        $description = formatWikipediaDescription($data, $wikipediaSummary);
        $source = 'wikipedia';
        $debug['source_used'] = 'wikipedia';
    } else {
        // Fallback if no Wikipedia data found - this should rarely happen
        // Send a generic message to inform the user
        $description = "Information about {$countryName} is currently being updated. Please check back soon for a detailed description.";
        $source = 'fallback';
        $debug['source_used'] = 'fallback_message';
    }
    
    // Send back the response
    echo json_encode([
        'success' => true,
        'country' => $countryName,
        'description' => $description,
        'source' => $source,
        'debug' => $debug
    ]);
} else {
    // If there's no data or an invalid request, return an error
    echo json_encode([
        'success' => false,
        'error' => 'Missing or invalid country data',
        'debug' => [
            'received_data' => $data ?? 'No data received',
            'time' => date('Y-m-d H:i:s')
        ]
    ]);
}
?> 