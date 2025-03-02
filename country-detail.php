<?php
// country-detail.php

include 'config.php';

// Get the country ID from the query string
$country_id = $_GET['id'] ?? null;
if (!$country_id) {
    die("Invalid country ID.");
}

try {
    // 1) Fetch the country row from the countries table.
    //    Cast coordinates to text so they appear exactly as stored.
    $stmt = $conn->prepare('
        SELECT
            "Country Name" AS country_name,
            "Flag Emoji"   AS flag_emoji,
            "Flag"         AS flag_url,
            "Entity Type"  AS entity_type,
            "Coordinates (Latitude)"::text  AS lat,
            "Coordinates (Longitude)"::text AS lon,
            "Languages"    AS languages,
            "Currency"     AS currency,
            "Region"       AS region,
            "Subregion"    AS subregion,
            "Population"   AS population,
            "Area (km2)"   AS area_km2,
            "Calling Code" AS calling_code,
            "Internet TLD" AS internet_tld
        FROM countries
        WHERE id = ?
        LIMIT 1
    ');
    $stmt->execute([$country_id]);
    $country = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$country) {
        die("Country not found.");
    }

    // 2) Fetch capitals exclusively from the capitals table.
    $stmt_cap = $conn->prepare('
        SELECT capital_name, capital_type
        FROM capitals
        WHERE country_id = ?
    ');
    $stmt_cap->execute([$country_id]);
    $capitals = $stmt_cap->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching country details: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($country['country_name'] ?? 'Country Detail'); ?> - ExploreCapitals</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Additional styles for the country detail page */
    .country-detail-header {
      margin-bottom: 30px;
    }
    .country-detail-header h1 {
      font-size: 2.5rem;
      margin-bottom: 10px;
    }
    .country-detail-entity {
      font-size: 1.2rem;
      color: #666;
      margin-bottom: 20px;
    }
    .country-detail-attributes {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      max-width: 500px;
      margin: 0 auto 30px;
      text-align: left;
    }
    .country-detail-attributes .attribute {
      width: 48%;
      margin-bottom: 15px;
      font-size: 1.1rem;
    }
    .country-detail-attributes .attribute strong {
      display: block;
      margin-bottom: 5px;
      color: #333;
    }
    .flag-image {
      text-align: center;
      margin-top: 30px;
    }
    .flag-image img {
      max-width: 300px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
  </style>
</head>
<body>
  <?php include 'navbar.php'; ?>

  <section class="page-content country-detail">
    <!-- Header Section: Country Name and Flag Emoji -->
    <div class="country-detail-header">
      <h1>
        <?php echo htmlspecialchars($country['country_name']); ?>
        <?php if (!empty($country['flag_emoji'])): ?>
          <?php echo ' ' . htmlspecialchars($country['flag_emoji']); ?>
        <?php endif; ?>
      </h1>
      <?php if (!empty($country['entity_type'])): ?>
        <div class="country-detail-entity"><?php echo htmlspecialchars($country['entity_type']); ?></div>
      <?php endif; ?>
    </div>

    <!-- Attributes Section -->
    <div class="country-detail-attributes">
      <?php if (!empty($capitals)): ?>
        <div class="attribute">
          <strong>Capitals:</strong>
          <ul style="list-style: none; padding: 0; margin: 0;">
            <?php foreach ($capitals as $cap): ?>
              <li><?php echo htmlspecialchars($cap['capital_name']); ?><?php if (!empty($cap['capital_type'])): ?> (<?php echo htmlspecialchars($cap['capital_type']); ?>)<?php endif; ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <?php if (!empty($country['lat']) && !empty($country['lon'])): ?>
        <div class="attribute">
          <strong>Coordinates:</strong>
          <?php echo htmlspecialchars($country['lat']) . ', ' . htmlspecialchars($country['lon']); ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($country['languages'])): ?>
        <div class="attribute">
          <strong>Languages:</strong>
          <?php echo htmlspecialchars($country['languages']); ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($country['currency'])): ?>
        <div class="attribute">
          <strong>Currency:</strong>
          <?php echo htmlspecialchars($country['currency']); ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($country['region'])): ?>
        <div class="attribute">
          <strong>Region:</strong>
          <?php echo htmlspecialchars($country['region']); ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($country['subregion'])): ?>
        <div class="attribute">
          <strong>Subregion:</strong>
          <?php echo htmlspecialchars($country['subregion']); ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($country['population'])): ?>
        <div class="attribute">
          <strong>Population:</strong>
          <?php echo htmlspecialchars($country['population']); ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($country['area_km2'])): ?>
        <div class="attribute">
          <strong>Area (km²):</strong>
          <?php echo htmlspecialchars($country['area_km2']); ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($country['calling_code'])): ?>
        <div class="attribute">
          <strong>Calling Code:</strong>
          <?php echo htmlspecialchars($country['calling_code']); ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($country['internet_tld'])): ?>
        <div class="attribute">
          <strong>Internet TLD:</strong>
          <?php echo htmlspecialchars($country['internet_tld']); ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Flag Image -->
    <?php if (!empty($country['flag_url'])): ?>
      <div class="flag-image">
        <img src="<?php echo htmlspecialchars($country['flag_url']); ?>"
             alt="Flag of <?php echo htmlspecialchars($country['country_name'] ?? ''); ?>">
      </div>
    <?php endif; ?>
  </section>
</body>
</html>
