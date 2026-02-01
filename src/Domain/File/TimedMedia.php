<?php

namespace App\Domain\File;

class TimedMedia
{
    public function __construct(
        public int $id,
        public string $name,
        public float  $size,
        public string $mime,
    ){}
}