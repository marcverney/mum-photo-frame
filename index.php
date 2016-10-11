<?php
// Directory where the images are stored (trailing "/" required)
const BASE_DIR = '/home/marc/Images/';
// Base URL for all images (trailing "/" required)
const BASE_URL = 'https://www.example.com/img/';
// Should the output be nicely formatted with indentation and extra space?
const FORMAT_OUTPUT = true;
// Title for the RSS feed
const RSS_TITLE = 'Mum\'s Pictures';
// The URL to the HTML website corresponding to the feed
const RSS_LINK = 'https://www.example.com';
// Phrase or sentence describing the feed
const RSS_DESCRIPTION = 'Pictures for Mum\'s PhotoFrame';

// Config validation
if (!is_dir(BASE_DIR)) {
    die('The provided base directory does not exist');
}
$dirHandle = opendir(BASE_DIR);
if (!$dirHandle) {
    die('Cannot open the base directory');
}

// Root
$doc = new DOMDocument('1.0', 'utf-8');

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

// Items
$count = 0;
while (($filename = readdir($dirHandle)) !== false) {
    $imageInfo = getimagesize(BASE_DIR . $filename);
    if (!$imageInfo) {
        continue;
    }

    $count++;

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

    $channel->appendChild($item);
}
closedir($dirHandle);

// OpenSearch pagination
$channel->appendChild($doc->createElement('openSearch:totalResults', $count));
$channel->appendChild($doc->createElement('openSearch:startIndex', 1));

// Output
header('Content-type: application/xml');
$doc->formatOutput = FORMAT_OUTPUT;
echo $doc->saveXML();
