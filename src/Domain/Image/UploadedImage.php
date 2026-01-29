<?php

namespace App\Domain\Image;

class UploadedImage
{
    public function __construct(
        public int $id,
        public string $name,
        public float  $size,
        public string $mime,
    ){}
}