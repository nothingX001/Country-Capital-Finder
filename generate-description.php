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
        curl_setopt($ch, CURLOPT_USERAGENT, 'ExploreCapitals/1.0 (https://explorecapitals.com; info@explorecapitals.com)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Increased from 10 seconds to 15
        
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
        
        if ($status === 200) {
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
    
    // Format Wikipedia summary into paragraphs
    $sentences = explode('. ', $wikipediaSummary);
    
    // First paragraph: Use first several sentences from Wikipedia
    $firstParaCount = min(6, max(3, ceil(count($sentences) / 3))); // Adaptive paragraph size
    $firstPara = array_slice($sentences, 0, $firstParaCount);
    $firstPara = implode('. ', $firstPara) . '.';
    
    // Second paragraph: Use next set of sentences from Wikipedia
    // If Wikipedia has enough content, use more sentences for second paragraph
    if (count($sentences) >= 9) {
        $secondParaCount = min(7, max(3, ceil(count($sentences) / 3)));
        $secondParaStart = $firstParaCount;
        $secondPara = array_slice($sentences, $secondParaStart, $secondParaCount);
        $secondPara = implode('. ', $secondPara) . '.';
    } else {
        // If Wikipedia doesn't have enough content, generate the second paragraph
        $secondPara = "{$name} is known for its rich cultural heritage and diverse traditions. " .
                     (count($countryData['capitals']) > 1 ? "Its capitals include {$capitals}, each offering unique cultural experiences. " : "Its capital, {$capitals}, serves as the cultural and political heart of the nation. ") .
                     "The people speak " . (strpos($languages, ',') !== false ? "several languages including {$languages}, reflecting the country's diverse heritage" : "{$languages}, which forms an essential part of the national identity") . ". " .
                     "The country is celebrated for its " . 
                     (strpos(strtolower($region), 'europe') !== false ? "historic architecture, artistic traditions, and renowned cuisine featuring local specialties. " : 
                     (strpos(strtolower($region), 'asia') !== false ? "ancient cultural practices, distinctive art forms, and flavorful culinary traditions. " : 
                     (strpos(strtolower($region), 'america') !== false ? "vibrant music, diverse cuisine, and spectacular natural landscapes. " : 
                     (strpos(strtolower($region), 'africa') !== false ? "rich musical heritage, traditional crafts, and breathtaking natural environments. " : 
                     (strpos(strtolower($region), 'oceania') !== false ? "unique island culture, pristine natural beauty, and strong connection to the ocean. " : 
                     "distinctive cultural identity and geographic features. ")))));
    }
    
    // Third paragraph: Use remaining sentences from Wikipedia or generate if not enough
    if (count($sentences) >= 12) {
        $thirdParaStart = $firstParaCount + $secondParaCount;
        $thirdParaCount = min(7, count($sentences) - $thirdParaStart);
        $thirdPara = array_slice($sentences, $thirdParaStart, $thirdParaCount);
        $thirdPara = implode('. ', $thirdPara) . '.';
    } else {
        // Generate third paragraph if not enough Wikipedia content
        $thirdPara = "In contemporary times, {$name} has developed " . 
                    ($entityType === 'territory' ? "unique administrative structures while maintaining connections to its sovereign state. " : "its own political and economic systems adapted to regional and global contexts. ") .
                    "The economy encompasses various sectors including " . 
                    (strpos(strtolower($region), 'europe') !== false ? "services, manufacturing, and technology. " : 
                    (strpos(strtolower($region), 'asia') !== false ? "agriculture, manufacturing, and emerging technologies. " : 
                    (strpos(strtolower($region), 'america') !== false ? "natural resources, agriculture, and services. " : 
                    (strpos(strtolower($region), 'africa') !== false ? "agriculture, mineral extraction, and tourism. " : 
                    (strpos(strtolower($region), 'oceania') !== false ? "tourism, agriculture, and maritime industries. " : 
                    "diverse industries adapted to local resources. "))))) .
                    "Visitors to {$name} are drawn to experience its unique culture, landscapes, and heritage firsthand.";
    }
    
    // Combine the paragraphs into the final description
    return $firstPara . "\n\n" . $secondPara . "\n\n" . $thirdPara;
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
    exit;
}
?> 