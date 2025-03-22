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
    
    // Create a prompt for the country description
    $capitals = implode(', ', $countryData['capitals']);
    $prompt = "Write a comprehensive but concise two-paragraph description of {$countryData['name']}. ";
    $prompt .= "Include information about its geographic location in {$countryData['region']} ";
    
    if (!empty($countryData['subregion'])) {
        $prompt .= "({$countryData['subregion']}), ";
    }
    
    if (!empty($capitals)) {
        $prompt .= "its capital(s) {$capitals}, ";
    }
    
    if (!empty($countryData['population'])) {
        $prompt .= "its population of {$countryData['population']}, ";
    }
    
    if (!empty($countryData['area'])) {
        $prompt .= "area of {$countryData['area']} km², ";
    }
    
    if (!empty($countryData['languages'])) {
        $prompt .= "and the languages spoken ({$countryData['languages']}). ";
    }
    
    if (!empty($countryData['currency'])) {
        $prompt .= "Include that its currency is {$countryData['currency']}. ";
    }
    
    if (isset($countryData['isTerritory']) && $countryData['isTerritory'] && !empty($countryData['sovereignState'])) {
        $prompt .= "Note that it is a territory of {$countryData['sovereignState']}. ";
    }
    
    $prompt .= "Highlight key cultural, historical, or economic aspects. The first paragraph should focus on geography, demographics, and basic information. The second paragraph should highlight culture, history, and interesting facts. Keep the tone informative and engaging. Do not use markdown. Format as flowing paragraphs.";
    
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
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a knowledgeable travel guide and geography expert that provides accurate and engaging information about countries and territories around the world. Your descriptions are informative, balanced, and culturally sensitive.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7,
        'max_tokens' => 600
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
    
    // First paragraph - geography and basic facts
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
// Fallback: $description = generateDemoDescription($data);

// Return the generated description
echo json_encode([
    'success' => true,
    'description' => $description
]);
?> 