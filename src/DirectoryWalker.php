<?php
declare(strict_types=1);

namespace App;

class DirectoryWalker implements FileRepositoryWalker
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $baseUrl;

    public function __construct(string $path, string $baseUrl)
    {
        $this->path = $path;
        $this->baseUrl = $baseUrl;
    }

    public function walk(): \Generator
    {
        $dir = opendir($this->path);
        if (!$dir) {
            throw new \RuntimeException("Cannot open base dir {$this->path}");
        }

        do {
            $entry = readdir($dir);

            if ($entry !== false) {
                yield $this->baseUrl . $entry;
            }
        } while ($entry !== false);

        closedir($dir);
    }
}
