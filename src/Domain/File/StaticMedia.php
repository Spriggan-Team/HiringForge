<?php

namespace App\Domain\File;

/**
 * This is a DTO only used for define static media schema
 */
class StaticMedia
{
    public function __construct(
        public string $name,
        public float  $size,
        public string $mime,
    ){}
}