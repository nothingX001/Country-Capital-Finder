<?php
$the_countries = [
    "united states",
    "united kingdom",
    "netherlands",
    "philippines",
    "bahamas",
    "gambia",
    "czech republic",
    "united arab emirates",
    "central african republic",
    "republic of the congo",
    "democratic republic of the congo",
    "maldives",
    "marshall islands",
    "seychelles",
    "solomon islands",
    "comoros"
];

// Normalize function to handle "the" countries
function normalizeInput($input) {
    global $the_countries;
    $input = strtolower(trim($input));
    $input = preg_replace('/^the\s+/i', '', $input); // Remove "the" if present
    return in_array($input, array_map('strtolower', $the_countries)) ? "the $input" : $input;
}
