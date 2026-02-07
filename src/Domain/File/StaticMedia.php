<?php

namespace App\Domain\File;

/**
 * This is a DTO only used for define static media schema
 */
class StaticMedia
{
    public function __construct(
        /** @var string $name must be a uniq name */
        public string $name,
        /** @var float $size represents the size of the file */
        public float  $size,
        /** @var string $mime here is the mime type */
        public string $mime,
    ){}

    public function mustBe(int $sizeLimitation, ?string $type = null)
    {
        //TODO: Implement
    }
}