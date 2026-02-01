<?php

namespace App\Domain\File;

use App\Domain\File\StaticMedia;
use App\Domain\File\TimedMedia;

final class FileUploadResult
{
    public function __construct(
        /** @var StaticMedia|TimedMedia  */
        public readonly array $stored = [],

        /** @var  array<string> */
        public readonly array $failed = [],
    ) {}
}
