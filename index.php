<?php

require 'conf.php';

// Config validation
$startIndex = isset($_GET['start']) && $_GET['start'] ? $_GET['start'] : 1;
if (!is_dir(BASE_DIR)) {
    die('The provided base directory does not exist');
}
$dirHandle = opendir(BASE_DIR);
if (!$dirHandle) {
    die('Cannot open the base directory');
}

// XML declaration
$doc = new DOMDocument('1.0', 'utf-8');

// Items
$imagesFound = 0;
$items = $doc->createDocumentFragment();
while (($filename = readdir($dirHandle)) !== false) {
    // Make sure $filename is an image file
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, BASE_DIR . $filename);
    finfo_close($finfo);
    if (strpos($mime, 'image') !== 0) {
        continue;
    }
    $imagesFound++;

    // Discard all images before $startIndex
    if ($imagesFound < $startIndex) {
        continue;
    }

    // Get image dimensions
    $imageInfo = getimagesize(BASE_DIR . $filename);

    $item = $doc->createElement('item');

    $group = $doc->createElement('media:group');
    $item->appendChild($group);

    $content = $doc->createElement('media:content');
    $content->setAttribute('medium', 'image');
    $content->setAttribute('url',    BASE_URL . $filename);
    $content->setAttribute('width',  $imageInfo[0]);
    $content->setAttribute('height', $imageInfo[1]);
    $content->setAttribute('type',   $imageInfo['mime']);
    $group->appendChild($content);

    $items->appendChild($item);
}
closedir($dirHandle);


// RSS
$rss = $doc->createElement('rss');
$rss->setAttribute('version', '2.0');
$rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:media', 'http://search.yahoo.com/mrss/');
$rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:openSearch', 'http://a9.com/-/spec/opensearchrss/1.0/');
$doc->appendChild($rss);

// Channel
$channel = $doc->createElement('channel');
$rss->appendChild($channel);
$channel->appendChild($doc->createElement('title', RSS_TITLE));
$channel->appendChild($doc->createElement('link', RSS_LINK));
$channel->appendChild($doc->createElement('description', RSS_DESCRIPTION));
$channel->appendChild($doc->createElement('openSearch:totalResults', $imagesFound));
$channel->appendChild($doc->createElement('openSearch:startIndex', $startIndex));

// Items
$itemCount = $imagesFound - $startIndex + 1;
if ($itemCount) {
    $channel->appendChild($items);
}

// Output
header('Content-type: application/xml');
$doc->formatOutput = FORMAT_OUTPUT;
echo $doc->saveXML();
