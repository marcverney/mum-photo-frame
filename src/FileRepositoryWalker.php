<?php
declare(strict_types=1);

namespace App;

interface FileRepositoryWalker
{
    public function walk(): \Generator;
}
