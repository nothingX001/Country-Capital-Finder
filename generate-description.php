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
    
    $prompt = "Write an engaging, educational, and comprehensive three-paragraph description about {$countryData['name']}. ";
    
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
    $prompt .= "\n\nIn your response, incorporate a comprehensive mix of the following elements:";
    $prompt .= "\n1) Fascinating historical narrative with key events and periods that shaped the country's identity";
    $prompt .= "\n2) Rich cultural traditions, customs, or festivals that define the nation's character";
    $prompt .= "\n3) Detailed culinary traditions, including famous dishes, beverages, and unique ingredients";
    $prompt .= "\n4) Notable architectural landmarks, natural wonders, and must-visit destinations";
    $prompt .= "\n5) Influential figures from history, arts, literature, science, sports, or politics";
    $prompt .= "\n6) Distinctive geographic features and biodiversity that make the country unique";
    $prompt .= "\n7) Economic landscape, including major industries, trade relationships, and global contributions";
    $prompt .= "\n8) Contemporary society, including modern challenges, achievements, and national aspirations";
    $prompt .= "\n9) Unusual or lesser-known facts that would surprise even well-traveled visitors";
    
    $prompt .= "\n\nStructure your response in three well-developed paragraphs:";
    $prompt .= "\n- First paragraph: Establish the country's identity, geographical context, and introduce 1-2 most distinctive aspects (historical or cultural significance).";
    $prompt .= "\n- Second paragraph: Explore the country's cultural heritage, traditions, cuisine, and important historical developments that shaped the nation.";
    $prompt .= "\n- Third paragraph: Highlight modern aspects including economy, tourism highlights, contemporary society, and conclude with what makes this country particularly special on the world stage.";
    
    $prompt .= "\n\nWrite in an engaging, authoritative, and educational tone that conveys genuine expertise about the country. Use vivid language and specific details rather than generalizations. Avoid simply restating the basic facts already provided in the country information card. Instead, provide rich context and insights that help users develop a deeper understanding and appreciation of the country.";
    
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
        'model' => 'gpt-4-turbo',  // Using the latest GPT-4 model for better quality descriptions
        'messages' => [
            ['role' => 'system', 'content' => 'You are a world-renowned expert on geography, history, culture, and global affairs with decades of experience researching and writing about countries worldwide. Your descriptions combine academic depth with engaging storytelling, offering readers rich, nuanced portraits of nations that balance historical context, cultural insights, and contemporary relevance. You provide accurate, balanced, and culturally sensitive information that avoids stereotypes while highlighting what makes each country truly distinctive.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7,  // Balanced for creativity and accuracy
        'max_tokens' => 1000   // Increased token limit for more comprehensive descriptions
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
    $entityType = strtolower($countryData['entityType'] ?? 'country');
    
    // Try to get some Wikipedia information for the fallback
    $wikiSummary = getWikipediaSummary($name);
    
    if (!empty($wikiSummary)) {
        // If we have Wikipedia data, create a more interesting description
        $sentences = explode('. ', $wikiSummary);
        $firstPara = array_slice($sentences, 0, min(5, count($sentences)));
        $firstPara = implode('. ', $firstPara) . '.';
        
        // Create a more detailed second paragraph
        $secondPara = "{$name} is renowned for its rich cultural heritage and diverse traditions. " .
                     (count($countryData['capitals']) > 1 ? "Its capitals include {$capitals}, each offering unique cultural experiences. " : "Its capital, {$capitals}, serves as the cultural and political heart of the nation. ") .
                     "The people speak " . (strpos($languages, ',') !== false ? "several languages including {$languages}, reflecting the country's diverse heritage" : "{$languages}, which forms an essential part of the national identity") . ". " .
                     "The country is celebrated for its " . 
                     (strpos(strtolower($region), 'europe') !== false ? "historic architecture, artistic traditions, and renowned cuisine featuring local specialties. " : 
                     (strpos(strtolower($region), 'asia') !== false ? "ancient cultural practices, distinctive art forms, and flavorful culinary traditions. " : 
                     (strpos(strtolower($region), 'america') !== false ? "vibrant music, diverse cuisine, and spectacular natural landscapes. " : 
                     (strpos(strtolower($region), 'africa') !== false ? "rich musical heritage, traditional crafts, and breathtaking natural environments. " : 
                     (strpos(strtolower($region), 'oceania') !== false ? "unique island culture, pristine natural beauty, and strong connection to the ocean. " : 
                     "distinctive cultural identity and geographic features. ")))));
        
        // Add a third paragraph about modern aspects
        $thirdPara = "In modern times, " . $name . " has " . 
                    (isset($countryData['population']) && strpos($population, 'million') !== false ? "developed into a significant nation with a population of {$population}. " : "evolved while maintaining its unique character. ") .
                    "Its economy is based on " .
                    (strpos(strtolower($region), 'europe') !== false ? "a mix of services, manufacturing, and technology sectors. " : 
                    (strpos(strtolower($region), 'asia') !== false ? "diverse industries ranging from manufacturing to innovative technologies. " : 
                    (strpos(strtolower($region), 'america') !== false ? "natural resources, agriculture, and expanding service sectors. " : 
                    (strpos(strtolower($region), 'africa') !== false ? "agriculture, mining, and increasingly, tourism and technology. " : 
                    (strpos(strtolower($region), 'oceania') !== false ? "tourism, agriculture, and maritime industries. " : 
                    "a variety of sectors adapted to its resources and location. "))))) .
                    "Visitors to " . $name . " are drawn to its " .
                    (strpos(strtolower($region), 'europe') !== false ? "historical sites, museums, and picturesque landscapes. " : 
                    (strpos(strtolower($region), 'asia') !== false ? "ancient temples, bustling markets, and diverse natural wonders. " : 
                    (strpos(strtolower($region), 'america') !== false ? "national parks, vibrant cities, and cultural festivals. " : 
                    (strpos(strtolower($region), 'africa') !== false ? "wildlife reserves, dramatic landscapes, and rich cultural experiences. " : 
                    (strpos(strtolower($region), 'oceania') !== false ? "stunning beaches, unique wildlife, and warm hospitality. " : 
                    "unique attractions and authentic cultural experiences. "))))) .
                    "The country continues to shape its identity while preserving its valuable heritage and traditions.";
        
        return $firstPara . "\n\n" . $secondPara . "\n\n" . $thirdPara;
    }
    
    // Complete fallback if no Wikipedia data
    $description = "{$name} is a " . ($entityType === 'territory' ? 'territory' : 'country') . " located in {$region}";
    if (!empty($subregion)) {
        $description .= ", specifically in the {$subregion} subregion";
    }
    $description .= ". With " . (isset($countryData['population']) ? "{$population} inhabitants" : "its population") . ", it represents an important presence in the region. ";
    $description .= "The " . (count($countryData['capitals']) > 1 ? "capitals are {$capitals}, which serve" : "capital is {$capitals}, which serves") . " as the political and cultural center";
    $description .= count($countryData['capitals']) > 1 ? "s" : "";
    $description .= " of the " . ($entityType === 'territory' ? 'territory' : 'nation') . ". The geographical landscape features diverse terrain that contributes to its unique character and natural resources.";
    
    // Second paragraph - culture and heritage
    $description .= "\n\n{$name} is known for its rich cultural heritage and diverse traditions that have evolved over centuries. ";
    $description .= "The people speak " . (strpos($languages, ',') !== false ? "several languages including {$languages}, reflecting the country's multicultural nature" : "{$languages}, which forms an essential element of the national identity") . ". ";
    $description .= "Traditional arts, crafts, music, and dance play important roles in expressing the cultural identity, while local cuisine features distinct flavors and preparation methods unique to the region. ";
    $description .= "Religious practices and festivals throughout the year showcase the spiritual traditions that remain an integral part of daily life.";
    
    // Third paragraph - modern aspects and significance
    $description .= "\n\nIn contemporary times, {$name} has developed " . 
                    ($entityType === 'territory' ? "unique administrative structures while maintaining connections to its sovereign state. " : "its own political and economic systems adapted to regional and global contexts. ") .
                    "The economy encompasses various sectors including " . 
                    (strpos(strtolower($region), 'europe') !== false ? "services, manufacturing, and technology. " : 
                    (strpos(strtolower($region), 'asia') !== false ? "agriculture, manufacturing, and emerging technologies. " : 
                    (strpos(strtolower($region), 'america') !== false ? "natural resources, agriculture, and services. " : 
                    (strpos(strtolower($region), 'africa') !== false ? "agriculture, mineral extraction, and tourism. " : 
                    (strpos(strtolower($region), 'oceania') !== false ? "tourism, agriculture, and maritime industries. " : 
                    "diverse industries adapted to local resources. "))))) .
                    "Visitors are attracted to " . 
                    (strpos(strtolower($region), 'europe') !== false ? "historical architecture, museums, and cultural sites. " : 
                    (strpos(strtolower($region), 'asia') !== false ? "ancient monuments, vibrant markets, and natural landscapes. " : 
                    (strpos(strtolower($region), 'america') !== false ? "natural wonders, cultural heritage sites, and urban attractions. " : 
                    (strpos(strtolower($region), 'africa') !== false ? "wildlife reserves, scenic vistas, and cultural experiences. " : 
                    (strpos(strtolower($region), 'oceania') !== false ? "pristine beaches, unique ecosystems, and island culture. " : 
                    "its distinctive attractions and authentic experiences. "))))) .
                    "As {$name} navigates the challenges and opportunities of globalization, it continues to preserve its unique identity while engaging with the broader international community.";
    
    return $description;
}

// Process the country data to generate a description
if (!empty($data)) {
    $countryName = $data['name'];
    $description = '';
    
    // Option 1: Use OpenAI if API key is available
    $description = generateDescriptionWithOpenAI($data);
    
    // Send back the response
    echo json_encode([
        'success' => true,
        'country' => $countryName,
        'description' => $description,
        // Let's include information about which method was used (for debugging/analytics)
        'source' => !empty(getOpenAIKey()) ? 'ai' : 'fallback'
    ]);
    exit;
}
?> 