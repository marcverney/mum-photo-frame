<?php
declare(strict_types=1);

namespace App;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;

class S3BucketWalker implements FileRepositoryWalker
{
    /**
     * @var S3Client
     */
    private $client;

    /**
     * @var string
     */
    private $bucketName;

    public function __construct(S3Client $s3Client, string $bucketName)
    {
        $this->client = $s3Client;
        $this->bucketName = $bucketName;
    }

    public function walk(): \Generator
    {
        try {
            $pages = $this->client->getPaginator(
                'ListObjectsV2',
                ['Bucket' => $this->bucketName]
            );

            foreach ($pages as $page) {
                foreach ($page->get('Contents') as $item) {
                    yield $this->createPresignedUrl($item['Key']);
                }
            }
        } catch (AwsException $e) {
            die(
                $e->getAwsRequestId() . PHP_EOL .
                $e->getAwsErrorType() . PHP_EOL .
                $e->getAwsErrorCode() . PHP_EOL .
                $e->getMessage()
            );
        }
    }

    private function createPresignedUrl(string $objectKey): string
    {
        $cmd = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucketName,
            'Key' => $objectKey
        ]);

        $request = $this->client->createPresignedRequest(
            $cmd,
            '+1 day'
        );

        return (string) $request->getUri();
    }
}
