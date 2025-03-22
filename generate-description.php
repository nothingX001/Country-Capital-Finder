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
    // URL encode the country name for the API request
    $encodedName = urlencode($countryName);
    
    // Make a request to the Wikipedia API
    $url = "https://en.wikipedia.org/api/rest_v1/page/summary/{$encodedName}";
    
    // Initialize curl
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ExploreCapitals/1.0 (https://explorecapitals.com; info@explorecapitals.com)');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($status === 200) {
        $data = json_decode($response, true);
        if (isset($data['extract'])) {
            return $data['extract'];
        }
    }
    
    // Try a simpler query if the first one fails
    if (strpos($countryName, 'The ') === 0) {
        return getWikipediaSummary(substr($countryName, 4));
    }
    
    return '';
}

// OPTION 1: Use an existing AI API like OpenAI
function generateDescriptionWithOpenAI($countryData) {
    // Get API key from environment variable or config
    $apiKey = getOpenAIKey();
    
    // If no API key is available, use the demo description
    if (empty($apiKey)) {
        return generateDemoDescription($countryData);
    }
    
    // For testing only: If called directly, return a sample immediately
    if (php_sapi_name() === 'cli' && !defined('OPENAI_API_FORCE_CALL')) {
        return generateDemoDescription($countryData);
    }
    
    // Get Wikipedia summary for additional context
    $wikipediaSummary = getWikipediaSummary($countryData['name']);
    
    // Create a prompt for the country description
    $capitals = implode(', ', $countryData['capitals']);
    
    $prompt = "Write an engaging and educational two-paragraph description about {$countryData['name']}. ";
    
    // Add basic country facts as context
    $prompt .= "\n\nCountry Facts:";
    $prompt .= "\n- Location: {$countryData['region']}" . (!empty($countryData['subregion']) ? " ({$countryData['subregion']})" : "");
    $prompt .= !empty($capitals) ? "\n- Capital(s): {$capitals}" : "";
    $prompt .= !empty($countryData['population']) ? "\n- Population: {$countryData['population']}" : "";
    $prompt .= !empty($countryData['area']) ? "\n- Area: {$countryData['area']} km²" : "";
    $prompt .= !empty($countryData['languages']) ? "\n- Languages: {$countryData['languages']}" : "";
    $prompt .= !empty($countryData['currency']) ? "\n- Currency: {$countryData['currency']}" : "";
    
    if (isset($countryData['isTerritory']) && $countryData['isTerritory'] && !empty($countryData['sovereignState'])) {
        $prompt .= "\n- Status: Territory of {$countryData['sovereignState']}";
    }
    
    // Add Wikipedia summary if available
    if (!empty($wikipediaSummary)) {
        $prompt .= "\n\nWikipedia information:\n{$wikipediaSummary}";
    }
    
    // Specific instructions to create varied and interesting descriptions
    $prompt .= "\n\nIn your response, include a mix of the following elements (choosing different aspects each time to ensure variety):";
    $prompt .= "\n1) Brief history with key events that shaped the country";
    $prompt .= "\n2) Notable cultural traditions, customs, or festivals";
    $prompt .= "\n3) Famous cuisine, dishes, or beverages";
    $prompt .= "\n4) Key landmarks or tourist attractions";
    $prompt .= "\n5) Notable figures from history, arts, sports, or politics";
    $prompt .= "\n6) Interesting geographic features";
    $prompt .= "\n7) Economic highlights (industries, exports, etc.)";
    $prompt .= "\n8) Unique or lesser-known facts that would surprise visitors";
    
    $prompt .= "\n\nThe first paragraph should establish the country's identity with basic information and one or two key historical/cultural aspects. The second paragraph should provide more depth on selected aspects from the list above.";
    
    $prompt .= "\n\nKeep the tone informative, educational, and engaging. Format as flowing paragraphs without headings or bullet points. Do not repeat the same facts that are obvious from the country information card. Instead, provide information that helps users develop a deeper understanding of the country.";
    
    // Call OpenAI API
    $response = callOpenAI($apiKey, $prompt);
    
    // Process and return the response
    if (isset($response['choices'][0]['message']['content'])) {
        return $response['choices'][0]['message']['content'];
    } else {
        // Fallback to demo description if API call fails
        return generateDemoDescription($countryData);
    }
}

// Function to retrieve the OpenAI API key securely
function getOpenAIKey() {
    // Option 1: Get from environment variable (recommended)
    if (getenv('OPENAI_API_KEY')) {
        return getenv('OPENAI_API_KEY');
    }
    
    // Option 2: Get from a separate config file that's not checked into version control
    $configFile = __DIR__ . '/api_keys.php';
    if (file_exists($configFile)) {
        include $configFile; // Should define $openai_api_key
        if (isset($openai_api_key)) {
            return $openai_api_key;
        }
    }
    
    // Option 3: Get from the main config file (make sure it's not in version control)
    global $conn, $openai_api_key;
    if (isset($openai_api_key)) {
        return $openai_api_key;
    }
    
    // If no key is found, return empty to trigger fallback
    return '';
}

function callOpenAI($apiKey, $prompt) {
    $url = 'https://api.openai.com/v1/chat/completions';
    
    $data = [
        'model' => 'gpt-4',  // Using GPT-4 for better quality descriptions
        'messages' => [
            ['role' => 'system', 'content' => 'You are a knowledgeable travel guide, historian, and cultural expert that provides accurate, engaging, and educational information about countries and territories around the world. Your descriptions are informative, balanced, and culturally sensitive.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.8,  // Slightly higher temperature for more variety
        'max_tokens' => 650
    ];
    
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => json_encode($data),
            'ignore_errors' => true
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        return null;
    }
    
    return json_decode($response, true);
}

// OPTION 2: Generate a demo description without an API
function generateDemoDescription($countryData) {
    $name = $countryData['name'];
    $region = $countryData['region'] ?? 'its region';
    $subregion = $countryData['subregion'] ?? '';
    $population = $countryData['population'] ?? 'a significant population';
    $languages = $countryData['languages'] ?? 'various languages';
    $capitals = !empty($countryData['capitals']) ? implode(', ', $countryData['capitals']) : 'its capital';
    
    // Try to get some Wikipedia information for the fallback
    $wikiSummary = getWikipediaSummary($name);
    
    if (!empty($wikiSummary)) {
        // If we have Wikipedia data, create a more interesting description
        $sentences = explode('. ', $wikiSummary);
        $firstPara = array_slice($sentences, 0, min(4, count($sentences)));
        $firstPara = implode('. ', $firstPara) . '.';
        
        $secondPara = "With " . (count($countryData['capitals']) > 1 ? "capitals including {$capitals}" : "its capital at {$capitals}") . 
                     ", {$name} has a rich cultural heritage. " .
                     "The people speak {$languages}, which contributes to the country's unique identity. " .
                     "Visitors are often drawn to its distinctive culture, traditions, and " .
                     (strpos(strtolower($region), 'europe') !== false ? "historical architecture and cuisine." : 
                     (strpos(strtolower($region), 'asia') !== false ? "ancient traditions and diverse landscapes." : 
                     (strpos(strtolower($region), 'america') !== false ? "natural beauty and vibrant cities." : 
                     (strpos(strtolower($region), 'africa') !== false ? "rich cultural traditions and stunning landscapes." : 
                     (strpos(strtolower($region), 'oceania') !== false ? "unique ecosystems and beautiful beaches." : 
                     "unique geographical and cultural features.")))));
        
        return $firstPara . "\n\n" . $secondPara;
    }
    
    // Original fallback if no Wikipedia data
    $description = "{$name} is a " . (strpos(strtolower($countryData['entityType'] ?? ''), 'territory') !== false ? 'territory' : 'country') . " located in {$region}";
    if (!empty($subregion)) {
        $description .= ", specifically in the {$subregion} subregion";
    }
    $description .= ". With {$population} inhabitants, it has " . (strpos($population, 'million') !== false ? "one of the larger populations in the region" : "a population that contributes to the region's diversity") . ". ";
    $description .= "The " . (count($countryData['capitals']) > 1 ? "capitals are {$capitals}, which serve" : "capital is {$capitals}, which serves") . " as the political and cultural center";
    $description .= count($countryData['capitals']) > 1 ? "s" : "";
    $description .= " of the " . (strpos(strtolower($countryData['entityType'] ?? ''), 'territory') !== false ? 'territory' : 'nation') . ".";
    
    // Second paragraph - culture and significance
    $description .= "\n\n{$name} is known for its unique cultural heritage and traditions. ";
    $description .= "The people speak {$languages}, which " . (strpos($languages, ',') !== false ? "reflect the linguistic diversity of the region" : "is an important part of the national identity") . ". ";
    $description .= "The country has a rich history that has shaped its current social and economic landscape. ";
    $description .= "Visitors to {$name} often appreciate its " . (strpos(strtolower($region), 'europe') !== false ? "historical architecture, cuisine, and cultural festivals" : 
                    (strpos(strtolower($region), 'asia') !== false ? "ancient traditions, diverse cuisines, and natural landscapes" : 
                    (strpos(strtolower($region), 'america') !== false ? "natural beauty, vibrant cities, and cultural diversity" : 
                    (strpos(strtolower($region), 'africa') !== false ? "rich cultural traditions, diverse wildlife, and stunning landscapes" : 
                    (strpos(strtolower($region), 'oceania') !== false ? "unique ecosystems, beautiful beaches, and indigenous cultures" : 
                    "unique cultural aspects and geographic features"))))) . ".";
    
    return $description;
}

// Choose which method to use
$description = generateDescriptionWithOpenAI($data); // Uses real API if key is available, falls back to demo if not

// Return the generated description
echo json_encode([
    'success' => true,
    'description' => $description
]);
?> 