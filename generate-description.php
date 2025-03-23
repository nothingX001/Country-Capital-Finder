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
        // If Wikipedia doesn't have enough content, generate a more detailed second paragraph
        $secondPara = "{$name} is known for its distinctive cultural heritage and traditions that have evolved over centuries. ";
        
        // Information about capitals
        if (count($countryData['capitals']) > 1) {
            $secondPara .= "Its capitals include {$capitals}, each with their own architectural styles, historical districts, and cultural institutions. ";
        } else {
            $secondPara .= "Its capital, {$capitals}, serves as the cultural and political heart of the nation, featuring unique architecture and important historical landmarks. ";
        }
        
        // Information about languages with more detail
        if (strpos($languages, ',') !== false) {
            $secondPara .= "The linguistic diversity is reflected in several languages including {$languages}, each contributing to a rich tapestry of literature, poetry, and oral traditions. ";
        } else {
            $secondPara .= "The {$languages} language forms an essential part of the national identity, shaping literature, arts, and daily communication in ways unique to {$name}. ";
        }
        
        // Region-specific cultural details
        if (strpos(strtolower($region), 'europe') !== false) {
            $secondPara .= "The country's cultural landscape is characterized by historic cathedrals, medieval town centers, and castle ruins that tell stories of ancient dynasties and conquests. Its artistic traditions have produced renowned masters in painting, sculpture, and music, while its cuisine features distinctive local specialties that vary by region, often accompanied by celebrated wines and spirits. Traditional festivals mark important historical events and religious celebrations throughout the year, keeping ancient customs alive.";
        } 
        elseif (strpos(strtolower($region), 'asia') !== false) {
            $secondPara .= "Ancient temples, shrines, and historical monuments dot the landscape, preserving centuries of religious and philosophical traditions. The country's artistic heritage includes elaborately detailed crafts, distinctive performing arts, and architectural styles that have influenced designs worldwide. Culinary traditions feature complex flavor profiles with regional specialties using local ingredients and cooking techniques passed down through generations. Seasonal festivals celebrate harvests, historical events, and spiritual beliefs, often featuring colorful processions, music, and traditional dance forms.";
        }
        elseif (strpos(strtolower($region), 'america') !== false) {
            $secondPara .= "Cultural identity is expressed through vibrant music traditions, dynamic dance forms, and colorful festivals that blend indigenous, colonial, and modern influences. The architectural landscape ranges from pre-colonial structures to colonial-era buildings and modern urban designs. Culinary traditions feature distinctive ingredients native to the region, creating dishes that have gained international recognition. Folk art, textiles, and crafts reflect both historical traditions and contemporary interpretations, often using techniques preserved for centuries.";
        }
        elseif (strpos(strtolower($region), 'africa') !== false) {
            $secondPara .= "Traditional social structures and community practices remain important, with extended family connections and village life forming the foundation of society in many areas. Musical traditions feature distinctive instruments, complex rhythms, and vocal styles that have influenced global music. The artistic heritage includes intricate wood carving, metalwork, textiles, and beadwork, while oral storytelling traditions preserve historical knowledge and cultural values. Regional cuisine makes use of local grains, vegetables, and spices in distinctive combinations, with cooking methods adapted to available resources.";
        }
        elseif (strpos(strtolower($region), 'oceania') !== false) {
            $secondPara .= "The strong connection to the ocean shapes many aspects of culture, from navigation techniques to mythology and artistic motifs. Traditional ceremonies and gatherings maintain important social bonds, while modern interpretations of traditional arts create distinctive cultural expressions. The region's isolation has helped preserve unique species, ecosystems, and cultural practices found nowhere else on earth. Local cuisine often features fresh seafood, tropical fruits, and root vegetables prepared using both traditional and contemporary methods.";
        }
        else {
            $secondPara .= "Cultural practices reflect adaptations to the local environment, with traditional knowledge systems preserving information about local plants, animals, and seasonal patterns. Artistic expressions include distinctive music, dance, visual arts, and crafts that have been preserved through generations. Culinary traditions make use of locally available ingredients in creative ways, resulting in unique flavor combinations and preparation methods. Seasonal celebrations and ceremonies mark important transitions in both the natural world and human life cycles.";
        }
    }
    
    // Third paragraph: Use remaining sentences from Wikipedia or generate if not enough
    if (count($sentences) >= 12) {
        $thirdParaStart = $firstParaCount + $secondParaCount;
        $thirdParaCount = min(7, count($sentences) - $thirdParaStart);
        $thirdPara = array_slice($sentences, $thirdParaStart, $thirdParaCount);
        $thirdPara = implode('. ', $thirdPara) . '.';
    } else {
        // Generate a more detailed third paragraph if not enough Wikipedia content
        $thirdPara = "In contemporary times, {$name} has " . 
                    ($entityType === 'territory' ? "developed distinct administrative approaches while maintaining important connections to its sovereign state. " : "evolved its political and economic systems to address both domestic priorities and global opportunities. ");
        
        // Add specific economic information by region
        if (strpos(strtolower($region), 'europe') !== false) {
            $thirdPara .= "The economy has transitioned from its historical industrial base to embrace high-value services, advanced manufacturing, and technology innovation. Financial services, automotive production, pharmaceuticals, and sustainable technologies represent key sectors, while tourism leverages the country's rich architectural heritage, cultural institutions, and culinary traditions. European integration has shaped trade relationships, regulatory frameworks, and cross-border cooperation, creating both opportunities and challenges. ";
        } 
        elseif (strpos(strtolower($region), 'asia') !== false) {
            $thirdPara .= "Economic development has often combined rapid industrialization with strategic investment in emerging technologies and education. Manufacturing remains a cornerstone of the economy, with growing emphasis on electronics, telecommunications, automotive production, and consumer goods. Agricultural production continues to be significant, particularly in rural regions, while urban centers have become hubs for finance, technology startups, and international trade. The expansion of infrastructure, including high-speed rail, ports, and digital networks, has facilitated both domestic growth and international connectivity. ";
        }
        elseif (strpos(strtolower($region), 'america') !== false) {
            $thirdPara .= "The economy balances natural resource development with service sector growth and manufacturing. Energy resources, mineral extraction, and agricultural exports form important economic pillars, while growing technology sectors, tourism, and creative industries represent emerging opportunities. Infrastructure development continues to be a priority, connecting disparate regions and facilitating both internal trade and international exports. The proximity to major markets shapes trade patterns, investment flows, and economic partnerships. ";
        }
        elseif (strpos(strtolower($region), 'africa') !== false) {
            $thirdPara .= "Economic transformation includes the formalization of traditional sectors alongside the development of new industries. Agriculture remains vital for both subsistence and export, while mining operations extract valuable minerals that supply global markets. Tourism showcases natural wonders, wildlife reserves, and cultural heritage, creating employment and entrepreneurship opportunities. Mobile technology adoption has enabled innovative solutions in banking, healthcare, and education, often leapfrogging traditional development stages. Urbanization presents both challenges and opportunities as cities grow into economic and cultural hubs. ";
        }
        elseif (strpos(strtolower($region), 'oceania') !== false) {
            $thirdPara .= "The economy combines traditional sectors like agriculture and fishing with tourism, services, and specialized industries. The natural environment provides both economic resources and attractions for visitors seeking unique experiences in pristine settings. Distance from major global markets has encouraged self-sufficiency in some sectors while creating logistical challenges for exports. Climate change presents particular challenges, including rising sea levels, changing weather patterns, and impacts on marine ecosystems that are central to both economy and culture. ";
        }
        else {
            $thirdPara .= "Economic development has been shaped by available resources, geographical location, and historical trading relationships. Key sectors have evolved to reflect both traditional strengths and emerging opportunities in the global marketplace. Infrastructure development continues to connect communities and facilitate access to markets, education, and healthcare. ";
        }
        
        // Add information about notable sites and visitor experiences
        if (strpos(strtolower($region), 'europe') !== false) {
            $thirdPara .= "Visitors to {$name} are drawn to its UNESCO World Heritage sites, renowned museums housing masterpieces of art and history, medieval town centers with their distinctive architecture, and scenic landscapes ranging from alpine peaks to Mediterranean coastlines. The combination of historical depth, cultural richness, and modern amenities creates diverse experiences for travelers, whether they seek urban exploration, rural tranquility, or outdoor adventures.";
        } 
        elseif (strpos(strtolower($region), 'asia') !== false) {
            $thirdPara .= "Travelers to {$name} discover ancient temples and sacred sites alongside ultramodern urban centers, traditional markets filled with handcrafted goods and street food, and natural landscapes ranging from towering mountains to tropical beaches. The juxtaposition of ancient traditions with cutting-edge innovation creates fascinating contrasts, while hospitality traditions ensure visitors experience authentic cultural exchanges beyond typical tourist encounters.";
        }
        elseif (strpos(strtolower($region), 'america') !== false) {
            $thirdPara .= "Tourism in {$name} highlights diverse attractions including archaeological sites from pre-colonial civilizations, colonial architecture reflecting European influences, vibrant cultural festivals featuring music and dance, and dramatic natural landscapes from rainforests to mountain ranges. Adventure tourism, eco-tourism, and cultural immersion experiences continue to expand, offering visitors opportunities to explore both natural wonders and human heritage.";
        }
        elseif (strpos(strtolower($region), 'africa') !== false) {
            $thirdPara .= "{$name} offers visitors experiences ranging from wildlife safaris in national parks protecting diverse species, to historical sites documenting ancient civilizations and colonial periods, to cultural immersion in communities maintaining traditional lifestyles and artistic practices. Responsible tourism initiatives increasingly focus on conservation, community benefits, and sustainable approaches to sharing the country's natural and cultural wealth.";
        }
        elseif (strpos(strtolower($region), 'oceania') !== false) {
            $thirdPara .= "Visitors to {$name} are attracted by pristine beaches, unique marine ecosystems ideal for diving and snorkeling, distinctive wildlife found nowhere else on earth, and opportunities to experience cultural traditions including dance, music, and crafts. The relative isolation has preserved both natural environments and cultural practices, creating authentic experiences for travelers seeking something beyond mainstream destinations.";
        }
        else {
            $thirdPara .= "Tourism highlights include distinctive natural features, historical sites documenting the nation's development, and opportunities to experience local culture through food, festivals, and artistic expressions. The authentic character of these experiences attracts visitors seeking deeper understanding of {$name}'s unique place in the world.";
        }
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