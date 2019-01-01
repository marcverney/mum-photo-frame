<?php

return [
    // Should the RSS output be nicely formatted with indentation and extra space?
    'FORMAT_OUTPUT' => true,

    // Title for the RSS feed
    'RSS_TITLE' => 'My pictures',

    // The URL to the HTML website corresponding to the feed
    'RSS_LINK' => 'https://www.example.com',

    // Phrase or sentence describing the feed
    'RSS_DESCRIPTION' => 'Pictures for my photo frame',

    // Password that must be provided
    'PASSWORD' => 'secret123',

    // AWS credentials
    'AWS_ACCESS_KEY_ID' => '******',
    'AWS_SECRET_ACCESS_KEY' => '******',

    // -----------------------
    // Local repository config
    // -----------------------
    // Local directory where the images are stored (if any). Trailing "/" required.
    'DIR' => '/home/marc/Images/',
    // Base URL for all images. Trailing "/" required.
    'BASE_URL' => 'https://www.example.com/img/',

    // --------------------
    // S3 repository config
    // --------------------
    // Amazon Web Services S3
    'S3_BUCKET_NAME' => 'my-bucket',
    'S3_BUCKET_REGION' => 'eu-west-1'
];
