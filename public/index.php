<?php
declare(strict_types=1);

namespace App;

use Aws\S3\S3Client;

require '../vendor/autoload.php';

const CONF_FILE_PATH = 'conf.php';

$conf = file_exists(CONF_FILE_PATH)
    ? require CONF_FILE_PATH
    : [
        'baseUrl' => getenv('BASE_URL'),
        'formatOutput' => getenv('FORMAT_OUTPUT'),
        'rssTitle' => getenv('RSS_TITLE'),
        'rssLink' => getenv('RSS_LINK'),
        'rssDescription' =>  getenv('RSS_DESCRIPTION'),
        'dir' => getenv('DIR'),
        's3BucketName' => getenv('S3_BUCKET_NAME'),
        's3BucketRegion' => getenv('S3_BUCKET_REGION')
    ];

$password = getenv('PASSWORD');
$dir = (string) $conf['dir'];
$baseUrl = (string) $conf['baseUrl'];
$s3BucketName = (string) $conf['s3BucketName'];
$s3BucketRegion = (string) $conf['s3BucketRegion'];
$formatOutput = (string) $conf['formatOutput'];
$rssTitle = $conf['rssTitle'] ?? 'Pictures';
$rssLink = $conf['rssLink'] ?? 'https://www.example.com';
$rssDescription = $conf['rssDescription'] ?? 'Pictures';
$startIndex = $_GET['start'] ?? 1;
$ssl = getenv('SSL');
$scheme = $ssl ? 'https' : 'http';

// Authorization
if (!isset($_GET['password']) || $_GET['password'] !== $password) {
    die('Invalid password');
}

// Scheme

// Walker instantiation
$walker = null;

if ($dir) {
    require '../src/DirectoryWalker.php';
    $walker = new DirectoryWalker($dir, $baseUrl);
} elseif ($s3BucketName) {
    require '../src/S3BucketWalker.php';

    $s3Client = new S3Client([
        'version' => 'latest',
        'region' => $s3BucketRegion
    ]);

    $walker = new S3BucketWalker($s3Client, $s3BucketName);
}

if ($walker === null) {
    die('Could not instantiate a walker. Please review configuration.');
}

// XML declaration
$doc = new \DOMDocument('1.0', 'utf-8');

// Items
$imagesFound = 0;
$items = $doc->createDocumentFragment();
foreach ($walker->walk() as $url) {
    $imagesFound++;

    $item = $doc->createElement('item');

    $group = $doc->createElement('media:group');
    $item->appendChild($group);

    $content = $doc->createElement('media:content');
    $content->setAttribute('medium', 'image');
    $content->setAttribute(
        'url',
        preg_replace('/^https?/', $scheme, $url)
    );
    $content->setAttribute('type', 'image/jpeg');
    $group->appendChild($content);

    $items->appendChild($item);
}

// RSS
$rss = $doc->createElement('rss');
$rss->setAttribute('version', '2.0');
$rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:media', 'http://search.yahoo.com/mrss/');
$rss->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:openSearch', 'http://a9.com/-/spec/opensearchrss/1.0/');
$doc->appendChild($rss);

// Channel
$channel = $doc->createElement('channel');
$rss->appendChild($channel);
$channel->appendChild($doc->createElement('title', $rssTitle));
$channel->appendChild($doc->createElement('link', $rssLink));
$channel->appendChild($doc->createElement('description', $rssDescription));
$channel->appendChild($doc->createElement('openSearch:totalResults', (string) $imagesFound));
$channel->appendChild($doc->createElement('openSearch:startIndex', (string) $startIndex));

// Items
$channel->appendChild($items);

// Output
header('Content-type: application/xml');
$doc->formatOutput = $formatOutput;
echo $doc->saveXML();
