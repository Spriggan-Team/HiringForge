<?php

namespace App\Domain\File;

class StaticMedia
{
    public function __construct(
        public string $name,
        public float  $size,
        public string $mime,
    ){}
}