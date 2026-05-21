<?php

namespace App\Domain\File;

use App\Domain\File\StaticMedia;
use App\Domain\File\TimedMedia;

/**
 * An Important class representing the result
 * of saving an image
 */
final class MediaUploadResult
{
    public function __construct(
        public string $originalName,
        public string $storedName ,         //represent the new name that has been generated for this this file,
    ) {}
}
