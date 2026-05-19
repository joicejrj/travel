<?php


function fetchWP($url) {
    $response = @file_get_contents($url);
    if (!$response) return null;
    $data = json_decode($response, true);
    return $data ?: $response;
}
function getWPCSS($url) {
    $response = @file_get_contents($url);
    if (!$response) return [];
    // Extract JSON part (important)
    $jsonStart = strpos($response, '{"css_files"');
    if ($jsonStart !== false) {
        $json = substr($response, $jsonStart);
        $data = json_decode($json, true);
        return $data['css_files'] ?? [];
    }
    return [];
}
function getWPJS($url) {
    $response = @file_get_contents($url);
    if (!$response) return [];
    // Find JSON part
    $jsonStart = strpos($response, '{"js_files"');
    if ($jsonStart !== false) {
        $json = substr($response, $jsonStart);
        $data = json_decode($json, true);
        return $data['js_files'] ?? [];
    }
    return [];
}
$wpHeader = fetchWP("https://www.indiavacations.com/wp-json/jkit/v1/header");
$wpFooter = fetchWP("https://www.indiavacations.com/wp-json/jkit/v1/footer");
// $wpCSS    = fetchWP("https://www.indiavacations.com/wp-json/jkit/v1/css");
$wpCSSFiles = getWPCSS("https://www.indiavacations.com/wp-json/jkit/v1/css");
$wpJSFiles = getWPJS("https://www.indiavacations.com/wp-json/jkit/v1/js");

?>

<?php foreach ($wpCSSFiles as $css): ?>
<link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
<?php endforeach; ?>

<?= $wpHeader['html'] ?? '' ?>



<div style="margin-top: 10em;">
PAGE CONTENT
</div>



<?= $wpFooter['html'] ?? '' ?>

<?php foreach ($wpJSFiles as $js): ?>
<script src="<?= htmlspecialchars($js) ?>"></script>
<?php endforeach; ?>