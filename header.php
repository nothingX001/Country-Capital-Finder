<?php
// Get the current protocol
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
// Get the host
$host = $_SERVER['HTTP_HOST'];
// Construct the base URL
$baseUrl = $protocol . $host . '/';
?>
<meta charset="UTF-8">
<base href="<?php echo htmlspecialchars($baseUrl); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<link rel="icon" type="image/jpeg" href="images/explore-capitals-logo.jpg">

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-94SRL3PBNE"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-94SRL3PBNE');
</script>

<!-- Cookiebot -->
<script id="Cookiebot" src="https://consent.cookiebot.com/uc.js" data-cbid="c7233634-6349-4f6d-8f04-54d9768b27b0" async></script> 