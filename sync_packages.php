<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

function updatePackageFromWp($package_id) {
    global $mysqli;

    /* ---------------------------------
       GET PACKAGE URL
    --------------------------------- */
    $stmt = $mysqli->prepare("SELECT wordpress_url FROM packages WHERE id=?");
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $stmt->bind_result($url);
    $stmt->fetch();
    $stmt->close();

    if (!$url) {
        return false;
    }

    /* ---------------------------------
       BUILD WP REST ENDPOINT
    --------------------------------- */
    $slug = trim(parse_url($url, PHP_URL_PATH), '/');
    $api  = "https://www.indiavacations.com/wp-json/wp/v2/pages?slug=" . $slug;

    $json = @file_get_contents($api);
    $data = json_decode($json, true);

    if (empty($data[0])) {
        return false;
    }

    $page = $data[0];

    /* ---------------------------------
       BASIC FIELDS
    --------------------------------- */
    $name        = strip_tags($page['title']['rendered'] ?? '');
    $description = $page['content']['rendered'] ?? '';

    /* ---------------------------------
       DURATION PARSE
    --------------------------------- */
    $duration_days = 0;
    $duration_nights = 0;

    if (preg_match('/(\d+)\s*Nights?/i', $name, $m)) {
        $duration_nights = (int)$m[1];
    }

    if (preg_match('/(\d+)\s*Days?/i', $name, $m)) {
        $duration_days = (int)$m[1];
    }

    /* ---------------------------------
       FEATURED IMAGE
    --------------------------------- */
    $mediaArr = [];
    // if (!empty($page['yoast_head_json']['og_image'][0]['url'])) {
    //     $mediaArr[] = $page['yoast_head_json']['og_image'][0]['url'];
    // }
    if (!empty($page['featured_media'])) {

        $mediaApi = "https://www.indiavacations.com/wp-json/wp/v2/media/" . $page['featured_media'];
        $mediaJson = @file_get_contents($mediaApi);
        $mediaData = json_decode($mediaJson, true);

        if (!empty($mediaData['source_url'])) {
            $mediaArr[] = $mediaData['source_url'];
        }
    }    
    $media_json = json_encode($mediaArr);

    /* ---------------------------------
       CLEAN HTML USING DOM
    --------------------------------- */
    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $description);
    $xpath = new DOMXPath($dom);

    /* ---------------------------------
       HELPER: Extract List Items Cleanly
    --------------------------------- */
    function extractListItems($node, $xpath) {
        $items = [];
        $lis = $xpath->query('.//li', $node);
        foreach ($lis as $li) {
            $text = trim(preg_replace('/\s+/', ' ', $li->textContent));
            if ($text) {
                $items[] = $text;
            }
        }
        return $items;
    }

    /* ---------------------------------
       CLEAN DESCRIPTION (Overview)
    --------------------------------- */
    $overview = '';
    $overviewNode = $xpath->query("//h2[contains(., 'Overview')]")->item(0);

    if ($overviewNode) {
        $next = $overviewNode->parentNode->nextSibling;
        while ($next) {
            if ($next->nodeName === 'p') {
                $overview .= trim($next->textContent) . "\n\n";
            }
            if ($next->nodeName === 'h2') break;
            $next = $next->nextSibling;
        }
    }

    $description_clean = trim($overview);

    /* ---------------------------------
       HIGHLIGHTS
    --------------------------------- */
    $highlights = [];
    $highlightNode = $xpath->query("//h2[contains(., 'Highlights')]")->item(0);

    if ($highlightNode) {
        $ul = $xpath->query("following::ul[1]", $highlightNode)->item(0);
        if ($ul) {
            $highlights = extractListItems($ul, $xpath);
        }
    }

    /* ---------------------------------
       INCLUSIONS
    --------------------------------- */
    $inclusions = [];
    $incNode = $xpath->query("//h2[contains(., 'include')]")->item(0);

    if ($incNode) {
        $ul = $xpath->query("following::ul[1]", $incNode)->item(0);
        if ($ul) {
            $inclusions = extractListItems($ul, $xpath);
        }
    }

    /* ---------------------------------
       EXCLUSIONS
    --------------------------------- */
    $exclusions = [];
    $excNode = $xpath->query("//h2[contains(., 'not include') or contains(., 'exclude')]")->item(0);

    if ($excNode) {
        $ul = $xpath->query("following::ul[1]", $excNode)->item(0);
        if ($ul) {
            $exclusions = extractListItems($ul, $xpath);
        }
    }

    /* ---------------------------------
       ITINERARY (Clean Text)
    --------------------------------- */
    $itineraryArr = [];

    $dayNodes = $xpath->query("//h3[contains(., 'Day') or contains(., 'DAY')]");

    foreach ($dayNodes as $day) {

        $title = trim(preg_replace('/\s+/', ' ', $day->textContent));
        $content = '';

        $next = $day->nextSibling;

        while ($next && $next->nodeName !== 'h3') {
            if ($next->nodeName === 'p') {
                $content .= trim($next->textContent) . "\n";
            }
            $next = $next->nextSibling;
        }

        $itineraryArr[] = [
            'title' => $title,
            'content' => trim($content)
        ];
    }



    $itinerary_json = json_encode($itineraryArr, JSON_UNESCAPED_UNICODE);

    $totalDays = count($itineraryArr);
    if ($totalDays > 0) {
        $duration_days = $totalDays;
        $duration_nights = $totalDays - 1;
    }

    /* ---------------------------------
       PRINT EVERYTHING (DEBUG)
    --------------------------------- */
    echo "<pre>";

    echo "==== API URL ====\n";
    echo $api . "\n\n";

    echo "==== RAW JSON ====\n";
    // print_r($page);

    echo "\n\n==== EXTRACTED DATA ====\n";

    $finalData = [
        'name'            => $name,
        'duration_days'   => $duration_days,
        'duration_nights' => $duration_nights,
        'media'           => $mediaArr,
        'description'     => $description_clean,
        'highlights'      => $highlights,
        'inclusions'      => $inclusions,
        'exclusions'      => $exclusions,
        'itinerary'       => $itineraryArr
    ];

    print_r($finalData);

    echo "</pre>";

    exit; // STOP before DB update

    /* ---------------------------------
       UPDATE DATABASE
    --------------------------------- */
    // $stmt = $mysqli->prepare("
    //     UPDATE packages SET
    //         name=?,
    //         description=?,
    //         duration_days=?,
    //         duration_nights=?,
    //         highlights=?,
    //         inclusions=?,
    //         exclusions=?,
    //         itinerary=?,
    //         media=?,
    //         updated_at=NOW()
    //     WHERE id=?
    // ");

    // $stmt->bind_param(
    //     "ssiisssssi",
    //     $name,
    //     $description,
    //     $duration_days,
    //     $duration_nights,
    //     $highlights,
    //     $inclusions,
    //     $exclusions,
    //     $itinerary_json,
    //     $media_json,
    //     $package_id
    // );

    // $success = $stmt->execute();
    // $stmt->close();

    // return $success;
}

function updatePackageFromUrl($package_id) {
    global $mysqli;

    // 1️⃣ Get URL from database
    $stmt = $mysqli->prepare("SELECT wordpress_url FROM packages WHERE id = ?");
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $stmt->bind_result($url);
    $stmt->fetch();
    $stmt->close();

    if (!$url) {
        return ['success' => false, 'message' => 'URL not found'];
    }

    // 2️⃣ Fetch HTML using cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html) {
        return ['success' => false, 'message' => 'Unable to fetch page'];
    }

    // 3️⃣ Load DOM
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // 4️⃣ Extract Title
    $titleNode = $xpath->query("//h1")->item(0);
    $title = $titleNode ? trim($titleNode->nodeValue) : '';

    // 5️⃣ Extract Main Description
    $descNode = $xpath->query("//div[contains(@class,'entry-content')]")->item(0);
    $description = $descNode ? trim($dom->saveHTML($descNode)) : '';

    // 6️⃣ Extract Duration (from title like 10 Nights 11 Days)
    preg_match('/(\d+\s*Nights?\s*\d*\s*Days?)/i', $title, $matches);
    $duration = $matches[0] ?? '';

    // 7️⃣ Extract Featured Image
    $imgNode = $xpath->query("//meta[@property='og:image']")->item(0);
    $image = $imgNode ? $imgNode->getAttribute("content") : '';

    $finalData = [
        'name'            => $title,
        // 'duration_days'   => $duration_days,
        'duration' => $duration,
        'media'           => $image,
        // 'highlights'      => $highlights,
        // 'inclusions'      => $inclusions,
        // 'exclusions'      => $exclusions,
        // 'itinerary'       => $itineraryArr,
        'description_length' => $description
    ];

    print_r($finalData);

    // 8️⃣ Update Database
    // $stmt = $mysqli->prepare("
    //     UPDATE packages 
    //     SET name = ?, 
    //         description = ?, 
    //         duration = ?, 
    //         image = ?
    //     WHERE id = ?
    // ");
    // $stmt->bind_param("ssssi", $title, $description, $duration, $image, $package_id);
    // $stmt->execute();
    // $stmt->close();

    return ['success' => true];
}


// json
updatePackageFromWp(100);

// curl
// updatePackageFromUrl(100);

?>