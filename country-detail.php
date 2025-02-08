<?php
// country-detail.php

include 'config.php';

$country_id = $_GET['id'] ?? null;
if (!$country_id) {
    die("Invalid country ID.");
}

// 1) Fetch the main country record
$stmt = $conn->prepare("
    SELECT country_name, flag_emoji, language,
           entity_type, disclaimer, parent_id,
           flag_image_url
    FROM countries
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$country_id]);
$country = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$country) {
    die("Country not found.");
}

// 2) Fetch all capitals
$stmt_capitals = $conn->prepare("
    SELECT capital_name, capital_type, latitude, longitude
    FROM capitals
    WHERE country_id = ?
");
$stmt_capitals->execute([$country_id]);
$capitals = $stmt_capitals->fetchAll(PDO::FETCH_ASSOC);

// 3) Fetch child territories
$stmt_child_terr = $conn->prepare("
    SELECT id, country_name, flag_emoji, disclaimer
    FROM countries
    WHERE parent_id = ?
      AND entity_type = 'territory'
    ORDER BY country_name ASC
");
$stmt_child_terr->execute([$country_id]);
$child_territories = $stmt_child_terr->fetchAll(PDO::FETCH_ASSOC);

// 4) Fetch child de facto states
$stmt_child_defacto = $conn->prepare("
    SELECT id, country_name, flag_emoji, disclaimer
    FROM countries
    WHERE parent_id = ?
      AND entity_type = 'de_facto_state'
    ORDER BY country_name ASC
");
$stmt_child_defacto->execute([$country_id]);
$child_de_factos = $stmt_child_defacto->fetchAll(PDO::FETCH_ASSOC);

// 5) If this is a territory or de_facto_state => fetch parent info
$parentInfo = null;
if (!empty($country['parent_id'])) {
    $stmt_parent = $conn->prepare("
        SELECT id, country_name
        FROM countries
        WHERE id = ?
    ");
    $stmt_parent->execute([$country['parent_id']]);
    $parentInfo = $stmt_parent->fetch(PDO::FETCH_ASSOC);
}

// 6) Fetch alternate names, but only display if not empty
$stmt_alt = $conn->prepare("
    SELECT alternate_name
    FROM country_alternate_names
    WHERE country_id = ?
    ORDER BY alternate_name
");
$stmt_alt->execute([$country_id]);
$alts = $stmt_alt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($country['country_name']); ?> Details</title>
    <link rel="stylesheet" href="styles.css"> <!-- Only the single stylesheet -->
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Use .page-content + .country-detail for unique styling, keep the existing ID if needed -->
    <section class="page-content country-detail" id="country-detail-card">
        <div class="card-header">
            <h1>
                <?php echo htmlspecialchars($country['country_name']); ?>
                <?php if (!empty($country['flag_emoji'])) {
                    echo ' ' . htmlspecialchars($country['flag_emoji']);
                } ?>
            </h1>
        </div>
        <div class="card-content">
            <div class="country-info">
                <?php
                // Show capitals
                if ($capitals) {
                    $capList = array_map(function($cap) {
                        $cName = htmlspecialchars($cap['capital_name'] ?? 'N/A');
                        $cType = htmlspecialchars($cap['capital_type'] ?? '');
                        return $cType ? "$cName ($cType)" : $cName;
                    }, $capitals);
                    $capital_label = (count($capitals) > 1) ? 'Capitals' : 'Capital';
                    $capital_list  = implode(' / ', $capList);
                } else {
                    $capital_label = 'Capital';
                    $capital_list  = 'N/A';
                }
                ?>
                <p><strong><?php echo $capital_label; ?>:</strong> <?php echo $capital_list; ?></p>

                <?php if (!empty($country['flag_image_url'])): ?>
                    <p><strong>Flag Image:</strong><br>
                        <img src="<?php echo htmlspecialchars($country['flag_image_url']); ?>"
                             alt="Flag of <?php echo htmlspecialchars($country['country_name']); ?>"
                             style="max-width:150px;">
                    </p>
                <?php endif; ?>

                <p><strong>Languages:</strong> <?php echo htmlspecialchars($country['language'] ?? 'N/A'); ?></p>

                <!-- Only show "Also Referred As:" if we actually have alternate names -->
                <?php if (!empty($alts)): ?>
                    <?php $altString = implode(', ', $alts); ?>
                    <p><strong>Also Referred As:</strong> <?php echo htmlspecialchars($altString); ?></p>
                <?php endif; ?>

                <!-- If territory => display "Territory of" + disclaimers -->
                <?php if ($country['entity_type'] === 'territory' && $parentInfo): ?>
                    <p>
                        <strong>Territory of:</strong>
                        <a href="country-detail.php?id=<?php echo htmlspecialchars($parentInfo['id']); ?>">
                            <?php echo htmlspecialchars($parentInfo['country_name']); ?>
                        </a>
                    </p>
                    <?php if (!empty($country['disclaimer'])): ?>
                        <p><em><?php echo nl2br(htmlspecialchars($country['disclaimer'])); ?></em></p>
                    <?php endif; ?>

                <!-- If de_facto_state => display "Claimed by" + disclaimers -->
                <?php elseif ($country['entity_type'] === 'de_facto_state' && $parentInfo): ?>
                    <p>
                        <strong>Claimed by:</strong>
                        <a href="country-detail.php?id=<?php echo htmlspecialchars($parentInfo['id']); ?>">
                            <?php echo htmlspecialchars($parentInfo['country_name']); ?>
                        </a>
                    </p>
                    <?php if (!empty($country['disclaimer'])): ?>
                        <p><em><?php echo nl2br(htmlspecialchars($country['disclaimer'])); ?></em></p>
                    <?php endif; ?>

                <!-- Otherwise (member_state, observer_state, or no parent) => disclaimers as usual -->
                <?php else: ?>
                    <?php if (!empty($country['disclaimer'])): ?>
                        <p><em><?php echo nl2br(htmlspecialchars($country['disclaimer'])); ?></em></p>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- If this is a main country => show child territories & de facto states -->
                <?php if (in_array($country['entity_type'], ['member_state','observer_state'])): ?>
                    <?php if (!empty($child_territories)): ?>
                        <h3>Territories Administered by <?php echo htmlspecialchars($country['country_name']); ?></h3>
                        <ul>
                            <?php foreach ($child_territories as $ct): ?>
                                <li>
                                    <a href="country-detail.php?id=<?php echo htmlspecialchars($ct['id']); ?>">
                                        <?php echo htmlspecialchars($ct['country_name']); ?>
                                        <?php if (!empty($ct['flag_emoji'])) {
                                            echo ' ' . htmlspecialchars($ct['flag_emoji']);
                                        } ?>
                                    </a>
                                    <?php if (!empty($ct['disclaimer'])): ?>
                                        <br><em><?php echo htmlspecialchars($ct['disclaimer']); ?></em>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (!empty($child_de_factos)): ?>
                        <h3>De Facto States Claimed by <?php echo htmlspecialchars($country['country_name']); ?></h3>
                        <ul>
                            <?php foreach ($child_de_factos as $cdf): ?>
                                <li>
                                    <a href="country-detail.php?id=<?php echo htmlspecialchars($cdf['id']); ?>">
                                        <?php echo htmlspecialchars($cdf['country_name']); ?>
                                        <?php if (!empty($cdf['flag_emoji'])) {
                                            echo ' ' . htmlspecialchars($cdf['flag_emoji']);
                                        } ?>
                                    </a>
                                    <?php if (!empty($cdf['disclaimer'])): ?>
                                        <br><em><?php echo htmlspecialchars($cdf['disclaimer']); ?></em>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </div> <!-- .country-info -->
        </div> <!-- .card-content -->
    </section> <!-- .page-content.country-detail -->
</body>
</html>
